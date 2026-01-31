<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if(!defined('ABSPATH'))exit;
class Diagnostic_Malicious_File_Upload_Not_Prevented extends Diagnostic_Base{protected static $slug='malicious-file-upload-not-prevented';protected static $title='Malicious File Upload Not Prevented';protected static $description='Checks file upload';protected static $family='security';public static function check(){if(!has_filter('init','validate_file_uploads')){return array('id'=>self::$slug,'title'=>self::$title,'description'=>__('Malicious file upload not prevented. Validate file type, size, and content. Store uploads outside webroot. Disable script execution.','wpshadow'),'severity'=>'high','threat_level'=>85,'auto_fixable'=>false,'kb_link'=>'https://wpshadow.com/kb/malicious-file-upload-not-prevented');}return null;}}
