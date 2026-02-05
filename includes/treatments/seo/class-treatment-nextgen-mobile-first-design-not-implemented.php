<?php
/**
 * NextGen Mobile First Design Not Implemented Treatment
 *
 * Checks if mobile first design is implemented.
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
 * NextGen Mobile First Design Not Implemented Treatment Class
 *
 * Detects missing mobile first approach.
 *
 * @since 1.6030.2352
 */
class Treatment_NextGen_Mobile_First_Design_Not_Implemented extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'nextgen-mobile-first-design-not-implemented';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'NextGen Mobile First Design Not Implemented';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if mobile first design is implemented';

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
		// Check for mobile viewport meta tag
		if ( ! has_filter( 'wp_head', 'add_mobile_viewport' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Mobile first design is not implemented. Ensure viewport meta tag and mobile-optimized CSS are in place for responsive design.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 60,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/nextgen-mobile-first-design-not-implemented',
			);
		}

		return null;
	}
}
