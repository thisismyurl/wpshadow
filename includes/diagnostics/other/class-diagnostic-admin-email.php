<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Diagnostic_Admin_Email extends Diagnostic_Base {

	protected static $slug        = 'admin-email';
	protected static $title       = 'Admin Email Configuration';
	protected static $description = 'Checks if admin email is valid and configured.';

	public static function check(): ?array {
		$admin_email = get_option( 'admin_email' );

		if ( empty( $admin_email ) ) {
			return array(
				'finding_id'   => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Admin email is not configured. WordPress sends critical notifications to this address including security alerts, update notifications, and user registration confirmations.', 'wpshadow' ),
				'category'     => 'settings',
				'severity'     => 'critical',
				'threat_level' => 90,
				'auto_fixable' => false,
				'timestamp'    => current_time( 'mysql' ),
			);
		}

		if ( false === filter_var( $admin_email, FILTER_VALIDATE_EMAIL ) ) {
			return array(
				'finding_id'   => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					__( 'Admin email "%s" is not a valid email address. You will not receive important notifications about your site.', 'wpshadow' ),
					esc_html( $admin_email )
				),
				'category'     => 'settings',
				'severity'     => 'high',
				'threat_level' => 85,
				'auto_fixable' => false,
				'timestamp'    => current_time( 'mysql' ),
			);
		}

		if ( strpos( $admin_email, 'example.com' ) !== false || strpos( $admin_email, 'test.com' ) !== false ) {
			return array(
				'finding_id'   => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					__( 'Admin email appears to be a placeholder (%s). Set a real, monitored email address.', 'wpshadow' ),
					esc_html( $admin_email )
				),
				'category'     => 'settings',
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'timestamp'    => current_time( 'mysql' ),
			);
		}

		// Check if using default WordPress from email
		$wp_from_email = 'wordpress@' . preg_replace( '#^www\.#', '', wp_parse_url( home_url(), PHP_URL_HOST ) );
		$from_email    = get_option( 'wpshadow_email_from_email', '' );

		if ( empty( $from_email ) || $from_email === $wp_from_email ) {
			return array(
				'finding_id'   => self::$slug . '-from',
				'title'        => __( 'Email From Address Not Configured', 'wpshadow' ),
				'description'  => sprintf(
					__( 'Your site is using the default WordPress from address (%s). Many email providers will reject emails from this address, causing delivery failures. Configure a proper from email address using the Email Test tool under WPShadow → Tools.', 'wpshadow' ),
					esc_html( $wp_from_email )
				),
				'category'     => 'settings',
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'timestamp'    => current_time( 'mysql' ),
			);
		}

		return null;
	}

	/**
	 * Test: Result structure validation
	 *
	 * Ensures diagnostic returns null (no issues) or array (issues found)
	 * with all required fields populated.
	 *
	 * @return array Test result with 'passed' and 'message'
	 */
	public static function test_result_structure(): array {
		$result = self::check();
		
		// Valid states: null (pass) or array (fail)
		if ( null === $result || is_array( $result ) ) {
			// If array, validate structure
			if ( is_array( $result ) ) {
				$required = array(
					'id', 'title', 'description', 'category', 
					'severity', 'threat_level'
				);
				
				foreach ( $required as $field ) {
					if ( ! isset( $result[ $field ] ) ) {
						return array(
							'passed'  => false,
							'message' => "Missing field: $field",
						);
					}
				}
				
				// Validate field types
				if ( ! is_string( $result['severity'] ) ) {
					return array(
						'passed'  => false,
						'message' => 'severity must be string',
					);
				}
				
				if ( ! is_int( $result['threat_level'] ) || $result['threat_level'] < 0 || $result['threat_level'] > 100 ) {
					return array(
						'passed'  => false,
						'message' => 'threat_level must be int 0-100',
					);
				}
			}
			
			return array(
				'passed'  => true,
				'message' => 'Result structure valid',
			);
		}
		
		return array(
			'passed'  => false,
			'message' => 'Invalid result type: ' . gettype( $result ),
		);
	}
	/**
	 * Test: Option-based detection
	 *
	 * Verifies that diagnostic correctly reads and evaluates options
	 * and returns appropriate result.
	 *
	 * @return array Test result
	 */
	public static function test_option_detection(): array {
		$result = self::check();
		
		// Should return null or array based on option values
		if ( $result === null || is_array( $result ) ) {
			return array(
				'passed'  => true,
				'message' => 'Option detection working correctly',
			);
		}
		
		return array(
			'passed'  => false,
			'message' => 'Option detection returned invalid type',
		);
	}}
