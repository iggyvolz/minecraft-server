<?php
require_once __DIR__ . "/vendor/autoload.php";
(new \iggyvolz\minecraft\MinecraftServer(\Amp\Socket\listen("127.0.0.1:25565"), $logger = new class extends \Psr\Log\AbstractLogger {

    public function log($level, \Stringable|string $message, array $context = []): void
    {
        echo "$level: $message\n";
    }
}))->run();
