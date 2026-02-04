<?php
/**
 * Child Theme In Use Diagnostic
 *
 * Encourages use of a child theme to protect customizations.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6035.1405
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Child_Theme_In_Use Class
 *
 * Checks if a child theme is active when a parent theme is used.
 *
 * @since 1.6035.1405
 */
class Diagnostic_Child_Theme_In_Use extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'child-theme-in-use';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Child Theme In Use';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether a child theme is active to protect customizations';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'design';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6035.1405
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if ( is_child_theme() ) {
			return null;
		}

		$theme = wp_get_theme();
		if ( ! $theme->exists() ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'No child theme is active. Customizations may be overwritten during theme updates.', 'wpshadow' ),
			'severity'     => 'medium',
			'threat_level' => 50,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/child-theme-in-use',
			'meta'         => array(
				'active_theme' => $theme->get_stylesheet(),
			),
		);
	}
}