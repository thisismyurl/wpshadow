<?php
/**
 * Business Hours and Holiday Schedule Diagnostic
 *
 * Detects when business hours and holiday schedules aren't clearly displayed.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Content
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics\Content;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Business Hours Holiday Schedule Diagnostic Class
 *
 * Checks if business hours and holiday schedules are clearly communicated.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Business_Hours_Holiday_Schedule extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'business-hours-holiday-schedule';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'No Business Hours or Holiday Schedule';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects when business hours and holiday schedules aren\'t displayed';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'content';

	/**
	 * Run the diagnostic check
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array or null if no issues found.
	 */
	public static function check() {
		// Check for business hours plugins.
		$hours_plugins = array(
			'opening-hours/opening-hours.php',
			'business-hours-indicator/business-hours-indicator.php',
			'we-are-open/we-are-open.php',
			'simple-business-hours/simple-business-hours.php',
		);

		$has_hours_plugin = false;
		foreach ( $hours_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_hours_plugin = true;
				break;
			}
		}

		if ( $has_hours_plugin ) {
			return null; // Business hours plugin installed.
		}

		// Check key pages for hours-related keywords.
		$pages_to_check = array( 'contact', 'about', 'locations', 'hours' );
		$hours_keywords = array( 'hours', 'open', 'closed', 'monday', 'tuesday', 'schedule', 'availability' );

		$has_hours_content = false;
		foreach ( $pages_to_check as $slug ) {
			$page = get_page_by_path( $slug );
			if ( ! $page ) {
				continue;
			}

			$content = strtolower( $page->post_content );
			foreach ( $hours_keywords as $keyword ) {
				if ( strpos( $content, $keyword ) !== false ) {
					$has_hours_content = true;
					break 2;
				}
			}
		}

		if ( $has_hours_content ) {
			return null; // Hours mentioned on site.
		}

		// Check if this is a business that needs hours (WooCommerce, bookings, etc.).
		$needs_hours_plugins = array(
			'woocommerce/woocommerce.php'          => 'WooCommerce (Physical Store)',
			'easy-appointments/easy-appointments.php' => 'Appointment Booking',
			'bookly-responsive-appointment-booking-tool/main.php' => 'Bookly Appointments',
			'restaurant-reservations/restaurant-reservations.php' => 'Restaurant Reservations',
		);

		$business_type = array();
		foreach ( $needs_hours_plugins as $plugin => $type ) {
			if ( is_plugin_active( $plugin ) ) {
				$business_type[] = $type;
			}
		}

		// If not a business needing hours, less critical.
		if ( empty( $business_type ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'Visitors don\'t know when you\'re open, what your hours are, or when you\'re closed for holidays. This leads to frustration and missed opportunities', 'wpshadow' ),
			'severity'     => 'medium',
			'threat_level' => 50,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/business-hours',
			'context'      => array(
				'business_type'      => $business_type,
				'has_plugin'         => $has_hours_plugin,
				'has_content'        => $has_hours_content,
				'impact'             => __( 'Customers call during closed hours, visit when you\'re closed, or assume you\'re unavailable. 63% of consumers check business hours before visiting.', 'wpshadow' ),
				'recommendation'     => array(
					__( 'Display business hours on Contact page and footer', 'wpshadow' ),
					__( 'Show current status: "Open Now" or "Closed - Opens at 9am"', 'wpshadow' ),
					__( 'List holiday closures and special hours', 'wpshadow' ),
					__( 'Add hours to Google My Business listing', 'wpshadow' ),
					__( 'Include hours in Schema.org markup for SEO', 'wpshadow' ),
					__( 'Consider a business hours plugin for dynamic display', 'wpshadow' ),
					__( 'Update hours proactively for holidays', 'wpshadow' ),
				),
				'customer_frustration' => __( '"Why isn\'t anyone answering?" - #1 complaint when hours aren\'t clear', 'wpshadow' ),
				'seo_benefit'        => __( 'Structured hours data improves local SEO and Google Business rankings', 'wpshadow' ),
			),
		);
	}
}
