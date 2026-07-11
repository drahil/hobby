<?php

declare(strict_types=1);

namespace src;

use Exception;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;

class Container
{
    private array $bindings = [];
    private array $instances = [];

    public function bind(string $abstract, string|callable $concrete): void
    {
        $this->bindings[$abstract] = $concrete;
    }

    public function singleton(string $abstract, string|callable $concrete): void
    {
        $this->bindings[$abstract] = function (self $container) use ($abstract, $concrete): object {
            if (isset($this->instances[$abstract])) {
                return $this->instances[$abstract];
            }

            $resolved = is_string($concrete)
                ? $container->build($concrete)
                : $concrete($container);

            if (! is_object($resolved)) {
                throw new Exception("Singleton binding for {$abstract} did not resolve to an object.");
            }

            return $this->instances[$abstract] = $resolved;
        };
    }

    public function make(string $abstract): object
    {
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        $concrete = $this->bindings[$abstract] ?? $abstract;

        if (is_string($concrete)) {
            return $this->build($concrete);
        }

        if (is_callable($concrete)) {
            $resolved = $concrete($this);

            if (! is_object($resolved)) {
                throw new Exception("Binding for {$abstract} did not resolve to an object.");
            }

            return $resolved;
        }

        throw new Exception("Unable to resolve {$abstract}.");
    }

    private function build(string $concrete): object
    {
        if (! class_exists($concrete)) {
            throw new Exception("Class {$concrete} does not exist.");
        }

        $reflection = new ReflectionClass($concrete);

        if (! $reflection->isInstantiable()) {
            throw new Exception("Class {$concrete} is not instantiable.");
        }

        $constructor = $reflection->getConstructor();

        if ($constructor === null) {
            return new $concrete();
        }

        return $reflection->newInstanceArgs(
            $this->resolveParameters($constructor->getParameters(), $concrete),
        );
    }

    public function instance(string $abstract, object $instance): void
    {
        $this->instances[$abstract] = $instance;
    }

    public function call(object $object, string $method = 'handle'): mixed
    {
        $class = $object::class;

        if (! method_exists($object, $method)) {
            throw new Exception("Method {$class}::{$method} does not exist.");
        }

        $reflection = new ReflectionMethod($object, $method);

        if (! $reflection->isPublic()) {
            throw new Exception("Method {$class}::{$method} is not public.");
        }

        return $reflection->invokeArgs(
            $object,
            $this->resolveParameters($reflection->getParameters(), "{$class}::{$method}"),
        );
    }

    /**
     * @param \ReflectionParameter[] $parameters
     */
    private function resolveParameters(array $parameters, string $target): array
    {
        $dependencies = [];

        foreach ($parameters as $parameter) {
            $type = $parameter->getType();

            if (! $type instanceof ReflectionNamedType || $type->isBuiltin()) {
                if ($parameter->isDefaultValueAvailable()) {
                    $dependencies[] = $parameter->getDefaultValue();
                    continue;
                }

                throw new Exception("Unable to resolve parameter \${$parameter->getName()} for {$target}.");
            }

            $dependencies[] = $this->make($type->getName());
        }

        return $dependencies;
    }
}
