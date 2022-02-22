<?php

namespace iggyvolz\minecraft;

use Amp\ByteStream\ReadableStream;
use Amp\ByteStream\WritableStream;
use iggyvolz\minecraft\Packet\ClientState;
use iggyvolz\minecraft\Packet\HandshakePacket;
use iggyvolz\minecraft\Packet\Packet;
use iggyvolz\minecraft\Packet\StatusPing;
use iggyvolz\minecraft\Packet\StatusRequest;
use iggyvolz\minecraft\Packet\StatusResponse;
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
        $this->logger->debug("Reading a packet...");
        while($packet = Packet::read($this->socket, $this->clientState)) {
            $this->logger->debug("Received $packet");
            if($packet instanceof HandshakePacket) {
                $this->clientState = $packet->nextState;
            } elseif($packet instanceof StatusPing) {
                $packet->pong()->write($this->socket, $this->logger);
            } elseif($packet instanceof StatusRequest) {
                StatusResponse::create(
                    versionName: "1.8.7",
                    versionProtocol: 47,
                    playersMax: 100,
                    playersOnline: 0,
                    description: "Test minecraft server"
                )->write($this->socket, $this->logger);
            }
            $this->logger->debug("Reading a packet...");
        }
    }
}