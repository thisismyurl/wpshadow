<?php
/**
 * CDN Performance Treatment
 *
 * Checks if static assets are served from CDN.
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
 * CDN Performance Treatment Class
 *
 * Verifies that a CDN is configured and that static assets are
 * being served from it for optimal performance.
 *
 * @since 0.6093.1200
 */
class Treatment_CDN_Performance extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'cdn-performance';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'CDN Performance';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if static assets are served from CDN';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'ecommerce';

	/**
	 * Run the CDN performance treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if CDN issues detected, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_CDN_Performance' );
	}
}
