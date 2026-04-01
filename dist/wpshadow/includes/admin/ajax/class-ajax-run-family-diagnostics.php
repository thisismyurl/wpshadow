<?php
/**
 * AJAX Handler: Run Diagnostics by Family
 *
 * Executes all diagnostics in a specific family (security, performance, SEO).
 *
 * @package    WPShadow
 * @subpackage Admin
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;
use WPShadow\Diagnostics\Diagnostic_Registry;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Run Family Diagnostics Handler
 *
 * @since 0.6093.1200
 */
class AJAX_Run_Family_Diagnostics extends AJAX_Handler_Base {

	/**
	 * Register the AJAX handler.
	 *
	 * @since 0.6093.1200
	 * @return void
	 */
	public static function register() {
		add_action( 'wp_ajax_wpshadow_run_family_diagnostics', array( __CLASS__, 'handle' ) );
	}

	/**
	 * Handle the AJAX request.
	 *
	 * @since 0.6093.1200
	 * @return void Dies after sending JSON response.
	 */
	public static function handle() {
		// Verify nonce and capability
		self::verify_request( 'wpshadow_security_scan', 'manage_options' );
		if ( function_exists( 'set_time_limit' ) ) {
			set_time_limit( 30 );
		}
		ini_set( 'default_socket_timeout', '10' );

		// Get and sanitize parameters
		$family = self::get_post_param( 'family', 'text', '', true );
		error_log( sprintf( 'WPShadow: %s diagnostics request started', $family ) );

		// Validate family
		$valid_families = array( 'security', 'performance', 'seo', 'accessibility', 'protection', 'email' );
		if ( ! in_array( $family, $valid_families, true ) ) {
			self::send_error( __( 'Invalid diagnostic family specified.', 'wpshadow' ) );
		}

		// Limit external HTTP calls to keep AJAX responsive.
		$http_args_filter = static function ( $args ) {
			$timeout = isset( $args['timeout'] ) ? (float) $args['timeout'] : 0;
			if ( 0 === $timeout || $timeout > 10 ) {
				$args['timeout'] = 10;
			}
			return $args;
		};
		add_filter( 'http_request_args', $http_args_filter, 10, 1 );

		update_option(
			'wpshadow_diagnostics_status',
			array(
				'family'    => $family,
				'slug'      => '',
				'last_slug' => '',
				'state'     => 'starting',
				'started'   => time(),
				'updated'   => time(),
			)
		);

		// Get all diagnostics
		$all_diagnostics = Diagnostic_Registry::get_all();
		$family_diagnostics = array();
		$findings = array();
		$start_time = microtime( true );
		$max_duration = 25;
		$timed_out = false;

		// Filter diagnostics by family
		foreach ( $all_diagnostics as $slug => $class ) {
			if ( ! class_exists( $class ) ) {
				continue;
			}

			// Check if diagnostic belongs to requested family
			$diagnostic_family = method_exists( $class, 'get_family' ) ? $class::get_family() : '';
			if ( $diagnostic_family !== $family ) {
				continue;
			}

			$family_diagnostics[ $slug ] = $class;
			update_option(
				'wpshadow_diagnostics_status',
				array(
					'family'    => $family,
					'slug'      => $slug,
					'last_slug' => $slug,
					'state'     => 'running',
					'started'   => time(),
					'updated'   => time(),
				)
			);
			error_log( sprintf( 'WPShadow: %s diagnostic start %s', $family, $slug ) );

			// Run the diagnostic
			try {
				$diagnostic_start = microtime( true );
				if ( method_exists( $class, 'execute' ) ) {
					$result = $class::execute();
				} elseif ( method_exists( $class, 'check' ) ) {
					$result = $class::check();
				} else {
					continue;
				}
				$diagnostic_duration = microtime( true ) - $diagnostic_start;
				error_log( sprintf( 'WPShadow: %s diagnostic end %s (%.3fs)', $family, $slug, $diagnostic_duration ) );
				if ( $diagnostic_duration > 1 ) {
					error_log( sprintf( 'WPShadow: %s diagnostic %s took %.3fs', $family, $slug, $diagnostic_duration ) );
				}

				// If finding detected, add to results
				if ( ! empty( $result ) && is_array( $result ) ) {
					$findings[] = array(
						'id'           => $result['id'] ?? $slug,
						'title'        => $result['title'] ?? ( method_exists( $class, 'get_title' ) ? $class::get_title() : $slug ),
						'description'  => $result['description'] ?? '',
						'severity'     => $result['severity'] ?? 'medium',
						'threat_level' => $result['threat_level'] ?? 50,
						'auto_fixable' => $result['auto_fixable'] ?? false,
						'kb_link'      => $result['kb_link'] ?? '',
						'family'       => $family,
					);
				}
			} catch ( \Exception $e ) {
				// Log error but continue with other diagnostics
				error_log( sprintf( 'WPShadow: Error running diagnostic %s: %s', $class, $e->getMessage() ) );
			}

			if ( ( microtime( true ) - $start_time ) >= $max_duration ) {
				$timed_out = true;
				error_log( sprintf( 'WPShadow: %s diagnostics stopped after %.2fs', $family, $max_duration ) );
				break;
			}
		}

		remove_filter( 'http_request_args', $http_args_filter, 10 );

		$duration = microtime( true ) - $start_time;
		error_log( sprintf( 'WPShadow: %s diagnostics completed in %.3fs (%d checks)', $family, $duration, count( $family_diagnostics ) ) );
		$current_status = get_option( 'wpshadow_diagnostics_status', array() );
		update_option(
			'wpshadow_diagnostics_status',
			array(
				'family'    => $family,
				'slug'      => '',
				'last_slug' => $current_status['last_slug'] ?? '',
				'state'     => $timed_out ? 'timed_out' : 'complete',
				'finished'  => time(),
				'updated'   => time(),
			)
		);

		// Sort findings by severity (critical > high > medium > low)
		usort(
			$findings,
			function ( $a, $b ) {
				$severity_order = array(
					'critical' => 4,
					'high'     => 3,
					'medium'   => 2,
					'low'      => 1,
				);

				$a_order = $severity_order[ $a['severity'] ] ?? 0;
				$b_order = $severity_order[ $b['severity'] ] ?? 0;

				return $b_order - $a_order;
			}
		);

		// Calculate statistics
		$stats = array(
			'total_diagnostics' => count( $family_diagnostics ),
			'issues_found'      => count( $findings ),
			'critical_count'    => count( array_filter( $findings, fn( $f ) => $f['severity'] === 'critical' ) ),
			'high_count'        => count( array_filter( $findings, fn( $f ) => $f['severity'] === 'high' ) ),
			'medium_count'      => count( array_filter( $findings, fn( $f ) => $f['severity'] === 'medium' ) ),
			'low_count'         => count( array_filter( $findings, fn( $f ) => $f['severity'] === 'low' ) ),
			'auto_fixable'      => count( array_filter( $findings, fn( $f ) => $f['auto_fixable'] ) ),
		);

		update_option(
			'wpshadow_last_family_results',
			array(
				'family'    => $family,
				'created'   => time(),
				'findings'  => $findings,
				'stats'     => $stats,
				'total'     => $stats['total_diagnostics'],
				'timed_out' => $timed_out,
			)
		);

		// Persist family findings so dashboard gauges reflect report-run diagnostics.
		$cached_findings = function_exists( 'wpshadow_get_cached_findings' )
			? \wpshadow_get_cached_findings()
			: get_option( 'wpshadow_site_findings', array() );
		if ( ! is_array( $cached_findings ) ) {
			$cached_findings = array();
		}

		$family_ids = array_map( 'strval', array_keys( $family_diagnostics ) );

		$other_findings = array_values(
			array_filter(
				$cached_findings,
				function ( $finding ) use ( $family, $family_ids ) {
					if ( ! is_array( $finding ) ) {
						return false;
					}

					$finding_family = isset( $finding['family'] ) ? (string) $finding['family'] : '';
					if ( $finding_family === $family ) {
						return false;
					}

					$finding_id = isset( $finding['id'] ) ? (string) $finding['id'] : '';
					if ( '' !== $finding_id && in_array( $finding_id, $family_ids, true ) ) {
						return false;
					}

					return true;
				}
			)
		);

		$merged_findings = array_merge( $other_findings, $findings );

		if ( function_exists( 'wpshadow_store_gauge_snapshot' ) ) {
			\wpshadow_store_gauge_snapshot( $merged_findings );
		} else {
			update_option( 'wpshadow_site_findings', \wpshadow_index_findings_by_id( $merged_findings ) );
		}

		update_option( 'wpshadow_last_quick_scan', time() );
		update_option( 'wpshadow_last_quick_checks', time() );

		self::send_success(
			array(
				'family'            => $family,
				'findings'          => $findings,
				'stats'             => $stats,
				'total_diagnostics' => $stats['total_diagnostics'],
				'timed_out'         => $timed_out,
				'message'           => sprintf(
					/* translators: 1: number of issues, 2: family name */
					_n(
						'%1$d issue found in %2$s diagnostics.',
						'%1$d issues found in %2$s diagnostics.',
						count( $findings ),
						'wpshadow'
					),
					count( $findings ),
					ucfirst( $family )
				),
			)
		);
	}
}
