<?php
/**
 * CSS-in-JS Performance Treatment
 *
 * Analyzes CSS-in-JS implementation and performance impact.
 *
 * @since   1.6033.2120
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CSS-in-JS Performance Treatment
 *
 * Evaluates CSS-in-JS patterns and identifies performance issues.
 *
 * @since 1.6033.2120
 */
class Treatment_CSS_In_JS_Performance extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'css-in-js-performance';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'CSS-in-JS Performance';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Analyzes CSS-in-JS implementation and performance impact';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6033.2120
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_CSS_In_JS_Performance' );
	}
}
