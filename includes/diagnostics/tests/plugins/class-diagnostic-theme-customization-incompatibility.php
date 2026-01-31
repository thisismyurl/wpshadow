<?php
/**
 * Theme Customization Incompatibility Diagnostic
 *
 * Checks if theme customization is compatible.
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
 * Theme Customization Incompatibility Diagnostic Class
 *
 * Detects theme customization issues.
 *
 * @since 1.2601.2310
 */
class Diagnostic_Theme_Customization_Incompatibility extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'theme-customization-incompatibility';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Theme Customization Incompatibility';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if theme customizations are compatible';

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
		$theme = wp_get_theme();

		// Check if theme is using child theme
		if ( is_child_theme() ) {
			$parent_theme = wp_get_theme( $theme->get( 'Template' ) );

			if ( ! $parent_theme->exists() ) {
				return array(
					'id'            => self::$slug,
					'title'         => self::$title,
					'description'   => __( 'Child theme is active but parent theme is missing. Install the parent theme to avoid visual issues.', 'wpshadow' ),
					'severity'      => 'high',
					'threat_level'  => 70,
					'auto_fixable'  => false,
					'kb_link'       => 'https://wpshadow.com/kb/theme-customization-incompatibility',
				);
			}
		}

		return null;
	}
}
