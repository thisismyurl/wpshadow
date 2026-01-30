<?php
/**
 * Bricks Builder Custom Elements Diagnostic
 *
 * Bricks Builder Custom Elements issues found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.820.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Bricks Builder Custom Elements Diagnostic Class
 *
 * @since 1.820.0000
 */
class Diagnostic_BricksBuilderCustomElements extends Diagnostic_Base {

	protected static $slug = 'bricks-builder-custom-elements';
	protected static $title = 'Bricks Builder Custom Elements';
	protected static $description = 'Bricks Builder Custom Elements issues found';
	protected static $family = 'functionality';

	public static function check() {
		// Check for Bricks Builder theme
		$has_bricks = defined( 'BRICKS_VERSION' ) ||
		              get_template() === 'bricks' ||
		              class_exists( 'Bricks\Elements' );
		
		if ( ! $has_bricks ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Custom elements registered
		$custom_elements = get_option( 'bricks_custom_elements', array() );
		if ( empty( $custom_elements ) ) {
			return null; // No custom elements
		}
		
		// Check 2: Asset loading
		$load_assets = get_option( 'bricks_custom_element_assets', 'inline' );
		if ( 'inline' === $load_assets ) {
			$issues[] = __( 'Inline assets (page bloat)', 'wpshadow' );
		}
		
		// Check 3: Version compatibility
		foreach ( $custom_elements as $element ) {
			if ( isset( $element['min_version'] ) && version_compare( BRICKS_VERSION, $element['min_version'], '<' ) ) {
				$issues[] = sprintf( __( 'Incompatible element: %s', 'wpshadow' ), $element['name'] );
				break;
			}
		}
		
		// Check 4: Security validation
		$validate_input = get_option( 'bricks_validate_element_input', 'yes' );
		if ( 'no' === $validate_input ) {
			$issues[] = __( 'Input validation disabled (XSS risk)', 'wpshadow' );
		}
		
		// Check 5: Documentation
		$missing_docs = 0;
		foreach ( $custom_elements as $element ) {
			if ( ! isset( $element['documentation'] ) || empty( $element['documentation'] ) ) {
				++$missing_docs;
			}
		}
		
		if ( $missing_docs > 0 ) {
			$issues[] = sprintf( __( '%d elements without documentation', 'wpshadow' ), $missing_docs );
		}
		
		// Check 6: Deprecated API usage
		$deprecated_usage = get_option( 'bricks_deprecated_api_usage', array() );
		if ( ! empty( $deprecated_usage ) ) {
			$issues[] = sprintf( __( '%d deprecated API calls', 'wpshadow' ), count( $deprecated_usage ) );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 50;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 62;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 56;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of custom element issues */
				__( 'Bricks Builder custom elements have %d issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/bricks-builder-custom-elements',
		);
	}
}
