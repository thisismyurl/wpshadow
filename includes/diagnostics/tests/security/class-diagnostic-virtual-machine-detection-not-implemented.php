<?php
/**
 * Virtual Machine Detection Not Implemented Diagnostic
 *
 * Checks if VM detection is implemented.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2352
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Virtual Machine Detection Not Implemented Diagnostic Class
 *
 * Detects missing VM detection.
 *
 * @since 1.2601.2352
 */
class Diagnostic_Virtual_Machine_Detection_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'virtual-machine-detection-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Virtual Machine Detection Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if VM detection is implemented';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for VM detection mechanism
		if ( ! has_filter( 'init', 'detect_virtual_environment' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Virtual machine detection is not implemented. Detect VMs to prevent automated attacks from cloud providers and implement environment-specific security measures.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 20,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/virtual-machine-detection-not-implemented',
			);
		}

		return null;
	}
}
