<?php
/**
 * RSS Feed URL Accessibility Diagnostic
 *
 * Verifies RSS feed URLs are accessible and properly configured for content syndication.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * RSS Feed URL Accessibility Diagnostic Class
 *
 * Checks that RSS feed endpoints are functioning correctly.
 *
 * @since 0.6093.1200
 */
class Diagnostic_RSS_Feed_URL_Accessibility extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'rss-feed-url-accessibility';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'RSS Feed URL Accessibility';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies RSS feed URLs are accessible';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'rss';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check if feeds are enabled.
		$feed_links = get_option( 'default_feed_links', true );
		if ( ! $feed_links ) {
			$issues[] = __( 'RSS feed links are disabled in theme settings', 'wpshadow' );
		}

		// Check main RSS feed.
		$rss_url = get_feed_link( 'rss2' );
		if ( empty( $rss_url ) ) {
			$issues[] = __( 'RSS feed URL could not be generated', 'wpshadow' );
		}

		// Check if feed uses custom URL.
		$feedburner_url = get_option( 'feedburner_url' );
		if ( ! empty( $feedburner_url ) ) {
			// FeedBurner is deprecated - warn users.
			$issues[] = __( 'Site uses deprecated FeedBurner service for RSS feeds', 'wpshadow' );
		}

		// Check feed permalink structure.
		$permalink_structure = get_option( 'permalink_structure' );
		if ( empty( $permalink_structure ) ) {
			$issues[] = __( 'Plain permalinks may cause feed accessibility issues', 'wpshadow' );
		}

		// Check if any plugins are blocking feeds.
		if ( has_filter( 'do_feed_rss2' ) ) {
			$filters = $GLOBALS['wp_filter']['do_feed_rss2'] ?? array();
			foreach ( $filters as $priority => $callbacks ) {
				foreach ( $callbacks as $callback ) {
					$callback_name = is_array( $callback['function'] ) ? get_class( $callback['function'][0] ) : $callback['function'];
					if ( false !== strpos( strtolower( (string) $callback_name ), 'disable' ) ||
					     false !== strpos( strtolower( (string) $callback_name ), 'block' ) ) {
						$issues[] = sprintf(
							/* translators: %s: callback name */
							__( 'Plugin or theme may be blocking RSS feeds: %s', 'wpshadow' ),
							$callback_name
						);
						break 2;
					}
				}
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/rss-feed-accessibility?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
