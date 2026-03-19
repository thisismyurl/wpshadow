<?php

/**
 * WPShadow AJAX Router
 *
 * Centralizes registration of all AJAX handlers.
 * Extracted from wpshadow.php as part of Phase 4.5 refactoring.
 *
 * Philosophy: Commandment #7 (Ridiculously Good - clear separation of concerns)
 *
 * @package WPShadow
 * @subpackage Core
 */

namespace WPShadow\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Routes and registers all AJAX handlers for WPShadow
 */
class AJAX_Router {

	/**
	 * Register all AJAX handlers via auto-discovery.
	 *
	 * Scans the includes/admin/ajax/ directory and automatically
	 * registers all classes that extend AJAX_Handler_Base.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function init() {
		self::discover_and_register_handlers();
	}

	/**
	 * Auto-discover and register all AJAX handlers.
	 *
	 * Convention: All handlers extend AJAX_Handler_Base and live in
	 * includes/admin/ajax/ directory.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	private static function discover_and_register_handlers() {
		$ajax_dir = defined( 'WPSHADOW_PATH' ) ? WPSHADOW_PATH . 'includes/admin/ajax/' : '';
		
		if ( empty( $ajax_dir ) || ! is_dir( $ajax_dir ) ) {
			return;
		}

		// Get all PHP files in ajax directory.
		$files = glob( $ajax_dir . '*.php' );
		
		if ( empty( $files ) ) {
			return;
		}

		foreach ( $files as $file ) {
			// Convert filename to expected class name.
			// Example: dismiss-finding-handler.php -> Dismiss_Finding_Handler.
			$basename   = basename( $file, '.php' );
			$class_name = self::filename_to_classname( $basename );
			$full_class = "\\WPShadow\\Admin\\Ajax\\{$class_name}";
			
			// Check if class exists and extends AJAX_Handler_Base.
			if ( class_exists( $full_class ) ) {
				if ( is_subclass_of( $full_class, '\\WPShadow\\Core\\AJAX_Handler_Base' ) ) {
					// Auto-register the handler.
					$full_class::register();
				}
			}
		}
	}

	/**
	 * Convert filename to class name.
	 *
	 * Examples:
	 * - dismiss-finding-handler.php -> Dismiss_Finding_Handler
	 * - class-site-dna-handler.php -> Site_Dna_Handler
	 *
	 * @since 1.6093.1200
	 * @param  string $filename Filename without .php extension.
	 * @return string Class name.
	 */
	private static function filename_to_classname( $filename ) {
		// Remove 'class-' prefix if present.
		$filename = preg_replace( '/^class-/', '', $filename );
		
		// Split on hyphens, capitalize each part.
		$parts = array_map( 'ucfirst', explode( '-', $filename ) );
		
		// Join with underscores.
		return implode( '_', $parts );
	}
}
