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

use WPShadow\Core\Form_Param_Helper;

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
		// Add defer/async to scripts that can load later
		add_filter( 'script_loader_tag', array( __CLASS__, 'optimize_script_loading' ), 10, 3 );

		// Preload essential assets
		add_action( 'admin_head', array( __CLASS__, 'preload_critical_assets' ), 1 );

		// Remove WordPress bloat from admin
		add_action( 'admin_init', array( __CLASS__, 'remove_admin_bloat' ) );

		// Keep heartbeat enabled so diagnostics can run automatically in the background.
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'ensure_heartbeat_script' ), 100 );
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

		// Immediate-load scripts that need to execute right away (in header)
		$critical_scripts = array(
			'wpshadow-design-system',
			'wpshadow-dashboard-realtime',
		);

		// Scripts that can be deferred (load after main content)
		$defer_scripts = array(
			'wpshadow-tooltips',
			'wpshadow-kanban-board',
			'wpshadow-workflow-list',
		);

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

		// Avoid manual preload hints for design-system assets in admin. Browsers
		// warn when these resources are not consumed quickly enough after load.

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

		// Remove admin bar on WPShadow fullscreen pages
		if ( '1' === Form_Param_Helper::get( 'fullscreen', 'text', '' ) ) {
			show_admin_bar( false );
		}
	}

	/**
	 * Ensure WordPress heartbeat script is enqueued on WPShadow admin pages.
	 *
		 * @since 1.6093.1200
	 * @return void
	 */
	public static function ensure_heartbeat_script(): void {
		if ( ! \function_exists( 'get_current_screen' ) ) {
			return;
		}

		$screen = \get_current_screen();
		if ( ! $screen || ! isset( $screen->id ) || strpos( $screen->id, 'wpshadow' ) === false ) {
			return;
		}

		if ( ! wp_script_is( 'heartbeat', 'enqueued' ) ) {
			wp_enqueue_script( 'heartbeat' );
		}
	}
}

// Initialize optimizer
Asset_Optimizer::init();
