<?php

namespace OpenPublicMedia\EngagingNetworksServices\Rest\Enums;

/**
 * Recurring frequencies
 *
 * @url https://www.engagingnetworks.support/api/rest/#/operations/processPage
 */
enum RecurringFrequency: string
{
    case annual = 'annual';
    case daily = 'daily';
    case monthly = 'monthly';
    case quarterly = 'quarterly';
    case semiAnnual = 'semi_annual';
}
