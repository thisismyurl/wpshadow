<?php
/**
 * Transient Cleanup Efficiency Treatment
 *
 * Checks for a large number of expired transients.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.5049.1354
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Transient Cleanup Efficiency Treatment Class
 *
 * Flags excessive expired transients that can bloat the options table.
 *
 * @since 1.5049.1354
 */
class Treatment_Transient_Cleanup_Efficiency extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'transient-cleanup-efficiency';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Transient Cleanup Efficiency';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for excessive expired transients in the database';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.5049.1354
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Transient_Cleanup_Efficiency' );
	}
}
