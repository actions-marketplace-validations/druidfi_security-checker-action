<?php

namespace App\Service;

class MarkdownService
{
    public static function render(array $updates): string
    {
        $hasDrupal = false;
        $markdown = "## Security updates available\n\n";

        foreach ($updates as $package_name => $data) {
            $markdown .= sprintf(
                "- `%s` from %s to [%s](%s)\n",
                $package_name,
                $data['current_version'],
                $data['update_to'],
                $data['read_more']
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
