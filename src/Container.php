<?php

declare(strict_types=1);

namespace src;

use Exception;
use ReflectionClass;
use ReflectionNamedType;

class Container
{
    private array $bindings = [];
    
    public function bind(string $abstract, string|callable $concrete): void
    {
        $this->bindings[$abstract] = $concrete;
    }

    public function singleton()
    {
        //
    }

    public function make(string $abstract): object
    {
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

        $dependencies = [];

        foreach ($constructor->getParameters() as $parameter) {
            $type = $parameter->getType();

            if (! $type instanceof ReflectionNamedType || $type->isBuiltin()) {
                if ($parameter->isDefaultValueAvailable()) {
                    $dependencies[] = $parameter->getDefaultValue();
                    continue;
                }

                throw new Exception("Unable to resolve parameter \${$parameter->getName()} for {$concrete}.");
            }

            $dependencies[] = $this->make($type->getName());
        }

        return $reflection->newInstanceArgs($dependencies);
    }

    public function instance()
    {
        //
    }

    public function call()
    {
        //
    }
}
