<?php
/**
 * Tool Debug Mode Settings Treatment
 *
 * Detects whether tools provide enhanced debugging information when
 * WP_DEBUG is enabled.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6033.1900
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Tool Debug Mode Settings Treatment Class
 *
 * Ensures tools respect WP_DEBUG settings and provide enhanced logging
 * and error reporting when debug mode is enabled.
 *
 * @since 1.6033.1900
 */
class Treatment_Tool_Debug_Mode_Settings extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'tool-debug-mode-settings';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Tools Respecting Debug Mode Settings';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies tools provide enhanced debugging when WP_DEBUG is enabled';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'tools';

	/**
	 * Run the treatment check.
	 *
	 * Checks:
	 * - WP_DEBUG is enabled for development sites
	 * - Tool error handling respects debug flag
	 * - Debug logging is configured
	 * - Error display settings are appropriate
	 *
	 * @since  1.6033.1900
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\\WPShadow\\Diagnostics\\Diagnostic_Tool_Debug_Mode_Settings' );
	}
}
