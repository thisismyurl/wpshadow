<?php
/**
 * Admin Color Scheme Security
 *
 * Checks if admin color schemes are from trusted sources and properly validated.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26033.0632
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: Admin Color Scheme Security
 *
 * @since 1.26033.0632
 */
class Diagnostic_Admin_Color_Scheme_Security extends Diagnostic_Base {

	protected static $slug = 'admin-color-scheme-security';
	protected static $title = 'Admin Color Scheme Security';
	protected static $description = 'Verifies admin color schemes are from trusted sources';
	protected static $family = 'admin-security';

	public static function check() {
		$issues = array();

		// Get registered color schemes
		global $_wp_admin_css_colors;
		$custom_schemes = 0;
		$colors          = $_wp_admin_css_colors;

		if ( ! empty( $colors ) ) {
			// Count non-default color schemes
			$default_schemes = array( 'fresh', 'light', 'blue', 'coffee', 'ectoplasm', 'midnight', 'ocean', 'sunrise' );
			foreach ( $colors as $slug => $color ) {
				if ( ! in_array( $slug, $default_schemes, true ) ) {
					$custom_schemes++;
				}
			}
		}

		if ( $custom_schemes > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of custom schemes */
				__( '%d custom color scheme(s) detected - verify they are from trusted plugins', 'wpshadow' ),
				$custom_schemes
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/admin-color-scheme-security',
			);
		}

		return null;
	}
}
