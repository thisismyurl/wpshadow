<?php
/**
 * Glossary Module for WPShadow Pro
 *
 * This module will eventually live in wpshadow-pro/modules/glossary/
 * Currently staged here for easier development and future extraction.
 *
 * @package WPShadow
 * @subpackage ProModules
 */

declare(strict_types=1);

namespace WPShadow_Pro\Modules\Glossary;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Glossary Module class.
 */
class Module {
	/**
	 * Module information.
	 *
	 * @return array
	 */
	public static function get_info(): array {
		return [
			'id'          => 'glossary',
			'name'        => __( 'Glossary Module', 'wpshadow' ),
			'description' => __( 'Create industry-specific glossary terms with automatic tooltips in content', 'wpshadow' ),
			'icon'        => '📖',
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
		// Load glossary classes
		require_once __DIR__ . '/includes/class-glossary-post-type.php';
		require_once __DIR__ . '/includes/class-glossary-content-processor.php';
		require_once __DIR__ . '/includes/class-glossary-tooltip-handler.php';
		
		// Initialize glossary post type
		\WPShadow\Glossary\Glossary_Post_Type::init();
		
		// Initialize content processor for tooltip injection
		\WPShadow\Glossary\Glossary_Content_Processor::init();
		
		// Register AJAX handler
		\WPShadow\Glossary\Glossary_Tooltip_Handler::register();
		
		// Enqueue assets
		self::enqueue_assets();
	}

	/**
	 * Enqueue frontend and admin assets.
	 */
	private static function enqueue_assets(): void {
		add_action( 'wp_enqueue_scripts', [ 'WPShadow_Pro\\Modules\\Glossary\\Module', 'enqueue_frontend_assets' ] );
		add_action( 'admin_enqueue_scripts', [ 'WPShadow_Pro\\Modules\\Glossary\\Module', 'enqueue_admin_assets' ] );
	}

	/**
	 * Enqueue frontend assets.
	 */
	public static function enqueue_frontend_assets(): void {
		wp_enqueue_style(
			'wpshadow-glossary',
			plugins_url( 'assets/glossary.css', __FILE__ ),
			[],
			'1.0.0'
		);

		wp_enqueue_script(
			'wpshadow-glossary',
			plugins_url( 'assets/glossary.js', __FILE__ ),
			[ 'jquery' ],
			'1.0.0',
			true
		);

		wp_localize_script( 'wpshadow-glossary', 'wpshadowGlossary', [
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'wpshadow_glossary_nonce' ),
		] );
	}

	/**
	 * Enqueue admin assets.
	 */
	public static function enqueue_admin_assets(): void {
		wp_enqueue_style(
			'wpshadow-glossary-admin',
			plugins_url( 'assets/glossary-admin.css', __FILE__ ),
			[],
			'1.0.0'
		);
	}
}
