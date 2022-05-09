<?php

namespace App\Service;

use App\List\PackageList;

class MarkdownService
{
    public static function render(PackageList $updates): string
    {
        $hasDrupal = false;
        $markdown = "## Security updates available\n\n";

        foreach ($updates as $package_name => $package) {
            $markdown .= sprintf(
                "- `%s` from %s to [%s](%s)\n",
                $package_name,
                $package->getVersion(),
                $package->getUpdateVersion(),
                $package->getUpdateUrl()
            );

            if ($package_name === 'drupal/core') {
                $hasDrupal = true;
            }
        }

        if ($hasDrupal) {
            $markdown .= "\nAs Drupal core has security updates that might indicate that updating Drupal core will";
            $markdown .= " solve some of these updates. You should start the updates from Drupal core.";
        }

        return $markdown;
    }
}
