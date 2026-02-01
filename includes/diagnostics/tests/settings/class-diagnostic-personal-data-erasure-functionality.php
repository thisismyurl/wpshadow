<?php
/**
 * Personal Data Erasure Functionality Diagnostic
 *
 * Tests personal data erasure tools are functional.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.1531
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Personal Data Erasure Functionality Diagnostic Class
 *
 * Validates that personal data erasure tools are properly configured and functional.
 *
 * @since 1.2601.1531
 */
class Diagnostic_Personal_Data_Erasure_Functionality extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'personal-data-erasure-functionality';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Personal Data Erasure Functionality';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests personal data erasure tools are functional';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * The family label
	 *
	 * @var string
	 */
	protected static $family_label = 'Security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.1531
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check if erasure page is configured.
		$erasure_page = get_option( 'wp_privacy_personal_data_erasure_page', 0 );
		if ( empty( $erasure_page ) ) {
			$issues[] = __( 'Personal data erasure page is not configured', 'wpshadow' );
		}

		// Check if erasers are registered.
		$has_erasers = has_filter( 'wp_privacy_personal_data_erasers' );
		if ( ! $has_erasers ) {
			$issues[] = __( 'No personal data erasers are registered', 'wpshadow' );
		} else {
			// Get registered erasers to verify functionality.
			$erasers = apply_filters( 'wp_privacy_personal_data_erasers', array() );
			if ( empty( $erasers ) ) {
				$issues[] = __( 'Personal data erasers filter registered but returns empty', 'wpshadow' );
			}
		}

		// Check if cron job for processing erasure requests exists.
		$erasure_cron = wp_next_scheduled( 'wp_privacy_delete_old_export_files' );
		if ( false === $erasure_cron ) {
			$issues[] = __( 'Cron job for cleaning old export files is not scheduled', 'wpshadow' );
		}

		// Check for orphaned personal data requests.
		global $wpdb;
		$orphaned_requests = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->prefix}posts 
				WHERE post_type = %s 
				AND post_status = %s 
				AND post_modified < DATE_SUB(NOW(), INTERVAL 30 DAY)",
				'user_request',
				'request-pending'
			)
		);

		if ( $orphaned_requests > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of orphaned requests */
				__( 'Found %d orphaned erasure requests older than 30 days', 'wpshadow' ),
				$orphaned_requests
			);
		}

		// Check if the wp_privacy_send_erasure_fulfillment_notification function exists.
		if ( ! function_exists( 'wp_privacy_send_erasure_fulfillment_notification' ) ) {
			$issues[] = __( 'Personal data erasure notification function is not available', 'wpshadow' );
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
				__( 'Found %d personal data erasure functionality issues', 'wpshadow' ),
				count( $issues )
			),
			'severity'           => 'high',
			'threat_level'       => 70,
			'site_health_status' => 'critical',
			'auto_fixable'       => false,
			'kb_link'            => 'https://wpshadow.com/kb/personal-data-erasure-functionality',
			'family'             => self::$family,
			'details'            => array(
				'issues'            => $issues,
				'erasure_page'      => $erasure_page,
				'has_erasers'       => $has_erasers,
				'orphaned_requests' => $orphaned_requests,
			),
		);
	}
}
