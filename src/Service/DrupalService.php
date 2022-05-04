<?php

namespace App\Service;

use App\Entity\DrupalRelease;
use App\Util\Version;
use Symfony\Component\Serializer\Encoder\XmlEncoder;

class DrupalService
{
    private string $drupalCoreDataUrl = 'https://updates.drupal.org/release-history/%s/current';

    public function getUpdates(array $installed): array
    {
        foreach ($installed as $package_name => $data) {
            if (str_starts_with($package_name, 'drupal/') && !str_starts_with($package_name, 'drupal/core-')) {
                if ($package_name === 'drupal/core') {
                    $project = 'drupal';
                } else {
                    $project = substr($package_name, 7);
                }

                $releases = $this->getData($project);

                foreach ($releases as $release) {
                    if (Version::isNew($release['version'], $data['current_version'], '>')) {
                        // Only the latest security update is marked as such in data from drupal.org
                        if ($release['security_update']) {
                            //$installed[$package_name]['updates'][] = $release;
                            $installed[$package_name]['update_to'] = $release['version'];
                            $installed[$package_name]['read_more'] = $release['url'];
                }
                    }
                }
            }
        }

        return $installed;
    }

    private function getData(string $project = 'drupal'): array
    {
        $url = sprintf($this->drupalCoreDataUrl, $project);
        $xmlData = (new XmlEncoder())->decode(file_get_contents($url), 'xml');
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
