<?php
/**
 * Favicon For Multiple Devices Not Generated Diagnostic
 *
 * Checks if favicons for all devices are generated.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6030.2352
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Favicon For Multiple Devices Not Generated Diagnostic Class
 *
 * Detects missing device-specific favicons.
 *
 * @since 1.6030.2352
 */
class Diagnostic_Favicon_For_Multiple_Devices_Not_Generated extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'favicon-for-multiple-devices-not-generated';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Favicon For Multiple Devices Not Generated';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if favicons for all devices are generated';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'admin';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6030.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for all favicon sizes
		$favicon_sizes = array( '32x32', '192x192', '180x180' );
		$favicon_count = 0;

		foreach ( $favicon_sizes as $size ) {
			if ( get_option( "favicon_$size" ) ) {
				$favicon_count++;
			}
		}

		if ( $favicon_count < 2 ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Favicons for multiple devices are not generated. Create 32x32, 192x192, and 180x180 favicons for desktop, Android, and iOS devices.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 5,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/favicon-for-multiple-devices-not-generated',
			);
		}

		return null;
	}
}
