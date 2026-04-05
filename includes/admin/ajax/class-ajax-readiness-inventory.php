<?php
/**
 * AJAX: Readiness Inventory
 *
 * Exposes lifecycle/readiness inventory for diagnostics and treatments.
 *
 * @package WPShadow
 * @since 0.7055
 */

declare(strict_types=1);

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;
use WPShadow\Core\Readiness_Registry;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Readiness inventory AJAX handler.
 */
class AJAX_Readiness_Inventory extends AJAX_Handler_Base {
	/**
	 * Handle inventory request.
	 *
	 * @return void
	 */
	public static function handle(): void {
		self::verify_manage_options_request( 'wpshadow_scan_nonce' );

		$inventory = Readiness_Registry::get_inventory();
		$summary   = array(
			'diagnostics' => self::summarize_states( isset( $inventory['diagnostics'] ) && is_array( $inventory['diagnostics'] ) ? $inventory['diagnostics'] : array() ),
			'treatments'  => self::summarize_states( isset( $inventory['treatments'] ) && is_array( $inventory['treatments'] ) ? $inventory['treatments'] : array() ),
		);

		self::send_success(
			array(
				'summary'   => $summary,
				'inventory' => $inventory,
			)
		);
	}

	/**
	 * Count items by readiness state.
	 *
	 * @param array<int, array<string, mixed>> $items Inventory rows.
	 * @return array<string, int>
	 */
	private static function summarize_states( array $items ): array {
		$counts = array(
			Readiness_Registry::STATE_PRODUCTION => 0,
			Readiness_Registry::STATE_BETA       => 0,
			Readiness_Registry::STATE_PLANNED    => 0,
		);

		foreach ( $items as $item ) {
			$state = isset( $item['state'] ) && is_string( $item['state'] ) ? strtolower( trim( $item['state'] ) ) : '';
			if ( isset( $counts[ $state ] ) ) {
				$counts[ $state ]++;
			}
		}

		$counts['total'] = count( $items );
		return $counts;
	}
}

\add_action( 'wp_ajax_wpshadow_readiness_inventory', array( '\\WPShadow\\Admin\\Ajax\\AJAX_Readiness_Inventory', 'handle' ) );
