<?php
declare(strict_types=1);

namespace MerchantOfComplexity\MessageMiddleware\Support\Contracts;

use Illuminate\Http\Request;

interface MetadataGatherer
{
    /**
     * Return data from request
     *
     * @param Request $request
     * @return array
     */
    public function extractFromRequest(Request $request): array;
}
