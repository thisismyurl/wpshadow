<?php
/**
 * Forum Performance at Scale Treatment
 *
 * Verifies forum sites are optimized for high traffic and large datasets
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6031.1445
 */

declare(strict_types=1);

namespace WPShadow\Treatments\Forum;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
exit;
}

/**
 * Treatment_ForumPerformanceScale Class
 *
 * Checks for caching, database optimization, CDN, lazy loading
 *
 * @since 1.6031.1445
 */
class Treatment_ForumPerformanceScale extends Treatment_Base {

/**
 * The treatment slug
 *
 * @var string
 */
protected static $slug = 'forum-performance-scale';

/**
 * The treatment title
 *
 * @var string
 */
protected static $title = 'Forum Performance at Scale';

/**
 * The treatment description
 *
 * @var string
 */
protected static $description = 'Verifies forum sites are optimized for high traffic and large datasets';

/**
 * The family this treatment belongs to
 *
 * @var string
 */
protected static $family = 'forum';

/**
 * Run the treatment check.
 *
 * @since  1.6031.1445
 * @return array|null Finding array if issue found, null otherwise.
 */
public static function check() {
		// Check for forum plugins.
		$active_plugins = get_option( 'active_plugins', array() );
		$forum_plugins = array( 'bbpress', 'buddypress', 'wpforo', 'asgaros-forum' );
		$has_forum = false;

		foreach ( $active_plugins as $plugin ) {
			foreach ( $forum_plugins as $f_plugin ) {
				if ( stripos( $plugin, $f_plugin ) !== false ) {
					$has_forum = true;
					break 2;
				}
			}
		}

		if ( ! $has_forum ) {
			return null;
		}

		$issues = array();

		// Check for object caching.
		if ( ! wp_using_ext_object_cache() ) {
			$issues[] = __( 'No external object cache configured (Redis/Memcached)', 'wpshadow' );
		}

		// Check for caching plugins.
		$caching_plugins = array( 'cache', 'wp-rocket', 'w3-total-cache', 'wp-super-cache', 'litespeed' );
		$has_caching = false;

		foreach ( $active_plugins as $plugin ) {
			foreach ( $caching_plugins as $c_plugin ) {
				if ( stripos( $plugin, $c_plugin ) !== false ) {
					$has_caching = true;
					break 2;
				}
			}
		}

		if ( ! $has_caching ) {
			$issues[] = __( 'No page caching plugin detected', 'wpshadow' );
		}

		// Check for CDN.
		$cdn_plugins = array( 'cdn', 'cloudflare', 'bunnycdn', 'cloudfront' );
		$has_cdn = false;

		foreach ( $active_plugins as $plugin ) {
			foreach ( $cdn_plugins as $cdn_plugin ) {
				if ( stripos( $plugin, $cdn_plugin ) !== false ) {
					$has_cdn = true;
					break 2;
				}
			}
		}

		if ( ! $has_cdn ) {
			$issues[] = __( 'No CDN configured for static assets', 'wpshadow' );
		}

		// Check database query load (sample).
		global $wpdb;
		if ( defined( 'SAVEQUERIES' ) && SAVEQUERIES && ! empty( $wpdb->queries ) ) {
			$query_count = count( $wpdb->queries );
			if ( $query_count > 100 ) {
				$issues[] = sprintf(
					/* translators: %d: number of queries */
					__( 'High database query count detected: %d queries', 'wpshadow' ),
					$query_count
				);
			}
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: comma-separated list of issues */
				__( 'Forum performance concerns: %s. High-traffic forums need caching and CDN.', 'wpshadow' ),
				implode( ', ', $issues )
			),
			'severity'     => 'medium',
			'threat_level' => 50,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/forum-performance-scale',
		);
