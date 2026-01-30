<?php
/**
 * Divi Builder Pro Theme Options Database Diagnostic
 *
 * Divi Builder Pro Theme Options Database issues found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.808.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Divi Builder Pro Theme Options Database Diagnostic Class
 *
 * @since 1.808.0000
 */
class Diagnostic_DiviBuilderProThemeOptionsDatabase extends Diagnostic_Base {

	protected static $slug = 'divi-builder-pro-theme-options-database';
	protected static $title = 'Divi Builder Pro Theme Options Database';
	protected static $description = 'Divi Builder Pro Theme Options Database issues found';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! function_exists( 'et_setup_theme' ) ) {
			return null;
		}
		
		global $wpdb;
		$issues = array();
		
		// Check 1: Theme options size
		$options_size = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT LENGTH(option_value) FROM {$wpdb->options} WHERE option_name = %s",
				'et_divi'
			)
		);
		
		if ( $options_size > 100000 ) {
			$issues[] = sprintf( __( 'Divi theme options: %.2f KB (optimization needed)', 'wpshadow' ), $options_size / 1024 );
		}
		
		// Check 2: Builder module library size
		$library_count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s",
				'et_pb_layout'
			)
		);
		
		if ( $library_count > 200 ) {
			$issues[] = sprintf( __( '%d items in Divi Library (performance impact)', 'wpshadow' ), $library_count );
		}
		
		// Check 3: Global presets
		$presets = get_option( 'et_pb_global_presets', array() );
		if ( is_array( $presets ) && count( $presets ) > 50 ) {
			$issues[] = sprintf( __( '%d global module presets (consolidation recommended)', 'wpshadow' ), count( $presets ) );
		}
		
		// Check 4: Builder history/revisions
		$history = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = %s",
				'_et_pb_ab_testing_enabled'
			)
		);
		
		// Check 5: Theme customizer data
		$customizer_data = get_option( 'et_divi_customizer', array() );
		if ( is_array( $customizer_data ) && strlen( serialize( $customizer_data ) ) > 50000 ) {
			$issues[] = __( 'Large customizer data (consider reset unused settings)', 'wpshadow' );
		}
		
		// Check 6: Autoload optimization
		$autoloaded = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT autoload FROM {$wpdb->options} WHERE option_name = %s",
				'et_divi'
			)
		);
		
		if ( 'yes' === $autoloaded && $options_size > 50000 ) {
			$issues[] = __( 'Large theme options set to autoload (memory impact)', 'wpshadow' );
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
				/* translators: %s: list of database issues */
				__( 'Divi Builder theme options have %d database issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => true,
			'kb_link'     => 'https://wpshadow.com/kb/divi-builder-pro-theme-options-database',
		);
	}
}
