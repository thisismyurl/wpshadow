<?php
/**
 * Oceanwp Theme Demo Import Diagnostic
 *
 * Oceanwp Theme Demo Import needs optimization.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1296.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Oceanwp Theme Demo Import Diagnostic Class
 *
 * @since 1.1296.0000
 */
class Diagnostic_OceanwpThemeDemoImport extends Diagnostic_Base {

	protected static $slug = 'oceanwp-theme-demo-import';
	protected static $title = 'Oceanwp Theme Demo Import';
	protected static $description = 'Oceanwp Theme Demo Import needs optimization';
	protected static $family = 'functionality';

	public static function check() {
		$theme = wp_get_theme();
		if ( 'OceanWP' !== $theme->get( 'Name' ) && 'OceanWP' !== $theme->get_template() ) {
			return null;
		}

		$issues = array();

		// Check 1: Demo data installed
		$demo_installed = get_option( 'oceanwp_demo_import', 'no' );
		if ( 'yes' === $demo_installed ) {
			$issues[] = __( 'Demo data still installed (bloated database)', 'wpshadow' );
		}

		// Check 2: Demo images
		$demo_images = get_option( 'oceanwp_demo_images', 'yes' );
		if ( 'yes' === $demo_images ) {
			$issues[] = __( 'Demo images in media library (wasted storage)', 'wpshadow' );
		}

		// Check 3: Demo posts count
		global $wpdb;
		$demo_posts = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_title LIKE '%demo%'"
		);
		if ( $demo_posts > 0 ) {
			$issues[] = sprintf( __( '%d demo posts found (cleanup needed)', 'wpshadow' ), $demo_posts );
		}

		// Check 4: Import in progress
		$import_status = get_transient( 'oceanwp_demo_import_status' );
		if ( false !== $import_status ) {
			$issues[] = __( 'Import in progress (resource intensive)', 'wpshadow' );
		}

		// Check 5: Failed imports
		$failed_imports = get_option( 'oceanwp_failed_imports', array() );
		if ( ! empty( $failed_imports ) ) {
			$issues[] = sprintf( __( '%d failed import attempts', 'wpshadow' ), count( $failed_imports ) );
		}

		// Check 6: Import timeout
		$timeout = get_option( 'oceanwp_import_timeout', 300 );
		if ( $timeout < 600 ) {
			$issues[] = __( 'Import timeout too short (failures likely)', 'wpshadow' );
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
				__( 'OceanWP demo import has %d issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/oceanwp-theme-demo-import',
		);
	}
}
