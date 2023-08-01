<?php

namespace OpenPublicMedia\EngagingNetworksServices\Enums;

/**
 * Page types and subtypes
 *
 * The type `dc` is undocumented but included here as it seems to be a parent
 * type to at least `ems`.
 *
 * @url https://www.engagingnetworks.support/api/rest/#/operations/listPages
 */
enum PageType: string
{
    case cc = 'cc';
    case dc = 'dc';
    case dcf = 'dcf';
    case ec = 'ec';
    case ecommerce = 'ecommerce';
    case ems = 'ems';
    case et = 'et';
    case ev = 'ev';
    case leadgen = 'leadgen';
    case mem = 'mem';
    case nd = 'nd';
    case pet = 'pet';
    case premium = 'premium';
    case sh = 'sh';
    case sp = 'sp';
    case ss = 'ss';
    case survey = 'survey';
    case tp = 'tp';
    case unsub = 'unsub';
}
