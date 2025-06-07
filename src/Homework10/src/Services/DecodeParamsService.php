<?php

namespace App\Services;

use App\Exceptions\ErrorDecodeParams;

class DecodeParamsService implements DecodeParamsInterface
{
    /**
     * @throws ErrorDecodeParams
     */
    public function decode(string $jsonStr): array
    {
        $data = json_decode($jsonStr, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new ErrorDecodeParams('Error decode json params: ' . json_last_error_msg());
        }
        if (empty($data) || !is_array($data)) {
            throw new ErrorDecodeParams('Params must not be empty array');
        }

        return $data;
    }
}
