<?php
/**
 * AJAX Handler: Run Diagnostics by Family
 *
 * Executes all diagnostics in a specific family (security, performance, SEO).
 *
 * @package    WPShadow
 * @subpackage Admin
 * @since      1.26030.1200
 */

declare(strict_types=1);

namespace WPShadow\Admin;

use WPShadow\Core\AJAX_Handler_Base;
use WPShadow\Diagnostics\Diagnostic_Registry;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Run Family Diagnostics Handler
 *
 * @since 1.26030.1200
 */
class AJAX_Run_Family_Diagnostics extends AJAX_Handler_Base {

	/**
	 * Register the AJAX handler.
	 *
	 * @since 1.26030.1200
	 * @return void
	 */
	public static function register() {
		add_action( 'wp_ajax_wpshadow_run_family_diagnostics', array( __CLASS__, 'handle' ) );
	}

	/**
	 * Handle the AJAX request.
	 *
	 * @since  1.26030.1200
	 * @return void Dies after sending JSON response.
	 */
	public static function handle() {
		// Verify nonce and capability
		self::verify_request( 'wpshadow_security_scan', 'manage_options' );

		// Get and sanitize parameters
		$family = self::get_post_param( 'family', 'text', '', true );

		// Validate family
		$valid_families = array( 'security', 'performance', 'seo', 'accessibility', 'protection' );
		if ( ! in_array( $family, $valid_families, true ) ) {
			self::send_error( __( 'Invalid diagnostic family specified.', 'wpshadow' ) );
		}

		// Get all diagnostics
		$all_diagnostics = Diagnostic_Registry::get_all();
		$family_diagnostics = array();
		$findings = array();

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

			// Run the diagnostic
			try {
				if ( method_exists( $class, 'execute' ) ) {
					$result = $class::execute();
				} elseif ( method_exists( $class, 'check' ) ) {
					$result = $class::check();
				} else {
					continue;
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
		}

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

		self::send_success(
			array(
				'family'            => $family,
				'findings'          => $findings,
				'stats'             => $stats,
				'total_diagnostics' => $stats['total_diagnostics'],
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
