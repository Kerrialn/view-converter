<?php

namespace ViewConverter\Printer\Contract;

interface BladePatternInterface
{
    public function apply(string $content): string;
}
