<?php

namespace iggyvolz\minecraft;

enum ClientState
{
    case Handshaking;
    case Status;
    case Login;
    case Play;
}