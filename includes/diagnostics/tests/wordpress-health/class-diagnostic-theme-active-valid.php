<?php
/**
 * Theme Active & Valid Diagnostic
 *
 * Ensures the active theme exists and is properly loaded.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6035.1400
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Theme_Active_Valid Class
 *
 * Checks that the active theme exists and is not broken.
 *
 * @since 1.6035.1400
 */
class Diagnostic_Theme_Active_Valid extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'theme-active-valid';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Theme Active & Valid';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Ensures the active theme exists and is valid';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'wordpress-health';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6035.1400
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$theme = wp_get_theme();

		if ( ! $theme->exists() ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'The active theme is missing or invalid. This can break site rendering.', 'wpshadow' ),
				'severity'     => 'critical',
				'threat_level' => 90,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/theme-active-valid',
			);
		}

		if ( ! $theme->is_allowed() ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'The active theme is not allowed for this site. Verify theme permissions.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/theme-active-valid',
				'meta'         => array(
					'theme' => $theme->get_stylesheet(),
				),
			);
		}

		return null;
	}
}