<?php

namespace App\Checker;

use App\Entity\Package;
use App\List\PackageList;
use App\Traits\LockFileAwareTrait;
use App\Util\Data;
use App\Util\Version;

class PhpChecker implements CheckerInterface
{
    use LockFileAwareTrait;

    public static function shouldCheck(PackageList $packages): bool
    {
        return true;
    }

    public function check(PackageList $packages): PackageList
    {
        // lpsc is the command added to Docker image to run the security check
        $json = shell_exec(sprintf('lpsc -path %s --format json', $this->getLockFile()));
        $json = str_replace('::set-output name=vulns::', '', $json);
        $data = Data::fromJSON($json);

        foreach ($data as $package_name => $advisory) {
            if ($packages->hasPackage($package_name)) {
                $package = $packages->getPackage($package_name);
                //$installed[$package_name]['advisories'] = $advisory['advisories'];
                $updates = $this->getPackageUpdates($package);

                $package->setHasUpdate(true);
                $package->setUpdateVersion($updates[0]['version']);
                $package->setUpdateUrl(sprintf(
                    'https://github.com/%s/releases/tag/%s',
                    $package_name,
                    $package->getUpdateVersion()
                ));

                $packages->add($package);
            }
        }

        return $packages;
    }

    private function getPackageUpdates(Package $package): array
    {
        $url = sprintf('https://repo.packagist.org/p2/%s.json', $package->getName());
        $data = Data::fromJSON(file_get_contents($url));
        $updates = [];

        foreach ($data['packages'][$package->getName()] as $update) {
            if (Version::isNew($update['version'], $package->getVersion())) {
                $updates[] = [
                    'version' => $update['version'],
                ];
            }
        }

        return $updates;
    }
}
