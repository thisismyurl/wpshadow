<?php
/**
 * Transient Cleanup Automation Treatment
 *
 * Tests if expired transients are being cleaned automatically.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.7034.1110
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Transient Cleanup Automation Treatment Class
 *
 * Validates that expired transients are cleaned automatically to
 * prevent database bloat and performance degradation.
 *
 * @since 1.7034.1110
 */
class Treatment_Transient_Cleanup_Automation extends Treatment_Base {

	/**
	 * Treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'transient-cleanup-automation';

	/**
	 * Treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Transient Cleanup Automation';

	/**
	 * Treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if expired transients are being cleaned automatically';

	/**
	 * Treatment family
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * Tests if transients are accumulating in the database and
	 * if automatic cleanup mechanisms are configured.
	 *
	 * @since  1.7034.1110
	 * @return array|null Finding array if issue detected, null if all clear.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Transient_Cleanup_Automation' );
	}
}
