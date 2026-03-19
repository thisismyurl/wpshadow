<?php
/**
 * AJAX Technical Errors Treatment
 *
 * Detects when AJAX failures display raw technical error messages instead of user-friendly messages.
 *
 * @package    WPShadow
 * @subpackage Treatments\UX
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments\UX;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AJAX Technical Errors Treatment Class
 *
 * Checks if AJAX error handlers show technical errors to users instead of friendly messages.
 *
 * @since 1.6093.1200
 */
class Treatment_AJAX_Technical_Errors extends Treatment_Base {

	/**
	 * Treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'ajax-technical-errors';

	/**
	 * Treatment title
	 *
	 * @var string
	 */
	protected static $title = 'AJAX Failures Show Technical Errors';

	/**
	 * Treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Detects when AJAX error handlers display technical errors instead of user-friendly messages';

	/**
	 * Treatment family
	 *
	 * @var string
	 */
	protected static $family = 'ux';

	/**
	 * Run the treatment check
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array or null if no issues found.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\UX\Diagnostic_AJAX_Technical_Errors' );
	}
}
