<?php
/**
 * Feed XML Validity Diagnostic
 *
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
 * Diagnostic_Feed_XML_Validity Class
 *
 */
class Diagnostic_Feed_XML_Validity extends Diagnostic_Base {
	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'feed-xml-validity';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Feed XML Validity';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if the main feed XML is well-formed and valid.';

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
		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Feed URL is not accessible.', 'wpshadow' ),
				'severity'    => 'high',
				'threat_level'=> 80,
				'auto_fixable'=> false,
				'kb_link'     => 'https://wpshadow.com/kb/feed-xml-validity',
			);
		}
		$body = wp_remote_retrieve_body( $response );
		libxml_use_internal_errors( true );
		$xml = simplexml_load_string( $body );
		if ( false === $xml ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Feed XML is not well-formed or valid.', 'wpshadow' ),
				'severity'    => 'high',
				'threat_level'=> 80,
				'auto_fixable'=> false,
				'kb_link'     => 'https://wpshadow.com/kb/feed-xml-validity',
			);
		}
		return null;
	}
}
