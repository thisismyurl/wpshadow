<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if(!defined('ABSPATH'))exit;
class Diagnostic_Backup_Immutability_Not_Enforced extends Diagnostic_Base{protected static $slug='backup-immutability-not-enforced';protected static $title='Backup Immutability Not Enforced';protected static $description='Checks backup immutability';protected static $family='functionality';public static function check(){if(!get_option('backup_immutable_mode')){return array('id'=>self::$slug,'title'=>self::$title,'description'=>__('Backup immutability not enforced. Use write-once storage for backups to prevent accidental deletion or tampering.','wpshadow'),'severity'=>'high','threat_level'=>70,'auto_fixable'=>false,'kb_link'=>'https://wpshadow.com/kb/backup-immutability-not-enforced');}return null;}}
