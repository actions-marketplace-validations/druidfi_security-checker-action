<?php

namespace App\Checker;

use App\Entity\DrupalRelease;
use App\Util\Data;
use App\Util\Version;

class DrupalChecker implements CheckerInterface
{
    public static string $corePackageName = 'drupal/core';

    private string $projectReleaseHistoryUrl = 'https://updates.drupal.org/release-history/%s/current';

    private array $ignoredPackages = [
        'drupal/core-composer-scaffold',
        'drupal/core-dev',
        'drupal/core-dev-pinned',
        'drupal/core-recommended',
        'drupal/core-project-message',
    ];

    public function check(array &$installed): void
    {
        foreach ($installed as $package_name => $data) {
            if (str_starts_with($package_name, 'drupal/') && !in_array($package_name, $this->ignoredPackages)) {
                $project = ($package_name === self::$corePackageName) ? 'drupal' : substr($package_name, 7);
                $releases = $this->getProjectData($project);

                foreach ($releases as $release) {
                    if (Version::isNew($release['version'], $data['current_version'], '>')) {
                        // Only the latest security update is marked as such in data from drupal.org
                        if ($release['security_update']) {
                            //$installed[$package_name]['updates'][] = $release;
                            $installed[$package_name]['update_to'] = Version::patch($release['version']);
                            $installed[$package_name]['read_more'] = $release['url'];
                        }
                    }
                }
            }
        }
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
