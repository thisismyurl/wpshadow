<?php
/**
 * Diagnostic: Sustainability Backup Redundancy
 *
 * Checks if backups are configured in multiple locations for redundancy.
 * This ensures that site data is protected against single point of failure.
 *
 * @since   1.2601.2148
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Sustainability_Backup_Redundancy Class
 *
 * Verifies that multiple backup strategies are in place to ensure
 * site data redundancy and disaster recovery preparedness.
 */
class Diagnostic_Sustainability_Backup_Redundancy extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'sustainability-backup-redundancy';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Sustainability Backup Redundancy';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Automatically initialized lean diagnostic for Sustainability Backup Redundancy. Optimized for minimal overhead while surfacing high-value signals.';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'general';

	/**
	 * The family label
	 *
	 * @var string
	 */
	protected static $family_label = 'General';

	/**
	 * Get diagnostic ID.
	 *
	 * @since  1.2601.2148
	 * @return string Diagnostic identifier.
	 */
	public static function get_id(): string {
		return 'sustainability-backup-redundancy';
	}

	/**
	 * Get diagnostic name.
	 *
	 * @since  1.2601.2148
	 * @return string Diagnostic name.
	 */
	public static function get_name(): string {
		return __( 'Are backups in multiple locations?', 'wpshadow' );
	}

	/**
	 * Get diagnostic description.
	 *
	 * @since  1.2601.2148
	 * @return string Diagnostic description.
	 */
	public static function get_description(): string {
		return __( 'Are backups in multiple locations? Part of Sustainability & Long-Term Health analysis.', 'wpshadow' );
	}

	/**
	 * Get diagnostic category.
	 *
	 * @since  1.2601.2148
	 * @return string Diagnostic category.
	 */
	public static function get_category(): string {
		return 'sustainability';
	}

	/**
	 * Run the diagnostic test.
	 *
	 * This method is maintained for backward compatibility.
	 * It delegates to check() for the actual implementation.
	 *
	 * @since  1.2601.2148
	 * @return array Finding data or empty array if no issue.
	 */
	public static function run(): array {
		$result = self::check();

		if ( null === $result ) {
			return array();
		}

		return $result;
	}

	/**
	 * Get threat level for this finding (0-100).
	 *
	 * @since  1.2601.2148
	 * @return int Threat level value between 0 and 100.
	 */
	public static function get_threat_level(): int {
		return 72;
	}

	/**
	 * Get KB article URL.
	 *
	 * @since  1.2601.2148
	 * @return string KB article URL.
	 */
	public static function get_kb_article(): string {
		return 'https://wpshadow.com/kb/sustainability-backup-redundancy/';
	}

	/**
	 * Get training video URL.
	 *
	 * @since  1.2601.2148
	 * @return string Training video URL.
	 */
	public static function get_training_video(): string {
		return 'https://wpshadow.com/training/sustainability-backup-redundancy/';
	}

	/**
	 * Run the diagnostic check.
	 *
	 * Checks if backups are configured in multiple locations.
	 * Multiple backup plugins indicate redundancy, which is important
	 * for disaster recovery and long-term site sustainability.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check(): ?array {
		// Ensure is_plugin_active() is available.
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		// Common backup plugins to check for.
		$backup_plugins = array(
			'duplicator/duplicator.php',
			'updraftplus/updraftplus.php',
			'backwpup/backwpup.php',
			'wp-database-backup/wp-database-backup.php',
			'all-in-one-wp-migration/all-in-one-wp-migration.php',
			'jetpack/jetpack.php',
			'backupbuddy/backupbuddy.php',
			'blogvault-real-time-backup/blogvault.php',
			'backup-wp/index.php',
			'wp-db-backup/wp-db-backup.php',
		);

		$active_backup_plugins = array();
		foreach ( $backup_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_backup_plugins[] = $plugin;
			}
		}

		$backup_count = count( $active_backup_plugins );

		// Require at least 2 backup plugins for redundancy.
		if ( $backup_count < 2 ) {
			$message = '';

			if ( 0 === $backup_count ) {
				$message = __( 'No backup plugins detected. For long-term sustainability, install at least two backup plugins to ensure redundancy.', 'wpshadow' );
			} elseif ( 1 === $backup_count ) {
				$message = __( 'Only one backup plugin detected. For redundancy, install an additional backup plugin or configure backups to multiple destinations.', 'wpshadow' );
			}

			return \WPShadow\Core\Diagnostic_Lean_Checks::build_finding(
				'sustainability-backup-redundancy',
				'Sustainability Backup Redundancy',
				$message,
				'sustainability',
				'high',
				72,
				'sustainability-backup-redundancy'
			);
		}

		return null;
	}

	/**
	 * Live test for this diagnostic.
	 *
	 * Diagnostic: Sustainability Backup Redundancy
	 * Slug: sustainability-backup-redundancy
	 *
	 * Test Purpose:
	 * - Verify that check() method returns the correct result based on site state
	 * - PASS: check() returns NULL when diagnostic condition is NOT met (site is healthy)
	 * - FAIL: check() returns array when diagnostic condition IS met (issue found)
	 * - Description: Verifies backup redundancy by checking for multiple active backup plugins.
	 *
	 * @since  1.2601.2148
	 * @return array {
	 *     Test result array.
	 *
	 *     @type bool   $passed  Whether the test passed.
	 *     @type string $message Human-readable test result message.
	 * }
	 */
	public static function test_live_sustainability_backup_redundancy(): array {
		$result = self::check();

		// Test logic: If check() returns null, the site has adequate backup redundancy.
		// If check() returns an array, there's an issue with backup redundancy.
		if ( null === $result ) {
			return array(
				'passed'  => true,
				'message' => __( 'Backup redundancy check passed: Multiple backup strategies detected.', 'wpshadow' ),
			);
		}

		// Issue detected - test fails but this is expected if site lacks redundancy.
		$severity = isset( $result['severity'] ) ? $result['severity'] : 'unknown';
		$message  = isset( $result['description'] ) ? $result['description'] : __( 'Backup redundancy issue detected.', 'wpshadow' );

		return array(
			'passed'  => false,
			'message' => sprintf(
				/* translators: 1: severity level, 2: issue description */
				__( 'Backup redundancy check failed (Severity: %1$s): %2$s', 'wpshadow' ),
				$severity,
				$message
			),
		);
	}
}
