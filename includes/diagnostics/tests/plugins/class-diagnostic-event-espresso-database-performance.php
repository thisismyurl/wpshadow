<?php
/**
 * Event Espresso Database Performance Diagnostic
 *
 * Event Espresso database slow.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.590.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Event Espresso Database Performance Diagnostic Class
 *
 * @since 1.590.0000
 */
class Diagnostic_EventEspressoDatabasePerformance extends Diagnostic_Base {

	protected static $slug = 'event-espresso-database-performance';
	protected static $title = 'Event Espresso Database Performance';
	protected static $description = 'Event Espresso database slow';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'EVENT_ESPRESSO_VERSION' ) ) {
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
				'severity'    => self::calculate_severity( 55 ),
				'threat_level' => 55,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/event-espresso-database-performance',
			);
		}
		
		return null;
	}
}
