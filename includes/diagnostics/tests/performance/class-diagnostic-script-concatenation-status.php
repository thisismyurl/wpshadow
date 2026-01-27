<?php
/**
 * Diagnostic: Script Concatenation Status
 *
 * Checks if scripts are being concatenated to reduce HTTP requests.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Performance
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Diagnostic_Script_Concatenation_Status
 *
 * Tests if scripts are concatenated for performance optimization.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Script_Concatenation_Status extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'script-concatenation-status';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Script Concatenation Status';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if scripts are concatenated to reduce HTTP requests';

	/**
	 * Check script concatenation status.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		// Check if CONCATENATE_SCRIPTS is defined and enabled.
		$concatenate_scripts = defined( 'CONCATENATE_SCRIPTS' ) ? CONCATENATE_SCRIPTS : true;

		if ( ! $concatenate_scripts ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Script concatenation is disabled (CONCATENATE_SCRIPTS = false). This increases HTTP requests and slows page load. Enable script concatenation for better performance.', 'wpshadow' ),
				'severity'    => 'low',
				'threat_level' => 20,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/script_concatenation_status',
				'meta'        => array(
					'concatenate_scripts' => $concatenate_scripts,
				),
			);
		}

		return null;
	}
}
