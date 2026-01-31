<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if(!defined('ABSPATH'))exit;
class Diagnostic_Weak_SSL_TLS_Configuration extends Diagnostic_Base{protected static $slug='weak-ssl-tls-configuration';protected static $title='Weak SSL/TLS Configuration';protected static $description='Checks SSL/TLS config';protected static $family='security';public static function check(){if(!has_filter('init','validate_ssl_tls_strength')){return array('id'=>self::$slug,'title'=>self::$title,'description'=>__('Weak SSL/TLS configuration detected. Use TLS 1.2+ and disable deprecated ciphers and protocols.','wpshadow'),'severity'=>'high','threat_level'=>80,'auto_fixable'=>false,'kb_link'=>'https://wpshadow.com/kb/weak-ssl-tls-configuration');}return null;}}
