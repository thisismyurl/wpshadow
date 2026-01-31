<?php
/**
 * Restaurant Reservations Email Diagnostic
 *
 * Restaurant notification emails misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.600.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Restaurant Reservations Email Diagnostic Class
 *
 * @since 1.600.0000
 */
class Diagnostic_RestaurantReservationsEmail extends Diagnostic_Base {

	protected static $slug = 'restaurant-reservations-email';
	protected static $title = 'Restaurant Reservations Email';
	protected static $description = 'Restaurant notification emails misconfigured';
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
		$has_issue = !empty($issues);
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 50 ),
				'threat_level' => 50,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/restaurant-reservations-email',
			);
		}
		
		return null;
	}
}
