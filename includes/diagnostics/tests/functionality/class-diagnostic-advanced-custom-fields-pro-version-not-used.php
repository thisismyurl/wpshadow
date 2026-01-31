<?php
/**
 * Advanced Custom Fields Pro Version Not Used Diagnostic
 *
 * Checks if ACF Pro is being used.
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
 * Advanced Custom Fields Pro Version Not Used Diagnostic Class
 *
 * Detects free ACF instead of Pro.
 *
 * @since 1.2601.2352
 */
class Diagnostic_Advanced_Custom_Fields_Pro_Version_Not_Used extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'advanced-custom-fields-pro-version-not-used';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Advanced Custom Fields Pro Version Not Used';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if ACF Pro is being used';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if ACF free is active
		if ( is_plugin_active( 'advanced-custom-fields/acf.php' ) && ! is_plugin_active( 'advanced-custom-fields-pro/acf.php' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'ACF Free is installed instead of Pro. Consider upgrading to ACF Pro for flexible content, repeater fields, and additional features.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 10,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/advanced-custom-fields-pro-version-not-used',
			);
		}

		return null;
	}
}
