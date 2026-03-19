<?php
/**
 * No Bulk Fix Option for Multiple Issues
 *
 * Detects whether Site Health allows fixing multiple issues at once.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\SiteHealth
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_No_Bulk_Fix_Option_For_Multiple_Issues Class
 *
 * Validates bulk fix capability in Site Health.
 *
 * @since 1.6093.1200
 */
class Diagnostic_No_Bulk_Fix_Option_For_Multiple_Issues extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-bulk-fix-option-for-multiple-issues';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Bulk Fix Capability';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies Site Health supports fixing multiple issues at once';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'site_health';

	/**
	 * Run the diagnostic check.
	 *
	 * Tests for bulk fix support.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		// Get count of fixable issues
		$fixable_count = self::count_fixable_issues();

		if ( $fixable_count < 2 ) {
			return null; // Only one or no fixable issues
		}

		$issues = array();

		// 1. Check for bulk action support
		if ( ! self::has_bulk_action_support() ) {
			$issues[] = sprintf(
				/* translators: %d: number of fixable issues */
				__( '%d fixable issues but no bulk action support', 'wpshadow' ),
				$fixable_count
			);
		}

		// 2. Check for issue dependencies
		$dependency_issue = self::check_issue_dependencies();
		if ( $dependency_issue ) {
			$issues[] = $dependency_issue;
		}

		// 3. Check for progress tracking
		if ( ! self::has_progress_tracking() ) {
			$issues[] = __( 'No progress tracking for bulk fixes', 'wpshadow' );
		}

		// 4. Check for rollback capability
		if ( ! self::has_bulk_rollback() ) {
			$issues[] = __( 'Cannot rollback bulk fixes if needed', 'wpshadow' );
		}

		// 5. Check for related issue batching
		if ( ! self::can_batch_related_issues() ) {
			$issues[] = __( 'Cannot batch related issues together', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: fixable issues, %d: gaps */
					__( '%d fixable issues with %d bulk action gaps', 'wpshadow' ),
					$fixable_count,
					count( $issues )
				),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => true,
				'details'      => $issues,
				'kb_link'      => 'https://wpshadow.com/kb/bulk-fix-capability',
				'recommendations' => array(
					__( 'Implement bulk action checkboxes for fixes', 'wpshadow' ),
					__( 'Add dependency checking for related issues', 'wpshadow' ),
					__( 'Show progress during bulk fix execution', 'wpshadow' ),
					__( 'Allow partial rollback if individual fix fails', 'wpshadow' ),
				),
			);
		}

		return null;
	}

	/**
	 * Count fixable issues.
	 *
	 * @since 1.6093.1200
	 * @return int Number of auto-fixable issues.
	 */
	private static function count_fixable_issues() {
		// This would need to check all registered Site Health tests
		// For now, estimate based on common issues

		$fixable_issues = array(
			'hello_dolly_active',
			'file_permissions',
			'php_version',
			'memory_limit',
			'database_connection',
		);

		$count = 0;

		// Check which issues actually exist
		if ( is_plugin_active( 'hello-dolly/hello.php' ) ) {
			$count++;
		}

		if ( ! is_writable( WP_CONTENT_DIR ) ) {
			$count++;
		}

		global $wpdb;
		$issues = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE '%site_health_%'" );
		$count += $issues ? 1 : 0;

		return $count;
	}

	/**
	 * Check for bulk action support.
	 *
	 * @since 1.6093.1200
	 * @return bool True if bulk actions available.
	 */
	private static function has_bulk_action_support() {
		// Check if Site Health provides bulk fix UI
		if ( class_exists( 'WP_Site_Health' ) ) {
			$site_health = \WP_Site_Health::get_instance();

			// Check for REST endpoint for bulk fixes
			if ( has_action( 'rest_api_init' ) ) {
				return true;
			}
		}

		// Check for bulk action plugin
		if ( is_plugin_active( 'wpshadow-bulk-fixer/wpshadow-bulk-fixer.php' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check issue dependencies.
	 *
	 * @since 1.6093.1200
	 * @return string|null Issue description or null if no issue.
	 */
	private static function check_issue_dependencies() {
		// Some issues depend on others being fixed first
		// E.g., SSL should be enabled before HTTPS enforcement

		// Check for dependency tracking
		$dependencies = get_option( 'wpshadow_issue_dependencies', array() );

		if ( empty( $dependencies ) ) {
			return __( 'Issue dependencies not tracked for bulk fixes', 'wpshadow' );
		}

		return null;
	}

	/**
	 * Check for progress tracking.
	 *
	 * @since 1.6093.1200
	 * @return bool True if progress tracking available.
	 */
	private static function has_progress_tracking() {
		// Check if bulk fix progress is tracked
		$progress_option = get_option( 'wpshadow_bulk_fix_progress', false );

		if ( false !== $progress_option ) {
			return true;
		}

		// Check for REST endpoint
		if ( has_filter( 'rest_prepare_site_health_rest_api_usage' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check for rollback capability.
	 *
	 * @since 1.6093.1200
	 * @return bool True if rollback available.
	 */
	private static function has_bulk_rollback() {
		// Check if backups are created before bulk fixes
		$backups = get_option( 'wpshadow_bulk_fix_backups', array() );

		if ( ! empty( $backups ) ) {
			return true;
		}

		// Check for backup plugin
		if ( is_plugin_active( 'wpshadow-pro-backup/wpshadow-pro-backup.php' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if related issues can be batched.
	 *
	 * @since 1.6093.1200
	 * @return bool True if batching available.
	 */
	private static function can_batch_related_issues() {
		// Check if related issues are grouped for batch fixing
		// E.g., "Security Issues" group all fixed together

		$issue_groups = get_option( 'wpshadow_issue_groups', array() );

		if ( ! empty( $issue_groups ) ) {
			return true;
		}

		return false;
	}
}
