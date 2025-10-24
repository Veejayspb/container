<?php

namespace veejay\container;

use Psr\Container\ContainerInterface;

/**
 * DI контейнер.
 *
 * 1. Вариант использования по названию класса.
 * $c = new Container;
 * $c->set(Component::class, fn() => new Component);
 * $c->get(Component::class);
 *
 * 2. Вариант использования с произвольным названием компонента, если один класс используется в двух разных компонентах.
 * $c = new Container;
 * $c->set('one', fn() => new Component);
 * $c->set('two', function(Container $di) {
 *     return new Component;
 * });
 * $c->get('one'); // Идентично
 * $c->one;        // Идентично
 *
 * Class Container
 */
class Container implements ContainerInterface
{
    /**
     * Экземпляры компонентов.
     * @var array
     */
    protected array $instances = [];

    /**
     * Конструкторы компонентов.
     * @var array
     */
    protected array $definitions = [];

    /**
     * @param string $name
     * @return object|null
     */
    public function __get(string $name)
    {
        return $this->get($name);
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function __set(string $name, mixed $value): void
    {
        $this->set($name, $value);
    }

    /**
     * @param string $name
     * @return bool
     */
    public function __isset(string $name): bool
    {
        return $this->has($name);
    }

    /**
     * @param string $name
     * @return void
     */
    public function __unset(string $name): void
    {
        $this->unset($name);
    }

    /**
     * Получение компонента.
     * @param string $id
     * @return object|null
     */
    public function get(string $id): ?object
    {
        if (isset($this->instances[$id])) {
            return $this->instances[$id];
        }

        if (!array_key_exists($id, $this->definitions)) {
            return null;
        }

        $definition = $this->definitions[$id];
        $instance = $definition($this);

        return $this->instances[$id] = $instance;
    }

    /**
     * Регистрация компонента.
     * @param string $id
     * @param callable $definition
     * @return void
     */
    public function set(string $id, callable $definition): void
    {
        $this->unset($id);
        $this->definitions[$id] = $definition;
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $id): bool
    {
        return array_key_exists($id, $this->definitions);
    }

    /**
     * Удаление компонента.
     * @param string $id
     * @return void
     */
    public function unset(string $id): void
    {
        if (array_key_exists($id, $this->instances)) {
            unset($this->instances[$id]);
        }

        if (array_key_exists($id, $this->definitions)) {
            unset($this->definitions[$id]);
        }
    }
}
