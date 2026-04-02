<?php
/**
 * Admin Email Deliverable Diagnostic (Stub)
 *
 * TODO stub mapped to the settings gauge.
 *
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Diagnostics\Helpers\Diagnostic_WP_Settings_Helper as WP_Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Admin_Email_Deliverable Class
 *
 * TODO: Implement full test logic and remediation guidance.
 */
class Diagnostic_Admin_Email_Deliverable extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'admin-email-deliverable';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Admin Email Deliverable';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether the WordPress admin email address is valid and not using a generic placeholder, ensuring site notifications are deliverable.';

	/**
	 * Gauge family/category.
	 *
	 * @var string
	 */
	protected static $family = 'settings';

	/**
	 * Run the diagnostic check.
	 *
	 * TODO Test Plan:
	 * - Check get_option('admin_email') for placeholder, invalid, or same-domain mismatch patterns.
	 *
	 * TODO Fix Plan:
	 * - Set a monitored admin email inbox used for alerts and moderation.
	 * - Use WordPress hooks, filters, settings, DB fixes, PHP config, or accessible server settings.
	 * - Do not modify WordPress core files.
	 * - Ensure performance/security/success impact and align with WPShadow commandments.
	 *
	 * @since  0.6093.1200
	 * @return array|null Finding array if issue exists, null if healthy.
	 */
	public static function check() {
		$email = WP_Settings::get_admin_email();

		if ( empty( $email ) ) {
			return null;
		}

		// Validate basic email format.
		if ( ! is_email( $email ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: the admin email address */
					__( 'The WordPress admin email address (%s) does not appear to be a valid email address. WordPress relies on this address for security alerts, update notifications, and comment moderation. Update it under Settings > General.', 'wpshadow' ),
					esc_html( $email )
				),
				'severity'     => 'high',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/admin-email-deliverable',
				'details'      => array(
					'email'  => $email,
					'reason' => 'invalid_format',
				),
			);
		}

		// Detect known placeholder or unmonitored-inbox patterns.
		$lower        = strtolower( $email );
		$placeholder_domains = array( 'example.com', 'example.org', 'example.net', 'test.com', 'localhost' );
		$generic_prefixes    = array( 'info@', 'admin@', 'webmaster@', 'no-reply@', 'noreply@', 'postmaster@', 'mail@', 'contact@' );

		foreach ( $placeholder_domains as $domain ) {
			if ( str_contains( $lower, '@' . $domain ) ) {
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => sprintf(
						/* translators: %s: the admin email address */
						__( 'The WordPress admin email (%s) uses a placeholder domain and will not receive any mail. Update it to a real, monitored inbox under Settings > General.', 'wpshadow' ),
						esc_html( $email )
					),
					'severity'     => 'high',
					'threat_level' => 65,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/admin-email-deliverable',
					'details'      => array(
						'email'  => $email,
						'reason' => 'placeholder_domain',
					),
				);
			}
		}

		foreach ( $generic_prefixes as $prefix ) {
			if ( str_starts_with( $lower, $prefix ) ) {
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => sprintf(
						/* translators: %s: the admin email address */
						__( 'The WordPress admin email (%s) uses a generic alias that is often unmonitored or routed to a shared inbox. WordPress sends security alerts and moderation notifications to this address. Update it to a personally monitored inbox.', 'wpshadow' ),
						esc_html( $email )
					),
					'severity'     => 'low',
					'threat_level' => 20,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/admin-email-deliverable',
					'details'      => array(
						'email'  => $email,
						'reason' => 'generic_prefix',
					),
				);
			}
		}

		return null;
	}
}
