<?php
/**
 * Transactional Email Delivery Diagnostic
 *
 * Tests if transactional emails can be delivered successfully.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Transactional Email Delivery Diagnostic Class
 *
 * Sends test email to admin to verify email delivery is working.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Transactional_Email_Delivery extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'transactional-email-delivery';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Transactional Email Delivery';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if transactional emails can be delivered';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'email-deliverability';

	/**
	 * Run the transactional email delivery diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if delivery issue detected, null otherwise.
	 */
	public static function check() {
		$cache_key = 'wpshadow_email_test';
		$cached = get_transient( $cache_key );

		// Only test once per hour to avoid spam.
		if ( false !== $cached ) {
			return $cached;
		}

		$admin_email = get_option( 'admin_email' );
		$site_name = get_bloginfo( 'name' );

		$subject = sprintf(
			/* translators: %s: site name */
			__( '[WPShadow] Email Delivery Test for %s', 'wpshadow' ),
			$site_name
		);

		$message = sprintf(
			/* translators: %s: site name */
			__( 'This is a test email from WPShadow to verify email delivery is working for %s.', 'wpshadow' ),
			$site_name
		) . "\n\n" . __( 'If you received this email, your email configuration is working correctly.', 'wpshadow' );

		$start_time = microtime( true );
		$sent = wp_mail( $admin_email, $subject, $message );
		$delivery_time = ( microtime( true ) - $start_time ) * 1000;

		$result = null;

		if ( ! $sent ) {
			$result = array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Failed to send test email. Email delivery may not be working properly.', 'wpshadow' ),
				'severity'    => 'high',
				'threat_level' => 85,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/email-delivery-issues',
				'meta'        => array(
					'delivery_time' => round( $delivery_time, 2 ),
				),
			);

			// Log to Activity Logger.
			if ( class_exists( '\WPShadow\Core\Activity_Logger' ) ) {
				\WPShadow\Core\Activity_Logger::log(
					'email_test_failed',
					__( 'Transactional email test failed', 'wpshadow' ),
					'monitoring',
					array(
						'recipient'     => $admin_email,
						'delivery_time' => round( $delivery_time, 2 ),
					)
				);
			}
		} else {
			// Log successful delivery.
			if ( class_exists( '\WPShadow\Core\Activity_Logger' ) ) {
				\WPShadow\Core\Activity_Logger::log(
					'email_test_success',
					__( 'Transactional email test succeeded', 'wpshadow' ),
					'monitoring',
					array(
						'recipient'     => $admin_email,
						'delivery_time' => round( $delivery_time, 2 ),
					)
				);
			}
		}

		set_transient( $cache_key, $result, HOUR_IN_SECONDS );

		return $result;
	}
}
