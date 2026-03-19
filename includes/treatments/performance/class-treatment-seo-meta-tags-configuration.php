<?php
/**
 * SEO Meta Tags Configuration Treatment
 *
 * Checks for proper SEO meta tags (title, description, canonical) that help
 * search engines understand and rank page content.
 *
 * @since 1.6093.1200
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * SEO Meta Tags Configuration Treatment Class
 *
 * Verifies SEO meta tag implementation:
 * - Meta description tags
 * - Canonical URLs
 * - Open Graph tags
 * - Title tag optimization
 *
 * @since 1.6093.1200
 */
class Treatment_Seo_Meta_Tags_Configuration extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'seo-meta-tags-configuration';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'SEO Meta Tags Configuration';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for proper SEO meta tags for search engine optimization';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issues found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Seo_Meta_Tags_Configuration' );
	}
}
