<?php

namespace NodejsPhpFallback;

interface WrapperInterface
{
    public function compile();

    public function fallback();
}
