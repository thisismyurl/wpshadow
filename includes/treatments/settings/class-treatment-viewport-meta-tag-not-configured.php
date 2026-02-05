<?php
/**
 * Viewport Meta Tag Not Configured Treatment
 *
 * Checks if viewport meta tag is configured.
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
 * Viewport Meta Tag Not Configured Treatment Class
 *
 * Detects missing viewport meta tag.
 *
 * @since 1.6030.2352
 */
class Treatment_Viewport_Meta_Tag_Not_Configured extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'viewport-meta-tag-not-configured';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Viewport Meta Tag Not Configured';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if viewport meta tag is configured';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6030.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for viewport meta tag in header
		if ( ! has_filter( 'wp_head', 'add_viewport_meta_tag' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Viewport meta tag is not configured. Add <meta name="viewport" content="width=device-width, initial-scale=1"> to enable responsive design on mobile devices.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 50,
				'auto_fixable'  => true,
				'kb_link'       => 'https://wpshadow.com/kb/viewport-meta-tag-not-configured',
			);
		}

		return null;
	}
}
