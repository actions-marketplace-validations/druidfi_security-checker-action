<?php

namespace App\Service;

class MarkdownService
{
    public static function render(array $updates): string
    {
        $markdown = "## Security updates available\n\n";

        foreach ($updates as $package_name => $data) {
            $markdown .= sprintf(
                "- `%s` from %s to [%s](%s)\n",
                $package_name,
                $data['current_version'],
                $data['update_to'],
                $data['read_more']
            );
        }

        return $markdown;
    }
}
