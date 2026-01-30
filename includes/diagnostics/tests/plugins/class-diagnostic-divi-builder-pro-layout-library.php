<?php
/**
 * Divi Builder Pro Layout Library Diagnostic
 *
 * Divi Builder Pro Layout Library issues found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.809.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Divi Builder Pro Layout Library Diagnostic Class
 *
 * @since 1.809.0000
 */
class Diagnostic_DiviBuilderProLayoutLibrary extends Diagnostic_Base {

	protected static $slug = 'divi-builder-pro-layout-library';
	protected static $title = 'Divi Builder Pro Layout Library';
	protected static $description = 'Divi Builder Pro Layout Library issues found';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! function_exists( 'et_setup_theme' ) ) {
			return null;
		}

		global $wpdb;
		$issues = array();

		// Check 1: Layout count
		$layout_count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s",
				'et_pb_layout'
			)
		);

		if ( $layout_count > 100 ) {
			$issues[] = sprintf( __( '%d layouts (slow library loading)', 'wpshadow' ), $layout_count );
		}

		// Check 2: Cloud sync
		$cloud_sync = get_option( 'et_pb_enable_cloud_sync', 'off' );
		if ( 'off' === $cloud_sync ) {
			$issues[] = __( 'Cloud sync disabled (no backup)', 'wpshadow' );
		}

		// Check 3: Library categories
		$category_count = wp_count_terms( 'layout_category' );
		if ( is_int( $category_count ) && $category_count === 0 ) {
			$issues[] = __( 'No layout categories (poor organization)', 'wpshadow' );
		}

		// Check 4: Orphaned layouts
		$orphaned = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} p
				 LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = '_et_pb_use_builder'
				 WHERE p.post_type = %s AND pm.meta_value IS NULL",
				'et_pb_layout'
			)
		);

		if ( $orphaned > 10 ) {
			$issues[] = sprintf( __( '%d orphaned layouts (database bloat)', 'wpshadow' ), $orphaned );
		}

		// Check 5: Global presets
		$presets = get_option( 'et_pb_global_presets', array() );
		if ( count( $presets ) > 50 ) {
			$issues[] = sprintf( __( '%d global presets (slow loading)', 'wpshadow' ), count( $presets ) );
		}

		// Check 6: Version control
		$version_control = get_option( 'et_pb_version_control', 'off' );
		if ( 'off' === $version_control ) {
			$issues[] = __( 'Version control disabled (no revisions)', 'wpshadow' );
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
				__( 'Divi Builder layout library has %d issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/divi-builder-pro-layout-library',
		);
	}
}
