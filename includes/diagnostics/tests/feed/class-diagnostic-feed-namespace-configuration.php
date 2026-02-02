<?php
/**
 * Feed Namespace Configuration Diagnostic
 *
 * Checks if the feed XML includes required namespaces for compatibility.
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
 * Diagnostic_Feed_Namespace_Configuration Class
 *
 * Checks if the feed XML includes required namespaces for compatibility.
 */
class Diagnostic_Feed_Namespace_Configuration extends Diagnostic_Base {
	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'feed-namespace-configuration';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Feed Namespace Configuration';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if the feed XML includes required namespaces for compatibility.';

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
		$body = wp_remote_retrieve_body( $response );
		if ( false === strpos( $body, 'xmlns="http://www.w3.org/2005/Atom"' ) && false === strpos( $body, 'xmlns:content="http://purl.org/rss/1.0/modules/content/"' ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Feed XML is missing required namespaces for compatibility.', 'wpshadow' ),
				'severity'    => 'medium',
				'threat_level'=> 40,
				'auto_fixable'=> false,
				'kb_link'     => 'https://wpshadow.com/kb/feed-namespace-configuration',
			);
		}
		return null;
	}
}
