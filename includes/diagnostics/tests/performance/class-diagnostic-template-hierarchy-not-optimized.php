<?php
/**
 * Template Hierarchy Not Optimized Diagnostic
 *
 * Checks if template hierarchy is optimized.
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
 * Template Hierarchy Not Optimized Diagnostic Class
 *
 * Detects unoptimized template hierarchy.
 *
 * @since 1.2601.2352
 */
class Diagnostic_Template_Hierarchy_Not_Optimized extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'template-hierarchy-not-optimized';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Template Hierarchy Not Optimized';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if template hierarchy is optimized';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for template optimization
		if ( ! has_filter( 'template_include', 'wp_optimize_template_selection' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Template hierarchy is not optimized. Use child themes and efficient template files to reduce load times.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 15,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/template-hierarchy-not-optimized',
			);
		}

		return null;
	}
}
