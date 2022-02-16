<?php

namespace iggyvolz\minecraft;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use RuntimeException;

// https://wiki.vg/Protocol
class MinecraftClient
{
    public const IS_BIG_ENDIAN = true;
    public function __construct(
        private readonly FixedSizeStream $socket,
        private readonly LoggerInterface $logger = new NullLogger(),
    )
    {
    }

    public function run()
    {
        while($packet = $this->readPacket()) {
            $this->logger->debug("Packet $packet->packetId: " . bin2hex($packet->data));
        }
    }

    private function readPacket(): Packet
    {
        $length = $this->readVarint();
        $packetIdAndData = $this->socket->read($length);
        $packetIdLength = 0;
        $packetId = $this->readVarint();
        $data = $this->socket->read($length - $packetIdLength);
        return new Packet($length, $packetId, $data);
    }
}