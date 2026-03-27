<?php
/**
 * AJAX Handler: Refresh Kanban Board
 *
 * Returns the latest findings grouped by Kanban status for lightweight
 * background refreshes on dashboard pages.
 *
 * @package WPShadow
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;
use WPShadow\Core\Finding_Status_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Refresh Kanban Board AJAX Handler
 */
class Refresh_Kanban_Board_Handler extends AJAX_Handler_Base {

	/**
	 * Register AJAX hook.
	 *
	 * @return void
	 */
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_refresh_kanban_board', array( __CLASS__, 'handle' ) );
	}

	/**
	 * Handle refresh request.
	 *
	 * @return void
	 */
	public static function handle(): void {
		self::verify_request( 'wpshadow_kanban', 'manage_options' );

		$findings = function_exists( 'wpshadow_get_cached_findings' ) ? \wpshadow_get_cached_findings() : array();
		if ( empty( $findings ) && function_exists( 'wpshadow_get_site_findings' ) ) {
			$findings = \wpshadow_get_site_findings();
		}

		if ( ! is_array( $findings ) ) {
			$findings = array();
		}

		$grouped = array(
			'detected'  => array(),
			'manual'    => array(),
			'automated' => array(),
			'ignored'   => array(),
			'fixed'     => array(),
		);

		foreach ( $findings as $finding ) {
			if ( ! is_array( $finding ) || empty( $finding['id'] ) ) {
				continue;
			}

			$status = Finding_Status_Manager::get_finding_status( (string) $finding['id'] );
			if ( empty( $status ) || ! isset( $grouped[ $status ] ) ) {
				$status = 'detected';
			}

			$grouped[ $status ][] = $finding;
		}

		self::send_success(
			array(
				'findings'        => $grouped,
				'status_manager'  => Finding_Status_Manager::get_findings_by_status(),
			)
		);
	}
}
