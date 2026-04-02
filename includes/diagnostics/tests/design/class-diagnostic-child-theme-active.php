<?php
/**
 * Child Theme In Use Diagnostic
 *
 * Checks whether the active theme is a child theme. Customising a parent theme
 * directly means all changes are overwritten when the theme is updated.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Child_Theme_Active Class
 *
 * Uses wp_get_theme() to check whether the active theme has a parent theme
 * (i.e. is a child theme). Returns null when a child theme is active. Returns
 * a low-severity finding when the active theme is a standalone parent theme.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Child_Theme_Active extends Diagnostic_Base {

	/**
	 * @var string
	 */
	protected static $slug = 'child-theme-active';

	/**
	 * @var string
	 */
	protected static $title = 'Child Theme In Use';

	/**
	 * @var string
	 */
	protected static $description = 'Checks whether the active theme is a child theme. Customising a parent theme directly means all changes are lost when the theme is updated.';

	/**
	 * @var string
	 */
	protected static $family = 'design';

	/**
	 * Run the diagnostic check.
	 *
	 * Reads the active theme via wp_get_theme() and checks for a parent theme.
	 * Returns null when a child theme is active. Returns a low-severity finding
	 * when the active theme appears to be directly customised without a child.
	 *
	 * @since  0.6093.1200
	 * @return array|null Finding array when no child theme is active, null when healthy.
	 */
	public static function check() {
		$theme = wp_get_theme();

		// If the theme has a parent, it is a child theme — healthy.
		if ( $theme->parent() ) {
			return null;
		}

		// Block-based themes and well-maintained themes without modification are
		// fine as parent themes. Flag this as informational/low rather than blocking.
		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: active theme name */
				__( 'The active theme "%s" is not a child theme. Any customisations made directly to this theme\'s files will be overwritten the next time the theme is updated. Create a child theme to protect your changes.', 'wpshadow' ),
				$theme->get( 'Name' )
			),
			'severity'     => 'low',
			'threat_level' => 10,
			'kb_link'      => 'https://wpshadow.com/kb/child-theme-active?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'details'      => array(
				'active_theme'  => $theme->get( 'Name' ),
				'theme_version' => $theme->get( 'Version' ),
				'is_child'      => false,
			),
		);
	}
}
