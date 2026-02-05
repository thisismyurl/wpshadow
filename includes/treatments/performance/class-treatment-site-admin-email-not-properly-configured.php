<?php
/**
 * Site Admin Email Not Properly Configured Treatment
 *
 * Tests for admin email configuration.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6033.0000
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Site Admin Email Not Properly Configured Treatment Class
 *
 * Tests for proper admin email configuration.
 *
 * @since 1.6033.0000
 */
class Treatment_Site_Admin_Email_Not_Properly_Configured extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'site-admin-email-not-properly-configured';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Site Admin Email Not Properly Configured';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests for proper admin email configuration';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6033.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check admin email.
		$admin_email = get_option( 'admin_email' );

		if ( empty( $admin_email ) ) {
			$issues[] = __( 'Admin email is not set', 'wpshadow' );
		} elseif ( ! is_email( $admin_email ) ) {
			$issues[] = sprintf(
				/* translators: %s: admin email */
				__( 'Admin email is not valid: %s', 'wpshadow' ),
				$admin_email
			);
		}

		// Check for common test/placeholder emails.
		if ( strpos( $admin_email, 'test@' ) === 0 || strpos( $admin_email, 'admin@localhost' ) === 0 ) {
			$issues[] = sprintf(
				/* translators: %s: admin email */
				__( 'Admin email appears to be a test address: %s', 'wpshadow' ),
				$admin_email
			);
		}

		// Check email deliverability (simple test).
		if ( ! empty( $admin_email ) ) {
			// Check if email domain is valid.
			$email_domain = substr( $admin_email, strpos( $admin_email, '@' ) + 1 );
			if ( ! checkdnsrr( $email_domain, 'MX' ) ) {
				$issues[] = sprintf(
					/* translators: %s: email domain */
					__( 'Email domain %s has no MX records - emails may not be deliverable', 'wpshadow' ),
					$email_domain
				);
			}
		}

		// Check for backup admin emails.
		$backup_emails = get_option( '_wpshadow_backup_admin_emails', array() );

		if ( empty( $backup_emails ) || ! is_array( $backup_emails ) ) {
			$issues[] = __( 'No backup admin email configured - critical notifications may be missed', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/site-admin-email-not-properly-configured',
			);
		}

		return null;
	}
}
