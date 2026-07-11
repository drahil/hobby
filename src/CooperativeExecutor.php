<?php

declare(strict_types=1);

namespace src;

use Fiber;
use src\Contracts\Awaitable;
use src\Contracts\Hobby;
use src\ValueObjects\AdvanceResult;
use src\ValueObjects\Task;

final class CooperativeExecutor
{
    private array $tasks = [];

    public function __construct(
        private readonly Container $container = new Container(),
    ) {}

    public function schedule(Hobby $hobby, mixed $context = null): void
    {
        $this->tasks[] = $this->makeTask($hobby, $context);
    }

    public function hasScheduledTasks(): bool
    {
        return $this->tasks !== [];
    }

    public function advance(): AdvanceResult
    {
        $taskIndex = $this->nextRunnableTaskIndex();

        if ($taskIndex === null) {
            return AdvanceResult::idle();
        }

        $task = $this->tasks[$taskIndex];

        try {
            if ($task->fiber->isStarted() && $this->shouldKeepWaiting($task)) {
                $this->tasks[$taskIndex] = $this->rescheduleWaitingTask($task);

                return AdvanceResult::progressed();
            }

            $waitingOn = $task->fiber->isStarted()
                ? $this->resumeTask($task)
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
            fiber: $task->fiber,
            runAt: $this->resolveRunAt($waitingOn),
            waitingOn: $waitingOn,
            context: $task->context,
        );

        return AdvanceResult::progressed();
    }

    private function makeTask(Hobby $hobby, mixed $context = null): Task
    {
        return new Task(
            fiber: new Fiber(fn() => $this->container->call($hobby, 'handle')),
            runAt: microtime(true),
            waitingOn: null,
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

    private function resolveRunAt(mixed $waitingOn): float
    {
        if (is_array($waitingOn) && ($waitingOn['awaitable'] ?? null) instanceof Awaitable) {
            $resumeAt = $waitingOn['awaitable']->nextCheckAt();
            $timeoutAt = $waitingOn['timeoutAt'] ?? null;

            return $timeoutAt === null ? $resumeAt : min($resumeAt, (float) $timeoutAt);
        }

        return microtime(true);
    }

    private function resolveAwaitResult(Task $task): mixed
    {
        if (! $this->isAwaiting($task)) {
            return null;
        }

        $awaitable = $this->awaitable($task);
        $now = microtime(true);

        if ($this->hasTimedOut($task, $now)) {
            throw new \RuntimeException('Awaitable timed out.');
        }

        return $awaitable->result();
    }

    private function resumeTask(Task $task): mixed
    {
        try {
            $result = $this->resolveAwaitResult($task);
        } catch (\Throwable $exception) {
            return $task->fiber->throw($exception);
        }

        return $task->fiber->resume($result);
    }

    private function shouldKeepWaiting(Task $task): bool
    {
        if (! $this->isAwaiting($task)) {
            return false;
        }

        $now = microtime(true);

        return ! $this->hasTimedOut($task, $now)
            && ! $this->awaitable($task)->ready();
    }

    private function rescheduleWaitingTask(Task $task): Task
    {
        return new Task(
            fiber: $task->fiber,
            runAt: $this->resolveRunAt($task->waitingOn),
            waitingOn: $task->waitingOn,
            context: $task->context,
        );
    }

    private function isAwaiting(Task $task): bool
    {
        return is_array($task->waitingOn)
            && ($task->waitingOn['awaitable'] ?? null) instanceof Awaitable;
    }

    private function awaitable(Task $task): Awaitable
    {
        $awaitable = $task->waitingOn['awaitable'] ?? null;

        if (! $awaitable instanceof Awaitable) {
            throw new \LogicException('Await signal received without an awaitable.');
        }

        return $awaitable;
    }

    private function hasTimedOut(Task $task, float $now): bool
    {
        if (! is_array($task->waitingOn)) {
            return false;
        }

        $timeoutAt = $task->waitingOn['timeoutAt'] ?? null;

        return $timeoutAt !== null && (float) $timeoutAt <= $now;
    }

    private function removeTask(int $taskIndex): void
    {
        unset($this->tasks[$taskIndex]);
        $this->tasks = array_values($this->tasks);
    }
}
