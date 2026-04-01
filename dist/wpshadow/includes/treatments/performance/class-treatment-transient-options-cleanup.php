<?php
/**
 * Transient and Options Table Cleanup
 *
 * Validates transient expiration and options table health.
 *
 * @since 0.6093.1200
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Transient_Options_Cleanup Class
 *
 * Checks transient and options table for bloat and cleanup needs.
 *
 * @since 0.6093.1200
 */
class Treatment_Transient_Options_Cleanup extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'transient-options-cleanup';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Transient and Options Table Cleanup';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validates transient expiration and options table optimization';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'database';

	/**
	 * Run the treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Transient_Options_Cleanup' );
	}
}
