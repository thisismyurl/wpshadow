<?php
/**
 * Feed Content Encoding Diagnostic
 *
 * Checks if the feed content encoding is set correctly (UTF-8, etc.).
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
 * Diagnostic_Feed_Content_Encoding Class
 *
 * Checks if the feed content encoding is set correctly (UTF-8, etc.).
 */
class Diagnostic_Feed_Content_Encoding extends Diagnostic_Base {
	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'feed-content-encoding';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Feed Content Encoding';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if the feed content encoding is set correctly (UTF-8, etc.).';

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
		$feed_url = get_feed_link();
		$response = wp_remote_get( $feed_url, array( 'timeout' => 5 ) );
		if ( is_wp_error( $response ) ) {
			return null;
		}
		$headers = wp_remote_retrieve_headers( $response );
		$encoding = isset( $headers['content-type'] ) ? $headers['content-type'] : '';
		if ( false === stripos( $encoding, 'charset=utf-8' ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Feed content encoding is not set to UTF-8.', 'wpshadow' ),
				'severity'    => 'medium',
				'threat_level'=> 50,
				'auto_fixable'=> false,
				'kb_link'     => 'https://wpshadow.com/kb/feed-content-encoding',
			);
		}
		return null;
	}
}
