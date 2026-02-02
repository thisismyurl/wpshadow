<?php
/**
 * Feed Pingback/Trackback Diagnostic
 *
 * Checks if pingbacks and trackbacks are enabled in feeds.
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
 * Diagnostic_Feed_Pingback_Trackback Class
 *
 * Checks if pingbacks and trackbacks are enabled in feeds.
 */
class Diagnostic_Feed_Pingback_Trackback extends Diagnostic_Base {
	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'feed-pingback-trackback';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Feed Pingback/Trackback';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if pingbacks and trackbacks are enabled in feeds.';

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
		$pingbacks = get_option( 'default_ping_status', 'open' );
		if ( 'open' === $pingbacks ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Pingbacks and trackbacks are enabled in feeds. Consider disabling for security.', 'wpshadow' ),
				'severity'    => 'medium',
				'threat_level'=> 40,
				'auto_fixable'=> true,
				'kb_link'     => 'https://wpshadow.com/kb/feed-pingback-trackback',
			);
		}
		return null;
	}
}
