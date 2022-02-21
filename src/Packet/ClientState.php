<?php

namespace iggyvolz\minecraft\Packet;

enum ClientState: int
{
    case Handshaking = 0;
    case Status = 1;
    case Login = 2;
    case Play = 3;
}