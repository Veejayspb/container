<?php

declare(strict_types=1);

namespace Test\Asset;

final class ClassWithConstructorParams
{
    public function __construct(
        protected ServiceInterface $a,
        protected AbstractClass $b,
        protected int $c = 1,
        protected string $d = 'test'
    ) {

    }
}
