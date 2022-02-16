<?php

namespace iggyvolz\minecraft;

use Amp\ByteStream\ReadableStream;
use Amp\ByteStream\WritableStream;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use RuntimeException;

// https://wiki.vg/Protocol
class MinecraftClient
{
    private ClientState $clientState = ClientState::Handshaking;
    public function __construct(
        private readonly ReadableStream&WritableStream $socket,
        private readonly LoggerInterface $logger = new NullLogger(),
    )
    {
    }

    public function run()
    {
        while($packet = Packet::read($this->clientState, $this->socket)) {
            $this->logger->debug("Packet $packet->packetId: " . bin2hex($packet->data));
        }
    }
}