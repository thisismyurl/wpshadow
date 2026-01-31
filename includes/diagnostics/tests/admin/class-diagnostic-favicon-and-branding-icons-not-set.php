<?php
/**
 * Favicon And Branding Icons Not Set Diagnostic
 *
 * Checks if favicon is set.
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
 * Favicon And Branding Icons Not Set Diagnostic Class
 *
 * Detects missing favicon and branding.
 *
 * @since 1.2601.2352
 */
class Diagnostic_Favicon_And_Branding_Icons_Not_Set extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'favicon-and-branding-icons-not-set';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Favicon And Branding Icons Not Set';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if favicon is set';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'admin';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for favicon
		if ( ! get_theme_mod( 'custom_logo' ) && ! has_site_icon() ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Favicon and branding icons are not set. Add a favicon and site icon for better brand recognition in browser tabs and bookmarks.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 10,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/favicon-and-branding-icons-not-set',
			);
		}

		return null;
	}
}
