<?php

namespace App\Service;

use App\Checker\CheckerInterface;
use App\Checker\DrupalChecker;
use App\Checker\PhpChecker;
use App\Entity\Package;
use App\List\PackageList;
use App\Traits\LockFileAwareTrait;
use App\Util\Data;

class UpdateService
{
    use LockFileAwareTrait;

    private array $checkers = [
        DrupalChecker::class,
        PhpChecker::class,
    ];

    public function checkUpdates(): PackageList
    {
        $packages = $this->readInstalledPackages();

        foreach ($this->checkers as $checker) {
            /** @var CheckerInterface $checker */
            $checker = (new $checker())->setLockFile($this->getLockFile());

            if ($checker->shouldCheck($packages)) {
                $packages = $checker->check($packages);
            }
        }

        return $this->availableUpdates($packages);
    }

    private function availableUpdates(PackageList $packages): PackageList
    {
        $available = new PackageList();

        foreach ($packages as $package) {
            if ($package->hasUpdate()) {
                $available->add($package);
            }
        }

        return $available;
    }

    private function readInstalledPackages(bool $include_dev = false): PackageList
    {
        $installed = new PackageList();
        $data = Data::fromJSON(file_get_contents($this->lockFile));

        foreach ($data['packages'] as $package_data) {
            $installed->add(new Package($package_data));
        }

        if ($include_dev) {
            foreach ($data['packages-dev'] as $package_data) {
                $installed->add(new Package($package_data));
            }
        }

        return $installed;
    }
}
