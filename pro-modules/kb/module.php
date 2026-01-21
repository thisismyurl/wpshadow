<?php
/**
 * Knowledge Base Module for WPShadow Pro
 *
 * This module will eventually live in wpshadow-pro/modules/kb/
 * Currently staged here for easier development and future extraction.
 *
 * @package WPShadow
 * @subpackage ProModules
 */

declare(strict_types=1);

namespace WPShadow_Pro\Modules\KB;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Knowledge Base Module class.
 */
class Module {
	/**
	 * Module information.
	 *
	 * @return array
	 */
	public static function get_info(): array {
		return [
			'id'          => 'kb',
			'name'        => __( 'Knowledge Base Module', 'wpshadow' ),
			'description' => __( 'Build comprehensive documentation with KB articles, search, and training integration', 'wpshadow' ),
			'icon'        => '📚',
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
		// Load KB classes
		require_once __DIR__ . '/includes/class-kb-formatter.php';
		require_once __DIR__ . '/includes/class-kb-article-generator.php';
		require_once __DIR__ . '/includes/class-kb-library.php';
		require_once __DIR__ . '/includes/class-kb-search.php';
		require_once __DIR__ . '/includes/class-training-provider.php';
		require_once __DIR__ . '/includes/class-training-progress.php';
		require_once __DIR__ . '/includes/class-kb-shortcodes.php';
		
		// Register shortcodes
		\WPShadow_Pro\Modules\KB\KB_Shortcodes::register();
		
		// Load KB Cloud Integration Block
		require_once __DIR__ . '/class-kb-cloud-integration-block.php';
		\WPShadow_Pro\Modules\KB\KB_Cloud_Integration_Block::register();
		
		// Load AJAX handlers
		require_once __DIR__ . '/class-clear-backup-cache-handler.php';
		\WPShadow_Pro\Modules\KB\Clear_Backup_Cache_Handler::register();

		// Enqueue shortcode styles
		add_action( 'wp_enqueue_scripts', [ __CLASS__, 'enqueue_shortcode_styles' ] );
	}

	/**
	 * Enqueue styles for KB shortcodes.
	 */
	public static function enqueue_shortcode_styles(): void {
		$css_path = plugin_dir_path( __FILE__ ) . 'assets/kb-shortcodes.css';
		$css_url  = plugin_dir_url( __FILE__ ) . 'assets/kb-shortcodes.css';

		if ( file_exists( $css_path ) ) {
			wp_enqueue_style(
				'wpshadow-kb-shortcodes',
				$css_url,
				[],
				filemtime( $css_path )
			);
		}
	}

	/**
	 * Check if module can be activated.
	 *
	 * @return bool|string True if can activate, error message if not.
	 */
	public static function can_activate() {
		// KB module has no special requirements
		return true;
	}

	/**
	 * Run on module activation.
	 */
	public static function on_activate(): void {
		// Register KB post type and flush rewrite rules
		flush_rewrite_rules();
	}

	/**
	 * Run on module deactivation.
	 */
	public static function on_deactivate(): void {
		// Flush rewrite rules
		flush_rewrite_rules();
	}
}
