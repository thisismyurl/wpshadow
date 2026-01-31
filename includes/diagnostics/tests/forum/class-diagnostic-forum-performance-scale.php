<?php
/**
 * Forum and Community Performance at Scale Diagnostic
 *
 * Checks if forum sites implement proper caching, database optimization,
 * and performance measures to handle large-scale community activity.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Forum
 * @since      1.6031.1454
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics\Forum;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Forum Performance Scale Diagnostic Class
 *
 * Verifies forum sites have performance optimization for scale.
 *
 * @since 1.6031.1454
 */
class Diagnostic_Forum_Performance_Scale extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'forum-performance-scale';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Forum and Community Performance at Scale';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies forum sites implement performance optimization for large-scale activity';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'forum';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6031.1454
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$active_plugins = get_option( 'active_plugins', array() );

		// Check for forum plugins.
		$forum_plugins = array(
			'bbpress',
			'buddypress',
			'wpforo',
			'asgaros-forum',
		);

		$has_forum = false;
		foreach ( $active_plugins as $plugin ) {
			foreach ( $forum_plugins as $forum_plugin ) {
				if ( stripos( $plugin, $forum_plugin ) !== false ) {
					$has_forum = true;
					break 2;
				}
			}
		}

		if ( ! $has_forum ) {
			return null; // No forum.
		}

		$issues = array();

		// Check for caching plugins.
		$has_caching = false;
		$cache_plugins = array(
			'wp-super-cache',
			'w3-total-cache',
			'wp-rocket',
			'litespeed-cache',
			'redis',
		);

		foreach ( $active_plugins as $plugin ) {
			foreach ( $cache_plugins as $cache_plugin ) {
				if ( stripos( $plugin, $cache_plugin ) !== false ) {
					$has_caching = true;
					break 2;
				}
			}
		}

		if ( ! $has_caching ) {
			$issues[] = __( 'No caching plugin detected', 'wpshadow' );
		}

		// Check for database optimization plugins.
		$has_db_optimization = false;
		$db_plugins = array(
			'wp-optimize',
			'wp-sweep',
			'advanced-database-cleaner',
			'database-optimization',
		);

		foreach ( $active_plugins as $plugin ) {
			foreach ( $db_plugins as $db_plugin ) {
				if ( stripos( $plugin, $db_plugin ) !== false ) {
					$has_db_optimization = true;
					break 2;
				}
			}
		}

		if ( ! $has_db_optimization ) {
			$issues[] = __( 'No database optimization plugin found', 'wpshadow' );
		}

		// Check for CDN integration.
		$has_cdn = false;
		$cdn_plugins = array(
			'cloudflare',
			'cdn',
			'jetpack',
		);

		foreach ( $active_plugins as $plugin ) {
			foreach ( $cdn_plugins as $cdn_plugin ) {
				if ( stripos( $plugin, $cdn_plugin ) !== false ) {
					$has_cdn = true;
					break 2;
				}
			}
		}

		if ( ! $has_cdn ) {
			$issues[] = __( 'No CDN integration detected', 'wpshadow' );
		}

		// Check for lazy loading.
		$has_lazy_load = false;
		if ( function_exists( 'wp_lazy_loading_enabled' ) && wp_lazy_loading_enabled( 'img', 'wp_get_attachment_image' ) ) {
			$has_lazy_load = true;
		}

		foreach ( $active_plugins as $plugin ) {
			if ( stripos( $plugin, 'lazy-load' ) !== false ) {
				$has_lazy_load = true;
				break;
			}
		}

		if ( ! $has_lazy_load ) {
			$issues[] = __( 'Lazy loading not enabled for images', 'wpshadow' );
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: comma-separated list of issues */
				__( 'Forum performance concerns: %s. Large community sites need caching, database optimization, and CDN to maintain performance under high load.', 'wpshadow' ),
				implode( ', ', $issues )
			),
			'severity'     => 'medium',
			'threat_level' => 55,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/forum-performance-scale',
		);
	}
}
