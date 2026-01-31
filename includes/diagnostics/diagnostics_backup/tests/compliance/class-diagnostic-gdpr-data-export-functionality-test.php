<?php
/**
 * GDPR Data Export Functionality Test Diagnostic
 *
 * Tests GDPR data export actually works and includes all user data.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26028.1905
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GDPR Data Export Functionality Test Class
 *
 * Tests whether GDPR data export is functional and complete.
 *
 * @since 1.26028.1905
 */
class Diagnostic_GDPR_Data_Export_Functionality_Test extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'gdpr-data-export-functionality-test';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'GDPR Data Export Functionality Test';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests GDPR data export actually works and includes all user data';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'compliance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26028.1905
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check if privacy tools are enabled (WordPress 4.9.6+).
		if ( ! function_exists( 'wp_privacy_personal_data_exporters' ) ) {
			$issues[] = __( 'WordPress version too old for GDPR privacy tools (requires 4.9.6+)', 'wpshadow' );
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( ' ', $issues ),
				'severity'     => 'critical',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/gdpr-data-export-functionality-test',
				'meta'         => array(
					'wordpress_version' => get_bloginfo( 'version' ),
				),
			);
		}

		// Check registered exporters.
		$exporters = self::get_registered_exporters();
		if ( count( $exporters ) < 2 ) { // WordPress core has at least 2 exporters.
			$issues[] = __( 'Insufficient data exporters registered (may not export all data)', 'wpshadow' );
		}

		// Check if WooCommerce data exporter is registered.
		if ( class_exists( 'WooCommerce' ) && ! self::has_woocommerce_exporter( $exporters ) ) {
			$issues[] = __( 'WooCommerce active but no WooCommerce data exporter registered', 'wpshadow' );
		}

		// Check for custom user meta.
		$custom_meta_count = self::count_custom_user_meta();
		if ( $custom_meta_count > 5 && ! self::has_custom_meta_exporter( $exporters ) ) {
			$issues[] = sprintf(
				/* translators: %d: number of custom meta fields */
				__( '%d custom user meta fields may not be included in export', 'wpshadow' ),
				$custom_meta_count
			);
		}

		// Check export timeout settings.
		$max_execution_time = ini_get( 'max_execution_time' );
		if ( $max_execution_time > 0 && $max_execution_time < 300 ) {
			$user_count = count_users();
			$total_users = $user_count['total_users'];
			if ( $total_users > 1000 ) {
				$issues[] = __( 'PHP max_execution_time too low for large user base (export may timeout)', 'wpshadow' );
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( ' ', $issues ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/gdpr-data-export-functionality-test',
				'meta'         => array(
					'exporters_registered'  => count( $exporters ),
					'custom_meta_count'     => $custom_meta_count,
					'max_execution_time'    => $max_execution_time,
					'issues_found'          => count( $issues ),
				),
			);
		}

		return null;
	}

	/**
	 * Get registered privacy data exporters.
	 *
	 * @since  1.26028.1905
	 * @return array Array of exporters.
	 */
	private static function get_registered_exporters() {
		$exporters = array();

		if ( function_exists( 'wp_privacy_personal_data_exporters' ) ) {
			$exporters = wp_privacy_personal_data_exporters();
		}

		return $exporters;
	}

	/**
	 * Check if WooCommerce data exporter is registered.
	 *
	 * @since  1.26028.1905
	 * @param  array $exporters Array of registered exporters.
	 * @return bool True if WooCommerce exporter found.
	 */
	private static function has_woocommerce_exporter( $exporters ) {
		foreach ( $exporters as $exporter ) {
			if ( isset( $exporter['exporter_friendly_name'] ) &&
				 false !== stripos( $exporter['exporter_friendly_name'], 'woocommerce' ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if custom meta exporter is registered.
	 *
	 * @since  1.26028.1905
	 * @param  array $exporters Array of registered exporters.
	 * @return bool True if custom meta exporter found.
	 */
	private static function has_custom_meta_exporter( $exporters ) {
		foreach ( $exporters as $exporter ) {
			if ( isset( $exporter['exporter_friendly_name'] ) &&
				 ( false !== stripos( $exporter['exporter_friendly_name'], 'meta' ) ||
				   false !== stripos( $exporter['exporter_friendly_name'], 'custom' ) ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Count custom user meta fields.
	 *
	 * @since  1.26028.1905
	 * @return int Number of custom meta keys.
	 */
	private static function count_custom_user_meta() {
		global $wpdb;

		// Get distinct custom meta keys (exclude WordPress core keys).
		$core_keys = array(
			'nickname',
			'first_name',
			'last_name',
			'description',
			'rich_editing',
			'syntax_highlighting',
			'comment_shortcuts',
			'admin_color',
			'use_ssl',
			'show_admin_bar_front',
			'locale',
			'wp_capabilities',
			'wp_user_level',
			'dismissed_wp_pointers',
			'show_welcome_panel',
		);

		$meta_keys = $wpdb->get_col(
			"SELECT DISTINCT meta_key FROM {$wpdb->usermeta}
			WHERE meta_key NOT LIKE 'wp_%'
			AND meta_key NOT LIKE '%capabilities'
			AND meta_key NOT LIKE '%user_level'"
		);

		return count( $meta_keys );
	}
}
