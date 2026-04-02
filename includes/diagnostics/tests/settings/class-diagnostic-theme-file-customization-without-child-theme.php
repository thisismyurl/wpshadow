<?php
/**
 * Theme File Customization Without Child Theme Diagnostic
 *
 * Checks if child theme is used.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme File Customization Without Child Theme Diagnostic Class
 *
 * Detects missing child theme.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Theme_File_Customization_Without_Child_Theme extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'theme-file-customization-without-child-theme';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Theme File Customization Without Child Theme';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if child theme is used';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if parent theme is set
		$parent_theme = wp_get_theme()->get( 'Template' );

		if ( empty( $parent_theme ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Theme customization is being done without a child theme. Create a child theme to preserve customizations during theme updates.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 20,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/theme-file-customization-without-child-theme',
			);
		}

		return null;
	}
}
