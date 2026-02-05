<?php
/**
 * Video Content Schema Markup Not Implemented Treatment
 *
 * Checks if video schema is implemented.
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
 * Video Content Schema Markup Not Implemented Treatment Class
 *
 * Detects missing video schema.
 *
 * @since 1.6030.2352
 */
class Treatment_Video_Content_Schema_Markup_Not_Implemented extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'video-content-schema-markup-not-implemented';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Video Content Schema Markup Not Implemented';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if video schema is implemented';

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
		// Check if videos are embedded with schema
		if ( ! is_plugin_active( 'schema-and-structured-data-for-json-ld/schema-plugin.php' ) && ! has_filter( 'wp_embed_oembed_html', 'wp_video_schema' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Video content schema markup is not implemented. Add Video schema to embedded videos for better search visibility.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 30,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/video-content-schema-markup-not-implemented',
			);
		}

		return null;
	}
}
