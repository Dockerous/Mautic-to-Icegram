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

require_once 'admin/options.php';
global $mtc_to_ig_options;
$mtc_to_ig_options = new MTI_Base_Options("mtc_to_ig");
require_once 'classes/ClassBase.php';
require_once 'classes/ClassMauticForm.php';
require_once 'admin/menu_class.php';
require_once 'admin/main_menu.php';
global $mtc_to_ig_main_menu;
$mtc_to_ig_main_menu = new MTC_to_IG_Main_Menu($mtc_to_ig_options);
require_once 'admin/form_menu.php';
global $mtc_to_ig_form_menu;
$mtc_to_ig_form_menu = new MTC_to_IG_Form_Menu($mtc_to_ig_options);
require_once 'post_type_base.php';
require_once 'post_type_mautic_form.php';
global $mautic_form_post_type;
$mautic_form_post_type = new Post_Type_Mautic_Form($mtc_to_ig_options);
