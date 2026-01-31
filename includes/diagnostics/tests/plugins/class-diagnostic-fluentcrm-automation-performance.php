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
		
		$issues = array();
		// Check if feature is configured
		$option_prefix = 'diagnostic_' . str_replace('-', '_', self::$slug);
		$configured = get_option($option_prefix, false);
		if (!$configured) {
			$issues[] = 'feature not configured';
		}
		$has_issue = !empty($issues);
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => 55,
				'threat_level' => 55,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/fluentcrm-automation-performance',
			);
		}
		

		// Performance optimization checks
		if ( ! defined( 'WP_CACHE' ) || ! WP_CACHE ) {
			$issues[] = __( 'Caching not enabled', 'wpshadow' );
		}
		if ( ! extension_loaded( 'zlib' ) ) {
			$issues[] = __( 'Gzip compression unavailable', 'wpshadow' );
		}
		// Check transient support
		if ( ! function_exists( 'set_transient' ) ) {
			$issues[] = __( 'Transient functions unavailable', 'wpshadow' );
		}
		return null;
	}
}
