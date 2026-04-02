<?php
/**
 * Content Performance Optimization Possible Treatment
 *
 * Tests for content optimization opportunities.
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
 * Content Performance Optimization Possible Treatment Class
 *
 * Tests for content optimization opportunities.
 *
 * @since 1.6093.1200
 */
class Treatment_Content_Performance_Optimization_Possible extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'content-performance-optimization-possible';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Content Performance Optimization Possible';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests for content optimization opportunities';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Content_Performance_Optimization_Possible' );
	}
}
