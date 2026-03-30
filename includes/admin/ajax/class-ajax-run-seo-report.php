<?php
/**
 * AJAX Handler for Running SEO Reports
 *
 * Handles the AJAX request to generate and save SEO reports.
 *
 * @package WPShadow\Admin\Ajax
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;
use WPShadow\Diagnostics\Diagnostic_Registry;
use WPShadow\Reporting\Report_Snapshot_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Run SEO Report AJAX Handler
 *
 * Generates SEO report data and saves it as a snapshot.
 *
 * @since 1.6093.1200
 */
class Run_SEO_Report_Handler extends AJAX_Handler_Base {

	/**
	 * Register the AJAX handler.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_run_seo_report', array( __CLASS__, 'handle' ) );
	}

	/**
	 * Handle the AJAX request to run an SEO report.
	 *
	 * @since 1.6093.1200
	 * @return void Dies after sending JSON response.
	 */
	public static function handle(): void {
		// Verify nonce and capability.
		self::verify_request( 'wpshadow_refresh_seo_reports', 'manage_options', 'nonce' );

		// Check if Report_Snapshot_Manager class exists.
		if ( ! class_exists( 'WPShadow\\Reporting\\Report_Snapshot_Manager' ) ) {
			self::send_error( __( 'Report system not available', 'wpshadow' ) );
		}

		$current_user_id = get_current_user_id();

		// Collect SEO data.
		$seo_diagnostics = array();
		$all_diagnostics = Diagnostic_Registry::get_all();

		foreach ( $all_diagnostics as $slug => $class ) {
			if ( ! class_exists( $class ) ) {
				continue;
			}

			$family = method_exists( $class, 'get_family' ) ? $class::get_family() : '';
			if ( 'seo' === $family ) {
				$seo_diagnostics[ $slug ] = $class;
			}
		}

		// Run SEO diagnostics to generate fresh findings for this report.
		$seo_findings = array();
		$executed_diagnostics = array();
		$diagnostic_results = array();
		$cached_findings_index = function_exists( 'wpshadow_get_cached_findings' )
			? \wpshadow_get_cached_findings()
			: get_option( 'wpshadow_site_findings', array() );
		if ( ! is_array( $cached_findings_index ) ) {
			$cached_findings_index = array();
		}
		if ( function_exists( 'wpshadow_index_findings_by_id' ) ) {
			$cached_findings_index = \wpshadow_index_findings_by_id( $cached_findings_index );
		}
		foreach ( $seo_diagnostics as $slug => $class ) {
			try {
				$cached_state = function_exists( 'wpshadow_get_valid_diagnostic_test_state' )
					? \wpshadow_get_valid_diagnostic_test_state( $class )
					: null;

				if ( is_array( $cached_state ) ) {
					$cached_status = (string) ( $cached_state['status'] ?? 'unknown' );
					if ( 'failed' === $cached_status ) {
						$cached_finding_id = (string) ( $cached_state['finding_id'] ?? '' );
						if ( '' !== $cached_finding_id && isset( $cached_findings_index[ $cached_finding_id ] ) && is_array( $cached_findings_index[ $cached_finding_id ] ) ) {
							$seo_findings[] = $cached_findings_index[ $cached_finding_id ];
						}
					}

					$diagnostic_results[ $class ] = array(
						'status'     => $cached_status,
						'category'   => 'seo',
						'finding_id' => (string) ( $cached_state['finding_id'] ?? '' ),
					);
					continue;
				}

				if ( method_exists( $class, 'execute' ) ) {
					$result = $class::execute();
				} elseif ( method_exists( $class, 'check' ) ) {
					$result = $class::check();
				} else {
					continue;
				}
				$executed_diagnostics[] = $class;

				if ( ! empty( $result ) && is_array( $result ) ) {
					$seo_findings[] = array(
						'id'           => $result['id'] ?? $slug,
						'title'        => $result['title'] ?? $slug,
						'description'  => $result['description'] ?? '',
						'severity'     => $result['severity'] ?? 'medium',
						'threat_level' => $result['threat_level'] ?? 50,
						'auto_fixable' => $result['auto_fixable'] ?? false,
						'kb_link'      => $result['kb_link'] ?? '',
						'family'       => 'seo',
					);
				}

				$diagnostic_results[ $class ] = array(
					'status'     => ( ! empty( $result ) && is_array( $result ) ) ? 'failed' : 'passed',
					'category'   => 'seo',
					'finding_id' => ( ! empty( $result ) && is_array( $result ) ) ? (string) ( $result['id'] ?? $slug ) : '',
				);
			} catch ( \Exception $e ) {
				// Continue running remaining diagnostics.
			}
		}

		// Persist SEO findings so dashboard gauges reflect report-run diagnostics.
		$cached_findings = function_exists( 'wpshadow_get_cached_findings' )
			? \wpshadow_get_cached_findings()
			: get_option( 'wpshadow_site_findings', array() );
		if ( ! is_array( $cached_findings ) ) {
			$cached_findings = array();
		}

		$seo_ids = array_map( 'strval', array_keys( $seo_diagnostics ) );

		$other_findings = array_values(
			array_filter(
				$cached_findings,
				function ( $finding ) use ( $seo_ids ) {
					if ( ! is_array( $finding ) ) {
						return false;
					}

					$finding_family = isset( $finding['family'] ) ? (string) $finding['family'] : '';
					if ( 'seo' === $finding_family ) {
						return false;
					}

					$finding_id = isset( $finding['id'] ) ? (string) $finding['id'] : '';
					if ( '' !== $finding_id && in_array( $finding_id, $seo_ids, true ) ) {
						return false;
					}

					return true;
				}
			)
		);

		$merged_findings = array_merge( $other_findings, $seo_findings );

		if ( function_exists( 'wpshadow_store_gauge_snapshot' ) ) {
			\wpshadow_store_gauge_snapshot( $merged_findings );
		} else {
			update_option( 'wpshadow_site_findings', \wpshadow_index_findings_by_id( $merged_findings ) );
		}
		$completed_at = time();
		if ( function_exists( 'wpshadow_record_diagnostic_run_coverage' ) ) {
			\wpshadow_record_diagnostic_run_coverage( $executed_diagnostics, $completed_at );
		}
		if ( function_exists( 'wpshadow_record_diagnostic_test_states' ) ) {
			\wpshadow_record_diagnostic_test_states( $diagnostic_results, $completed_at );
		}

		update_option( 'wpshadow_last_quick_scan', $completed_at );
		update_option( 'wpshadow_last_quick_checks', $completed_at );

		// Get site SEO metadata.
		$site_title       = get_bloginfo( 'name' );
		$site_description = get_bloginfo( 'description' );
		$site_url         = get_site_url();
		$robots_txt_exists = file_exists( ABSPATH . 'robots.txt' );
		$sitemap_url      = get_sitemap_url( 'index' );

		// Check for SEO plugins.
		$seo_plugins    = array();
		$active_plugins = get_option( 'active_plugins', array() );
		foreach ( $active_plugins as $plugin ) {
			if ( false !== strpos( $plugin, 'seo' ) || false !== strpos( $plugin, 'yoast' ) || false !== strpos( $plugin, 'rank-math' ) ) {
				$seo_plugins[] = $plugin;
			}
		}

		// Calculate SEO score.
		$seo_score       = 100;
		$critical_issues = 0;
		$moderate_issues = 0;

		foreach ( $seo_findings as $finding ) {
			$severity = $finding['severity'] ?? 'low';
			if ( in_array( $severity, array( 'critical', 'high' ), true ) ) {
				$seo_score -= 10;
				$critical_issues++;
			} elseif ( 'medium' === $severity ) {
				$seo_score -= 5;
				$moderate_issues++;
			} else {
				$seo_score -= 2;
			}
		}

		$seo_score = max( 0, min( 100, $seo_score ) );

		$report_summary = array(
			'site_url'         => $site_url,
			'site_title'       => $site_title,
			'seo_score'        => $seo_score,
			'seo_issues_count' => count( $seo_findings ),
			'critical_issues'  => $critical_issues,
			'moderate_issues'  => $moderate_issues,
			'diagnostics_count' => count( $seo_diagnostics ),
			'has_robots_txt'   => $robots_txt_exists,
			'has_sitemap'      => ! empty( $sitemap_url ),
			'seo_plugins_count' => count( $seo_plugins ),
		);

		$report_data = array(
			'generated_at' => current_time( 'mysql' ),
			'summary'      => $report_summary,
			'findings'     => $seo_findings,
			'diagnostics'  => array_keys( $seo_diagnostics ),
			'seo_metadata' => array(
				'title'       => $site_title,
				'description' => $site_description,
				'url'         => $site_url,
				'robots_txt'  => $robots_txt_exists,
				'sitemap'     => $sitemap_url,
			),
			'seo_plugins'  => $seo_plugins,
			'seo_score'    => $seo_score,
		);

		try {
			Report_Snapshot_Manager::save_snapshot(
				'seo-report',
				$report_data,
				array(
					'requested_by' => $current_user_id,
					'summary'      => $report_summary,
				)
			);

			self::send_success(
				array(
					'message' => __( 'SEO report generated successfully', 'wpshadow' ),
				)
			);
		} catch ( \Exception $e ) {
			self::send_error( __( 'Failed to save SEO report', 'wpshadow' ) );
		}
	}
}
