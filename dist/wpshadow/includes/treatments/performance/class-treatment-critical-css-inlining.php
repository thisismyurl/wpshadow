<?php
/**
 * Critical CSS Inlining Treatment
 *
 * Issue #4934: No Critical CSS Inline (Render Blocking)
 * Pillar: ⚙️ Murphy's Law
 *
 * Checks if critical CSS is inlined.
 * External CSS blocks rendering and delays first paint.
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
 * Treatment_Critical_CSS_Inlining Class
 *
 * @since 0.6093.1200
 */
class Treatment_Critical_CSS_Inlining extends Treatment_Base {

	protected static $slug = 'critical-css-inlining';
	protected static $title = 'No Critical CSS Inline (Render Blocking)';
	protected static $description = 'Checks if above-the-fold CSS is inlined';
	protected static $family = 'performance';

	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Critical_CSS_Inlining' );
	}
}
