<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if(!defined('ABSPATH'))exit;
class Diagnostic_Knowledge_Base_Integration_Not_Configured extends Diagnostic_Base{protected static $slug='knowledge-base-integration-not-configured';protected static $title='Knowledge Base Integration Not Configured';protected static $description='Checks KB integration';protected static $family='admin';public static function check(){if(!get_option('knowledge_base_integrated')){return array('id'=>self::$slug,'title'=>self::$title,'description'=>__('Knowledge base integration not configured. Link documentation to help users self-serve and reduce support tickets.','wpshadow'),'severity'=>'low','threat_level'=>10,'auto_fixable'=>false,'kb_link'=>'https://wpshadow.com/kb/knowledge-base-integration-not-configured');}return null;}}
