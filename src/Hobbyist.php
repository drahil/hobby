<?php

declare(strict_types=1);

namespace src;

use Predis\Client;
use ReflectionClass;
use src\Attributes\ExecuteConcurrently;
use src\Attributes\MaxAttempts;
use src\Contracts\Hobby;
use src\ValueObjects\AdvanceResult;
use src\ValueObjects\HobbyContext;

final class Hobbyist
{
    private bool $running = true;
    private readonly CooperativeExecutor $cooperativeExecutor;
    private readonly Container $container;

    public function __construct(
        private readonly Client $redis,
        ?CooperativeExecutor $cooperativeExecutor = null,
        ?Container $container = null,
    ) {
        $this->container = $container ?? new Container();
        $this->cooperativeExecutor = $cooperativeExecutor ?? new CooperativeExecutor($this->container);

        pcntl_signal(SIGTERM, fn() => $this->running = false);
        pcntl_signal(SIGINT,  fn() => $this->running = false);
    }

    public function run(string $queue = 'default'): void
    {
        while ($this->running) {
            $this->prepareWorkerLoop();

            if ($this->advanceConcurrentWork()) {
                continue;
            }

            $payload = $this->pullNextPayload($queue);

            if ($payload !== null) {
                $this->process($payload);
                continue;
            }

            $this->idle();
        }
    }

    private function process(string $payload): void
    {
        $data = json_decode($payload, associative: true);

        $class = $data['class'];
        $args = $data['args'];
        $attempts = $data['attempts'] + 1;
        $queue = $data['queue'];

        $hobby = new $class(...$args);

        $maxAttempts = $this->resolveMaxAttempts($hobby);
        $context = new HobbyContext(
            class: $class,
            args: $args,
            attempts: $attempts,
            maxAttempts: $maxAttempts,
            queue: $queue,
        );

        if ($this->runsInFiber($hobby)) {
            $this->cooperativeExecutor->schedule($hobby, $context);

            return;
        }

        $this->handleHobbyExecution(
            hobby: $hobby,
            context: $context,
        );
    }

    private function promoteDelayedJobs(): void
    {
        /**
         * get all the hobbies that are due for promotion (score <= current timestamp)
         */
        $payloads = $this->redis->zrangebyscore('queue:delayed', 0, time());

        foreach ($payloads as $payload) {
            $targetQueue = json_decode($payload, associative: true)['queue'];
            /**
             * push to the main queue first, then remove from the delayed set
             * this order ensures the job is never lost if something crashes in between
             */
            $this->redis->rpush("queue:{$targetQueue}", (array) $payload);
            $this->redis->zrem('queue:delayed', $payload);
        }
    }

    private function output(string $message): void
    {
        echo sprintf("[%s] %s\n", date('H:i:s'), $message);
    }

    private function prepareWorkerLoop(): void
    {
        pcntl_signal_dispatch();
        $this->promoteDelayedJobs();
    }

    private function advanceConcurrentWork(): bool
    {
        $advanceResult = $this->cooperativeExecutor->advance();
        $this->handleConcurrentAdvanceResult($advanceResult);

        return ! $advanceResult->isIdle();
    }

    private function pullNextPayload(string $queue): ?string
    {
        $item = $this->shouldBlockOnQueue()
            ? $this->redis->blpop(["queue:{$queue}"], 2)
            : $this->redis->lpop("queue:{$queue}");

        if ($item === null) {
            return null;
        }

        return is_array($item) ? $item[1] : $item;
    }

    private function shouldBlockOnQueue(): bool
    {
        return ! $this->cooperativeExecutor->hasScheduledTasks();
    }

    private function idle(): void
    {
        if (! $this->cooperativeExecutor->hasScheduledTasks()) {
            return;
        }

        usleep(10_000);
    }

    private function resolveMaxAttempts(object $hobby): int
    {
        $attributes = (new ReflectionClass($hobby))->getAttributes(MaxAttempts::class);

        return $attributes ? $attributes[0]->newInstance()->tries : 3;
    }

    private function runsInFiber(object $hobby): bool
    {
        return (new ReflectionClass($hobby))->getAttributes(ExecuteConcurrently::class) !== [];
    }

    private function handleConcurrentAdvanceResult(AdvanceResult $advanceResult): void
    {
        if ($advanceResult->task === null || ! $advanceResult->task->context instanceof HobbyContext) {
            return;
        }

        if ($advanceResult->isCompleted()) {
            $this->outputSuccess($advanceResult->task->context);

            return;
        }

        if ($advanceResult->isFailed() && $advanceResult->error !== null) {
            $this->handleFailure($advanceResult->task->context, $advanceResult->error);
        }
    }

    private function handleHobbyExecution(Hobby $hobby, HobbyContext $context): void
    {
        try {
            $this->container->call($hobby, 'handle');
            $this->outputSuccess($context);
        } catch (\Throwable $e) {
            $this->handleFailure($context, $e);
        }
    }

    private function outputSuccess(HobbyContext $context): void
    {
        $this->output("✓ {$context->class} succeeded (attempt {$context->attempts}/{$context->maxAttempts})");
    }

    private function handleFailure(HobbyContext $context, \Throwable $e): void
    {
        if ($context->attempts < $context->maxAttempts) {
            $this->redis->rpush("queue:{$context->queue}", (array) json_encode([
                'class' => $context->class,
                'args' => $context->args,
                'attempts' => $context->attempts,
                'queue' => $context->queue,
            ]));
            $this->output("↺ {$context->class} failed, retrying (attempt {$context->attempts}/{$context->maxAttempts}): {$e->getMessage()}");

            return;
        }

        $this->redis->rpush('queue:failed', (array) json_encode([
            'class' => $context->class,
            'args' => $context->args,
            'attempts' => $context->attempts,
            'queue' => $context->queue,
            'error' => $e->getMessage(),
        ]));
        $this->output("✗ {$context->class} failed permanently after {$context->attempts} attempts: {$e->getMessage()}");
    }
}
