<?php
/**
 * Unused CSS Removal Treatment
 *
 * Issue #4985: Unused CSS Not Removed (Dead Code)
 * Pillar: ⚙️ Murphy's Law
 *
 * Checks if unused CSS is removed.
 * Dead CSS bloats stylesheets unnecessarily.
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
 * Treatment_Unused_CSS_Removal Class
 *
 * @since 0.6093.1200
 */
class Treatment_Unused_CSS_Removal extends Treatment_Base {

	protected static $slug = 'unused-css-removal';
	protected static $title = 'Unused CSS Not Removed (Dead Code)';
	protected static $description = 'Checks if unused CSS is identified and removed';
	protected static $family = 'performance';

	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Unused_CSS_Removal' );
	}
}
