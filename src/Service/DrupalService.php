<?php

namespace App\Service;

use App\Entity\DrupalCoreRelease;
use Symfony\Component\Serializer\Encoder\XmlEncoder;

class DrupalService
{
    private string $drupalCoreDataUrl = 'https://updates.drupal.org/release-history/drupal/current';
    private array $coreReleases = [];

    public function __construct()
    {
        $xml = new XmlEncoder();
        $drupalData = $xml->decode(file_get_contents($this->drupalCoreDataUrl), 'xml');

        foreach ($drupalData['releases']['release'] as $release) {
            $drupalCoreRelease = new DrupalCoreRelease($release);

            if ($drupalCoreRelease->isStable()) {
                $this->coreReleases[] = $drupalCoreRelease->toArray();
            }
        }
    }

    public function getCoreReleases(): array
    {
        return $this->coreReleases;
    }
}
