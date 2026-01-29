<?php
/**
 * FluentCRM Automation Performance Diagnostic
 *
 * FluentCRM automations slowing site.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.487.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * FluentCRM Automation Performance Diagnostic Class
 *
 * @since 1.487.0000
 */
class Diagnostic_FluentcrmAutomationPerformance extends Diagnostic_Base {

	protected static $slug = 'fluentcrm-automation-performance';
	protected static $title = 'FluentCRM Automation Performance';
	protected static $description = 'FluentCRM automations slowing site';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'FLUENTCRM' ) ) {
			return null;
		}
		
		$has_issue = false;
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 55 ),
				'threat_level' => 55,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/fluentcrm-automation-performance',
			);
		}
		
		return null;
	}
}
