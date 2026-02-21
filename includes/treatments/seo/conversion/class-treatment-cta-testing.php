<?php
/**
 * Call-to-Action Testing Treatment
 *
 * Tests if CTA elements are regularly tested and optimized.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6035.1513
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CTA Testing Treatment Class
 *
 * Evaluates whether call-to-action elements are being tested and optimized.
 * Checks for CTA plugins, A/B testing tools, button optimization, and variation testing.
 *
 * @since 1.6035.1513
 */
class Treatment_CTA_Testing extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'tests_call_to_action';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'CTA Testing';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if CTA elements are regularly tested and optimized';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 *
	 */
	protected static $family = 'conversion';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6035.1513
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_CTA_Testing' );
	}
}
