<?php

namespace App\Entity;

class DrupalCoreRelease
{
    private string $version;
    private string $status;
    private array $terms = [];

    public function __construct(array $data = [])
    {
        $this->version = $data['version'] ?? '';
        $this->status = $data['status'] ?? '';
        $this->terms = $data['terms'] ?? [];
    }

    public function toArray(): array
    {
        return [
            'version' => $this->version,
            'stable' => $this->isStable(),
            'insecure' => $this->isInsecure(),
            'security_update' => $this->isSecurityUpdate(),
        ];
    }

    public function isStable(): bool
    {
        if (preg_match('/(dev|alpha|beta|rc)/', $this->version)) {
            return false;
        }

        return $this->status === 'published';
    }

    private function isInsecure(): bool
    {
        foreach ($this->terms as $term) {
            if ($term['value'] === 'Insecure') {
                return true;
            }
        }

        return false;
    }

    private function isSecurityUpdate(): bool
    {
        foreach ($this->terms as $term) {
            if ($term['value'] === 'Security update') {
                return true;
            }
        }

        return false;
    }
}
