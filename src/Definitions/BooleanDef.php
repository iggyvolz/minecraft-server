<?php

namespace iggyvolz\minecraft\Definitions;

use Amp\ByteStream\ReadableStream;
use Amp\ByteStream\WritableStream;

/** @template-extends Definition<bool> */
class BooleanDef extends Definition
{

    public static function read(ReadableStream $input): bool
    {
        return ByteDef::read($input) === 1;
    }

    public static function write(WritableStream $output, mixed $data): void
    {
        if(!is_bool($data)) throw new \TypeError();
        ByteDef::write($output, $data ? 1 : 0);
    }
}