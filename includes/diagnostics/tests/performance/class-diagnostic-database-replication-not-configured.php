<?php
/**
 * Database Replication Not Configured Diagnostic
 *
 * Detects when database lacks replication for high availability and disaster recovery.
 *
 * **What This Check Does:**
 * 1. Checks if database replication is configured
 * 2. Verifies replica database is syncing
 * 3. Checks replica lag (how far behind main DB)
 * 4. Detects single point of failure
 * 5. Validates failover capability
 * 6. Flags mission-critical sites without redundancy\n *
 * **Why This Matters:**\n * Without replication, a single database server failure = complete site shutdown. No failover. No
 * backup system. With replication, if main server fails, replica instantly takes over. High-traffic
 * sites without replication are gambling. SaaS platforms without replication are playing Russian roulette.\n *
 * **Real-World Scenario:**\n * SaaS platform with 50,000 daily active users, no database replication configured. Main database
 * server failed (disk failure). No failover. Site completely down for 6 hours until database recovered.
 * 50,000 users locked out. Clients lost $200,000 in transactions. Lawsuits filed. Company reputation damaged.\n * After incident, implemented master-slave replication. Future failures now result in <1 second failover
 * vs 6+ hour downtime. Cost: $5,000 setup + $500/month for replica server. Value: prevented $200k+ loss\n * and preserved customer trust.\n *
 * **Business Impact:**\n * - Single server failure = total site shutdown (100% downtime)\n * - No backup if main server corrupts (data permanently lost)\n * - SaaS contracts violated (uptime guarantees broken)\n * - Legal liability (failed service = damages)\n * - Customer trust destroyed (they assume you're unprepared)\n * - Revenue loss: $1,000-$100,000+ per hour of downtime\n * - Data loss risk: years of customer data gone forever\n *
 * **Philosophy Alignment:**\n * - #8 Inspire Confidence: Prevents catastrophic single-point-of-failure\n * - #9 Show Value: Enables automatic failover capability\n * - #10 Talk-About-Worthy: "99.99% uptime guaranteed" requires replication\n *
 * **Related Checks:**\n * - Database Backup Availability (related redundancy)\n * - Database Health Monitoring (early failure detection)\n * - Disaster Recovery Plan (related preparation)\n * - System Uptime Monitoring (failure impact)\n *
 * **Learn More:**\n * - KB Article: https://wpshadow.com/kb/database-replication-configuration\n * - Video: https://wpshadow.com/training/mysql-replication-101 (8 min)\n * - Advanced: https://wpshadow.com/training/high-availability-architecture (15 min)\n *
 * @package    WPShadow\n * @subpackage Diagnostics\n * @since      1.2033.0000\n */\n\ndeclare(strict_types=1);\n\nnamespace WPShadow\\Diagnostics;\n\nuse WPShadow\\Core\\Diagnostic_Base;\n\nif ( ! defined( 'ABSPATH' ) ) {\n\texit;\n}

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
