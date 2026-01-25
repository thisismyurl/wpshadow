<?php
/**
 * Treatment: Limit Post Revisions
 *
 * Limits post revisions and optionally cleans up old ones.
 *
 * Philosophy: Helpful Neighbor (#1) - Offers choice, explains impact
 * KB Link: https://wpshadow.com/kb/post-revisions-bloat
 * Training: https://wpshadow.com/training/post-revisions-bloat
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;
use WPShadow\Core\KPI_Tracker;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Limit Post Revisions treatment
 */
class Treatment_Limit_Post_Revisions extends Treatment_Base {

	/**
	 * Apply the treatment
	 *
	 * @param array $options Treatment options
	 * @return bool Success status
	 */
	public static function apply( array $options = array() ): bool {
		$limit       = isset( $options['limit'] ) ? (int) $options['limit'] : 5;
		$cleanup_old = isset( $options['cleanup_old'] ) ? (bool) $options['cleanup_old'] : false;

		// Create backup
		$backup = array(
			'previous_limit' => defined( 'WP_POST_REVISIONS' ) ? WP_POST_REVISIONS : 'unlimited',
			'timestamp'      => time(),
		);

		self::create_backup( $backup );

		// Add constant to wp-config.php
		$config_path = ABSPATH . 'wp-config.php';
		if ( ! file_exists( $config_path ) || ! is_writable( $config_path ) ) {
			return false;
		}

		$config_content = file_get_contents( $config_path );

		// Check if already defined
		if ( strpos( $config_content, 'WP_POST_REVISIONS' ) !== false ) {
			// Update existing
			$config_content = preg_replace(
				"/define\s*\(\s*['\"]WP_POST_REVISIONS['\"]\s*,\s*[^)]+\s*\)\s*;/",
				"define( 'WP_POST_REVISIONS', {$limit} );",
				$config_content
			);
		} else {
			// Add new (before "That's all")
			$config_content = str_replace(
				"/* That's all, stop editing!",
				"define( 'WP_POST_REVISIONS', {$limit} );\n\n/* That's all, stop editing!",
				$config_content
			);
		}

		$result = file_put_contents( $config_path, $config_content );

		// Optionally cleanup old revisions
		if ( $result && $cleanup_old ) {
			global $wpdb;

			// Keep only the latest N revisions per post
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Uses wpdb table property, properly prepared with placeholders
			$deleted = $wpdb->query(
				$wpdb->prepare(
					"DELETE FROM {$wpdb->posts} 
					WHERE post_type = 'revision' 
					AND ID IN (
						SELECT ID FROM (
							SELECT r.ID,
								ROW_NUMBER() OVER (PARTITION BY r.post_parent ORDER BY r.post_modified DESC) as row_num
							FROM {$wpdb->posts} r
							WHERE r.post_type = 'revision'
						) as ranked
						WHERE row_num > %d
					)",
					$limit
				)
			);

			// Clean up orphaned revision meta
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Uses wpdb table properties, no user input
			$wpdb->query(
				"DELETE pm FROM {$wpdb->postmeta} pm 
				LEFT JOIN {$wpdb->posts} p ON pm.post_id = p.ID 
				WHERE p.ID IS NULL"
			);

			// Track cleanup in backup
			$backup['deleted_count'] = $deleted;
			self::create_backup( $backup );
		}

		// Track KPI
		if ( $result ) {
			KPI_Tracker::record_treatment_applied( __CLASS__, 2 );
		}

		return (bool) $result;
	}

	/**
	 * Undo the treatment
	 *
	 * @return bool Success status
	 */
	public static function undo(): bool {
		$backup = self::restore_backup();
		if ( ! $backup ) {
			return false;
		}

		$config_path = ABSPATH . 'wp-config.php';
		if ( ! file_exists( $config_path ) || ! is_writable( $config_path ) ) {
			return false;
		}

		$config_content = file_get_contents( $config_path );

		// Restore previous value
		$previous = $backup['previous_limit'];
		if ( $previous === 'unlimited' || $previous === true ) {
			// Remove the line
			$config_content = preg_replace(
				"/define\s*\(\s*['\"]WP_POST_REVISIONS['\"]\s*,\s*[^)]+\s*\)\s*;\n?/",
				'',
				$config_content
			);
		} else {
			// Restore specific value
			$config_content = preg_replace(
				"/define\s*\(\s*['\"]WP_POST_REVISIONS['\"]\s*,\s*[^)]+\s*\)\s*;/",
				"define( 'WP_POST_REVISIONS', {$previous} );",
				$config_content
			);
		}

		return (bool) file_put_contents( $config_path, $config_content );
	}

	/**
	 * Get display name
	 *
	 * @return string
	 */
	public static function get_name(): string {
		return __( 'Limit Post Revisions', 'wpshadow' );
	}

	/**
	 * Get description
	 *
	 * @return string
	 */
	public static function get_description(): string {
		return sprintf(
			__( 'Limits post revisions to a reasonable number (default: 5) and optionally cleans up old revisions. <a href="%s" target="_blank">Learn about revision management</a>', 'wpshadow' ),
			'https://wpshadow.com/kb/post-revisions-bloat'
		);
	}
}
