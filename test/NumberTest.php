<?php
namespace iggyvolz\minecraft\test;
use Amp\ByteStream\ReadableBuffer;
use Amp\ByteStream\WritableBuffer;
use iggyvolz\minecraft\Definitions\BooleanDef;
use iggyvolz\minecraft\Definitions\ByteDef;
use iggyvolz\minecraft\Definitions\DoubleDef;
use iggyvolz\minecraft\Definitions\FloatDef;
use iggyvolz\minecraft\Definitions\IntDef;
use iggyvolz\minecraft\Definitions\LongDef;
use iggyvolz\minecraft\Definitions\PositionDef;
use iggyvolz\minecraft\Definitions\ShortDef;
use iggyvolz\minecraft\Definitions\StringDef;
use iggyvolz\minecraft\Definitions\TestCase;
use iggyvolz\minecraft\Definitions\UByteDef;
use iggyvolz\minecraft\Definitions\UShortDef;
use iggyvolz\minecraft\Definitions\UuidDef;
use iggyvolz\minecraft\Definitions\VarintDef;
use iggyvolz\minecraft\Definitions\VarlongDef;
use Iggyvolz\SimpleAttributeReflection\AttributeReflection;
use Tester\Assert;

require_once __DIR__ . "/../vendor/autoload.php";
\Tester\Environment::setup();

foreach([
        BooleanDef::class,
        ByteDef::class,
        DoubleDef::class,
        FloatDef::class,
        IntDef::class,
        LongDef::class,
        PositionDef::class,
        ShortDef::class,
        StringDef::class,
        UByteDef::class,
        UShortDef::class,
        UuidDef::class,
        VarintDef::class,
        VarlongDef::class,
    ] as $class) {
    /** @var TestCase $testCase */
    echo "Class $class\n";
    $instance = new $class;
    foreach(AttributeReflection::getAttributes(new \ReflectionClass($class), TestCase::class) as $i => $testCase) {
        echo "Test case $i: " . bin2hex($testCase->input) . PHP_EOL;
        $testCase->test($class);
    }
}





$byteCases = [
    "\x00" => 0,
    "\x01" => 1,
    "\xff" => -1,
    "\xfe" => -2,
];
$shortCases = [
    "\x00\x00" => 0,
    "\x00\x02" => 2,
    "\xff\xff" => -1,
    "\xff\xfe" => -2,
];
$intCases = [
    "\x00\x00\x00\x00" => 0,
    "\x00\x00\x00\x02" => 2,
    "\xff\xff\xff\xff" => -1,
    "\xff\xff\xff\xfe" => -2,
];
$longCases = [
    "\x00\x00\x00\x00\x00\x00\x00\x00" => 0,
    "\x00\x00\x00\x00\x00\x00\x00\x02" => 2,
    "\xff\xff\xff\xff\xff\xff\xff\xff" => -1,
    "\xff\xff\xff\xff\xff\xff\xff\xfe" => -2,
];
$varintCases = [
    "\x00" => 0,
    "\x01" => 1,
    "\x02" => 2,
    "\x7f" => 127,
    "\x80\x01" => 128,
    "\xff\x01" => 255,
    "\xdd\xc7\x01" => 25565,
    "\xff\xff\x7f" => 2097151,
    "\xff\xff\xff\xff\x07" => 2147483647,
    "\xff\xff\xff\xff\x0f" => -1,
    "\x80\x80\x80\x80\x08" => -2147483648,
];

$varlongCases = [
    "\x00" => 0,
    "\x01" => 1,
    "\x02" => 2,
    "\x7f" => 127,
    "\x80\x01" => 128,
    "\xff\x01" => 255,
    "\xff\xff\xff\xff\x07" => 2147483647,
    "\xff\xff\xff\xff\xff\xff\xff\xff\x7f" => 9223372036854775807,
    "\xff\xff\xff\xff\xff\xff\xff\xff\xff\x01" => -1,
    "\x80\x80\x80\x80\xf8\xff\xff\xff\xff\x01" => -2147483648,
    "\x80\x80\x80\x80\x80\x80\x80\x80\x80\x01" => -9223372036854775808.0,
];

$singleCases = [
    "\x00\x00\x00\x01" => 2**-149,
    "\x00\x7f\xff\xff" => 2**-126 * (1-2**-23),
    "\x00\x80\x00\x00" => 2**-126,
    "\x7f\x7f\xff\xff" => 2**127 * (2-2**-23),
    "\x3f\x7f\xff\xff" => 1-2**-24,
    "\x3f\x80\x00\x00" => 1.0,
    "\x3f\x80\x00\x01" => 1 + 2**-23,
    "\xc0\x00\x00\x00" => -2.0,
    "\x00\x00\x00\x00" => 0.0,
//    "\x80\x00\x00\x00" => -0.0, // PHP has no -0 TODO test this just one-way
    "\x40\x49\x0f\xdb" => 3.14159274101257324,
    "\x3e\xaa\xaa\xab" => 0.333333343267440796,
    "\x7f\x80\x00\x00" => INF,
    "\xff\x80\x00\x00" => -INF,
];

$doubleCases = [
    "\x3F\xF0\x00\x00\x00\x00\x00\x00" => 1.0,
    "\x3F\xF0\x00\x00\x00\x00\x00\x01" => 1.0000000000000002,
    "\x3F\xF0\x00\x00\x00\x00\x00\x02" => 1.0000000000000004,
    "\x40\x00\x00\x00\x00\x00\x00\x00" => 2.0,
    "\xC0\x00\x00\x00\x00\x00\x00\x00" => -2.0,
    "\x40\x08\x00\x00\x00\x00\x00\x00" => 3.0,
    "\x40\x10\x00\x00\x00\x00\x00\x00" => 4.0,
    "\x40\x14\x00\x00\x00\x00\x00\x00" => 5.0,
    "\x40\x18\x00\x00\x00\x00\x00\x00" => 6.0,
    "\x40\x37\x00\x00\x00\x00\x00\x00" => 23.0,
    "\x3F\x88\x00\x00\x00\x00\x00\x00" => 0.01171875,
    "\x00\x00\x00\x00\x00\x00\x00\x01" => 2**-1074,
    "\x00\x0F\xFF\xFF\xFF\xFF\xFF\xFF" => 2**-1022 * (1-2**-52),
    "\x00\x10\x00\x00\x00\x00\x00\x00" => 2**-1022,
    "\x7F\xEF\xFF\xFF\xFF\xFF\xFF\xFF" => 2**1023 * (2-2**-52),
    "\x00\x00\x00\x00\x00\x00\x00\x00" => 0.0,
//    "\x80\x00\x00\x00\x00\x00\x00\x00" => -0.0,  // PHP has no -0 TODO test this just one-way
    "\x7F\xF0\x00\x00\x00\x00\x00\x00" => INF,
    "\xFF\xF0\x00\x00\x00\x00\x00\x00" => -INF,
//    "\x7F\xF0\x00\x00\x00\x00\x00\x01" => NAN, // TODO test one-way
    "\x3d\x55\x55\x55\x55\x55\x55\x55" => 3.0316490059097606E-13,
    "\x40\x09\x21\xfb\x54\x44\x2d\x18" => pi(),
];
foreach ([
     [
         "name" => "byte",
         "cases" => $byteCases,
         "write" => ByteDef::write(...),
         "read" => ByteDef::read(...),
         "endianTest" => true,
     ],
     [
         "name" => "short",
         "cases" => $shortCases,
         "write" => ShortDef::write(...),
         "read" => ShortDef::read(...),
         "endianTest" => true,
     ],
     [
         "name" => "int",
         "cases" => $intCases,
         "write" => IntDef::write(...),
         "read" => IntDef::read(...),
         "endianTest" => true,
     ],
     [
         "name" => "long",
         "cases" => $longCases,
         "write" => LongDef::write(...),
         "read" => LongDef::read(...),
         "endianTest" => true,
     ],
     [
         "name" => "varint",
         "cases" => $varintCases,
         "write" => VarintDef::write(...),
         "read" => VarintDef::read(...),
         "endianTest" => false, // Always big endian - so there is no use in flipping byte order
     ],
     [
         "name" => "varlong",
         "cases" => $varlongCases,
         "write" => VarlongDef::write(...),
         "read" => VarlongDef::read(...),
         "endianTest" => false,
     ],
     [
         "name" => "single",
         "cases" => $singleCases,
         "write" => FloatDef::write(...),
         "read" => FloatDef::read(...),
         "endianTest" => true,
     ],
     [
         "name" => "double",
         "cases" => $doubleCases,
         "write" => DoubleDef::write(...),
         "read" => DoubleDef::read(...),
         "endianTest" => true,
     ],
     ] as ["name" => $name, "cases" => $cases, "write" => $write, "read" => $read, "endianTest" => $endianTest]) foreach($cases as $bytes => $value) {
    echo "Test case $name " . bin2hex($bytes) . "\n";
    $buf = new ReadableBuffer($endianTest ? strrev($bytes) : $bytes);
    $readValue = $read($buf, false);
    // NAN !== NAN
    if(is_nan($value) && is_nan($readValue)) {
        Assert::true(true);
    } else {
        Assert::same($value, is_float($value) ? floatval($readValue) : $readValue);
    }
    Assert::false($buf->isReadable());

    $buf = new WritableBuffer();
    $write($buf, $value, false);
    $buf->close();
    Assert::same($endianTest ? strrev($bytes) : $bytes, $buf->buffer());
}