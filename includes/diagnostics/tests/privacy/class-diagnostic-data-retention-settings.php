<?php
/**
 * Data Retention Settings Diagnostic
 *
 * Validates data retention policies are configured for privacy compliance.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2602.0100
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
 * Checks if data retention policies are configured for compliance with
 * GDPR, CCPA, and other privacy regulations.
 *
 * @since 1.2602.0100
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
	protected static $family = 'privacy';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2602.0100
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues  = array();
		$details = array();

		// Check comment retention settings.
		$comment_max_age                 = get_option( 'comment_max_age_days', 0 );
		$details['comment_max_age_days'] = $comment_max_age;

		if ( 0 === (int) $comment_max_age ) {
			$issues[] = __( 'No comment retention policy configured. Comments are kept indefinitely, which may violate data retention policies.', 'wpshadow' );
		}

		// Check if old comments are auto-deleted (moderation setting).
		$comment_moderation            = get_option( 'comment_moderation', 0 );
		$details['comment_moderation'] = $comment_moderation;

		// Check for WordPress personal data retention settings.
		$remove_personal_data_days            = get_option( 'remove_personal_data_days', 0 );
		$details['remove_personal_data_days'] = $remove_personal_data_days;

		if ( 0 === (int) $remove_personal_data_days ) {
			$issues[] = __( 'Personal data retention period not set. Consider configuring automatic removal of personal data after a specified period for privacy compliance.', 'wpshadow' );
		}

		// Check for transient cleanup.
		global $wpdb;
		$transient_count = 0;

		// Count expired transients safely.
		if ( isset( $wpdb->options ) ) {
			$query = $wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE %s AND option_value < %d",
				'_transient_timeout_%',
				time()
			);

			$transient_count = (int) $wpdb->get_var( $query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		}

		$details['expired_transients'] = $transient_count;

		if ( $transient_count > 100 ) {
			$issues[] = sprintf(
				/* translators: %d: Number of expired transients */
				__( 'Found %d expired transients in database. Regular cleanup needed to prevent database bloat.', 'wpshadow' ),
				$transient_count
			);
		}

		// Check if any data retention plugin is active.
		$retention_plugins = array(
			'gdpr-data-request-form/gdpr-data-request-form.php',
			'wp-gdpr-compliance/wp-gdpr-compliance.php',
			'cookie-law-info/cookie-law-info.php',
		);

		$has_retention_plugin = false;
		foreach ( $retention_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_retention_plugin        = true;
				$details['retention_plugin'] = $plugin;
				break;
			}
		}

		// If no retention settings and no plugin, this is an issue.
		if ( ! $has_retention_plugin && 0 === (int) $comment_max_age && 0 === (int) $remove_personal_data_days ) {
			$issues[] = __( 'No data retention system configured. For GDPR/CCPA compliance, implement policies for data lifecycle management.', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'                 => self::$slug,
				'title'              => self::$title,
				'description'        => __( 'Data retention policies not configured. This may cause privacy compliance issues with GDPR, CCPA, and other regulations.', 'wpshadow' ),
				'severity'           => 'medium',
				'threat_level'       => 40,
				'site_health_status' => 'good',
				'auto_fixable'       => false,
				'kb_link'            => 'https://wpshadow.com/kb/privacy-data-retention-settings',
				'family'             => self::$family,
				'details'            => array(
					'issues' => $issues,
					'info'   => $details,
				),
			);
		}

		return null;
	}
}
