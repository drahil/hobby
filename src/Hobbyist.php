<?php

declare(strict_types=1);

namespace src;

use Predis\Client;
use ReflectionClass;
use src\Attributes\MaxAttempts;

final class Hobbyist
{
    private bool $running = true;

    public function __construct(private readonly Client $redis)
    {
        pcntl_signal(SIGTERM, fn() => $this->running = false);
        pcntl_signal(SIGINT,  fn() => $this->running = false);
    }

    public function run(string $queue = 'default'): void
    {
        while ($this->running) {
            pcntl_signal_dispatch();

            $this->promoteDelayedJobs();

            /**
             * BLPOP blocks waiting to pop from the left (front) of the list,
             * returns when a job arrives or the 2 second timeout expires
             */
            $item = $this->redis->blpop(["queue:{$queue}"], 2);

            if ($item !== null) {
                $this->process($item[1]);
            }
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

        try {
            $hobby->handle();
            $this->output("✓ {$class} succeeded (attempt {$attempts}/{$maxAttempts})");
        } catch (\Throwable $e) {
            if ($attempts < $maxAttempts) {
                /**
                 * push the hobby to the right (end) of the queue
                 * this means that queue is FIFO
                 */
                $this->redis->rpush("queue:{$queue}", (array) json_encode([
                    'class' => $class,
                    'args' => $args,
                    'attempts' => $attempts,
                    'queue' => $queue,
                ]));
                $this->output("↺ {$class} failed, retrying (attempt {$attempts}/{$maxAttempts}): {$e->getMessage()}");
            } else {
                $this->redis->rpush('queue:failed', (array) json_encode([
                    'class' => $class,
                    'args' => $args,
                    'attempts' => $attempts,
                    'queue' => $queue,
                    'error' => $e->getMessage(),
                ]));
                $this->output("✗ {$class} failed permanently after {$attempts} attempts: {$e->getMessage()}");
            }
        }
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

    private function resolveMaxAttempts(object $hobby): int
    {
        $attributes = (new ReflectionClass($hobby))->getAttributes(MaxAttempts::class);

        return $attributes ? $attributes[0]->newInstance()->tries : 3;
    }
}
