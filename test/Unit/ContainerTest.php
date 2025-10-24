<?php

declare(strict_types=1);

namespace Test\Unit;

use Closure;
use DateTimeZone;
use PHPUnit\Framework\TestCase;
use Test\Asset\AbstractClass;
use Test\Asset\ClassWithAbstractParent;
use Test\Asset\ClassWithConstructorParams;
use Test\Asset\ClassWithInterface;
use Test\Asset\ClassWithoutConstructor;
use Test\Asset\ClassWithPrivateConstructor;
use Test\Asset\ServiceInterface;
use Veejay\Container\Container;
use Veejay\Container\ContainerException;
use Veejay\Container\NotFoundException;

final class ContainerTest extends TestCase
{
    // Tests for each method -------------------------------------------------------------------------------------------

    public function testConstruct()
    {
        $actual = new Container([
            ClassWithoutConstructor::class => ClassWithoutConstructor::class,
        ]);

        $expected = new Container;
        $expected->set(ClassWithoutConstructor::class, ClassWithoutConstructor::class);

        $this->assertEquals($expected, $actual);
    }

    public function testGet()
    {
        $container = new Container;

        $this->assertSame(
            $container->get(ClassWithoutConstructor::class),
            $container->get(ClassWithoutConstructor::class)
        );
    }

    public function testGetNew()
    {
        $container = new Container;

        $this->assertNotSame(
            $container->get(ClassWithoutConstructor::class),
            $container->getNew(ClassWithoutConstructor::class)
        );

        $this->assertNotSame(
            $container->getNew(ClassWithoutConstructor::class),
            $container->getNew(ClassWithoutConstructor::class)
        );
    }

    public function testSet()
    {
        $container = new Container;

        // object
        $expected = new ClassWithInterface;
        $container->set(ServiceInterface::class, $expected);
        $actual = $container->get(ServiceInterface::class);
        $this->assertSame($expected, $actual);

        // closure
        $expected = new ClassWithInterface;
        $container->set(ServiceInterface::class, fn() => $expected);
        $actual = $container->get(ServiceInterface::class);
        $this->assertSame($expected, $actual);

        // string
        $container->set(ServiceInterface::class, ClassWithInterface::class);
        $actual = $container->get(ServiceInterface::class);
        $this->assertEquals(new ClassWithInterface, $actual);

    }

    public function testSetMultiple()
    {
        $actual = new Container;
        $actual->setMultiple([
            ClassWithoutConstructor::class => ClassWithoutConstructor::class,
        ]);

        $expected = new Container;
        $expected->set(ClassWithoutConstructor::class, ClassWithoutConstructor::class);

        $this->assertEquals($expected, $actual);
    }

    public function testHas()
    {
        $container = new Container;

        $actual = $container->has(ClassWithoutConstructor::class);
        $this->assertFalse($actual);

        $container->set(ClassWithoutConstructor::class, ClassWithoutConstructor::class);
        $actual = $container->has(ClassWithoutConstructor::class);
        $this->assertTrue($actual);
    }

    // Tests for different scenario ------------------------------------------------------------------------------------

    public function testGetNonexistent()
    {
        $container = new Container;

        $this->expectException(NotFoundException::class);
        $container->get('NonexistentClass');
    }

    public function testClosureReturnsNotObject()
    {
        $container = new Container;

        $container->set(ClassWithoutConstructor::class, fn() => 123);
        $this->expectException(ContainerException::class);
        $container->get(ClassWithoutConstructor::class);
    }

    public function testGetUndefined()
    {
        $container = new Container;

        $actual = $container->get(ClassWithoutConstructor::class);
        $this->assertEquals(new ClassWithoutConstructor, $actual);
    }

    public function testUseStringAsKey()
    {
        $container = new Container([
            ServiceInterface::class => ClassWithInterface::class,
            AbstractClass::class => ClassWithAbstractParent::class,
            'service' => ClassWithConstructorParams::class,
        ]);

        $actual = $container->get('service');
        $expected = new ClassWithConstructorParams(new ClassWithInterface, new ClassWithAbstractParent);
        $this->assertEquals($expected, $actual);
    }

    public function testClassWithPrivateConstructor()
    {
        $container = new Container;

        $container->set(ClassWithPrivateConstructor::class, ClassWithPrivateConstructor::class);
        $this->expectException(ContainerException::class);
        $container->get(ClassWithPrivateConstructor::class);
    }

    public function testAbstractClass()
    {
        $container = new Container;

        $container->set(AbstractClass::class, AbstractClass::class);
        $this->expectException(ContainerException::class);
        $container->get(AbstractClass::class);
    }

    public function testInterface()
    {
        $container = new Container;

        $container->set(ServiceInterface::class, ServiceInterface::class);
        $this->expectException(ContainerException::class);
        $container->get(ServiceInterface::class);
    }

    public function testNoninstantifiableClass()
    {
        $container = new Container;

        $container->set(Closure::class, Closure::class);
        $this->expectException(ContainerException::class);
        $container->get(Closure::class);
    }

    public function testBuiltinClass()
    {
        $container = new Container;

        $container->set(DateTimeZone::class, fn() => new DateTimeZone('UTC'));
        $actual = $container->get(DateTimeZone::class);
        $this->assertEquals(new DateTimeZone('UTC'), $actual);
    }

    public function testClassWithoutConstructor()
    {
        $container = new Container;

        $container->set(ClassWithoutConstructor::class, ClassWithoutConstructor::class);
        $actual = $container->get(ClassWithoutConstructor::class);
        $this->assertEquals(new ClassWithoutConstructor, $actual);
    }

    public function testClassWithInterface()
    {
        $container = new Container;

        $container->set(ServiceInterface::class, ClassWithInterface::class);
        $actual = $container->get(ServiceInterface::class);
        $this->assertEquals(new ClassWithInterface, $actual);

        $actual = $container->get(ClassWithInterface::class);
        $this->assertEquals(new ClassWithInterface, $actual);

        $this->assertNotSame(
            $container->get(ServiceInterface::class),
            $container->get(ClassWithInterface::class)
        );
    }

    public function testAutoWiring()
    {
        $container = new Container([
            ServiceInterface::class => ClassWithInterface::class,
            AbstractClass::class => ClassWithAbstractParent::class,
        ]);

        $actual = $container->get(ClassWithConstructorParams::class);
        $expected = new ClassWithConstructorParams(new ClassWithInterface, new ClassWithAbstractParent);
        $this->assertEquals($expected, $actual);
    }
}
