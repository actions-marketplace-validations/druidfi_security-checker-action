<?php

namespace App\Checker;

use App\Entity\DrupalRelease;
use App\List\PackageList;
use App\Traits\LockFileAwareTrait;
use App\Util\Data;
use App\Util\Version;

class DrupalChecker implements CheckerInterface
{
    use LockFileAwareTrait;

    public static string $corePackageName = 'drupal/core';

    private string $projectReleaseHistoryUrl = 'https://updates.drupal.org/release-history/%s/current';

    private array $ignoredPackages = [
        'drupal/core-composer-scaffold',
        'drupal/core-dev',
        'drupal/core-dev-pinned',
        'drupal/core-recommended',
        'drupal/core-project-message',
    ];

    public static function shouldCheck(PackageList $packages): bool
    {
        return $packages->hasPackage(self::$corePackageName);
    }

    public function check(PackageList $packages): PackageList
    {
        foreach ($packages as $package_name => $package) {
            if (str_starts_with($package_name, 'drupal/') && !in_array($package_name, $this->ignoredPackages)) {
                $project = ($package_name === self::$corePackageName) ? 'drupal' : substr($package_name, 7);
                $releases = $this->getProjectData($project);

                foreach ($releases as $release) {
                    if (Version::isNew($release['version'], $package->getVersion(), '>')) {
                        // Only the latest security update is marked as such in data from drupal.org
                        if ($release['security_update']) {
                            //$installed[$package_name]['updates'][] = $release;
                            $package->setHasUpdate(true);
                            $package->setUpdateVersion(Version::patch($release['version']));
                            $package->setUpdateUrl($release['url']);
                        }
                    }
                }
            }
        }

        return $packages;
    }

    /**
     * Get project data from drupal.org.
     *
     * @param string $project
     * @return array
     */
    private function getProjectData(string $project): array
    {
        $url = sprintf($this->projectReleaseHistoryUrl, $project);
        $xmlData = Data::fromXML(file_get_contents($url));
        $data = [];

        if (isset($xmlData['releases']['release'])) {
            foreach ($xmlData['releases']['release'] as $release) {
                $release = new DrupalRelease($release);

                if ($release->isStable()) {
                    $data[] = $release->toArray();
                }
            }
        }

        return $data;
    }
}
