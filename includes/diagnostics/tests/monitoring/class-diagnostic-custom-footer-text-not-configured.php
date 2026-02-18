<?php
/**
 * Custom Footer Text Not Configured Diagnostic
 *
 * Checks if custom footer text is configured.
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
 * Custom Footer Text Not Configured Diagnostic Class
 *
 * Detects missing custom footer text.
 *
 * @since 1.6030.2352
 */
class Diagnostic_Custom_Footer_Text_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'custom-footer-text-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Custom Footer Text Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if custom footer text is configured';

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
		// Check for custom footer text
		if ( ! get_option( 'custom_footer_text' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Custom footer text is not configured. Customize the WordPress footer with your own branding and copyright information.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 5,
				'auto_fixable'  => true,
				'kb_link'       => 'https://wpshadow.com/kb/custom-footer-text-not-configured',
			);
		}

		return null;
	}
}
