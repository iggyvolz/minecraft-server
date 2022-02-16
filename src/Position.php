<?php

namespace iggyvolz\minecraft;

final class Position
{
    public function __construct(
        public readonly int $x,
        public readonly int $y,
        public readonly int $z,
    )
    {
    }
}