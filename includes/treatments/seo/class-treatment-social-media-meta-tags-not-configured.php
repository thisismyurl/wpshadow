<?php
/**
 * Social Media Meta Tags Not Configured Treatment
 *
 * Checks if social media meta tags are configured.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6030.2352
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Social Media Meta Tags Not Configured Treatment Class
 *
 * Detects missing social media meta tags.
 *
 * @since 1.6030.2352
 */
class Treatment_Social_Media_Meta_Tags_Not_Configured extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'social-media-meta-tags-not-configured';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Social Media Meta Tags Not Configured';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if social media meta tags are configured';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6030.2352
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
