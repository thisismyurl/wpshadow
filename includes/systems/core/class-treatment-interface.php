<?php
/**
 * Treatment Interface
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment Interface
 *
 * All treatment classes must implement this interface.
 */
interface Treatment_Interface {
	/**
	 * Get the finding ID this treatment addresses.
	 *
	 * @return string Finding ID.
	 */
	public static function get_finding_id();

	/**
	 * Check if this treatment can be applied in the current environment.
	 *
	 * @return bool True if treatment can be applied.
	 */
	public static function can_apply();

	/**
	 * Apply the treatment to fix the finding.
	 *
	 * @return array Result array with 'success' and 'message' keys.
	 */
	public static function apply();

	/**
	 * Undo the treatment.
	 *
	 * @return array Result array with 'success' and 'message' keys.
	 */
	public static function undo();
}
