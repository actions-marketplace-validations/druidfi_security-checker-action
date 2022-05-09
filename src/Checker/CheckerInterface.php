<?php

namespace App\Checker;

use App\List\PackageList;

interface CheckerInterface
{
    public function check(PackageList $packages): PackageList;

    public function shouldCheck(PackageList $packages): bool;
}
