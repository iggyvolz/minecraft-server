<?php
namespace iggyvolz\minecraft\test;
use Amp\ByteStream\ReadableBuffer;
use iggyvolz\minecraft\StreamReaderWriter;
use Tester\Assert;

require_once __DIR__ . "/../vendor/autoload.php";
\Tester\Environment::setup();
$testCases = [
    "\x00\x00\x00\x01" => 2**-149,
    "\x00\x7f\xff\xff" => 2**-126 * (1-2**-23),
    "\x00\x80\x00\x00" => 2**-126,
    "\x7f\x7f\xff\xff" => 2**127 * (2-2**-23),
    "\x3f\x7f\xff\xff" => 1-2**-24,
    "\x3f\x80\x00\x00" => 1.0,
    "\x3f\x80\x00\x01" => 1 + 2**-23,
    "\xc0\x00\x00\x00" => -2.0,
    "\x00\x00\x00\x00" => 0.0,
    "\x80\x00\x00\x00" => -0.0,
    "\x7f\x80\x00\x00" => INF,
    "\xff\x80\x00\x00" => -INF,
];

foreach($testCases as $bytes => $value) {
    $buf = new ReadableBuffer($bytes);
    Assert::same($value, StreamReaderWriter::readFloat($buf, false));
    Assert::false($buf->isReadable());
}