<?php

declare(strict_types=1);

namespace Veejay\Container;

use Closure;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionParameter;

class Container implements ContainerInterface
{
    /**
     * Services collection.
     * @var object[]
     */
    protected array $instances = [];

    /**
     * Definitions collection.
     * @var array
     */
    protected array $definitions = [];

    /**
     * @param array $definitions
     */
    public function __construct(array $definitions = [])
    {
        $this->setMultiple($definitions);
    }

    /**
     * Get service by ID.
     * @param string $id
     * @return object
     * @throws NotFoundException|ContainerException
     */
    public function get(string $id): object
    {
        if (array_key_exists($id, $this->instances)) {
            return $this->instances[$id];
        }

        return $this->instances[$id] = $this->getNew($id);
    }

    /**
     * Create new object by ID (always new instance).
     * @param string $id
     * @return object
     * @throws NotFoundException|ContainerException
     */
    public function getNew(string $id): object
    {
        if (!$this->has($id)) {
            if ($this->isInstantiable($id)) {
                return $this->createInstance($id);
            }

            throw new NotFoundException(sprintf('Definition not found: %s', $id));
        }

        $definition = $this->definitions[$id];
        $type = gettype($definition);

        if ($definition instanceof Closure) {
            $object = $definition($this);

            if (is_object($object)) {
                return $object;
            }

            throw new ContainerException(sprintf('Closure must returns an object: %s', $id));
        } elseif ($type == 'object') {
            return $definition;
        } elseif ($this->isInstantiable($definition)) { // $type == 'string'
            return $this->createInstance($definition);
        } else { // $type == 'string'
            throw new ContainerException(sprintf('Class cannot be instantiated: %s', $definition));
        }
    }

    /**
     * Set definition.
     * @param string $id
     * @param object|callable|string $definition
     * @return void
     */
    public function set(string $id, object|callable|string $definition): void
    {
        if (array_key_exists($id, $this->instances)) {
            unset($this->instances[$id]);
        }

        $this->definitions[$id] = $definition;
    }

    /**
     * Set multiple definitions.
     * @param array $definitions
     * @return void
     */
    public function setMultiple(array $definitions): void
    {
        foreach ($definitions as $id => $definition) {
            $this->set($id, $definition);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $id): bool
    {
        return array_key_exists($id, $this->definitions);
    }

    /**
     * Create an object by class name.
     * @param string $class
     * @return object
     * @throws ContainerException
     */
    protected function createInstance(string $class): object
    {
        try {
            $reflection = new ReflectionClass($class);
        } catch (ReflectionException $e) {
            throw new ContainerException(sprintf('Class does not exists: %s', $class));
        }

        $constructor = $reflection->getConstructor();

        if (!$constructor) {
            return $reflection->newInstance();
        }

        $args = [];

        foreach ($constructor->getParameters() as $param) {
            $args[] = $this->getParamValue($param);
        }

        return $reflection->newInstanceArgs($args);
    }

    /**
     * Return the value of the specified constructor parameter.
     * @param ReflectionParameter $param
     * @return mixed
     */
    private function getParamValue(ReflectionParameter $param): mixed
    {
        $type = $param->getType();

        if (!is_null($type)) {
            $type = (string)$type;
        }

        if (!is_null($type) && (class_exists($type) || interface_exists($type))) {
            return $this->get($type);
        }

        if ($param->isDefaultValueAvailable()) {
            return $param->getDefaultValue();
        }

        return null;
    }

    /**
     * Whether the class is instantiable.
     * @param string $class
     * @return bool
     */
    private function isInstantiable(string $class): bool
    {
        if (!class_exists($class)) {
            return false;
        }

        $reflectionClass = new ReflectionClass($class);
        return $reflectionClass->isInstantiable();
    }
}
