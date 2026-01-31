<?php
/**
 * Favicon And App Icon Not Configured Diagnostic
 *
 * Checks if favicon and app icons are configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2348
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Favicon And App Icon Not Configured Diagnostic Class
 *
 * Detects missing favicon and app icons.
 *
 * @since 1.2601.2348
 */
class Diagnostic_Favicon_And_App_Icon_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'favicon-and-app-icon-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Favicon And App Icon Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if favicon and app icons are configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2348
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if site icon is set
		$site_icon = get_option( 'site_icon' );

		if ( empty( $site_icon ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Favicon and app icons are not configured. Add a site icon (favicon) for better branding on browser tabs and mobile devices.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 10,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/favicon-and-app-icon-not-configured',
			);
		}

		return null;
	}
}
