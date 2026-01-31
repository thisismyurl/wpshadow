<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if(!defined('ABSPATH'))exit;
class Diagnostic_Backup_Encryption_Not_Enabled extends Diagnostic_Base{protected static $slug='backup-encryption-not-enabled';protected static $title='Backup Encryption Not Enabled';protected static $description='Checks backup encryption';protected static $family='security';public static function check(){if(!get_option('backup_encryption_enabled')){return array('id'=>self::$slug,'title'=>self::$title,'description'=>__('Backup encryption not enabled. Encrypt all backups at rest using AES-256 or similar.','wpshadow'),'severity'=>'high','threat_level'=>80,'auto_fixable'=>false,'kb_link'=>'https://wpshadow.com/kb/backup-encryption-not-enabled');}return null;}}
