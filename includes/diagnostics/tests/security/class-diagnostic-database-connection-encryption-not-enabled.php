<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if(!defined('ABSPATH'))exit;
class Diagnostic_Database_Connection_Encryption_Not_Enabled extends Diagnostic_Base{protected static $slug='database-connection-encryption-not-enabled';protected static $title='Database Connection Encryption Not Enabled';protected static $description='Checks DB encryption';protected static $family='security';public static function check(){if(!has_filter('init','verify_db_encryption')){return array('id'=>self::$slug,'title'=>self::$title,'description'=>__('Database connection encryption not enabled. Use SSL/TLS for all database connections to prevent eavesdropping.','wpshadow'),'severity'=>'high','threat_level'=>80,'auto_fixable'=>false,'kb_link'=>'https://wpshadow.com/kb/database-connection-encryption-not-enabled');}return null;}}
