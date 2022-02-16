<?php

namespace iggyvolz\minecraft\Definitions;

use Amp\ByteStream\ReadableStream;
use Amp\ByteStream\WritableStream;

/** @template-extends Definition<int> */
class ShortDef extends Definition
{

    public static function read(ReadableStream $input): int
    {
        return self::readNumber($input, 2, true, true);
    }

    public static function write(WritableStream $output, mixed $data): void
    {
        if(!is_int($data)) throw new \TypeError();
        self::writeNumber($output, $data, true, 2);
    }
}