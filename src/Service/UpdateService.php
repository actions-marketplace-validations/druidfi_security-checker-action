<?php

namespace App\Service;

use App\Checker\DrupalChecker;
use App\Checker\PhpChecker;
use App\Traits\LockFileAwareTrait;
use App\Util\Data;

class UpdateService
{
    use LockFileAwareTrait;

    private array $installed;

    public function checkUpdates(): array
    {
        $this->readInstalledPackages();

        if ($this->hasPackage(DrupalChecker::$corePackageName)) {
            (new DrupalChecker)->check($this->installed);
        }

        (new PhpChecker)->setLockFile($this->getLockFile())->check($this->installed);

        return $this->availableUpdates();
    }

    private function availableUpdates(): array
    {
        foreach ($this->installed as $package => $data) {
            if (!array_key_exists('update_to', $data)) {
                unset($this->installed[$package]);
            }
        }

        return $this->installed;
    }

    private function readInstalledPackages(bool $include_dev = true): void
    {
        $data = Data::fromJSON(file_get_contents($this->lockFile));

        foreach ($data['packages'] as $package) {
            $this->installed[$package['name']]['current_version'] = $package['version'];
        }

        if ($include_dev) {
            foreach ($data['packages-dev'] as $package) {
                $this->installed[$package['name']]['current_version'] = $package['version'];
            }
        }
    }

    private function hasPackage(string $package_name): bool
    {
        return isset($this->installed[$package_name]);
    }
}
