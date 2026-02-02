<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if(!defined('ABSPATH'))exit;
class Diagnostic_Subdomain_Takeover_Risk_Not_Mitigated extends Diagnostic_Base{protected static $slug='subdomain-takeover-risk-not-mitigated';protected static $title='Subdomain Takeover Risk Not Mitigated';protected static $description='Checks subdomain takeover';protected static $family='security';public static function check(){if(!has_filter('init','mitigate_subdomain_takeover')){return array('id'=>self::$slug,'title'=>self::$title,'description'=>__('Subdomain takeover risk not mitigated. Remove unused DNS records and document all subdomains with owners.','wpshadow'),'severity'=>'high','threat_level'=>70,'auto_fixable'=>false,'kb_link'=>'https://wpshadow.com/kb/subdomain-takeover-risk-not-mitigated');}return null;}}
