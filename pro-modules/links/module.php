<?php
/**
 * Links Module for WPShadow Pro
 *
 * This module will eventually live in wpshadow-pro/modules/links/
 * Currently staged here for easier development and future extraction.
 *
 * @package WPShadow
 * @subpackage ProModules
 */

declare(strict_types=1);

namespace WPShadow_Pro\Modules\Links;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Links Module class.
 */
class Module {
	/**
	 * Module information.
	 *
	 * @return array
	 */
	public static function get_info(): array {
		return [
			'id'          => 'links',
			'name'        => __( 'Links Module', 'wpshadow' ),
			'description' => __( 'Create managed link repositories with affiliate disclosure and ad-blocker resistance', 'wpshadow' ),
			'icon'        => '🔗',
			'requires'    => [], // No dependencies
			'version'     => '1.0.0',
			'author'      => 'WPShadow',
		];
	}

	/**
	 * Initialize the module.
	 */
	public static function init(): void {
		// Hook initialization to init so classes are available
		add_action( 'init', array( __CLASS__, 'register_features' ), 5 );
	}

	/**
	 * Register all features on init hook.
	 */
	public static function register_features(): void {
		// Avoid registering features until WordPress is fully installed
		if ( function_exists( 'is_blog_installed' ) && ! is_blog_installed() ) {
			return;
		}
		// Load link classes
		require_once __DIR__ . '/includes/class-links-post-type.php';
		require_once __DIR__ . '/includes/class-links-content-processor.php';
		require_once __DIR__ . '/includes/class-links-redirect-handler.php';
		
		// Initialize links post type
		\WPShadow\Links\Links_Post_Type::init();
		
		// Initialize content processor for link injection
		\WPShadow\Links\Links_Content_Processor::init();
		
		// Initialize redirect handler for affiliate links
		\WPShadow\Links\Links_Redirect_Handler::init();
		
		// Enqueue assets
		self::enqueue_assets();
	}

	/**
	 * Enqueue frontend and admin assets.
	 */
	private static function enqueue_assets(): void {
		add_action( 'wp_enqueue_scripts', [ 'WPShadow_Pro\\Modules\\Links\\Module', 'enqueue_frontend_assets' ] );
		add_action( 'admin_enqueue_scripts', [ 'WPShadow_Pro\\Modules\\Links\\Module', 'enqueue_admin_assets' ] );
	}

	/**
	 * Enqueue frontend assets.
	 */
	public static function enqueue_frontend_assets(): void {
		wp_enqueue_style(
			'wpshadow-links',
			plugins_url( 'assets/links.css', __FILE__ ),
			[],
			'1.0.0'
		);

		wp_enqueue_script(
			'wpshadow-links',
			plugins_url( 'assets/links.js', __FILE__ ),
			[ 'jquery' ],
			'1.0.0',
			true
		);

		wp_localize_script( 'wpshadow-links', 'wpshadowLinks', [
			'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
			'nonce'     => wp_create_nonce( 'wpshadow_links_nonce' ),
			'hideAffiliate' => get_option( 'wpshadow_links_hide_affiliate_text', '0' ) === '1',
		] );
	}

	/**
	 * Enqueue admin assets.
	 */
	public static function enqueue_admin_assets(): void {
		wp_enqueue_style(
			'wpshadow-links-admin',
			plugins_url( 'assets/links-admin.css', __FILE__ ),
			[],
			'1.0.0'
		);
	}
}
