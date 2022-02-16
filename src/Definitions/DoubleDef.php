<?php

namespace iggyvolz\minecraft\Definitions;

use Amp\ByteStream\ReadableStream;
use Amp\ByteStream\WritableStream;

class DoubleDef extends AFloatDef
{

    public static function read(ReadableStream $input): float
    {
        return self::readFloat($input, 52, 11, true);
    }

    /**
     * @inheritDoc
     */
    public static function write(WritableStream $output, mixed $data): void
    {
        if(!is_float($data)) throw new \TypeError();
        self::writeFloat($output, 52, 11, $data, true);
    }
}