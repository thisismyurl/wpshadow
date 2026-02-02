<?php
/**
 * Feed Discovery Links Diagnostic
 *
 * Checks if feed discovery links are present in the site <head>.
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
 * Diagnostic_Feed_Discovery_Links Class
 *
 * Checks if feed discovery links are present in the site <head>.
 */
class Diagnostic_Feed_Discovery_Links extends Diagnostic_Base {
	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'feed-discovery-links';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Feed Discovery Links';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if feed discovery links are present in the site <head>.';

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
		// Use WordPress API to check if feed links are added
		$has_feed_links = has_action( 'wp_head', 'feed_links', 2 ) || has_action( 'wp_head', 'feed_links_extra', 3 );
		if ( ! $has_feed_links ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Feed discovery links are missing from the <head>.', 'wpshadow' ),
				'severity'    => 'medium',
				'threat_level'=> 40,
				'auto_fixable'=> false,
				'kb_link'     => 'https://wpshadow.com/kb/feed-discovery-links',
			);
		}
		return null;
	}
}
