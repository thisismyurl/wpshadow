<?php
/**
 * Generatepress Premium Elements Diagnostic
 *
 * Generatepress Premium Elements needs optimization.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1297.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Generatepress Premium Elements Diagnostic Class
 *
 * @since 1.1297.0000
 */
class Diagnostic_GeneratepressPremiumElements extends Diagnostic_Base {

	protected static $slug = 'generatepress-premium-elements';
	protected static $title = 'Generatepress Premium Elements';
	protected static $description = 'Generatepress Premium Elements needs optimization';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'GP_PREMIUM_VERSION' ) && ! class_exists( 'GeneratePress_Elements' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Elements enabled.
		$elements_enabled = get_option( 'generate_elements_enabled', '1' );
		if ( '0' === $elements_enabled ) {
			$issues[] = 'premium elements disabled';
		}

		// Check 2: Element caching.
		$element_cache = get_option( 'generate_elements_cache', '1' );
		if ( '0' === $element_cache ) {
			$issues[] = 'element caching disabled';
		}

		// Check 3: Unused elements.
		$active_elements = get_option( 'generate_active_elements', array() );
		if ( empty( $active_elements ) ) {
			$issues[] = 'no elements active';
		}

		// Check 4: Element CSS minification.
		$css_minify = get_option( 'generate_elements_minify_css', '1' );
		if ( '0' === $css_minify ) {
			$issues[] = 'CSS minification disabled';
		}

		// Check 5: Element JS minification.
		$js_minify = get_option( 'generate_elements_minify_js', '1' );
		if ( '0' === $js_minify ) {
			$issues[] = 'JS minification disabled';
		}

		// Check 6: Display conditions.
		$display_conditions = get_option( 'generate_elements_display_conditions', '1' );
		if ( '0' === $display_conditions ) {
			$issues[] = 'display conditions disabled';
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 65, 50 + ( count( $issues ) * 3 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'GeneratePress issues: ' . implode( ', ', $issues ),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/generatepress-premium-elements',
			);
		}

		return null;
	}
}
