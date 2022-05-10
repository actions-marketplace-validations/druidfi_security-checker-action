<?php

namespace App\Service;

use App\List\PackageList;

class MarkdownService
{
    public static function render(PackageList $updates): string
    {
        $hasUpdates = $updates->count();

        if ($hasUpdates === 0) {
            return 'No updates available.';
        }

        $hasDrupal = false;
        $markdown_security_updates = [];
        $markdown_not_installed = [];
        $markdown_footnote = [];

        foreach ($updates as $package_name => $package) {
            if ($package->isInstalled()) {
                $markdown_security_updates[] = sprintf(
                    "- `%s` from %s to [%s](%s)\n",
                    $package_name,
                    $package->getVersion(),
                    $package->getUpdateVersion(),
                    $package->getUpdateUrl()
                );
            }
            else {
                $markdown_not_installed[] = sprintf(
                    "- `%s`\n",
                    $package_name
                );
            }

            if ($package_name === 'drupal/core') {
                $hasDrupal = true;
            }
        }

        if ($hasDrupal) {
            $markdown_footnote[] = "\nAs Drupal core has security updates that might indicate that updating Drupal core";
            $markdown_footnote[] = " will solve some of these updates. You should start the updates from Drupal core.";
        }

        $markdown = '';

        if (count($markdown_security_updates) > 0) {
            $markdown .= "## Security updates available\n\n";
            $markdown .= join("", $markdown_security_updates);
        }

        if (count($markdown_not_installed) > 0) {
            $markdown .= "\n## Packages not installed\n\n";
            $markdown .= join("", $markdown_not_installed);
        }

        if (count($markdown_footnote) > 0) {
            $markdown .= join("", $markdown_footnote);
        }

        return $markdown;
    }
}
