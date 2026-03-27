<?php
/**
 * Mobile CSS-in-JS Performance
 *
 * Detects CSS-in-JS (styled-components, emotion) overhead.
 *
 * @package    WPShadow
 * @subpackage Treatments\Performance
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile CSS-in-JS Performance
 *
 * Identifies CSS-in-JS library usage and measures runtime
 * CSS generation overhead.
 *
 * @since 1.6093.1200
 */
class Treatment_Mobile_CSS_In_JS_Performance extends Treatment_Base {

	/**
	 * The treatment slug.
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-css-in-js-perf';

	/**
	 * The treatment title.
	 *
	 * @var string
	 */
	protected static $title = 'Mobile CSS-in-JS Performance';

	/**
	 * The treatment description.
	 *
	 * @var string
	 */
	protected static $description = 'Detects CSS-in-JS overhead';

	/**
	 * The treatment family.
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Mobile_CSS_In_JS_Performance' );
	}
}
