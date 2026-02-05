<?php
/**
 * Open Graph Tags Not Configured Treatment
 *
 * Checks if Open Graph tags are configured.
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
 * Open Graph Tags Not Configured Treatment Class
 *
 * Detects missing Open Graph tags.
 *
 * @since 1.6030.2352
 */
class Treatment_Open_Graph_Tags_Not_Configured extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'open-graph-tags-not-configured';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Open Graph Tags Not Configured';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if Open Graph tags are configured';

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
		// Check for OG tags
		if ( ! has_filter( 'wp_head', 'wp_add_og_tags' ) && ! is_plugin_active( 'yoast-seo/wp-seo.php' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Open Graph tags are not configured. Add og:title, og:description, and og:image tags for better social media sharing.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 15,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/open-graph-tags-not-configured',
			);
		}

		return null;
	}
}
