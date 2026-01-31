<?php
/**
 * Mobile Responsiveness Not Optimized Diagnostic
 *
 * Checks if site is mobile responsive.
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
 * Mobile Responsiveness Not Optimized Diagnostic Class
 *
 * Detects non-responsive site.
 *
 * @since 1.2601.2352
 */
class Diagnostic_Mobile_Responsiveness_Not_Optimized extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-responsiveness-not-optimized';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Responsiveness Not Optimized';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if site is mobile responsive';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for viewport meta tag
		global $wp_version;

		if ( version_compare( $wp_version, '5.0', '<' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Mobile responsiveness is not optimized. Ensure your theme has a viewport meta tag and is fully responsive across devices.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 60,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/mobile-responsiveness-not-optimized',
			);
		}

		return null;
	}
}
