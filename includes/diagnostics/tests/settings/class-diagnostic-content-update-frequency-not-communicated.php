<?php
/**
 * Content Update Frequency Not Communicated Diagnostic
 *
 * Checks if update frequency is communicated.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6030.2352
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Content Update Frequency Not Communicated Diagnostic Class
 *
 * Detects missing update frequency communication.
 *
 * @since 1.6030.2352
 */
class Diagnostic_Content_Update_Frequency_Not_Communicated extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'content-update-frequency-not-communicated';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Content Update Frequency Not Communicated';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if update frequency is communicated';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6030.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for last modified date in sitemap
		if ( ! has_filter( 'wp_sitemaps_posts_entry', 'wp_add_lastmod' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Content update frequency is not communicated to users. Add "last updated" dates to content and sitemap for transparency.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 15,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/content-update-frequency-not-communicated',
			);
		}

		return null;
	}
}
