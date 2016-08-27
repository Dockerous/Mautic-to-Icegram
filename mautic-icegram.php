<?php
/**
 * Plugin Name: Mautic to Icegram
 * Description: Allow integration of Mautic forms into Icegram
 * Version: 1.0
 * Author: Dockerous
 * Author URI: http://do.ckero.us
 * License: GPL3 or Later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 */

require_once __DIR__ . '/log/Psr/Log/LoggerInterface.php';
require_once __DIR__ . '/log/Psr/Log/AbstractLogger.php';
require_once __DIR__ . '/log/Psr/Log/InvalidArgumentException.php';
require_once __DIR__ . '/log/Psr/Log/LogLevel.php';
require_once __DIR__ . '/log/Psr/Log/LoggerAwareInterface.php';
require_once __DIR__ . '/log/Psr/Log/LoggerAwareTrait.php';
require_once __DIR__ . '/log/Psr/Log/LoggerTrait.php';
require_once __DIR__ . '/log/Psr/Log/NullLogger.php';
require_once __DIR__ . '/api-ibrary/lib/Api/Api.php';
require_once __DIR__ . '/api-ibrary/lib/Api/Assets.php';
require_once __DIR__ . '/api-ibrary/lib/Api/Campaigns.php';
require_once __DIR__ . '/api-ibrary/lib/Api/Contacts.php';
require_once __DIR__ . '/api-ibrary/lib/Api/Data.php';
require_once __DIR__ . '/api-ibrary/lib/Api/Emails.php';
require_once __DIR__ . '/api-ibrary/lib/Api/Forms.php';
require_once __DIR__ . '/api-ibrary/lib/Api/Leads.php';
require_once __DIR__ . '/api-ibrary/lib/Api/Segments.php';
require_once __DIR__ . '/api-ibrary/lib/Api/Lists.php';
require_once __DIR__ . '/api-ibrary/lib/Api/Pages.php';
require_once __DIR__ . '/api-ibrary/lib/Api/PointTriggers.php';
require_once __DIR__ . '/api-ibrary/lib/Api/Points.php';
require_once __DIR__ . '/api-ibrary/lib/Api/Reports.php';
require_once __DIR__ . '/api-ibrary/lib/Auth/ApiAuth.php';
require_once __DIR__ . '/api-ibrary/lib/Auth/AuthInterface.php';
require_once __DIR__ . '/api-ibrary/lib/Auth/OAuth.php';
require_once __DIR__ . '/api-ibrary/lib/Exception/ActionNotSupportedException.php';
require_once __DIR__ . '/api-ibrary/lib/Exception/ContextNotFoundException.php';
require_once __DIR__ . '/api-ibrary/lib/Exception/IncorrectParametersReturnedException.php';
require_once __DIR__ . '/api-ibrary/lib/Exception/UnexpectedResponseFormatException.php';
require_once __DIR__ . '/api-ibrary/lib/MauticApi.php';
require_once __DIR__ . '/urlify/URLify.php';

