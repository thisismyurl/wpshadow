<?php
/**
 * Asset Optimizer for WPShadow
 *
 * Combines and minifies assets, adds defer/async loading,
 * and implements script dependency optimization.
 *
 * Philosophy: Ridiculously Good (#7) - Fast admin = confidence
 * 
 * @package WPShadow
 * @subpackage Admin
 */

declare(strict_types=1);

namespace WPShadow\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Asset Optimizer class
 */
class Asset_Optimizer {
	
	/**
	 * Initialize optimizer
	 */
	public static function init(): void {
		// Add defer/async to non-critical scripts
		add_filter( 'script_loader_tag', [ __CLASS__, 'optimize_script_loading' ], 10, 3 );
		
		// Preload critical assets
		add_action( 'admin_head', [ __CLASS__, 'preload_critical_assets' ], 1 );
		
		// Remove WordPress bloat from admin
		add_action( 'admin_init', [ __CLASS__, 'remove_admin_bloat' ] );
	}
	
	/**
	 * Add defer/async attributes to scripts
	 * 
	 * @param string $tag Script tag HTML
	 * @param string $handle Script handle
	 * @param string $src Script source URL
	 * @return string Modified script tag
	 */
	public static function optimize_script_loading( string $tag, string $handle, string $src ): string {
		// Only optimize WPShadow scripts
		if ( strpos( $handle, 'wpshadow' ) === false ) {
			return $tag;
		}
		
		// Critical scripts that need immediate execution (in header)
		$critical_scripts = [
			'wpshadow-design-system',
			'wpshadow-dashboard-realtime',
		];
		
		// Scripts that can be deferred (non-critical)
		$defer_scripts = [
			'wpshadow-tooltips',
			'wpshadow-kanban-board',
			'wpshadow-workflow-list',
			'wpshadow-color-contrast',
			'wpshadow-mobile-friendliness',
			'wpshadow-guardian-dashboard-settings',
		];
		
		// Don't modify critical scripts
		if ( in_array( $handle, $critical_scripts, true ) ) {
			return $tag;
		}
		
		// Add defer to non-critical scripts
		if ( in_array( $handle, $defer_scripts, true ) ) {
			return str_replace( ' src', ' defer src', $tag );
		}
		
		return $tag;
	}
	
	/**
	 * Preload critical assets for faster loading
	 */
	public static function preload_critical_assets(): void {
		if ( ! \function_exists( 'get_current_screen' ) ) {
			return;
		}
		
		$screen = \get_current_screen();
		if ( ! $screen || ! isset( $screen->id ) || strpos( $screen->id, 'wpshadow' ) === false ) {
			return;
		}
		
		// Preload critical CSS
		echo '<link rel="preload" href="' . esc_url( WPSHADOW_URL . 'assets/css/design-system.css' ) . '" as="style">' . "\n";
		
		// Preload critical JS
		echo '<link rel="preload" href="' . esc_url( WPSHADOW_URL . 'assets/js/design-system.js' ) . '" as="script">' . "\n";
		
		// DNS prefetch for external resources (if any)
		// echo '<link rel="dns-prefetch" href="//fonts.googleapis.com">' . "\n";
	}
	
	/**
	 * Remove WordPress admin bloat on WPShadow pages
	 */
	public static function remove_admin_bloat(): void {
		if ( ! \function_exists( 'get_current_screen' ) ) {
			return;
		}
		
		$screen = \get_current_screen();
		if ( ! $screen || ! isset( $screen->id ) || strpos( $screen->id, 'wpshadow' ) === false ) {
			return;
		}
		
		// Remove emoji scripts (saves ~15KB + 1 HTTP request)
		remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
		remove_action( 'admin_print_styles', 'print_emoji_styles' );
		
		// Remove unnecessary admin scripts on WPShadow pages
		wp_dequeue_script( 'heartbeat' ); // Saves periodic AJAX calls
		
		// Remove admin bar on WPShadow fullscreen pages
		if ( isset( $_GET['fullscreen'] ) && $_GET['fullscreen'] === '1' ) {
			show_admin_bar( false );
		}
	}
}

// Initialize optimizer
Asset_Optimizer::init();
