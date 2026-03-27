<?php
/**
 * CSRF Vulnerabilities in Tool Actions
 *
 * Detects whether tool actions are protected against CSRF attacks.
 *
 * @package    WPShadow
 * @subpackage Treatments\Tools
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_CSRF_Vulnerabilities_In_Tool_Actions Class
 *
 * Validates CSRF protection in tool operations.
 *
 * @since 1.6093.1200
 */
class Treatment_CSRF_Vulnerabilities_In_Tool_Actions extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'csrf-vulnerabilities-in-tool-actions';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Tool Action CSRF Protection';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies tool actions are protected against CSRF attacks';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'tools';

	/**
	 * Run the treatment check.
	 *
	 * Tests CSRF protection in tool actions.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\\WPShadow\\Diagnostics\\Diagnostic_CSRF_Vulnerabilities_In_Tool_Actions' );
	}
}
