<?php

namespace App\Entity;

class DrupalRelease
{
    private array $data;
    private string $version;
    private string $status;
    private array $terms;
    private string $url;

    public function __construct(array $data = [])
    {
        $this->data = $data;
        $this->version = $data['version'] ?? '';
        $this->status = $data['status'] ?? '';
        $this->terms = $data['terms'] ?? [];
        $this->url = $data['release_link'] ?? '';
    }

    public function toArray(): array
    {
        return [
            'version' => $this->version,
            'security_update' => $this->isSecurityUpdate(),
            'url' => $this->url,
            //'data' => $this->data,
        ];
    }

    public function isStable(): bool
    {
        if (preg_match('/(dev|alpha|beta|rc|unstable)/', $this->version)) {
            return false;
        }

        return $this->status === 'published';
    }

    private function isInsecure(): bool
    {
        foreach ($this->terms as $term) {
            if (isset($term['value']) && $term['value'] === 'Insecure') {
                return true;
            }
        }

        return false;
    }

    private function isSecurityUpdate(): bool
    {
        foreach ($this->terms as $term) {
            if (isset($term['value']) && $term['value'] === 'Security update') {
                return true;
            }
        }

        return false;
    }
}
