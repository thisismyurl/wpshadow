<?php
/**
 * Homepage Title Tag Business Optimization Treatment
 *
 * Issue #4801: Homepage Title Tag Not Optimized for Business
 * Family: business-performance
 *
 * Checks if homepage title tag targets business keywords and value proposition.
 * Homepage title is the most important title on your site.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Homepage_Title_Optimization Class
 *
 * Checks homepage title tag quality.
 *
 * @since 0.6093.1200
 */
class Treatment_Homepage_Title_Optimization extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'homepage-title-optimization';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Homepage Title Tag Not Optimized for Business';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if homepage title follows business SEO best practices';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Homepage_Title_Optimization' );
	}
}
