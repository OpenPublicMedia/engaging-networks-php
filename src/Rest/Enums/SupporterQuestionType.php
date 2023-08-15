<?php

namespace OpenPublicMedia\EngagingNetworksServices\Rest\Enums;

/**
 * Question types.
 *
 * @url https://www.engagingnetworks.support/api/rest/#/operations/listSupporterQuestions
 */
enum SupporterQuestionType: string
{
    case conf = 'conf';
    case gen = 'gen';
    case opt = 'opt';
}
