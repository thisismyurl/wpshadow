<?php
/**
 * Feed Custom Endpoints Diagnostic
 *
 * Checks for custom feed endpoints registered in WordPress.
 *
 * @since   1.26032.1921
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Feed_Custom_Endpoints Class
 *
 * Checks for custom feed endpoints registered in WordPress.
 */
class Diagnostic_Feed_Custom_Endpoints extends Diagnostic_Base {
	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'feed-custom-endpoints';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Feed Custom Endpoints';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for custom feed endpoints registered in WordPress.';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'feed';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26032.1921
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wp_rewrite;
		$custom_feeds = array();
		if ( isset( $wp_rewrite->feeds ) && is_array( $wp_rewrite->feeds ) ) {
			foreach ( $wp_rewrite->feeds as $feed ) {
				if ( ! in_array( $feed, array( 'rss2', 'atom', 'rdf', 'rss' ), true ) ) {
					$custom_feeds[] = $feed;
				}
			}
		}
		if ( ! empty( $custom_feeds ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Custom feed endpoints are registered.', 'wpshadow' ),
				'feeds'       => $custom_feeds,
				'severity'    => 'low',
				'threat_level'=> 20,
				'auto_fixable'=> false,
				'kb_link'     => 'https://wpshadow.com/kb/feed-custom-endpoints',
			);
		}
		return null;
	}
}
