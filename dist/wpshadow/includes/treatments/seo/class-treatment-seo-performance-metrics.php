<?php
/**
 * SEO Performance Metrics Treatment
 *
 * Tests if SEO metrics are tracked and reported through
 * analytics tools and SEO monitoring platforms.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * SEO Performance Metrics Treatment Class
 *
 * Evaluates whether the site has proper SEO tracking and
 * analytics implementation for measuring SEO performance.
 *
 * @since 1.6093.1200
 */
class Treatment_SEO_Performance_Metrics extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'tracks-seo-metrics';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'SEO Performance Metrics Tracking';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if SEO metrics are tracked and reported';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the SEO performance metrics treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if SEO metrics tracking issues detected, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_SEO_Performance_Metrics' );
	}
}
