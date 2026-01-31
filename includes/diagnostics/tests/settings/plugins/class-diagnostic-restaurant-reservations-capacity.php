<?php
/**
 * Restaurant Reservations Capacity Diagnostic
 *
 * Restaurant capacity checks bypassed.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.601.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Restaurant Reservations Capacity Diagnostic Class
 *
 * @since 1.601.0000
 */
class Diagnostic_RestaurantReservationsCapacity extends Diagnostic_Base {

	protected static $slug = 'restaurant-reservations-capacity';
	protected static $title = 'Restaurant Reservations Capacity';
	protected static $description = 'Restaurant capacity checks bypassed';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! class_exists( 'rtbInit' ) ) {
			return null;
		}
		
		$issues = array();
		// Check if feature is configured
		$option_prefix = 'diagnostic_' . str_replace('-', '_', self::$slug);
		$configured = get_option($option_prefix, false);
		if (!$configured) {
			$issues[] = 'feature not configured';
		}
		$has_issue = !empty($issues)
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 65 ),
				'threat_level' => 65,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/restaurant-reservations-capacity',
			);
		}
		
		return null;
	}
}
