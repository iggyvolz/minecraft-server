<?php

namespace iggyvolz\minecraft;

use Amp\ByteStream\ReadableStream;
use Amp\ByteStream\WritableStream;
use iggyvolz\minecraft\Packet\ClientState;
use iggyvolz\minecraft\Packet\Packet;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

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
        while($packet = Packet::read($this->socket, $this->clientState)) {
            $this->logger->debug("Packet $packet");
        }
    }
}