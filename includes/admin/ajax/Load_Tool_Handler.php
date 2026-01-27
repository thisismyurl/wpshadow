<?php
/**
 * Load Tool AJAX Handler
 *
 * Loads tool content via AJAX for tab-based display
 *
 * @package WPShadow
 * @subpackage Admin/AJAX
 */

declare(strict_types=1);

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AJAX handler for loading tools
 */
class Load_Tool_Handler extends AJAX_Handler_Base {

	/**
	 * Register AJAX hook
	 */
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_load_tool', array( __CLASS__, 'handle' ) );
	}

	/**
	 * Handle tool load request
	 */
	public static function handle(): void {
		// Verify nonce and capability
		self::verify_request( 'wpshadow_load_tool', 'read' );

		// Get tool parameter
		$tool = self::get_post_param( 'tool', 'key', '', true );

		// Validate tool name (alphanumeric, hyphens only)
		if ( ! preg_match( '/^[a-z0-9-]+$/', $tool ) ) {
			self::send_error( __( 'Invalid tool name', 'wpshadow' ) );
			return;
		}

		// Build tool file path
		$tool_file = WPSHADOW_PATH . 'includes/views/tools/' . $tool . '.php';

		// Verify file exists
		if ( ! file_exists( $tool_file ) ) {
			self::send_error( 
				sprintf(
					/* translators: %s: tool name */
					__( 'Tool "%s" not found', 'wpshadow' ),
					$tool
				)
			);
			return;
		}

		// Load tool content via output buffering
		ob_start();
		include $tool_file;
		$content = ob_get_clean();

		self::send_success(
			array(
				'content' => $content,
				'tool'    => $tool,
			)
		);
	}
}
