<?php
/**
 * Membership Data Portability Diagnostic
 *
 * Verifies GDPR-compliant data export and deletion for membership sites
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6031.1445
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics\Membership;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
exit;
}

/**
 * Diagnostic_MembershipDataPortability Class
 *
 * Checks for GDPR export, data deletion, privacy policy
 *
 * @since 1.6031.1445
 */
class Diagnostic_MembershipDataPortability extends Diagnostic_Base {

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
protected static $title = 'Membership Data Portability';

/**
 * The diagnostic description
 *
 * @var string
 */
protected static $description = 'Verifies GDPR-compliant data export and deletion for membership sites';

/**
 * The family this diagnostic belongs to
 *
 * @var string
 */
protected static $family = 'membership';

/**
 * Run the diagnostic check.
 *
 * @since  1.6031.1445
 * @return array|null Finding array if issue found, null otherwise.
 */
public static function check() {
		// Check for membership plugins.
		$active_plugins = get_option( 'active_plugins', array() );
		$membership_plugins = array( 'membership', 'memberpress', 'paid-memberships-pro', 'restrict-content', 'wishlist' );
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
			return null;
		}

		$issues = array();

		// Check for GDPR data export plugins.
		$export_plugins = array( 'gdpr', 'data-export', 'wp-gdpr-compliance', 'export-personal-data' );
		$has_export = false;

		foreach ( $active_plugins as $plugin ) {
			foreach ( $export_plugins as $exp_plugin ) {
				if ( stripos( $plugin, $exp_plugin ) !== false ) {
					$has_export = true;
					break 2;
				}
			}
		}

		if ( ! $has_export ) {
			$issues[] = __( 'No GDPR data export plugin detected', 'wpshadow' );
		}

		// Check if WordPress privacy tools are enabled.
		$privacy_page = get_option( 'wp_page_for_privacy_policy' );
		if ( ! $privacy_page ) {
			$issues[] = __( 'No privacy policy page configured', 'wpshadow' );
		}

		// Check for data deletion tools.
		$deletion_plugins = array( 'data-deletion', 'delete-me', 'right-to-be-forgotten' );
		$has_deletion = false;

		foreach ( $active_plugins as $plugin ) {
			foreach ( $deletion_plugins as $del_plugin ) {
				if ( stripos( $plugin, $del_plugin ) !== false ) {
					$has_deletion = true;
					break 2;
				}
			}
		}

		if ( ! $has_deletion ) {
			$issues[] = __( 'No automated data deletion tool detected', 'wpshadow' );
		}

		// Check for member data retention policy.
		$retention_days = get_option( 'wpshadow_member_retention_days' );
		if ( ! $retention_days ) {
			$issues[] = __( 'No member data retention policy configured', 'wpshadow' );
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: comma-separated list of issues */
				__( 'Data portability concerns: %s. Membership sites must enable data export and deletion per GDPR.', 'wpshadow' ),
				implode( ', ', $issues )
			),
			'severity'     => 'high',
			'threat_level' => 75,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/membership-data-portability',
		);
