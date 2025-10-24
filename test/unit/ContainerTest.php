<?php

use PHPUnit\Framework\TestCase;
use veejay\container\Container;

final class ContainerTest extends TestCase
{
    const ID = 'testId';

    public function test__get()
    {
        $c = $this->getContainer();
        $model = new stdClass;
        $c->definitions[self::ID] = fn() => $model;

        $this->assertSame($model, $c->{self::ID});
        $this->assertArrayHasKey(self::ID, $c->instances);
        $this->assertSame($model, $c->instances[self::ID]);

        $this->assertNull($c->undefined);
    }

    public function test__set()
    {
        $c = $this->getContainer();
        $model = new stdClass;

        // Добавление зависимости стрелочной функцией
        $c->{self::ID} = fn() => $model;
        $this->assertArrayHasKey(self::ID, $c->definitions);
        $this->assertEquals($model, $c->definitions[self::ID]());

        // Добавление зависимости анонимной функцией
        $c->{self::ID} = function () use ($model) {
            return $model;
        };
        $this->assertArrayHasKey(self::ID, $c->definitions);
        $this->assertEquals($model, $c->definitions[self::ID]());
    }

    public function test__isset()
    {
        $c = $this->getContainer();

        $actual = isset($c->{self::ID});
        $this->assertFalse($actual);

        $c->definitions[self::ID] = fn() => new stdClass;
        $actual = isset($c->{self::ID});
        $this->assertTrue($actual);
    }

    public function test__unset()
    {
        $c = $this->getContainer();
        $model = new stdClass;
        $c->instances[self::ID] = $model;
        $c->definitions[self::ID] = fn() => $model;

        unset($c->{self::ID});
        $this->assertSame([], $c->definitions);
        $this->assertSame([], $c->instances);
    }

    public function testGet()
    {
        $c = $this->getContainer();
        $model = new stdClass;
        $c->definitions[self::ID] = fn() => $model;

        $actual = $c->get(self::ID);
        $this->assertSame($model, $actual);
        $this->assertArrayHasKey(self::ID, $c->instances);
        $this->assertSame($model, $c->instances[self::ID]);

        $actual = $c->get('undefined');
        $this->assertNull($actual);
    }

    public function testSet()
    {
        $c = $this->getContainer();
        $model = new stdClass;

        // При создании контейнера все зависимости пусты
        $this->assertSame([], $c->definitions);
        $this->assertSame([], $c->instances);

        // Добавление зависимости стрелочной функцией (название класса в кач-ве ID)
        $c->set(stdClass::class, fn() => $model);
        $this->assertArrayHasKey(stdClass::class, $c->definitions);
        $this->assertEquals($model, $c->definitions[stdClass::class]());

        // Добавление зависимости анонимной функцией (строка в кач-ве ID)
        $c->set(self::ID, function () use ($model) {
            return $model;
        });
        $this->assertArrayHasKey(self::ID, $c->definitions);
        $this->assertEquals($model, $c->definitions[self::ID]());
    }

    public function testHas()
    {
        $c = $this->getContainer();

        $actual = $c->has(self::ID);
        $this->assertFalse($actual);

        $c->definitions[self::ID] = fn() => new stdClass;
        $actual = $c->has(self::ID);
        $this->assertTrue($actual);
    }

    public function testUnset()
    {
        $c = $this->getContainer();
        $model = new stdClass;
        $c->instances[self::ID] = $model;
        $c->definitions[self::ID] = fn() => $model;

        $c->unset(self::ID);
        $this->assertSame([], $c->definitions);
        $this->assertSame([], $c->instances);
    }

    /**
     * @return Container
     */
    protected function getContainer()
    {
        return new class extends Container
        {
            public array $definitions = [];
            public array $instances = [];
        };
    }
}
