<?php
/**
 * Multivariate Testing Treatment
 *
 * Tests for complex multivariate testing experiments that test
 * multiple variables simultaneously.
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
 * Multivariate Testing Treatment Class
 *
 * Evaluates whether the site has multivariate testing
 * capability for complex optimization experiments.
 *
 * @since 1.6093.1200
 */
class Treatment_Multivariate_Testing extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'runs-multivariate-tests';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Multivariate Testing';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests for complex multivariate testing experiments';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'conversion';

	/**
	 * Run the multivariate testing treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if multivariate testing issues detected, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Multivariate_Testing' );
	}
}
