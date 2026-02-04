<?php
/**
 * Link Title Attributes Not Added Diagnostic
 *
 * Checks if link title attributes are added.
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
 * Link Title Attributes Not Added Diagnostic Class
 *
 * Detects missing link title attributes.
 *
 * @since 1.6030.2352
 */
class Diagnostic_Link_Title_Attributes_Not_Added extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'link-title-attributes-not-added';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Link Title Attributes Not Added';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if link title attributes are added';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
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
