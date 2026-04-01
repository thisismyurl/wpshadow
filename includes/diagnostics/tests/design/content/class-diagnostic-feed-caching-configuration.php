<?php
/**
 * Feed Caching Configuration Diagnostic
 *
 * Verifies RSS feed caching is optimally configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Diagnostics\Helpers\Diagnostic_Request_Helper;
use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Feed Caching Configuration Diagnostic Class
 *
 * Checks RSS feed caching configuration for performance.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Feed_Caching_Configuration extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'feed-caching-configuration';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Feed Caching Configuration';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies RSS feed caching configuration';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'reading';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check if object caching is enabled.
		$has_cache = wp_cache_is_enabled();
		if ( ! $has_cache ) {
			$issues[] = __( 'Object caching disabled - feeds will generate on each request', 'wpshadow' );
		}

		// Check RSS settings.
		$rss_use_excerpt = get_option( 'rss_use_excerpt', 0 );

		// Check feed update frequency.
		// WordPress caches feeds with wp_cache_set, typically for WP_FEED_CACHE_TRANSIENT_TIME.
		if ( defined( 'WP_FEED_CACHE_TRANSIENT_TIME' ) ) {
			$cache_time = WP_FEED_CACHE_TRANSIENT_TIME;
			if ( $cache_time < 600 ) {
				$issues[] = sprintf(
					/* translators: %d: cache time in seconds */
					__( 'Feed cache TTL very short (%d seconds) - feeds regenerated frequently', 'wpshadow' ),
					$cache_time
				);
			}
		}

		// Check how many feeds are enabled.
		$feed_plugins_active = 0;
		if ( is_plugin_active( 'feedwordpress/feedwordpress.php' ) ) {
			$feed_plugins_active++;
		}

		// WordPress generates multiple feeds by default:
		// /feed/ - Main feed
		// /feed/rss/ - RSS 0.92
		// /feed/rss2/ - RSS 2.0
		// /feed/atom/ - Atom
		// /category/*/feed/ - Category feeds
		// /tag/*/feed/ - Tag feeds
		// /author/*/feed/ - Author feeds

		// Check if too many category/tag feeds exist.
		global $wpdb;
		$category_count = wp_count_terms( array( 'taxonomy' => 'category' ) );
		$tag_count = wp_count_terms( array( 'taxonomy' => 'post_tag' ) );

		$total_feed_urls = 4 + $category_count + $tag_count;

		if ( $total_feed_urls > 500 ) {
			$issues[] = sprintf(
				/* translators: %d: number of feed URLs */
				__( 'Large number of feed URLs (%d) - may cause performance issues', 'wpshadow' ),
				$total_feed_urls
			);
		}

		// Check if feeds are accessible.
		$main_feed = get_feed_link();
		$response = Diagnostic_Request_Helper::head_result( $main_feed, array( 'timeout' => 5 ) );

		if ( ! $response['success'] ) {
			$issues[] = sprintf(
				/* translators: %s: error */
				__( 'Main feed not accessible: %s', 'wpshadow' ),
				$response['error_message']
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'low',
				'threat_level' => 15,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/feed-caching-configuration?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
