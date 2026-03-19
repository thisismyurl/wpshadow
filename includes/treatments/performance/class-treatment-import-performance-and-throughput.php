<?php
/**
 * Import Performance and Throughput Treatment
 *
 * Tests import performance metrics and throughput.
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
 * Import Performance and Throughput Treatment Class
 *
 * Tests whether import performance meets expected throughput targets.
 *
 * @since 1.6093.1200
 */
class Treatment_Import_Performance_And_Throughput extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'import-performance-and-throughput';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Import Performance and Throughput';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests import performance metrics and throughput';

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
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Import_Performance_And_Throughput' );
	}
}
