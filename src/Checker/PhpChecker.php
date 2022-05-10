<?php

namespace App\Checker;

use App\Entity\Package;
use App\List\PackageList;
use App\Traits\LockFileAwareTrait;
use App\Util\Data;
use App\Util\Version;
use Composer\Semver\Semver;

class PhpChecker implements CheckerInterface
{
    use LockFileAwareTrait;

    public function shouldCheck(PackageList $packages): bool
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
                $affectedVersions = $this->getPackagistSecurityAdvisory($package);
                $updates = $this->getPackageUpdates($package, $affectedVersions);

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

    private function getPackageUpdates(Package $package, string $affectedVersions): array
    {
        $url = sprintf('https://repo.packagist.org/p2/%s.json', $package->getName());
        $data = Data::fromJSON(file_get_contents($url));
        $releases = array_reverse($data['packages'][$package->getName()]);
        $updates = [];

        foreach ($releases as $update) {
            if (Version::isNew($update['version'], $package->getVersion())) {
                if ($affectedVersions && !Semver::satisfies($update['version_normalized'], $affectedVersions)) {
                    $updates[] = [
                        'version' => $update['version'],
                    ];

                    break;
                }
            }
        }

        return $updates;
    }

    private function getPackagistSecurityAdvisory(Package $package): ?string
    {
        $url = sprintf('https://packagist.org/api/security-advisories/?packages[]=%s', $package->getName());
        $data = Data::fromJSON(file_get_contents($url));

        return $data['advisories'][$package->getName()][0]['affectedVersions'] ?? null;
    }
}
