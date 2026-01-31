<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if(!defined('ABSPATH'))exit;
class Diagnostic_JWT_Token_Validation_Not_Implemented extends Diagnostic_Base{protected static $slug='jwt-token-validation-not-implemented';protected static $title='JWT Token Validation Not Implemented';protected static $description='Checks JWT validation';protected static $family='security';public static function check(){if(!has_filter('init','validate_jwt_tokens')){return array('id'=>self::$slug,'title'=>self::$title,'description'=>__('JWT token validation not implemented. Always verify signature, expiration, and algorithm when using JWT tokens.','wpshadow'),'severity'=>'high','threat_level'=>75,'auto_fixable'=>false,'kb_link'=>'https://wpshadow.com/kb/jwt-token-validation-not-implemented');}return null;}}
