<?php
/**
 * Database Query Caching Diagnostic
 *
 * Analyzes query result caching and optimization strategies.
 *
 * @since   1.6033.2130
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Database Query Caching Diagnostic
 *
 * Evaluates query result caching implementation and effectiveness.
 *
 * @since 1.6033.2130
 */
class Diagnostic_Database_Query_Caching extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'database-query-caching';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Database Query Caching';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Analyzes query result caching and optimization strategies';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6033.2130
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		// Check if object caching is available
		$has_object_cache = wp_using_ext_object_cache();

		// Check for common object cache plugins
		$cache_plugins = array(
			'redis-cache/redis-cache.php'           => 'Redis Object Cache',
			'wp-redis/wp-redis.php'                 => 'WP Redis',
			'memcached/memcached.php'               => 'Memcached',
			'w3-total-cache/w3-total-cache.php'     => 'W3 Total Cache',
			'wp-rocket/wp-rocket.php'               => 'WP Rocket',
		);

		$active_cache_plugin = null;
		foreach ( $cache_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_cache_plugin = $name;
				break;
			}
		}

		// Check MySQL query cache status (deprecated in MySQL 8.0+)
		$query_cache_status = $wpdb->get_var( "SHOW VARIABLES WHERE Variable_name = 'query_cache_type'" );
		$query_cache_enabled = $query_cache_status && $query_cache_status !== 'OFF';

		// Check database server version
		$db_version = $wpdb->get_var( 'SELECT VERSION()' );
		$mysql_version = floatval( $db_version );

		// Get site metrics
		$post_count = wp_count_posts()->publish ?? 0;
		$is_large_site = $post_count > 500;

		// Generate findings if no object caching on large sites
		if ( ! $has_object_cache && $is_large_site ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of posts */
					__( 'No object caching configured for site with %s posts. Object caching significantly reduces database queries.', 'wpshadow' ),
					number_format_i18n( $post_count )
				),
				'severity'     => 'high',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/database-query-caching',
				'meta'         => array(
					'has_object_cache'     => $has_object_cache,
					'active_cache_plugin'  => $active_cache_plugin,
					'post_count'           => $post_count,
					'query_cache_enabled'  => $query_cache_enabled,
					'mysql_version'        => $mysql_version,
					'recommendation'       => 'Install Redis Object Cache or Memcached',
					'impact_estimate'      => '50-80% reduction in database queries',
					'typical_improvement'  => '200-500ms faster page loads',
					'cache_solutions'      => array(
						'Redis Object Cache (recommended)',
						'Memcached',
						'W3 Total Cache with Redis/Memcached',
						'WP Rocket (with object caching add-on)',
					),
				),
			);
		}

		// Inform about MySQL 8.0+ query cache deprecation
		if ( $mysql_version >= 8.0 && $is_large_site && ! $has_object_cache ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'MySQL 8.0+ removed query cache. Implement application-level caching (Redis/Memcached) for query result caching.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/database-query-caching',
				'meta'         => array(
					'mysql_version'       => $mysql_version,
					'query_cache_removed' => true,
					'has_object_cache'    => $has_object_cache,
					'recommendation'      => 'Implement Redis or Memcached object caching',
					'mysql_8_note'        => 'MySQL 8.0+ removed query cache in favor of InnoDB buffer pool',
					'alternative'         => 'Use object caching for application-level query result caching',
				),
			);
		}

		return null;
	}
}
