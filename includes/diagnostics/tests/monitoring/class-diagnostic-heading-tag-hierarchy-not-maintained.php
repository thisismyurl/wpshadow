<?php
/**
 * Heading Tag Hierarchy Not Maintained Diagnostic
 *
 * Checks if heading hierarchy is maintained.
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
 * Heading Tag Hierarchy Not Maintained Diagnostic Class
 *
 * Detects improper heading hierarchy.
 *
 * @since 1.6030.2352
 */
class Diagnostic_Heading_Tag_Hierarchy_Not_Maintained extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'heading-tag-hierarchy-not-maintained';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Heading Tag Hierarchy Not Maintained';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if heading hierarchy is maintained';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'admin';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6030.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for heading hierarchy validation
		if ( ! has_filter( 'the_content', 'wp_validate_heading_hierarchy' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Heading tag hierarchy is not maintained. Use H1, H2, H3 tags in proper order for accessibility and SEO.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 20,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/heading-tag-hierarchy-not-maintained',
			);
		}

		return null;
	}
}
