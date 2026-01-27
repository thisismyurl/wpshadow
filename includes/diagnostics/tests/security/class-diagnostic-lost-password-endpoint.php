<?php
/**
 * Diagnostic: Lost Password Endpoint
 *
 * Verifies the lost password endpoint is accessible and functional.
 * This is critical for user account recovery.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Security
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Diagnostic_Lost_Password_Endpoint
 *
 * Tests lost password functionality.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Lost_Password_Endpoint extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'lost-password-endpoint';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Lost Password Endpoint';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Verifies lost password endpoint is accessible';

	/**
	 * Check lost password endpoint.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		// Check if lost password is disabled.
		$allow_password_reset = apply_filters( 'allow_password_reset', true );

		if ( ! $allow_password_reset ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Password reset functionality is disabled. Users cannot recover their accounts if they forget passwords.', 'wpshadow' ),
				'severity'    => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/lost_password_endpoint',
				'meta'        => array(
					'allow_password_reset' => false,
				),
			);
		}

		// Test if lost password URL is accessible.
		$lost_password_url = wp_lostpassword_url();

		$response = wp_remote_get(
			$lost_password_url,
			array(
				'timeout'   => 5,
				'sslverify' => false,
			)
		);

		if ( is_wp_error( $response ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %s: Error message */
					__( 'Lost password endpoint is not accessible: %s', 'wpshadow' ),
					$response->get_error_message()
				),
				'severity'    => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/lost_password_endpoint',
				'meta'        => array(
					'url'   => $lost_password_url,
					'error' => $response->get_error_message(),
				),
			);
		}

		$status_code = wp_remote_retrieve_response_code( $response );

		// Lost password page should respond with 200.
		if ( 200 !== $status_code ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %d: HTTP status code */
					__( 'Lost password endpoint returned unexpected status code: %d', 'wpshadow' ),
					$status_code
				),
				'severity'    => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/lost_password_endpoint',
				'meta'        => array(
					'url'         => $lost_password_url,
					'status_code' => $status_code,
				),
			);
		}

		// Check if email delivery is configured.
		$phpmailer_init_count = has_action( 'phpmailer_init' );
		$wp_mail_from         = apply_filters( 'wp_mail_from', '' );

		if ( empty( $wp_mail_from ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Email "From" address is not configured. Password reset emails may not be delivered or may be marked as spam.', 'wpshadow' ),
				'severity'    => 'info',
				'threat_level' => 20,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/lost_password_endpoint',
				'meta'        => array(
					'wp_mail_from'         => $wp_mail_from,
					'phpmailer_init_count' => $phpmailer_init_count,
				),
			);
		}

		// Lost password endpoint is functional.
		return null;
	}
}
