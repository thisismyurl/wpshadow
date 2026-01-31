<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if(!defined('ABSPATH'))exit;
class Diagnostic_Incident_Response_Plan_Not_Documented extends Diagnostic_Base{protected static $slug='incident-response-plan-not-documented';protected static $title='Incident Response Plan Not Documented';protected static $description='Checks incident response';protected static $family='admin';public static function check(){if(!get_option('incident_response_plan_documented')){return array('id'=>self::$slug,'title'=>self::$title,'description'=>__('Incident response plan not documented. Create playbooks for common security incidents and test procedures.','wpshadow'),'severity'=>'medium','threat_level'=>40,'auto_fixable'=>false,'kb_link'=>'https://wpshadow.com/kb/incident-response-plan-not-documented');}return null;}}
