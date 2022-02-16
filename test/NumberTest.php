<?php
namespace iggyvolz\minecraft\test;
use iggyvolz\minecraft\Numbers;
use Tester\Assert;

require_once __DIR__ . "/../vendor/autoload.php";
\Tester\Environment::setup();
$cases = [
    "\x00" => 0,
    "\x01" => 1,
    "\x00\x00" => 0,
    "\x00\x02" => 2,
    "\xff\xff" => -1,
];
foreach ($cases as $string => $int) {
    Assert::same($string, Numbers::intToString($int, false, strlen($string)));
    Assert::same(strrev($string), Numbers::intToString($int, true, strlen($string)));
    Assert::same($int, Numbers::stringToInt($string, false, $int < 0));
    Assert::same($int, Numbers::stringToInt(strrev($string), true, $int < 0));
}