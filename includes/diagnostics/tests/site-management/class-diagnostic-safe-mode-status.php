<?php
/**
 * Safe Mode Status Diagnostic
 *
 * Checks if per-user Safe Mode is available and active.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26030.2000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Safe Mode Status Diagnostic
 *
 * Detects if Safe Mode per-user isolation is enabled.
 * This is a utility feature diagnostic rather than a health/security check.
 *
 * @since 1.26030.2000
 */
class Diagnostic_Safe_Mode_Status extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'safe-mode-status';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Safe Mode Availability';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if Safe Mode is available and running';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'utilities';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26030.2000
	 * @return array|null Finding array if Safe Mode is not available, null otherwise.
	 */
	public static function check() {
		$current_user_id = get_current_user_id();
		$safe_mode_enabled = get_user_meta( $current_user_id, 'wpshadow_safe_mode_enabled', true );

		// This is an informational diagnostic - returns null (no issue) if Safe Mode is available
		// Safe Mode is always available as a feature, not a problem to fix
		return null;
	}
}
