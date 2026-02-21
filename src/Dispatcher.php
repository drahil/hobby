<?php

declare(strict_types=1);

namespace src;

use Predis\Client;
use ReflectionClass;
use src\Attributes\Delay;
use src\Attributes\OnQueue;
use src\Contracts\Hobby;

final readonly class Dispatcher
{
    public function __construct(private Client $redis)
    {}

    /**
     * @throws \ReflectionException
     */
    public function dispatch(Hobby $hobby): void
    {
        $queue = $this->resolveQueue($hobby);
        $delay = $this->resolveDelay($hobby);
        $payload = json_encode([
            'class' => $hobby::class,
            'args' => $this->extractArgs($hobby),
            'attempts' => 0,
            'queue' => $queue,
        ]);

        if ($delay > 0) {
            /**
             * adding a hobby in a sorted fashion, with a score
             * this score is the timestamp when the hobby should be promoted to the main queue and be available for processing
             */
            $this->redis->zadd('queue:delayed', [strval($payload) => time() + $delay]);
        } else {
            $this->redis->rpush("queue:{$queue}", (array) $payload);
        }
    }

    private function resolveDelay(object $hobby): int
    {
        $attributes = (new ReflectionClass($hobby))->getAttributes(Delay::class);

        return $attributes ? $attributes[0]->newInstance()->seconds : 0;
    }

    private function resolveQueue(object $hobby): string
    {
        $attributes = (new ReflectionClass($hobby))->getAttributes(OnQueue::class);

        return $attributes ? $attributes[0]->newInstance()->name : 'default';
    }

    /**
     * @throws \ReflectionException
     */
    private function extractArgs(object $hobby): array
    {
        $reflection = new ReflectionClass($hobby);
        $args = [];

        foreach ($reflection->getConstructor()?->getParameters() ?? [] as $param) {
            $property = $reflection->getProperty($param->getName());
            $args[] = $property->getValue($hobby);
        }

        return $args;
    }
}
