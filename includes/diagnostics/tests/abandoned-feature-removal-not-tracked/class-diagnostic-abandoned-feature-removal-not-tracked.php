<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if(!defined('ABSPATH'))exit;
class Diagnostic_Abandoned_Feature_Removal_Not_Tracked extends Diagnostic_Base{protected static $slug='abandoned-feature-removal-not-tracked';protected static $title='Abandoned Feature Removal Not Tracked';protected static $description='Checks feature removal';protected static $family='functionality';public static function check(){if(!has_filter('init','track_abandoned_features')){return array('id'=>self::$slug,'title'=>self::$title,'description'=>__('Abandoned feature removal not tracked. Deprecate features gradually with notices before removal.','wpshadow'),'severity'=>'low','threat_level'=>5,'auto_fixable'=>false,'kb_link'=>'https://wpshadow.com/kb/abandoned-feature-removal-not-tracked');}return null;}}
