<?php
/**
 * SEO Meta Tags Treatment
 *
 * Checks if articles have proper meta descriptions and SEO tags.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6035.1300
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * SEO Meta Tags Treatment Class
 *
 * Verifies that articles and pages have proper meta descriptions,
 * titles, and other SEO-critical tags.
 *
 * @since 1.6035.1300
 */
class Treatment_SEO_Meta_Tags extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'seo-meta-tags';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'SEO Meta Tags';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if articles have proper meta descriptions and SEO tags';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'publisher';

	/**
	 * Run the SEO meta tags treatment check.
	 *
	 * @since  1.6035.1300
	 * @return array|null Finding array if SEO issues detected, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_SEO_Meta_Tags' );
	}
}
