<?php
/**
 * Treatment Interface
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

/**
 * Interface for treatment/fix implementations
 */
interface Treatment_Interface {
	/**
	 * Get the finding ID this treatment addresses
	 *
	 * @return string
	 */
	public static function get_finding_id();
	
	/**
	 * Check if this treatment can be applied
	 *
	 * @return bool True if treatment can run.
	 */
	public static function can_apply();
	
	/**
	 * Apply the treatment/fix
	 *
	 * @return array Result with 'success' bool and 'message' string.
	 */
	public static function apply();
	
	/**
	 * Undo the treatment (if possible)
	 *
	 * @return array Result with 'success' bool and 'message' string.
	 */
	public static function undo();
}
