<?php
declare(strict_types=1);

namespace MerchantOfComplexity\MessageMiddleware\Support;

use Illuminate\Http\Request;
use MerchantOfComplexity\MessageMiddleware\Support\Contracts\MetadataGatherer;

final class NoOpMetadataGatherer implements MetadataGatherer
{
    public function extractFromRequest(Request $request): array
    {
        return [];
    }
}
