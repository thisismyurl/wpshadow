<?php
/**
 * AJAX Error Messaging Treatment
 *
 * Issue #4856: AJAX Failures Show Technical Errors Not User-Friendly Messages
 * Pillar: ⚙️ Murphy's Law, Commandment #1: Helpful Neighbor
 *
 * Checks if AJAX errors show user-friendly messages instead of technical details.
 * Stack traces and database errors confuse users and expose system internals.
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
 * Treatment_AJAX_Error_Messaging Class
 *
 * Checks for:
 * - AJAX error responses with database/SQL errors
 * - Stack traces exposed to client
 * - PHP warnings/notices in AJAX responses
 * - Unhandled exceptions in AJAX
 * - Missing error translation/user-friendly messages
 *
 * Good error handling:
 * - User sees: "Couldn't save changes. Please try again."
 * - Server logs: Full error details for debugging
 * - Never shows: Database structure, code paths, system info
 *
 * @since 1.6093.1200
 */
class Treatment_AJAX_Error_Messaging extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $slug = 'ajax-error-messaging';

	/**
	 * The treatment title
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $title = 'AJAX Failures Show Technical Errors Not User-Friendly Messages';

	/**
	 * The treatment description
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $description = 'Checks if AJAX errors show user-friendly messages instead of technical details';

	/**
	 * The family this treatment belongs to
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $family = 'reliability';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_AJAX_Error_Messaging' );
	}
}
