<?php

declare(strict_types=1);

namespace WPShadow\Admin;

use WPShadow\CoreSupport\WPSHADOW_Issue_Repository;
use WPShadow\CoreSupport\WPSHADOW_Issue_Registry;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class WPSHADOW_Reports_Page {

	private const MENU_SLUG = 'wpshadow-reports';

	private const PAGE_TITLE = 'WPShadow Reports';

	private const MENU_TITLE = 'Reports';

	private const CAPABILITY = 'manage_options';

	private WPSHADOW_Issue_Repository $repository;

	private WPSHADOW_Issue_Registry $registry;

	private string $page_hook = '';

	public function __construct() {
		$this->repository = new WPSHADOW_Issue_Repository();
		$this->registry = WPSHADOW_Issue_Registry::get_instance();
	}

	public function init(): void {
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'wp_ajax_wpshadow_refresh_issues', array( $this, 'ajax_refresh_issues' ) );
		add_action( 'wp_ajax_wpshadow_export_pdf', array( $this, 'ajax_export_pdf' ) );
		add_action( 'wp_ajax_wpshadow_delete_issue', array( $this, 'ajax_delete_issue' ) );
	}

	public function register_menu(): void {
		if ( ! current_user_can( self::CAPABILITY ) ) {
			return;
		}

		$this->page_hook = add_submenu_page(
			'wpshadow',
			self::PAGE_TITLE,
			self::MENU_TITLE,
			self::CAPABILITY,
			self::MENU_SLUG,
			array( $this, 'render_page' )
		);
	}

	public function render_page(): void {
		if ( ! current_user_can( self::CAPABILITY ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'wpshadow' ) );
		}

		$issues = $this->repository->get_current_issues();
		$breakdown = $this->repository->get_severity_breakdown();
		$stats = $this->repository->get_snapshot_statistics();
		$latest_snapshot = $this->repository->get_latest_snapshot();
		$history = $this->repository->get_history( 7 );

		$severity_filter = isset( $_GET['severity'] ) ? sanitize_text_field( wp_unslash( $_GET['severity'] ) ) : '';
		$search_query = isset( $_GET['search'] ) ? sanitize_text_field( wp_unslash( $_GET['search'] ) ) : '';
		$sort_by = isset( $_GET['sort'] ) ? sanitize_text_field( wp_unslash( $_GET['sort'] ) ) : 'severity';

		$filtered_issues = $this->filter_issues( $issues, $severity_filter, $search_query );
		$sorted_issues = $this->sort_issues( $filtered_issues, $sort_by );

		include WP_PLUGIN_DIR . '/wpshadow/includes/views/reports-dashboard-template.php';
	}

	public function enqueue_assets( string $hook ): void {
		if ( $hook !== $this->page_hook ) {
			return;
		}

		wp_enqueue_script(
			'wpshadow-reports-js',
			plugins_url( 'assets/js/reports-dashboard.js', WP_PLUGIN_DIR . '/wpshadow/wpshadow.php' ),
			array( 'jquery' ),
			'1.0.0',
			true
		);

		wp_enqueue_style(
			'wpshadow-reports-css',
			plugins_url( 'assets/css/reports-dashboard.css', WP_PLUGIN_DIR . '/wpshadow/wpshadow.php' ),
			array(),
			'1.0.0'
		);

		wp_localize_script(
			'wpshadow-reports-js',
			'wpShadowReports',
			array(
				'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
				'nonce'       => wp_create_nonce( 'wpshadow-reports' ),
				'i18n'        => array(
					'refreshing'     => esc_html__( 'Refreshing...', 'wpshadow' ),
					'exporting'      => esc_html__( 'Exporting...', 'wpshadow' ),
					'deleting'       => esc_html__( 'Deleting...', 'wpshadow' ),
					'deleted'        => esc_html__( 'Issue dismissed', 'wpshadow' ),
					'exportComplete' => esc_html__( 'Export complete', 'wpshadow' ),
					'error'          => esc_html__( 'An error occurred', 'wpshadow' ),
				),
			)
		);
	}

	public function ajax_refresh_issues(): void {
		check_ajax_referer( 'wpshadow-reports', 'nonce' );

		if ( ! current_user_can( self::CAPABILITY ) ) {
			wp_send_json_error( 'Insufficient permissions' );
		}

		$detected_issues = $this->registry->get_all_issues();
		$this->repository->store_issues( $detected_issues );

		$issues = $this->repository->get_current_issues();
		$breakdown = $this->repository->get_severity_breakdown();

		wp_send_json_success(
			array(
				'total_issues' => count( $issues ),
				'breakdown'    => $breakdown,
				'timestamp'    => current_time( 'Y-m-d H:i:s' ),
			)
		);
	}

	public function ajax_export_pdf(): void {
		check_ajax_referer( 'wpshadow-reports', 'nonce' );

		if ( ! current_user_can( self::CAPABILITY ) ) {
			wp_send_json_error( 'Insufficient permissions' );
		}

		$today = gmdate( 'Ymd' );
		$json = $this->repository->export_snapshot( $today, 'json' );

		if ( empty( $json ) ) {
			wp_send_json_error( 'No snapshot data available' );
		}

		wp_send_json_success(
			array(
				'data'     => $json,
				'filename' => 'wpshadow-report-' . $today . '.json',
			)
		);
	}

	public function ajax_delete_issue(): void {
		check_ajax_referer( 'wpshadow-reports', 'nonce' );

		if ( ! current_user_can( self::CAPABILITY ) ) {
			wp_send_json_error( 'Insufficient permissions' );
		}

		$issue_id = isset( $_POST['issue_id'] ) ? sanitize_text_field( wp_unslash( $_POST['issue_id'] ) ) : '';

		if ( empty( $issue_id ) ) {
			wp_send_json_error( 'Missing issue ID' );
		}

		$deleted = $this->repository->delete_issue( $issue_id );

		if ( ! $deleted ) {
			wp_send_json_error( 'Failed to delete issue' );
		}

		wp_send_json_success(
			array(
				'message' => 'Issue dismissed',
			)
		);
	}

	private function filter_issues( array $issues, string $severity = '', string $search = '' ): array {
		$filtered = $issues;

		if ( ! empty( $severity ) ) {
			$filtered = array_filter(
				$filtered,
				function( $issue ) use ( $severity ) {
					return isset( $issue['severity'] ) && $issue['severity'] === $severity;
				}
			);
		}

		if ( ! empty( $search ) ) {
			$search_lower = strtolower( $search );
			$filtered = array_filter(
				$filtered,
				function( $issue ) use ( $search_lower ) {
					$title = strtolower( $issue['title'] ?? '' );
					$desc = strtolower( $issue['description'] ?? '' );
					return strpos( $title, $search_lower ) !== false || strpos( $desc, $search_lower ) !== false;
				}
			);
		}

		return $filtered;
	}

	private function sort_issues( array $issues, string $sort_by ): array {
		$sorted = $issues;

		switch ( $sort_by ) {
			case 'severity':
				usort(
					$sorted,
					function( $a, $b ) {
						$severity_order = array( 'critical' => 1, 'high' => 2, 'medium' => 3, 'low' => 4 );
						$a_order = $severity_order[ $a['severity'] ?? 'low' ] ?? 5;
						$b_order = $severity_order[ $b['severity'] ?? 'low' ] ?? 5;
						return $a_order - $b_order;
					}
				);
				break;

			case 'name':
				usort(
					$sorted,
					function( $a, $b ) {
						return strcmp( $a['title'] ?? '', $b['title'] ?? '' );
					}
				);
				break;

			case 'time':
				usort(
					$sorted,
					function( $a, $b ) {
						$a_time = $a['detected_at'] ?? 0;
						$b_time = $b['detected_at'] ?? 0;
						return $b_time - $a_time;
					}
				);
				break;
		}

		return $sorted;
	}
}
