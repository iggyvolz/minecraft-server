<?php
namespace iggyvolz\minecraft\test;
use Amp\ByteStream\ReadableBuffer;
use iggyvolz\minecraft\FixedSizeStreamWrapper;
use iggyvolz\minecraft\StreamReaderWriter;
use Tester\Assert;

require_once __DIR__ . "/../vendor/autoload.php";
\Tester\Environment::setup();
$varintCases = [
    0 => "\x00",
    1 => "\x01",
    2 => "\x02",
    127 => "\x7f",
    128 => "\x80\x01",
    255 => "\xff\x01",
    25565 => "\xdd\xc7\x01",
    2097151 => "\xff\xff\x7f",
    2147483647 => "\xff\xff\xff\xff\x07",
    -1 => "\xff\xff\xff\xff\x0f",
    -2147483648 => "\x80\x80\x80\x80\x08",
];

foreach($varintCases as $value => $bytes) {
    $client = new FixedSizeStreamWrapper($buf = new ReadableBuffer($bytes));
    Assert::same($value, StreamReaderWriter::readVarint($client));
    Assert::false($buf->isReadable());
}