<?php

namespace App\Entity;

class DrupalRelease
{
    private string $version;
    private string $status;
    private array $terms;
    private string $url;

    public function __construct(array $data = [])
    {
        $this->version = $data['version'] ?? '';
        $this->status = $data['status'] ?? '';
        $this->terms = $data['terms'] ?? [];
        $this->url = $data['release_link'] ?? '';
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function isStable(): bool
    {
        if (preg_match('/(dev|alpha|beta|rc|unstable)/', $this->version)) {
            return false;
        }

        return $this->status === 'published';
    }

    public function isSecurityUpdate(): bool
    {
        foreach ($this->terms as $term) {
            if (isset($term['value']) && $term['value'] === 'Security update') {
                return true;
            }
        }

        return false;
    }
}
