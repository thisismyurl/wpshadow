<?php
/**
 * Media Mobile Bandwidth Optimization Treatment
 *
 * Checks if media delivery is optimized for mobile bandwidth constraints.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Media Mobile Bandwidth Optimization Treatment Class
 *
 * Verifies that media is optimized for mobile devices with bandwidth constraints,
 * including lazy loading, adaptive images, and compression.
 *
 * @since 0.6093.1200
 */
class Treatment_Media_Mobile_Bandwidth_Optimization extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-mobile-bandwidth-optimization';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Media Mobile Bandwidth Optimization';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if media delivery is optimized for mobile bandwidth constraints';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Media_Mobile_Bandwidth_Optimization' );
	}
}
