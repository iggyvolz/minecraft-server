<?php
namespace iggyvolz\minecraft\test;
use Amp\ByteStream\ReadableBuffer;
use iggyvolz\minecraft\FixedSizeStreamWrapper;
use iggyvolz\minecraft\StreamReaderWriter;
use Tester\Assert;

require_once __DIR__ . "/../vendor/autoload.php";
\Tester\Environment::setup();
$varlongCases = [
    0 => "\x00",
    1 => "\x01",
    2 => "\x02",
    127 => "\x7f",
    128 => "\x80\x01",
    255 => "\xff\x01",
    2147483647 => "\xff\xff\xff\xff\x07",
    9223372036854775807 => "\xff\xff\xff\xff\xff\xff\xff\xff\x7f",
    -1 => "\xff\xff\xff\xff\xff\xff\xff\xff\xff\x01",
    -2147483648 => "\x80\x80\x80\x80\xf8\xff\xff\xff\xff\x01",
    -9223372036854775808 => "\x80\x80\x80\x80\x80\x80\x80\x80\x80\x01",
];

foreach($varlongCases as $value => $bytes) {
    $client = new FixedSizeStreamWrapper($buf = new ReadableBuffer($bytes));
    Assert::same($value, StreamReaderWriter::readVarlong($client));
    Assert::false($buf->isReadable());
}