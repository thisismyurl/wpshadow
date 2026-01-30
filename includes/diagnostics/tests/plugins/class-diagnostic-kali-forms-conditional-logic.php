<?php
/**
 * Kali Forms Conditional Logic Diagnostic
 *
 * Kali Forms Conditional Logic issue found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1213.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Kali Forms Conditional Logic Diagnostic Class
 *
 * @since 1.1213.0000
 */
class Diagnostic_KaliFormsConditionalLogic extends Diagnostic_Base {

	protected static $slug = 'kali-forms-conditional-logic';
	protected static $title = 'Kali Forms Conditional Logic';
	protected static $description = 'Kali Forms Conditional Logic issue found';
	protected static $family = 'functionality';

	public static function check() {
		// Check for Kali Forms plugin
		if ( ! class_exists( 'KaliForms\Inc\KaliForms' ) && ! defined( 'KALIFORMS_VERSION' ) ) {
			return null;
		}
		
		global $wpdb;
		$issues = array();
		
		// Check 1: Forms with conditional logic
		$forms_with_logic = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts}
				 WHERE post_type = %s AND post_content LIKE %s",
				'kaliforms_forms',
				'%conditionalLogic%'
			)
		);
		
		if ( $forms_with_logic === 0 ) {
			return null;
		}
		
		// Check 2: Complex logic rules
		$complex_forms = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts}
			 WHERE post_type = 'kaliforms_forms'
			 AND (LENGTH(post_content) - LENGTH(REPLACE(post_content, 'conditionalLogic', ''))) > 1000"
		);
		
		if ( $complex_forms > 2 ) {
			$issues[] = sprintf( __( '%d forms with complex conditional logic (performance impact)', 'wpshadow' ), $complex_forms );
		}
		
		// Check 3: Logic validation
		$validate_logic = get_option( 'kaliforms_validate_conditional_logic', true );
		if ( ! $validate_logic ) {
			$issues[] = __( 'Conditional logic validation disabled (broken forms risk)', 'wpshadow' );
		}
		
		// Check 4: Field dependency tracking
		$track_dependencies = get_option( 'kaliforms_track_field_dependencies', false );
		if ( ! $track_dependencies ) {
			$issues[] = __( 'Field dependency tracking not enabled (circular reference risk)', 'wpshadow' );
		}
		
		// Check 5: Logic caching
		$cache_logic = get_option( 'kaliforms_cache_conditional_logic', false );
		if ( ! $cache_logic && $forms_with_logic > 5 ) {
			$issues[] = __( 'Conditional logic not cached (repeated processing)', 'wpshadow' );
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
				/* translators: %s: list of conditional logic issues */
				__( 'Kali Forms conditional logic has %d configuration issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/kali-forms-conditional-logic',
		);
	}
}
