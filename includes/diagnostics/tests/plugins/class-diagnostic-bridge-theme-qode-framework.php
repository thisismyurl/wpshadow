<?php
/**
 * Bridge Theme Qode Framework Diagnostic
 *
 * Bridge Theme Qode Framework needs optimization.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1315.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Bridge Theme Qode Framework Diagnostic Class
 *
 * @since 1.1315.0000
 */
class Diagnostic_BridgeThemeQodeFramework extends Diagnostic_Base {

	protected static $slug = 'bridge-theme-qode-framework';
	protected static $title = 'Bridge Theme Qode Framework';
	protected static $description = 'Bridge Theme Qode Framework needs optimization';
	protected static $family = 'functionality';

	public static function check() {
		$issues = array();

		// Check 1: Qode Framework version
		$framework_version = get_option( 'qode_framework_version', '' );
		if ( empty( $framework_version ) ) {
			$issues[] = 'Qode Framework version not detected';
		}

		// Check 2: Theme update available
		$update_available = get_option( 'qode_theme_update_available', false );
		if ( $update_available ) {
			$issues[] = 'Theme update available';
		}

		// Check 3: Shortcode optimization
		$shortcode_opt = get_option( 'qode_shortcode_optimization', false );
		if ( ! $shortcode_opt ) {
			$issues[] = 'Shortcode optimization disabled';
		}

		// Check 4: CSS optimization enabled
		$css_opt = get_option( 'qode_css_optimization', false );
		if ( ! $css_opt ) {
			$issues[] = 'CSS optimization disabled';
		}

		// Check 5: JavaScript minification
		$js_min = get_option( 'qode_js_minification', false );
		if ( ! $js_min ) {
			$issues[] = 'JavaScript minification disabled';
		}

		// Check 6: Demo content cleanup
		$demo_cleanup = get_option( 'qode_demo_content_cleaned', false );
		if ( ! $demo_cleanup ) {
			$issues[] = 'Demo content not cleaned up';
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 65, 35 + ( count( $issues ) * 5 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'Bridge Theme Qode Framework issues: ' . implode( ', ', $issues ),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/bridge-theme-qode-framework',
			);
		}

		return null;
	}
		}
		return null;
	}
}
