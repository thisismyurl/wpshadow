<?php
/**
 * Social Media Sharing Buttons Not Configured Treatment
 *
 * Checks if social sharing is configured.
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
 * Social Media Sharing Buttons Not Configured Treatment Class
 *
 * Detects unconfigured social sharing.
 *
 * @since 1.6030.2352
 */
class Treatment_Social_Media_Sharing_Buttons_Not_Configured extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'social-media-sharing-buttons-not-configured';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Social Media Sharing Buttons Not Configured';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if social sharing is configured';

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
		// Check for social sharing plugin
		if ( ! is_plugin_active( 'social-snap/social-snap.php' ) && ! has_filter( 'the_content', 'add_social_sharing_buttons' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Social media sharing buttons are not configured. Add social sharing buttons to increase content reach and engagement.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 10,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/social-media-sharing-buttons-not-configured',
			);
		}

		return null;
	}
}
