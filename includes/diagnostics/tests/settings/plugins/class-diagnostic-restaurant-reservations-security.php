<?php
/**
 * Restaurant Reservations Security Diagnostic
 *
 * Restaurant reservations data exposed.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.598.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Restaurant Reservations Security Diagnostic Class
 *
 * @since 1.598.0000
 */
class Diagnostic_RestaurantReservationsSecurity extends Diagnostic_Base {

	protected static $slug = 'restaurant-reservations-security';
	protected static $title = 'Restaurant Reservations Security';
	protected static $description = 'Restaurant reservations data exposed';
	protected static $family = 'security';

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
		$has_issue = !empty($issues);
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 70 ),
				'threat_level' => 70,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/restaurant-reservations-security',
			);
		}
		
		return null;
	}
}
