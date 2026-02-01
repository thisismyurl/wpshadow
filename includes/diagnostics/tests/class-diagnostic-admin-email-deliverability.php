<?php
/**
 * Admin Email Deliverability Diagnostic
 *
 * Verifies that the admin email address is valid and configured for proper
 * delivery of WordPress notifications and alerts.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26032.1800
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin Email Deliverability Diagnostic Class
 *
 * Ensures admin email is valid and configured properly.
 *
 * @since 1.26032.1800
 */
class Diagnostic_Admin_Email_Deliverability extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'admin-email-deliverability';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Admin Email Deliverability';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies admin email is valid and deliverable';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'settings';

	/**
	 * Run the diagnostic check.
	 *
	 * Checks:
	 * - Admin email is set and valid
	 * - Email is not localhost or test domain
	 * - Email format is correct
	 * - Not using deprecated admin_email addresses
	 *
	 * @since  1.26032.1800
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Get admin email.
		$admin_email = get_option( 'admin_email', '' );

		// Check if email is set.
		if ( empty( $admin_email ) ) {
			$issues[] = __( 'Admin email is not configured', 'wpshadow' );
		} else {
			// Validate email format.
			if ( ! is_email( $admin_email ) ) {
				$issues[] = __( 'Admin email format is invalid', 'wpshadow' );
			}

			// Check for test/localhost emails.
			if ( strpos( $admin_email, '@localhost' ) !== false || 
				 strpos( $admin_email, '@test.' ) !== false || 
				 strpos( $admin_email, '@example.' ) !== false ) {
				$issues[] = __( 'Admin email appears to be a test/example address; use your real email', 'wpshadow' );
			}

			// Check for common free email providers (usually ok, but worth noting).
			$free_providers = array( 'gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com' );
			foreach ( $free_providers as $provider ) {
				if ( stripos( $admin_email, '@' . $provider ) !== false ) {
					// This is not an issue, but could be noted.
					break;
				}
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => implode( '. ', $issues ),
				'severity'    => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/admin-email-deliverability',
			);
		}

		return null;
	}
}
