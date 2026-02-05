<?php
/**
 * Link Title Attributes Not Added Treatment
 *
 * Checks if link title attributes are added.
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
 * Link Title Attributes Not Added Treatment Class
 *
 * Detects missing link title attributes.
 *
 * @since 1.6030.2352
 */
class Treatment_Link_Title_Attributes_Not_Added extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'link-title-attributes-not-added';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Link Title Attributes Not Added';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if link title attributes are added';

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
		// Check if title attribute filter is active
		if ( ! has_filter( 'the_permalink', 'add_link_title_attributes' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Link title attributes are not added. Add descriptive title attributes to links for improved accessibility and user experience.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 10,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/link-title-attributes-not-added',
			);
		}

		return null;
	}
}
