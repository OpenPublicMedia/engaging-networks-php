<?php

namespace OpenPublicMedia\EngagingNetworksServices\Rest\Enums;

/**
 * Question HTML field types.
 *
 * @url https://www.engagingnetworks.support/api/rest/#/operations/listSupporterQuestions
 */
enum SupporterQuestionHtmlFieldType: string
{
    case calendar = 'calendar';
    case checkbox = 'checkbox';
    case hidden = 'hidden';
    case imgselect = 'imgselect';
    case password = 'password';
    case radio = 'radio';
    case select = 'select';
    case telephone = 'telephone';
    case text = 'text';
    case textarea = 'textarea';
}
