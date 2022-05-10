<?php

namespace App\Checker;

use App\Entity\DrupalRelease;
use App\List\PackageList;
use App\Traits\LockFileAwareTrait;
use App\Util\Data;
use App\Util\Version;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class DrupalChecker implements CheckerInterface
{
    use LockFileAwareTrait;

    private string $coreExtensionsYaml = 'core.extension.yml';
    private string $corePackageName = 'drupal/core';
    private string $coreProjectName = 'drupal';
    private string $projectReleaseHistoryUrl = 'https://updates.drupal.org/release-history/%s/current';

    private array $ignoredPackages = [
        'drupal/core-composer-scaffold',
        'drupal/core-dev',
        'drupal/core-dev-pinned',
        'drupal/core-recommended',
        'drupal/core-project-message',
    ];

    public function shouldCheck(PackageList $packages): bool
    {
        return $packages->hasPackage($this->corePackageName);
    }

    public function check(PackageList $packages): PackageList
    {
        // Get only drupal projects from the package list.
        $drupalProjects = $this->getDrupalProjects($packages);

        // Cross-check the projects with the core.extension.yml file.
        $projects = $this->checkIfInstalled($drupalProjects);

        foreach ($projects as $project => $package) {
            /** @var DrupalRelease $release */
            foreach ($this->getProjectData($project) as $release) {
                if (Version::isNew($release->getVersion(), $package->getVersion())) {
                    // Only the latest security update is marked as such in data from drupal.org
                    if ($release->isSecurityUpdate()) {
                        $package->setHasUpdate(true);
                        $package->setUpdateVersion(Version::patch($release->getVersion()));
                        $package->setUpdateUrl($release->getUrl());

                        // Break from loop, we really care only about latest security update.
                        break;
                    }
                }
            }
        }

        return $packages;
    }

    private function checkIfInstalled(PackageList $drupalProjects): PackageList
    {
        $enabledProjects = $this->getEnabledProjects();

        foreach ($drupalProjects as $project => $package) {
            if ($project !== $this->coreProjectName && !in_array($project, $enabledProjects, true)) {
                $package->setIsInstalled(false);
            }
        }

        return $drupalProjects;
    }

    private function getEnabledProjects(): array
    {
        // Search recursively for core.extension.yml file.
        $directory = pathinfo($this->getLockFile(), PATHINFO_DIRNAME);
        $iterator = new RecursiveDirectoryIterator($directory);
        $files = new RecursiveIteratorIterator($iterator);
        $coreExtensionYml = false;

        foreach ($files as $file) {
            if ($file->getFilename() === $this->coreExtensionsYaml) {
                $coreExtensionYml = $file->getRealPath();
                break;
            }
        }

        if ($coreExtensionYml && file_exists($coreExtensionYml)) {
            $data = Data::fromYaml(file_get_contents($coreExtensionYml));

            return array_keys(array_merge($data['module'], $data['theme']));
        }

        return [];
    }

    private function getDrupalProjects(PackageList $packages): PackageList
    {
        $drupalProjects = new PackageList();

        foreach ($packages as $package_name => $package) {
            if ($package->startsWith($this->coreProjectName) && !in_array($package_name, $this->ignoredPackages)) {
                $project = ($package_name === $this->corePackageName) ? $this->coreProjectName : substr($package_name, 7);
                $drupalProjects->add($package, $project);
            }
        }

        return $drupalProjects;
    }

    /**
     * Get project stable releases from drupal.org.
     *
     * @param string $project
     * @return array
     */
    private function getProjectData(string $project): array
    {
        $url = sprintf($this->projectReleaseHistoryUrl, $project);
        $xmlData = Data::fromXML(file_get_contents($url));
        $releases = [];

        if (isset($xmlData['releases']['release'])) {
            foreach ($xmlData['releases']['release'] as $release) {
                $release = new DrupalRelease($release);

                if ($release->isStable()) {
                    $releases[] = $release;
                }
            }
        }

        return $releases;
    }
}
