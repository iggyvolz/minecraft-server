<?php

namespace iggyvolz\minecraft;

use Amp\ByteStream\ReadableStream;

abstract class Packet
{
    public final function __construct(
        public readonly int $length,
        public readonly int $packetId,
        ReadableStream $data,
    )
    {
        $this->read($data);
    }

    protected abstract function read(ReadableStream $data);
}