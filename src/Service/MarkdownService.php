<?php

namespace App\Service;

class MarkdownService
{
    public static function render(array $updates): string
    {
        $markdown = "# Security updates available\n\n";

        foreach ($updates as $package_name => $data) {
            #$markdown .= sprintf("\n\n## %s", $package_name);
            $markdown .= sprintf(
                "- [ ] %s from %s to %s\n",
                $package_name,
                $data['current_version'],
                $data['update_to']
            );
        }

        return $markdown;
    }
}
