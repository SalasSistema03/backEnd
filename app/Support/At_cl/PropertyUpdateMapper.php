<?php

namespace App\Support\At_cl;

final class PropertyUpdateMapper
{
    /**
     * Maps only supplied payload keys to database columns.
     * Explicit null values are retained to allow intentional clearing.
     */
    public static function map(array $payload, array $fieldMap): array
    {
        $updates = [];

        foreach ($fieldMap as $payloadKey => $column) {
            if (array_key_exists($payloadKey, $payload)) {
                $updates[$column] = $payload[$payloadKey];
            }
        }

        return $updates;
    }
}
