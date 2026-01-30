<?php
/**
 * Betheme Demo Installation Diagnostic
 *
 * Betheme Demo Installation needs optimization.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1319.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Betheme Demo Installation Diagnostic Class
 *
 * @since 1.1319.0000
 */
class Diagnostic_BethemeDemoInstallation extends Diagnostic_Base {

	protected static $slug = 'betheme-demo-installation';
	protected static $title = 'Betheme Demo Installation';
	protected static $description = 'Betheme Demo Installation needs optimization';
	protected static $family = 'functionality';

	public static function check() {
		// Check for BeTheme
		$theme = wp_get_theme();
		if ( 'Betheme' !== $theme->name && 'Betheme' !== $theme->parent_theme ) {
			return null;
		}
		
		global $wpdb;
		$issues = array();
		
		// Check 1: Demo content flag
		$demo_installed = get_option( 'betheme_demo_installed', false );
		if ( ! $demo_installed ) {
			return null;
		}
		
		// Check 2: Demo pages still present
		$demo_pages = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts}
				 WHERE post_type = 'page' AND post_title LIKE %s AND post_status = 'publish'",
				'%Demo%'
			)
		);
		
		if ( $demo_pages > 5 ) {
			$issues[] = sprintf( __( '%d demo pages still published', 'wpshadow' ), $demo_pages );
		}
		
		// Check 3: Imported demo images
		$demo_media = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts}
				 WHERE post_type = 'attachment' AND guid LIKE %s",
				'%betheme-demo%'
			)
		);
		
		if ( $demo_media > 20 ) {
			$issues[] = sprintf( __( '%d demo images in media library (disk space)', 'wpshadow' ), $demo_media );
		}
		
		// Check 4: Theme options from demo
		$muffin_options = get_option( 'mfn-options', array() );
		if ( is_array( $muffin_options ) && isset( $muffin_options['demo-data'] ) ) {
			$issues[] = __( 'Demo theme options not cleaned up (unnecessary data)', 'wpshadow' );
		}
		
		// Check 5: Demo sliders
		$demo_sliders = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->prefix}revslider_sliders WHERE title LIKE '%Demo%'"
		);
		
		if ( $demo_sliders > 0 ) {
			$issues[] = sprintf( __( '%d demo Revolution Sliders (database bloat)', 'wpshadow' ), $demo_sliders );
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
				/* translators: %s: list of demo content issues */
				__( 'BeTheme demo installation has %d cleanup issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => true,
			'kb_link'     => 'https://wpshadow.com/kb/betheme-demo-installation',
		);
	}
}
