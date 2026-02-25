<?php
/**
 * Mobile JavaScript Bundle Size Detection
 *
 * Detects JavaScript bundles too large for mobile networks.
 *
 * @package    WPShadow
 * @subpackage Treatments\Performance
 * @since      1.602.1600
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile JavaScript Bundle Size Detection
 *
 * Monitors total JavaScript size and identifies unused code that
 * should be split or removed for faster mobile loading.
 *
 * @since 1.602.1600
 */
class Treatment_Mobile_JS_Bundle_Size extends Treatment_Base {

	/**
	 * The treatment slug.
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-js-bundle-size';

	/**
	 * The treatment title.
	 *
	 * @var string
	 */
	protected static $title = 'Mobile JavaScript Bundle Size Detection';

	/**
	 * The treatment description.
	 *
	 * @var string
	 */
	protected static $description = 'Detects JavaScript bundles too large for mobile';

	/**
	 * The treatment family.
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.602.1600
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Mobile_JS_Bundle_Size' );
	}
}
