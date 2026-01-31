<?php
/**
 * Breadcrumb Navigation Not Implemented Diagnostic
 *
 * Checks if breadcrumb navigation is implemented.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2350
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Breadcrumb Navigation Not Implemented Diagnostic Class
 *
 * Detects missing breadcrumb navigation.
 *
 * @since 1.2601.2350
 */
class Diagnostic_Breadcrumb_Navigation_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'breadcrumb-navigation-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Breadcrumb Navigation Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if breadcrumb navigation is implemented';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2350
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for breadcrumb filter or function
		if ( ! has_filter( 'wp_get_breadcrumb' ) && ! function_exists( 'breadcrumb_trail' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Breadcrumb navigation is not implemented. Add breadcrumbs to improve user experience and SEO.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 15,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/breadcrumb-navigation-not-implemented',
			);
		}

		return null;
	}
}
