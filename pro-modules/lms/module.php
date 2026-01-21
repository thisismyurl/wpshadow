<?php
/**
 * LMS Module for WPShadow Pro
 *
 * This module will eventually live in wpshadow-pro/modules/lms/
 * Currently staged here for easier development and future extraction.
 *
 * @package WPShadow
 * @subpackage ProModules
 */

declare(strict_types=1);

namespace WPShadow_Pro\Modules\LMS;

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
			'id'          => 'lms',
			'name'        => __( 'LMS Module', 'wpshadow' ),
			'description' => __( 'Learning Management System integration for WPShadow', 'wpshadow' ),
			'icon'        => '🎓',
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
		// Load the LMS post type class
		require_once __DIR__ . '/module-lms.php';
		
		// Initialize LMS post type
		\WPShadow\LMS\LMS_Post_Type::init();
	}

	/**
	 * Check if module can be activated.
	 *
	 * @return bool|string True if can activate, error message if not.
	 */
	public static function can_activate() {
		// LMS module has no special requirements
		return true;
	}

	/**
	 * Run on module activation.
	 */
	public static function on_activate(): void {
		// Flush rewrite rules when LMS module is activated
		flush_rewrite_rules();
	}

	/**
	 * Run on module deactivation.
	 */
	public static function on_deactivate(): void {
		// Flush rewrite rules when LMS module is deactivated
		flush_rewrite_rules();
	}
}
