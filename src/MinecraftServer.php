<?php
declare(strict_types=1);

namespace iggyvolz\minecraft;

use Amp\Socket\ResourceSocket;
use Amp\Socket\ResourceSocketServer;
use Psr\Log\LoggerInterface;
use function Amp\async;

class MinecraftServer
{
    public function __construct(
        private readonly ResourceSocketServer $server,
        private readonly LoggerInterface $logger,
    )
    {
    }
    public function run(): void
    {
        $this->logger->info("Starting minecraft server on " . $this->server->getAddress()->toString());

        while ($socket = $this->server->accept()) {
            async((function() use($socket){
                $this->logger->info("Got connection from " . $socket->getRemoteAddress()->toString());
                try {
                    (new MinecraftClient($socket, $this->logger))->run();
                } catch(\Throwable $t) {
                    $this->logger->error($t);
                    throw $t;
                } finally {
                    $socket->close();
                }
            }))->ignore();
        }
    }
}