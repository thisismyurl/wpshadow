<?php
/**
 * Membership Site Data Portability and Export Diagnostic
 *
 * Checks if membership sites implement GDPR-compliant data export functionality
 * allowing members to download their data in portable formats.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Membership
 * @since      1.6031.1506
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics\Membership;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Membership Data Portability Diagnostic Class
 *
 * Verifies membership sites implement GDPR data portability requirements.
 *
 * @since 1.6031.1506
 */
class Diagnostic_Membership_Data_Portability extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'membership-data-portability';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Membership Site Data Portability and Export';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies membership sites implement GDPR-compliant data export functionality';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'membership';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6031.1506
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$active_plugins = get_option( 'active_plugins', array() );

		// Check for membership plugins.
		$membership_plugins = array(
			'memberpress',
			'paid-memberships-pro',
			'restrict-content',
			's2member',
			'wishlist-member',
		);

		$has_membership = false;
		foreach ( $active_plugins as $plugin ) {
			foreach ( $membership_plugins as $mem_plugin ) {
				if ( stripos( $plugin, $mem_plugin ) !== false ) {
					$has_membership = true;
					break 2;
				}
			}
		}

		if ( ! $has_membership ) {
			return null; // No membership.
		}

		$issues = array();

		// Check for GDPR/data export plugins.
		$has_gdpr_export = false;
		$gdpr_plugins = array(
			'gdpr',
			'wp-gdpr-compliance',
			'gdpr-data-request',
			'export-personal-data',
		);

		foreach ( $active_plugins as $plugin ) {
			foreach ( $gdpr_plugins as $gdpr_plugin ) {
				if ( stripos( $plugin, $gdpr_plugin ) !== false ) {
					$has_gdpr_export = true;
					break 2;
				}
			}
		}

		// WordPress has built-in export, but check if it's accessible.
		$privacy_page = get_option( 'wp_page_for_privacy_policy' );
		if ( empty( $privacy_page ) ) {
			$issues[] = __( 'No privacy policy page configured (required for data requests)', 'wpshadow' );
		}

		if ( ! $has_gdpr_export ) {
			$issues[] = __( 'No GDPR/data portability plugin detected (relying on WordPress built-in only)', 'wpshadow' );
		}

		// Check for member data deletion capability.
		$has_deletion = false;
		$deletion_plugins = array(
			'gdpr',
			'wp-gdpr-compliance',
			'delete-me',
		);

		foreach ( $active_plugins as $plugin ) {
			foreach ( $deletion_plugins as $del_plugin ) {
				if ( stripos( $plugin, $del_plugin ) !== false ) {
					$has_deletion = true;
					break 2;
				}
			}
		}

		if ( ! $has_deletion ) {
			$issues[] = __( 'No member data deletion/erasure plugin found', 'wpshadow' );
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: comma-separated list of issues */
				__( 'Data portability concerns: %s. Membership sites must allow members to export their data in portable formats per GDPR Article 20.', 'wpshadow' ),
				implode( ', ', $issues )
			),
			'severity'     => 'high',
			'threat_level' => 75,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/membership-data-portability',
		);
	}
}
