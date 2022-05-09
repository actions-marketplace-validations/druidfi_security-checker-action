<?php

namespace App\Traits;

trait LockFileAwareTrait
{
    private string $lockFile;

    public function getLockFile(): string
    {
        return $this->lockFile;
    }

    public function setLockFile(string $lockFile): self
    {
        $this->lockFile = $lockFile;

        return $this;
    }
}
