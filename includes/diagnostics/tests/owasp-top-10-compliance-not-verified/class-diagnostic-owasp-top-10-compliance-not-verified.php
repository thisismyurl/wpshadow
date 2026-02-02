<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if(!defined('ABSPATH'))exit;
class Diagnostic_OWASP_Top_10_Compliance_Not_Verified extends Diagnostic_Base{protected static $slug='owasp-top-10-compliance-not-verified';protected static $title='OWASP Top 10 Compliance Not Verified';protected static $description='Checks OWASP Top 10 compliance';protected static $family='security';public static function check(){if(!get_option('owasp_compliance_checked')){return array('id'=>self::$slug,'title'=>self::$title,'description'=>__('OWASP Top 10 not verified. Test against OWASP Top 10 vulnerabilities: injection, broken auth, XSS, XXE, broken access control, misconfig, sensitive data, XXE, poor logging, missing SSRF.','wpshadow'),'severity'=>'high','threat_level'=>85,'auto_fixable'=>false,'kb_link'=>'https://wpshadow.com/kb/owasp-top-10-compliance-not-verified');}return null;}}
