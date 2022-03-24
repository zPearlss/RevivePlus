<?php

namespace zPearlss\RevivePlus\utils;

use zPearlss\RevivePlus\RevivePlus;

class ConfigUtils
{

    public static function get(string $key, array $replaces = []): string
    {
        if (RevivePlus::getInstance()->getConfig()->getNested($key) === null) {
            return $key;
        } else $message = RevivePlus::getInstance()->getConfig()->getNested($key);

        foreach ($replaces as $replace => $value) {
            $message = str_replace("{" . $replace . "}", $value, $message);
        }

        return str_replace("&", "§", $message);
    }
}