<?php

namespace OpenPublicMedia\EngagingNetworksServices\Rest\Enums;

/**
 * Page request result types.
 *
 * @url https://www.engagingnetworks.support/api/rest/#/operations/processPage
 */
enum PageRequestResultType: string
{
    case creditSingle = 'credit_single';
    case creditRecurring = 'credit_recurring';
    case unmanagedRecurring = 'recur_unmanaged';
}
