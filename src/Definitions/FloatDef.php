<?php

namespace iggyvolz\minecraft\Definitions;

use Amp\ByteStream\ReadableStream;
use Amp\ByteStream\WritableStream;

class FloatDef extends AFloatDef
{

    public static function read(ReadableStream $input): float
    {
        return self::readFloat($input, 23, 8, true);
    }

    /**
     * @inheritDoc
     */
    public static function write(WritableStream $output, mixed $data): void
    {
        if(!is_float($data)) throw new \TypeError();
        self::writeFloat($output, 23, 8, $data, true);
    }
}