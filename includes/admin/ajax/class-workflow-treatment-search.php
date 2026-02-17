<?php
/**
 * AJAX: Search Treatments for Workflow Action
 *
 * Provides a lightweight search endpoint for selecting a treatment
 * in the workflow wizard.
 *
 * @package WPShadow
 * @since   1.6037.1200
 */

declare(strict_types=1);

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;
use WPShadow\Workflow\Workflow_Discovery;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Workflow_Treatment_Search class.
 */
class Workflow_Treatment_Search extends AJAX_Handler_Base {

	/**
	 * Register AJAX hook.
	 *
	 * @return void
	 */
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_workflow_search_treatments', array( __CLASS__, 'handle' ) );
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

		$treatments = Workflow_Discovery::discover_treatments();

		if ( ! empty( $treatments ) ) {
			foreach ( $treatments as $treatment ) {
				$label = $treatment['label'] ?? '';
				$class = $treatment['class'] ?? '';

				if ( empty( $label ) || empty( $class ) ) {
					continue;
				}

				if ( ! empty( $search ) ) {
					$haystack = strtolower( $label . ' ' . $class );
					if ( false === strpos( $haystack, strtolower( $search ) ) ) {
						continue;
					}
				}

				$items[] = array(
					'class_name' => $class,
					'label'      => $label,
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

Workflow_Treatment_Search::register();
