<?php
/**
 * Social Media Meta Tags Not Configured Diagnostic
 *
 * Checks if social media meta tags are configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2352
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Social Media Meta Tags Not Configured Diagnostic Class
 *
 * Detects missing social media meta tags.
 *
 * @since 1.2601.2352
 */
class Diagnostic_Social_Media_Meta_Tags_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'social-media-meta-tags-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Social Media Meta Tags Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if social media meta tags are configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for social media plugin
		if ( ! is_plugin_active( 'yoast-seo/wp-seo.php' ) && ! is_plugin_active( 'all-in-one-seo-pack/all_in_one_seo_pack.php' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Social media meta tags are not configured. Add Open Graph and Twitter Card tags for better social sharing previews.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 20,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/social-media-meta-tags-not-configured',
			);
		}

		return null;
	}
}
