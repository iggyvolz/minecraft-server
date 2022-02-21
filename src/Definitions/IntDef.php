<?php

namespace iggyvolz\minecraft\Definitions;

use Amp\ByteStream\ReadableStream;
use Amp\ByteStream\WritableStream;

#[\Attribute(\Attribute::TARGET_PARAMETER)]
/** @template-extends Definition<int> */
class IntDef extends Definition
{

    public static function read(ReadableStream $input): int
    {
        return self::readNumber($input, 4, true, true);
    }

    public static function write(WritableStream $output, mixed $data): void
    {
        if(!is_int($data)) throw new \TypeError();
        self::writeNumber($output, $data, true, 4);
    }
}