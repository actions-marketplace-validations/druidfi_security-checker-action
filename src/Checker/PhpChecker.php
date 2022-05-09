<?php

namespace App\Checker;

use App\Traits\LockFileAwareTrait;
use App\Util\Data;
use App\Util\Version;

class PhpChecker implements CheckerInterface
{
    use LockFileAwareTrait;

    public function check(array &$installed): void
    {
        // lpsc is the command added to Docker image to run the security check
        $json = shell_exec(sprintf('lpsc -path %s --format json', $this->getLockFile()));
        $json = str_replace('::set-output name=vulns::', '', $json);
        $data = Data::fromJSON($json);

        foreach ($data as $package_name => $advisory) {
            if (isset($installed[$package_name])) {
                //$installed[$package_name]['advisories'] = $advisory['advisories'];
                $updates = $this->getPackageUpdates(
                    $package_name,
                    $installed[$package_name]['current_version']
                );
                $installed[$package_name]['update_to'] = $updates[0]['version'];
                $installed[$package_name]['read_more'] = sprintf(
                    'https://github.com/%s/releases/tag/%s',
                    $package_name,
                    $installed[$package_name]['update_to']
                );
            }
        }
    }

    private function getPackageUpdates(string $package_name, string $current_version): array
    {
        $url = sprintf('https://repo.packagist.org/p2/%s.json', $package_name);
        $data = Data::fromJSON(file_get_contents($url));
        $updates = [];

        foreach ($data['packages'][$package_name] as $update) {
            if (Version::isNew($update['version'], $current_version)) {
                $updates[] = [
                    'version' => $update['version'],
                ];
            }
        }

        return $updates;
    }
}
