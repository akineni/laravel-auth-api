<?php

namespace App\Helpers;

class GeneralHelper
{
    public static function formatJsonArrayToTitles(array|string|null $jsonData): array
    {
        if (empty($jsonData)) {
            return [];
        }
        
        if (is_string($jsonData)) {
            $jsonData = json_decode($jsonData, true);
        }
        
        if (!is_array($jsonData) || empty($jsonData)) {
            return [];
        }
        
        $formatted = [];
        foreach ($jsonData as $key => $value) {
            $formattedKey = Str::title($key);
            
            if (is_string($value)) {
                $formatted[$formattedKey] = Str::title($value);
            } elseif (is_array($value)) {
                // Recursively format nested arrays
                $formatted[$formattedKey] = self::formatJsonArrayToTitles($value);
            } else {
                $formatted[$formattedKey] = $value;
            }
        }
        
        return $formatted;
    }
}
