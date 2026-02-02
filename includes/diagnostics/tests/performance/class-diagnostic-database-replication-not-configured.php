<?php
/**
 * Database Replication Not Configured Diagnostic
 *
 * Checks if database replication is configured for high availability.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2033.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Database_Replication_Not_Configured Class
 *
 * Detects when database lacks replication/redundancy configuration.
 * Critical for high-traffic sites and mission-critical applications.
 *
 * @since 1.2033.0000
 */
class Diagnostic_Database_Replication_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'database-replication-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Database Replication Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for database replication configuration';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * Checks for:
	 * - HyperDB configuration
	 * - Read replica configuration
	 * - Database cluster setup
	 * - Multi-master replication
	 *
	 * @since  1.2033.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		// Check for HyperDB (WordPress's database replication plugin).
		if ( class_exists( 'hyperdb' ) ) {
			return null;
		}

		// Check for custom db.php drop-in (often used for replication).
		$db_dropin = WP_CONTENT_DIR . '/db.php';
		if ( file_exists( $db_dropin ) ) {
			// Basic check if it contains replication keywords.
			$content = file_get_contents( $db_dropin );
			if ( false !== stripos( $content, 'replica' ) || false !== stripos( $content, 'replication' ) ) {
				return null;
			}
		}

		// Check if this is a high-traffic site (>100k pageviews/month).
		$is_high_traffic = false;
		if ( function_exists( 'jetpack_stats_get_total_views' ) ) {
			$stats = jetpack_stats_get_total_views();
			if ( isset( $stats['total'] ) && $stats['total'] > 3000000 ) { // ~100k/month.
				$is_high_traffic = true;
			}
		}

		// Only flag for high-traffic sites or multisite networks.
		if ( $is_high_traffic || is_multisite() ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Your database has no replication configured. For high-traffic sites, a single database server is a single point of failure. If the database goes down, your entire site becomes unavailable. Database replication provides: automatic failover (seconds, not hours), load distribution across read replicas (faster page loads), and protection against hardware failure. E-commerce sites: Every minute of database downtime = lost revenue.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/database-replication-setup',
			);
		}

		return null;
	}
}
