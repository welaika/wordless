<?php

namespace Phug;

class ParserEvent
{
    const PARSE = 'parser.parse';
    const DOCUMENT = 'parser.document';
    const STATE_ENTER = 'parser.state_enter';
    const STATE_LEAVE = 'parser.state_leave';
    const STATE_STORE = 'parser.state_store';
}
