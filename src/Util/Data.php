<?php

namespace App\Util;

use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;

class Data
{
    public static function fromXML(string $xml): array
    {
        return (new XmlEncoder())->decode($xml, 'xml');
    }

    public static function fromJSON(string $json): array
    {
        return (new JsonEncoder())->decode($json, 'json');
    }
}
