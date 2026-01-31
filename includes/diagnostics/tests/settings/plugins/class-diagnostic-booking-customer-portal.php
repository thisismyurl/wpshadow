<?php
/**
 * Booking Customer Portal Diagnostic
 *
 * Booking portal permissions wrong.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.625.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Booking Customer Portal Diagnostic Class
 *
 * @since 1.625.0000
 */
class Diagnostic_BookingCustomerPortal extends Diagnostic_Base {

	protected static $slug = 'booking-customer-portal';
	protected static $title = 'Booking Customer Portal';
	protected static $description = 'Booking portal permissions wrong';
	protected static $family = 'security';

	public static function check() {
		if ( ! true // Generic plugin check ) {
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
				'kb_link'     => 'https://wpshadow.com/kb/booking-customer-portal',
			);
		}
		
		return null;
	}
}
