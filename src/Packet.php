<?php

namespace iggyvolz\minecraft;

use Amp\ByteStream\ReadableBuffer;
use Amp\ByteStream\ReadableStream;

abstract class Packet implements \Stringable
{
    public function __construct(
        public readonly int $length,
        public readonly int $packetId,
        ReadableStream $data,
    )
    {
    }

    public static function read(ClientState $state, ReadableStream $input): self
    {
        $length = StreamReaderWriter::readVarint($input);
        $packet = new ReadableBuffer(StreamReaderWriter::read($input, $length));
        $packetId = StreamReaderWriter::readVarint($packet);

    }
}