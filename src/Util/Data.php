<?php

namespace App\Util;

use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\YamlEncoder;

class Data
{
    public static function fromXML(string $xml): array
    {
        $result = (new XmlEncoder())->decode($xml, 'xml');

        return (is_array($result)) ? $result : [];
    }

    public static function fromJSON(string $json): array
    {
        return (new JsonEncoder())->decode($json, 'json');
    }

    public static function fromYaml(string $yaml): array
    {
        return (new YamlEncoder())->decode($yaml, 'yaml');
    }
}
