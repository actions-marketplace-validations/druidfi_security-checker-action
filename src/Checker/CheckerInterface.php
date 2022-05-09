<?php

namespace App\Checker;

interface CheckerInterface
{
    public function check(array &$installed): void;
}
