<?php

namespace OpenPublicMedia\EngagingNetworksServices\Rest\Enums;

/**
 * Payment types
 *
 * @url https://www.engagingnetworks.support/api/rest/#/operations/processPage
 */
enum PaymentType: string
{
    case ach = 'ach';
    case acheft = 'acheft';
    case americanExpress = 'american express';
    case americanExpressSsl = 'amex-ssl';
    case amex = 'amex';
    case amx = 'amx';
    case ax = 'ax';
    case bacs = 'bacs';
    case bacsDebit = 'bacs_debit';
    case bank = 'bank';
    case card = 'card';
    case cartesBancaires = 'cartes_bancaires';
    case checking = 'checking';
    case ddt = 'ddt';
    case delta = 'delta';
    case di = 'di';
    case diners = 'diners';
    case directDebit = 'direct debit';
    case discover = 'discover';
    case eCheck = 'echeck';
    case ec = 'ec';
    case ecmcSsl = 'ecmc-ssl';
    case eft = 'eft';
    case jcb = 'jcb';
    case master = 'master';
    case mastercard = 'mastercard';
    case mc = 'mc';
    case payPal = 'paypal';
    case savings = 'savings';
    case sepa = 'sepa';
    case sepaDebit = 'sepa_debit';
    case unionpay = 'unionpay';
    case venmo = 'venmo';
    case vi = 'vi';
    case visa = 'visa';
    case visaElectron = 'visa electron';
    case visaSsl = 'visa-ssl';
}
