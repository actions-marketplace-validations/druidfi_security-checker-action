<?php

namespace App\List;

use App\Entity\Package;

class PackageList extends AbstractList
{
    public function add(Package $package): void
    {
        $this->offsetSet($package->getName(), $package);
    }

    public function remove(Package $package): void
    {
        $this->offsetUnset($package->getName());
    }

    public function getPackage(string $package_name): Package
    {
        return $this->offsetGet($package_name);
    }

    public function hasPackage(string $package_name): bool
    {
        return $this->offsetExists($package_name);
    }

    public function current(): Package
    {
        return current($this->data);
    }

    public function toArray(): array
    {
        $this->rewind();
        $data = [];

        foreach ($this->data as $package) {
            $data[$package->getName()] = [
                'current_version' => $package->getVersion(),
                'update_to' => $package->getUpdateVersion(),
                'read_more' => $package->getUpdateUrl(),
            ];
        }

        return $data;
    }
}
