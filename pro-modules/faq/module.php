<?php
/**
 * FAQ Module for WPShadow Pro
 *
 * This module will eventually live in wpshadow-pro/modules/faq/
 * Currently staged here for easier development and future extraction.
 *
 * @package WPShadow
 * @subpackage ProModules
 */

declare(strict_types=1);

namespace WPShadow_Pro\Modules\FAQ;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * FAQ Module class.
 */
class Module {
	/**
	 * Module information.
	 *
	 * @return array
	 */
	public static function get_info(): array {
		return [
			'id'          => 'faq',
			'name'        => __( 'FAQ Module', 'wpshadow' ),
			'description' => __( 'Create and manage FAQ content with Schema.org markup and reusable blocks', 'wpshadow' ),
			'icon'        => '📝',
			'requires'    => [], // No dependencies
			'version'     => '1.0.0',
			'author'      => 'WPShadow',
		];
	}

	/**
	 * Initialize the module.
	 */
	public static function init(): void {
		// Defer feature registration to init hook
		add_action( 'init', array( __CLASS__, 'register_features' ), 5 );
	}

	/**
	 * Register all features on init hook.
	 */
	public static function register_features(): void {
		// Load the FAQ post type class
		require_once __DIR__ . '/module-faq.php';
		
		// Initialize FAQ post type
		\WPShadow\FAQ\FAQ_Post_Type::init();
	}

	/**
	 * Check if module can be activated.
	 *
	 * @return bool|string True if can activate, error message if not.
	 */
	public static function can_activate() {
		// FAQ module has no special requirements
		return true;
	}

	/**
	 * Run on module activation.
	 */
	public static function on_activate(): void {
		// Flush rewrite rules when FAQ module is activated
		flush_rewrite_rules();
	}

	/**
	 * Run on module deactivation.
	 */
	public static function on_deactivate(): void {
		// Flush rewrite rules when FAQ module is deactivated
		flush_rewrite_rules();
	}
}
