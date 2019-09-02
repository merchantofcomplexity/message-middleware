<?php
declare(strict_types=1);

namespace MerchantOfComplexity\MessageMiddleware\Support\Contracts;

interface RequestMessage
{
    const MESSAGE_PAYLOAD_KEY = 'message_name';

    const COMMAND_PREFIX = 'command:';

    const QUERY_PREFIX = 'query:';

    const EVENT_PREFIX = 'event:';
 }
