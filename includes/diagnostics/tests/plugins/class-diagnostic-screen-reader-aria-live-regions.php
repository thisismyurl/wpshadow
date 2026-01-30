<?php
/**
 * Screen Reader Aria Live Regions Diagnostic
 *
 * Screen Reader Aria Live Regions not compliant.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1140.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Screen Reader Aria Live Regions Diagnostic Class
 *
 * @since 1.1140.0000
 */
class Diagnostic_ScreenReaderAriaLiveRegions extends Diagnostic_Base {

	protected static $slug = 'screen-reader-aria-live-regions';
	protected static $title = 'Screen Reader Aria Live Regions';
	protected static $description = 'Screen Reader Aria Live Regions not compliant';
	protected static $family = 'functionality';

	public static function check() {
		// Check for accessibility plugins that add ARIA
		$has_aria_plugin = defined( 'WP_ACCESSIBILITY_VERSION' ) ||
		                   class_exists( 'One_User_Avatar' ) ||
		                   function_exists( 'wp_accessibility_toolbar' );
		
		$issues = array();
		
		// Check 1: Theme support for ARIA
		if ( ! current_theme_supports( 'html5', 'navigation-widgets' ) ) {
			$issues[] = __( 'Theme lacks HTML5 semantic support', 'wpshadow' );
		}
		
		// Check 2: Skip to content link
		$has_skip_link = false;
		$header_file = get_template_directory() . '/header.php';
		if ( file_exists( $header_file ) ) {
			$header_content = file_get_contents( $header_file );
			$has_skip_link = strpos( $header_content, 'skip' ) !== false &&
			                 strpos( $header_content, 'content' ) !== false;
		}
		if ( ! $has_skip_link ) {
			$issues[] = __( 'No skip to content link (keyboard navigation)', 'wpshadow' );
		}
		
		// Check 3: ARIA landmarks
		if ( ! current_theme_supports( 'html5', 'search-form' ) ) {
			$issues[] = __( 'Theme lacks HTML5 search form support', 'wpshadow' );
		}
		
		// Check 4: Focus management
		$focus_styles = get_option( 'wpshadow_focus_styles_enabled', 'no' );
		if ( 'no' === $focus_styles ) {
			$issues[] = __( 'Focus indicators may be missing (A11Y issue)', 'wpshadow' );
		}
		
		// Check 5: Dynamic content announcements
		global $wp_scripts;
		$has_a11y_js = false;
		if ( isset( $wp_scripts->registered['wp-a11y'] ) ) {
			$has_a11y_js = true;
		}
		if ( ! $has_a11y_js ) {
			$issues[] = __( 'WP A11Y script not enqueued (dynamic updates)', 'wpshadow' );
		}
		
		// Check 6: ARIA live regions in forms
		$forms_accessible = get_option( 'wpshadow_forms_accessible', 'no' );
		if ( 'no' === $forms_accessible ) {
			$issues[] = __( 'Forms lack ARIA live regions (error announcements)', 'wpshadow' );
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
				__( 'Screen reader support has %d issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/screen-reader-aria-live-regions',
		);
	}
}
