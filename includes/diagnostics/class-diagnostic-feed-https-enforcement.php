<?php
/**
 * Feed HTTPS Enforcement Diagnostic
 *
 * Checks if feed URLs are served over HTTPS.
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
 * Diagnostic_Feed_HTTPS_Enforcement Class
 *
 * Checks if feed URLs are served over HTTPS.
 */
class Diagnostic_Feed_HTTPS_Enforcement extends Diagnostic_Base {
	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'feed-https-enforcement';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Feed HTTPS Enforcement';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if feed URLs are served over HTTPS.';

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
		if ( 0 !== strpos( $feed_url, 'https://' ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Feed URL is not served over HTTPS.', 'wpshadow' ),
				'severity'    => 'high',
				'threat_level'=> 70,
				'auto_fixable'=> false,
				'kb_link'     => 'https://wpshadow.com/kb/feed-https-enforcement',
			);
		}
		return null;
	}
}
