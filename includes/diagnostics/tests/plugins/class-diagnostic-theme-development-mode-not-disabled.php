<?php
/**
 * Theme Development Mode Not Disabled Diagnostic
 *
 * Checks if theme development mode is still enabled.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2310
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme Development Mode Not Disabled Diagnostic Class
 *
 * Detects development mode in production.
 *
 * @since 1.2601.2310
 */
class Diagnostic_Theme_Development_Mode_Not_Disabled extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'theme-development-mode-not-disabled';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Theme Development Mode Not Disabled';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if theme development mode is disabled';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'plugins';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2310
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if SCRIPT_DEBUG is enabled
		if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'SCRIPT_DEBUG is enabled. Disable this constant in wp-config.php for production to avoid serving unminified assets.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 15,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/theme-development-mode-not-disabled',
			);
		}

		return null;
	}
}
