<?php

namespace OpenPublicMedia\EngagingNetworksServices\Enums;

/**
 * Page statuses
 *
 * @url https://www.engagingnetworks.support/api/rest/#/operations/listPages
 */
enum PageStatus: string
{
    case block = 'block';
    case close = 'close';
    case delete = 'delete';
    case live = 'live';
    case new = 'new';
    case tested = 'tested';
}
