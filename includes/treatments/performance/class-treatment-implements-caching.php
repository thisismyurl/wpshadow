<?php
/**
 * Caching Strategy Implemented Treatment
 *
 * Tests if caching layers are configured.
 *
 * @since   1.6050.0000
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Caching Strategy Implemented Treatment Class
 *
 * Verifies that page/object caching is enabled.
 *
 * @since 1.6050.0000
 */
class Treatment_Implements_Caching extends Treatment_Base {

	protected static $slug = 'implements-caching';
	protected static $title = 'Caching Strategy Implemented';
	protected static $description = 'Tests if caching layers are configured';
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6050.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Implements_Caching' );
	}
}
