<?php
/**
 * CSS/JS Minification Configuration Treatment
 *
 * Tests if CSS and JavaScript files are minified for performance.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.7034.1150
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CSS/JS Minification Configuration Treatment Class
 *
 * Validates that CSS and JavaScript assets are minified and
 * concatenated for optimal loading performance.
 *
 * @since 1.7034.1150
 */
class Treatment_CSS_JS_Minification_Configuration extends Treatment_Base {

	/**
	 * Treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'css-js-minification-configuration';

	/**
	 * Treatment title
	 *
	 * @var string
	 */
	protected static $title = 'CSS/JS Minification Configuration';

	/**
	 * Treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if CSS and JavaScript files are minified for performance';

	/**
	 * Treatment family
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * Tests if CSS/JS minification and concatenation is enabled
	 * via plugins or manual configuration.
	 *
	 * @since  1.7034.1150
	 * @return array|null Finding array if issue detected, null if all clear.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_CSS_JS_Minification_Configuration' );
	}
}
