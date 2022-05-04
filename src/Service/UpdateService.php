<?php

namespace App\Service;

use App\Util\Version;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

class UpdateService
{
    public function checkUpdates(): void
    {
        $installed = $this->getInstalledPackages();

        if (isset($installed['drupal/core'])) {
            $drupalService = new DrupalService();
            $installed = $drupalService->getUpdates($installed);
        }

        $installed = $this->getSecurityAdvisories($installed);

        foreach ($installed as $package => $data) {
            if (!array_key_exists('update_to', $data)) {
                unset($installed[$package]);
            }
        }

        print_r($installed);
        //echo (new JsonEncoder())->encode($installed, JsonEncoder::FORMAT);
    }

    private function getInstalledPackages(): array
    {
        $data = (new JsonEncoder())->decode(file_get_contents('composer.lock'), 'json');
        $installed = [];

        foreach ($data['packages'] as $package) {
            $installed[$package['name']]['current_version'] = $package['version'];
        }

        return $installed;
    }

    private function getSecurityAdvisories(array $installed): array
    {
        // lpsc is the command added to Docker image to run the security check
        $json = shell_exec('lpsc --format json');
        $json = str_replace('::set-output name=vulns::', '', $json);
        $data = (new JsonEncoder())->decode($json, 'json');

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

        return $installed;
    }

    private function getPackageUpdates(string $package_name, string $current_version): array
    {
        $url = sprintf('https://repo.packagist.org/p2/%s.json', $package_name);
        $data = (new JsonEncoder())->decode(file_get_contents($url), 'json');
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
