<?php

declare(strict_types=1);

namespace src;

use Fiber;
use src\Contracts\AwaitResolver;
use src\Contracts\Hobby;
use src\ValueObjects\AdvanceResult;
use src\ValueObjects\Task;

final class CooperativeExecutor
{
    /** @var list<Task> */
    private array $tasks = [];

    public function __construct(
        private readonly ?AwaitResolver $awaitResolver = null,
    ) {}

    public function schedule(Hobby $hobby, mixed $context = null): void
    {
        $this->tasks[] = $this->makeTask($hobby, $context);
    }

    /** @return list<Task> */
    public function scheduledTasks(): array
    {
        return $this->tasks;
    }

    public function hasScheduledTasks(): bool
    {
        return $this->tasks !== [];
    }

    public function scheduleMany(array $hobbies): void
    {
        foreach ($hobbies as $hobby) {
            $this->schedule($hobby);
        }
    }

    public function advance(): AdvanceResult
    {
        $taskIndex = $this->nextRunnableTaskIndex();

        if ($taskIndex === null) {
            return AdvanceResult::idle();
        }

        $task = $this->tasks[$taskIndex];

        try {
            $signal = $task->started
                ? $task->fiber->resume($this->resolveResumeValue($task))
                : $task->fiber->start();
        } catch (\Throwable $exception) {
            $failedTask = $task;
            $this->removeTask($taskIndex);

            return AdvanceResult::failed($failedTask, $exception);
        }

        if ($task->fiber->isTerminated()) {
            $completedTask = $task;
            $this->removeTask($taskIndex);

            return AdvanceResult::completed($completedTask);
        }

        $this->tasks[$taskIndex] = new Task(
            hobby: $task->hobby,
            fiber: $task->fiber,
            started: true,
            runAt: $this->resolveRunAt($signal),
            signal: $signal,
            context: $task->context,
        );

        return AdvanceResult::progressed();
    }

    public function runUntilEmpty(): void
    {
        while ($this->tasks !== []) {
            if (! $this->advance()->isIdle()) {
                continue;
            }

            usleep($this->microsecondsUntilNextTask());
        }
    }

    private function makeTask(Hobby $hobby, mixed $context = null): Task
    {
        return new Task(
            hobby: $hobby,
            fiber: new Fiber(static fn() => $hobby->handle()),
            started: false,
            runAt: microtime(true),
            signal: null,
            context: $context,
        );
    }

    private function nextRunnableTaskIndex(): ?int
    {
        $now = microtime(true);

        foreach ($this->tasks as $index => $task) {
            if ($task->runAt <= $now) {
                return $index;
            }
        }

        return null;
    }

    private function resolveRunAt(mixed $signal): float
    {
        if (is_array($signal) && in_array($signal['type'] ?? null, ['sleep', 'await'], true)) {
            return (float) ($signal['until'] ?? microtime(true));
        }

        return microtime(true);
    }

    private function resolveResumeValue(Task $task): mixed
    {
        if (! is_array($task->signal) || ($task->signal['type'] ?? null) !== 'await') {
            return null;
        }

        if ($this->awaitResolver === null) {
            throw new \LogicException('Await signal received, but no await resolver is configured.');
        }

        return $this->awaitResolver->resolve($task->signal);
    }

    private function microsecondsUntilNextTask(): int
    {
        if ($this->tasks === []) {
            return 0;
        }

        $nextRunAt = min(array_map(
            static fn(Task $task): float => $task->runAt,
            $this->tasks,
        ));

        return max(0, (int) (($nextRunAt - microtime(true)) * 1_000_000));
    }

    private function removeTask(int $taskIndex): void
    {
        unset($this->tasks[$taskIndex]);
        $this->tasks = array_values($this->tasks);
    }
}
