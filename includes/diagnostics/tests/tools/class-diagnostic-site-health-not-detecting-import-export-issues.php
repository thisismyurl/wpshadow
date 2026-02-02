<?php
/**
 * Site Health Not Detecting Import/Export Issues
 *
 * Checks if Site Health integrates with import/export diagnostics.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Tools
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Site_Health_Not_Detecting_Import_Export_Issues Class
 *
 * Validates Site Health integration with import/export diagnostics.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Site_Health_Not_Detecting_Import_Export_Issues extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'site-health-no-import-export';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Site Health Import/Export Integration';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates Site Health integration with import/export diagnostics';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'tools';

	/**
	 * Run the diagnostic check.
	 *
	 * Tests Site Health integration for import/export issues.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// 1. Check for Site Health tests
		if ( ! self::has_site_health_tests() ) {
			$issues[] = __( 'No import/export checks in Site Health', 'wpshadow' );
		}

		// 2. Check for integration with WP_Site_Health
		if ( ! self::integrates_with_site_health() ) {
			$issues[] = __( 'Not integrated with WordPress Site Health API', 'wpshadow' );
		}

		// 3. Check for test results
		if ( ! self::reports_results_to_site_health() ) {
			$issues[] = __( 'Results not reported to Site Health status', 'wpshadow' );
		}

		// 4. Check for sync capability
		if ( ! self::syncs_with_site_health_status() ) {
			$issues[] = __( 'Site Health status not synced with import/export status', 'wpshadow' );
		}

		// 5. Check for user notification
		if ( ! self::notifies_users_via_site_health() ) {
			$issues[] = __( 'Users not notified of issues via Site Health', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of integration issues */
					__( '%d Site Health integration issues found', 'wpshadow' ),
					count( $issues )
				),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => true,
				'details'      => $issues,
				'kb_link'      => 'https://wpshadow.com/kb/site-health-import-export',
				'recommendations' => array(
					__( 'Register import/export tests with Site Health', 'wpshadow' ),
					__( 'Integrate with WP_Site_Health API', 'wpshadow' ),
					__( 'Report check results to Site Health status', 'wpshadow' ),
					__( 'Sync import/export status with Site Health', 'wpshadow' ),
					__( 'Show import/export issues in Site Health notices', 'wpshadow' ),
				),
			);
		}

		return null;
	}

	/**
	 * Check for Site Health tests.
	 *
	 * @since  1.2601.2148
	 * @return bool True if tests implemented.
	 */
	private static function has_site_health_tests() {
		// Check for test registration hook
		if ( has_filter( 'site_status_tests' ) ) {
			return true;
		}

		// Check for direct test class
		if ( class_exists( 'WPShadow\Integration\Site_Health_Tests' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check for Site Health API integration.
	 *
	 * @since  1.2601.2148
	 * @return bool True if integrated.
	 */
	private static function integrates_with_site_health() {
		// Check if WP_Site_Health is available
		if ( ! class_exists( 'WP_Site_Health' ) ) {
			return false;
		}

		// Check for integration hook
		if ( has_filter( 'wpshadow_site_health_integration' ) ) {
			return true;
		}

		// Check for test callback registration
		if ( has_filter( 'site_status_tests' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check for result reporting.
	 *
	 * @since  1.2601.2148
	 * @return bool True if results reported.
	 */
	private static function reports_results_to_site_health() {
		// Check for result filter
		if ( has_filter( 'wpshadow_report_site_health_result' ) ) {
			return true;
		}

		// Check for status update hook
		if ( has_action( 'wpshadow_update_site_health_status' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check for status sync.
	 *
	 * @since  1.2601.2148
	 * @return bool True if status synced.
	 */
	private static function syncs_with_site_health_status() {
		// Check for sync option
		$sync_status = get_option( 'wpshadow_sync_site_health' );
		if ( ! empty( $sync_status ) ) {
			return true;
		}

		// Check for sync filter
		if ( has_filter( 'wpshadow_sync_site_health_status' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check for user notification.
	 *
	 * @since  1.2601.2148
	 * @return bool True if notification implemented.
	 */
	private static function notifies_users_via_site_health() {
		// Check for notification hook
		if ( has_filter( 'wpshadow_notify_site_health' ) ) {
			return true;
		}

		// Check for admin notice integration
		if ( has_action( 'admin_notices' ) ) {
			// Check for Site Health specific notice
			if ( has_filter( 'wpshadow_site_health_notice' ) ) {
				return true;
			}
		}

		return false;
	}
}
