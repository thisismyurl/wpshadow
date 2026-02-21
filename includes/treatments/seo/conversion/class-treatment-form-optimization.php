<?php
/**
 * Form Optimization Treatment
 *
 * Tests if forms are optimized for conversion.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6035.1514
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Form Optimization Treatment Class
 *
 * Evaluates whether forms are optimized for conversion and user experience.
 * Checks for form plugins, validation, analytics, A/B testing, and optimization features.
 *
 * @since 1.6035.1514
 */
class Treatment_Form_Optimization extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'optimizes_forms';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Form Optimization';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if forms are optimized for conversion';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'conversion';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6035.1514
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Form_Optimization' );
	}
}
