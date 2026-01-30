<?php
/**
 * Genesis Framework Child Theme Loading Diagnostic
 *
 * Genesis Framework Child Theme Loading needs optimization.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1289.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Genesis Framework Child Theme Loading Diagnostic Class
 *
 * @since 1.1289.0000
 */
class Diagnostic_GenesisFrameworkChildThemeLoading extends Diagnostic_Base {

	protected static $slug = 'genesis-framework-child-theme-loading';
	protected static $title = 'Genesis Framework Child Theme Loading';
	protected static $description = 'Genesis Framework Child Theme Loading needs optimization';
	protected static $family = 'performance';

	public static function check() {
		// Check for Genesis Framework
		if ( ! function_exists( 'genesis' ) && ! defined( 'GENESIS_VERSION' ) ) {
			return null;
		}
		
		$theme = wp_get_theme();
		if ( ! $theme->parent() ) {
			return null; // Not a child theme
		}
		
		$issues = array();
		
		// Check 1: Parent theme is Genesis
		if ( 'Genesis' !== $theme->parent()->get( 'Name' ) ) {
			return null;
		}
		
		// Check 2: Child theme functions.php exists
		$child_functions = get_stylesheet_directory() . '/functions.php';
		if ( ! file_exists( $child_functions ) ) {
			$issues[] = __( 'Child theme functions.php missing', 'wpshadow' );
		} else {
			// Check 3: Functions.php size (overly complex)
			$file_size = filesize( $child_functions );
			if ( $file_size > 102400 ) { // 100KB
				$issues[] = sprintf( __( 'Functions.php %dKB (consider splitting into includes)', 'wpshadow' ), $file_size / 1024 );
			}
		}
		
		// Check 4: Genesis hooks properly used
		if ( function_exists( 'genesis_register_sidebar' ) ) {
			$sidebars = wp_get_sidebars_widgets();
			$genesis_sidebars = array_filter( array_keys( $sidebars ), function( $key ) {
				return strpos( $key, 'genesis-' ) === 0;
			} );
			
			if ( count( $genesis_sidebars ) > 10 ) {
				$issues[] = sprintf( __( '%d Genesis sidebars (widget query overhead)', 'wpshadow' ), count( $genesis_sidebars ) );
			}
		}
		
		// Check 5: Style enqueuing method
		$style_handle = get_stylesheet();
		if ( ! wp_style_is( $style_handle, 'enqueued' ) ) {
			$issues[] = __( 'Child theme styles not properly enqueued', 'wpshadow' );
		}
		
		// Check 6: Genesis version compatibility
		if ( defined( 'GENESIS_VERSION' ) ) {
			$genesis_version = GENESIS_VERSION;
			$child_requires = $theme->get( 'Requires at least' );
			
			if ( ! empty( $child_requires ) && version_compare( $genesis_version, $child_requires, '<' ) ) {
				$issues[] = sprintf(
					/* translators: 1: required version, 2: current version */
					__( 'Genesis version %1$s required, %2$s installed', 'wpshadow' ),
					$child_requires,
					$genesis_version
				);
			}
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 55;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 68;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 62;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of child theme issues */
				__( 'Genesis child theme has %d loading issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/genesis-framework-child-theme-loading',
		);
	}
}
