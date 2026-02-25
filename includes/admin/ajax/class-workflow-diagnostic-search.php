<?php
/**
 * AJAX: Search Diagnostics for Workflow Trigger
 *
 * @package WPShadow
 * @since   1.6052.1200
 */

declare(strict_types=1);

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;
use WPShadow\Workflow\Workflow_Discovery;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Workflow_Diagnostic_Search class.
 */
class Workflow_Diagnostic_Search extends AJAX_Handler_Base {

	/**
	 * Register AJAX hook.
	 *
	 * @return void
	 */
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_workflow_search_diagnostics', array( __CLASS__, 'handle' ) );
	}

	/**
	 * Handle AJAX request.
	 *
	 * @return void
	 */
	public static function handle(): void {
		self::verify_request( 'wpshadow_workflow', 'manage_options' );

		$search = self::get_post_param( 'search', 'text', '' );
		$items  = array();

		$diagnostics = Workflow_Discovery::discover_diagnostics();

		if ( ! empty( $diagnostics ) ) {
			foreach ( $diagnostics as $slug => $diagnostic ) {
				$label = $diagnostic['label'] ?? '';

				if ( empty( $label ) || empty( $slug ) ) {
					continue;
				}

				if ( ! empty( $search ) ) {
					$haystack = strtolower( $label . ' ' . $slug );
					if ( false === strpos( $haystack, strtolower( $search ) ) ) {
						continue;
					}
				}

				$items[] = array(
					'slug'  => (string) $slug,
					'label' => (string) $label,
				);
			}
		}

		self::send_success(
			array(
				'items' => array_slice( $items, 0, 20 ),
			)
		);
	}
}

Workflow_Diagnostic_Search::register();
