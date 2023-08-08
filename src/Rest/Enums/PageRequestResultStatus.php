<?php

namespace OpenPublicMedia\EngagingNetworksServices\Rest\Enums;

/**
 * Page request result statuses.
 *
 * @url https://www.engagingnetworks.support/api/rest/#/operations/processPage
 */
enum PageRequestResultStatus: string
{
    case success = 'success';
    case error = 'error';
}
