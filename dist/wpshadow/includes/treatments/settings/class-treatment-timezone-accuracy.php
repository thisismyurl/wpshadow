<?php
/**
 * Timezone Accuracy
 *
 * Checks if site timezone is correctly configured.
 *
 * @package    WPShadow
 * @subpackage Treatments\Configuration
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Timezone_Accuracy Class
 *
 * Validates timezone configuration accuracy.
 *
 * @since 0.6093.1200
 */
class Treatment_Timezone_Accuracy extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'timezone-accuracy';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Timezone Accuracy';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validates timezone configuration matches actual location';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'configuration';

	/**
	 * Run the treatment check.
	 *
	 * Tests timezone configuration.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Timezone_Accuracy' );
	}
}
