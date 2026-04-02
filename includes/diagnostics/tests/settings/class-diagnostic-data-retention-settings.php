<?php
/**
 * Data Retention Settings Diagnostic
 *
 * Validates data retention policies are configured.
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
 * Data Retention Settings Diagnostic Class
 *
 * Checks if data retention policies are properly configured for compliance.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Data_Retention_Settings extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'data-retention-settings';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Data Retention Settings';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates data retention policies are configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'settings';

	/**
	 * The family label
	 *
	 * @var string
	 */
	protected static $family_label = 'Settings';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check comment retention settings.
		$comment_max_age = get_option( 'comment_max_age_days', false );
		if ( false === $comment_max_age || empty( $comment_max_age ) ) {
			$issues[] = __( 'Comment retention period not configured', 'wpshadow' );
		}

		// Check if old comments are automatically closed.
		$close_old_comments = get_option( 'close_comments_for_old_posts', '0' );
		if ( '0' === $close_old_comments || 0 === $close_old_comments ) {
			$issues[] = __( 'Automatic comment closing for old posts is disabled', 'wpshadow' );
		}

		// Check if there's a scheduled event for transient cleanup.
		$transient_cleanup = wp_next_scheduled( 'delete_expired_transients' );
		if ( false === $transient_cleanup ) {
			$issues[] = __( 'Transient cleanup is not scheduled', 'wpshadow' );
		}

		// Check for personal data retention policy page.
		$privacy_page = get_option( 'wp_page_for_privacy_policy', 0 );
		if ( 0 === (int) $privacy_page ) {
			$issues[] = __( 'Privacy policy page not configured for data retention disclosure', 'wpshadow' );
		}

		// Check if user data retention settings exist.
		$user_data_retention = get_option( 'wpshadow_user_data_retention_days', false );
		if ( false === $user_data_retention ) {
			// Check for common third-party retention settings.
			$has_retention_policy = false;
			$retention_options    = array(
				'gdpr_retention_period',
				'wp_gdpr_retention_days',
				'data_retention_period',
			);
			foreach ( $retention_options as $option ) {
				if ( false !== get_option( $option, false ) ) {
					$has_retention_policy = true;
					break;
				}
			}
			if ( ! $has_retention_policy ) {
				$issues[] = __( 'User data retention period not configured', 'wpshadow' );
			}
		}

		// If no issues found, return null.
		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'                 => self::$slug,
			'title'              => self::$title,
			'description'        => sprintf(
				/* translators: %d: number of issues */
				__( 'Found %d data retention configuration issues', 'wpshadow' ),
				count( $issues )
			),
			'severity'           => 'medium',
			'threat_level'       => 60,
			'site_health_status' => 'recommended',
			'auto_fixable'       => false,
			'kb_link'            => 'https://wpshadow.com/kb/data-retention-settings',
			'family'             => self::$family,
			'details'            => array(
				'issues'            => $issues,
				'comment_max_age'   => $comment_max_age,
				'transient_cleanup' => $transient_cleanup,
				'privacy_page_id'   => $privacy_page,
			),
		);
	}
}
