<?php
/**
 * Transient Cleanup Treatment
 *
 * Checks for expired transients that should be cleaned up.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6033.2066
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Transient Cleanup Treatment Class
 *
 * Detects excessive expired transients. Old transients bloat
 * the options table and slow queries.
 *
 * @since 1.6033.2066
 */
class Treatment_Transient_Cleanup extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'transient-cleanup';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Transient Cleanup';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for expired transients needing cleanup';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * Counts expired transients in options table.
	 * Threshold: >500 expired transients
	 *
	 * @since  1.6033.2066
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Transient_Cleanup' );
	}
}
