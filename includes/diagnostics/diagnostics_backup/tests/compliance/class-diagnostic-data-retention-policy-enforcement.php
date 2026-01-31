<?php
/**
 * Data Retention Policy Enforcement Diagnostic
 *
 * Validates data retention policy exists and is actually enforced.
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
 * Data Retention Policy Enforcement Class
 *
 * Tests whether data retention policy is documented and enforced.
 *
 * @since 1.26028.1905
 */
class Diagnostic_Data_Retention_Policy_Enforcement extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'data-retention-policy-enforcement';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Data Retention Policy Enforcement';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates data retention policy exists and is actually enforced';

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

		// Check if data retention policy is documented.
		$policy_page = self::find_retention_policy();
		if ( ! $policy_page ) {
			$issues[] = __( 'No documented data retention policy found', 'wpshadow' );
		}

		// Check for inactive users.
		$inactive_users = self::count_inactive_users();
		if ( $inactive_users > 1000 ) {
			$issues[] = sprintf(
				/* translators: %d: number of inactive users */
				__( '%d user accounts inactive for 2+ years (potential GDPR violation)', 'wpshadow' ),
				$inactive_users
			);
		}

		// Check for automatic deletion mechanisms.
		if ( ! self::has_automatic_deletion() ) {
			$issues[] = __( 'No automatic data deletion configured (cron jobs or plugins)', 'wpshadow' );
		}

		// Check WooCommerce order retention.
		if ( class_exists( 'WooCommerce' ) ) {
			$order_retention = self::check_order_retention();
			if ( $order_retention['has_old_orders'] ) {
				$issues[] = sprintf(
					/* translators: %d: number of old orders */
					__( '%d WooCommerce orders older than 7 years (consider archiving)', 'wpshadow' ),
					$order_retention['old_order_count']
				);
			}
		}

		// Check log file accumulation.
		$log_size = self::check_log_files();
		if ( $log_size > 100 * 1024 * 1024 ) { // 100 MB.
			$issues[] = sprintf(
				/* translators: %s: log file size */
				__( 'Log files accumulating (%s total) - no automatic rotation', 'wpshadow' ),
				size_format( $log_size )
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( ' ', $issues ),
				'severity'     => 'high',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/data-retention-policy-enforcement',
				'meta'         => array(
					'inactive_users'   => $inactive_users,
					'total_log_size'   => $log_size,
					'has_policy'       => (bool) $policy_page,
					'issues_found'     => count( $issues ),
				),
			);
		}

		return null;
	}

	/**
	 * Find data retention policy page.
	 *
	 * @since  1.26028.1905
	 * @return \WP_Post|false Post object or false if not found.
	 */
	private static function find_retention_policy() {
		// Check privacy policy for retention section.
		$privacy_page_id = (int) get_option( 'wp_page_for_privacy_policy', 0 );
		if ( $privacy_page_id > 0 ) {
			$privacy_page = get_post( $privacy_page_id );
			if ( $privacy_page ) {
				// Check if content mentions retention.
				if ( false !== stripos( $privacy_page->post_content, 'retention' ) ||
					 false !== stripos( $privacy_page->post_content, 'how long we keep' ) ) {
					return $privacy_page;
				}
			}
		}

		// Search for dedicated retention policy page.
		$pages = get_posts(
			array(
				'post_type'      => 'page',
				'posts_per_page' => 1,
				's'              => 'data retention',
				'post_status'    => 'publish',
			)
		);

		return ! empty( $pages ) ? $pages[0] : false;
	}

	/**
	 * Count users inactive for 2+ years.
	 *
	 * @since  1.26028.1905
	 * @return int Number of inactive users.
	 */
	private static function count_inactive_users() {
		global $wpdb;

		$two_years_ago = gmdate( 'Y-m-d H:i:s', strtotime( '-2 years' ) );

		$query = $wpdb->prepare(
			"SELECT COUNT(*) FROM {$wpdb->users} u
			LEFT JOIN {$wpdb->usermeta} um ON u.ID = um.user_id AND um.meta_key = 'last_login'
			WHERE u.user_registered < %s
			AND (um.meta_value IS NULL OR um.meta_value < %s)",
			$two_years_ago,
			$two_years_ago
		);

		return (int) $wpdb->get_var( $query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}

	/**
	 * Check if automatic deletion is configured.
	 *
	 * @since  1.26028.1905
	 * @return bool True if automatic deletion found.
	 */
	private static function has_automatic_deletion() {
		// Check for common GDPR plugins.
		$gdpr_plugins = array(
			'gdpr-cookie-compliance/gdpr-cookie-compliance.php',
			'cookie-law-info/cookie-law-info.php',
			'wp-gdpr-compliance/wp-gdpr-compliance.php',
		);

		foreach ( $gdpr_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return true;
			}
		}

		// Check for scheduled cron jobs related to cleanup.
		$cron_jobs = _get_cron_array();
		if ( $cron_jobs ) {
			foreach ( $cron_jobs as $timestamp => $cron ) {
				foreach ( $cron as $hook => $dings ) {
					if ( false !== stripos( $hook, 'cleanup' ) ||
						 false !== stripos( $hook, 'delete' ) ||
						 false !== stripos( $hook, 'purge' ) ) {
						return true;
					}
				}
			}
		}

		return false;
	}

	/**
	 * Check WooCommerce order retention.
	 *
	 * @since  1.26028.1905
	 * @return array Order retention data.
	 */
	private static function check_order_retention() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return array(
				'has_old_orders'   => false,
				'old_order_count'  => 0,
			);
		}

		$seven_years_ago = gmdate( 'Y-m-d H:i:s', strtotime( '-7 years' ) );

		$old_orders = wc_get_orders(
			array(
				'limit'        => 1,
				'date_created' => '<' . $seven_years_ago,
				'return'       => 'ids',
			)
		);

		$old_order_count = 0;
		if ( ! empty( $old_orders ) ) {
			// Count all old orders.
			$old_order_count = count(
				wc_get_orders(
					array(
						'limit'        => -1,
						'date_created' => '<' . $seven_years_ago,
						'return'       => 'ids',
					)
				)
			);
		}

		return array(
			'has_old_orders'  => $old_order_count > 0,
			'old_order_count' => $old_order_count,
		);
	}

	/**
	 * Check log file sizes.
	 *
	 * @since  1.26028.1905
	 * @return int Total size of log files in bytes.
	 */
	private static function check_log_files() {
		$log_locations = array(
			WP_CONTENT_DIR . '/debug.log',
			WP_CONTENT_DIR . '/wc-logs/',
			WP_CONTENT_DIR . '/uploads/wc-logs/',
		);

		$total_size = 0;

		foreach ( $log_locations as $location ) {
			if ( file_exists( $location ) ) {
				if ( is_file( $location ) ) {
					$total_size += filesize( $location );
				} elseif ( is_dir( $location ) ) {
					$total_size += self::get_directory_size( $location );
				}
			}
		}

		return $total_size;
	}

	/**
	 * Get total size of directory.
	 *
	 * @since  1.26028.1905
	 * @param  string $directory Directory path.
	 * @return int Total size in bytes.
	 */
	private static function get_directory_size( $directory ) {
		$size = 0;
		$files = glob( rtrim( $directory, '/' ) . '/*', GLOB_NOSORT );
		
		if ( ! $files ) {
			return 0;
		}

		foreach ( $files as $file ) {
			if ( is_file( $file ) ) {
				$size += filesize( $file );
			} elseif ( is_dir( $file ) ) {
				$size += self::get_directory_size( $file );
			}
		}

		return $size;
	}
}
