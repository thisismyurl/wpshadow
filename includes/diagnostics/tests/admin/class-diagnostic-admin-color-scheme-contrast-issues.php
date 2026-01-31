<?php
/**
 * Admin Color Scheme Contrast Issues Diagnostic
 *
 * Checks if admin color scheme meets contrast standards.
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
 * Admin Color Scheme Contrast Issues Diagnostic Class
 *
 * Detects admin color scheme contrast problems.
 *
 * @since 1.2601.2310
 */
class Diagnostic_Admin_Color_Scheme_Contrast_Issues extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'admin-color-scheme-contrast-issues';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Admin Color Scheme Contrast Issues';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks admin color scheme accessibility';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'admin';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2310
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wp_admin_bar;

		// Get current admin color scheme
		if ( function_exists( 'get_user_option' ) ) {
			$current_user_id = get_current_user_id();
			$color_scheme    = get_user_option( 'admin_color', $current_user_id );
		}

		// Check if custom color schemes might have contrast issues
		// WordPress core schemes are WCAG compliant, but custom ones might not be
		global $_wp_admin_css_colors;
		
		if ( ! empty( $_wp_admin_css_colors ) && count( $_wp_admin_css_colors ) > 7 ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Multiple custom admin color schemes are registered. Custom schemes may not meet WCAG contrast standards.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 20,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/admin-color-scheme-contrast-issues',
			);
		}

		return null;
	}
}
