<?php
/**
 * Feed URL Accessibility Diagnostic
 *
 * Checks if the main feed URLs are accessible and return valid XML.
 *
 * @since 0.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Feed_URL_Accessibility Class
 *
 * Checks if the main feed URLs are accessible and return valid XML.
 */
class Diagnostic_Feed_URL_Accessibility extends Diagnostic_Base {
	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'feed-url-accessibility';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Feed URL Accessibility';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if the main feed URLs are accessible and return valid XML.';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'feed';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$feed_urls = array(
			get_feed_link(),
			get_feed_link('comments_rss2'),
		);
		$inaccessible = array();
		foreach ( $feed_urls as $url ) {
			$response = wp_remote_get( $url, array( 'timeout' => 5 ) );
			if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
				$inaccessible[] = $url;
				continue;
			}
			$body = wp_remote_retrieve_body( $response );
			if ( false === strpos( $body, '<rss' ) && false === strpos( $body, '<feed' ) ) {
				$inaccessible[] = $url;
			}
		}
		if ( ! empty( $inaccessible ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Some feed URLs are inaccessible or do not return valid XML.', 'wpshadow' ),
				'urls'        => $inaccessible,
				'severity'    => 'high',
				'threat_level'=> 70,
				'auto_fixable'=> false,
				'kb_link'     => 'https://wpshadow.com/kb/feed-url-accessibility?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}
		return null;
	}
}
