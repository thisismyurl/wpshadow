<?php
/**
 * Generate Customization Audit Handler
 *
 * Runs customization-related diagnostics and generates an audit report.
 *
 * @package    WPShadow
 * @subpackage Admin\Ajax
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;
use WPShadow\Diagnostics\Diagnostic_Registry;
use WPShadow\Core\Activity_Logger;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Generate Customization Audit Handler Class
 *
 * Generates comprehensive audit reports of site customizations.
 *
 * @since 0.6093.1200
 */
class Generate_Customization_Audit_Handler extends AJAX_Handler_Base {

	/**
	 * Register AJAX handler.
	 *
	 * @since 0.6093.1200
	 * @return void
	 */
	public static function register() {
		add_action( 'wp_ajax_wpshadow_generate_customization_audit', array( __CLASS__, 'handle' ) );
	}

	/**
	 * Handle the AJAX request.
	 *
	 * @since 0.6093.1200
	 * @return void Dies after sending JSON response.
	 */
	public static function handle() {
		// Verify nonce and capability.
		self::verify_request( 'wpshadow_generate_audit', 'manage_options' );

		// Generate the audit report.
		$report = self::generate_audit_report();

		if ( $report ) {
			// Save report to history.
			self::save_report( $report );

			// Log activity.
			Activity_Logger::log(
				'customization_audit_generated',
				array(
					'risk_level'     => $report['overall_risk'],
					'total_issues'   => $report['total_issues'],
					'custom_themes'  => $report['custom_themes'],
					'custom_plugins' => $report['custom_plugins'],
				),
				'audit'
			);

			self::send_success(
				array(
					'message' => __( 'Audit report generated successfully', 'wpshadow' ),
					'report'  => $report,
				)
			);
		} else {
			self::send_error( __( 'Failed to generate audit report', 'wpshadow' ) );
		}
	}

	/**
	 * Generate audit report by running relevant diagnostics.
	 *
	 * @since 0.6093.1200
	 * @return array|false Audit report data or false on failure.
	 */
	private static function generate_audit_report() {
		// Get all registered diagnostics.
		$all_diagnostics = Diagnostic_Registry::get_all();

		// Define customization-related diagnostic families and specific checks.
		$customization_families = array(
			'design',      // Theme-related checks.
			'settings',    // Plugin and configuration checks.
			'code-quality', // Code quality checks.
		);

		$findings         = array();
		$custom_themes    = 0;
		$custom_plugins   = 0;
		$db_modifications = 0;
		$total_issues     = 0;

		// Run relevant diagnostics.
		foreach ( $all_diagnostics as $slug => $class ) {
			if ( ! class_exists( $class ) ) {
				continue;
			}

			// Check if diagnostic is relevant to customization audit.
			$family = $class::get_family();
			if ( ! in_array( $family, $customization_families, true ) ) {
				continue;
			}

			// Execute the diagnostic.
			try {
				$finding = $class::execute();
				if ( $finding ) {
					$findings[] = $finding;
					++$total_issues;

					// Categorize the issue.
					if ( false !== stripos( $slug, 'theme' ) ) {
						++$custom_themes;
					} elseif ( false !== stripos( $slug, 'plugin' ) ) {
						++$custom_plugins;
					} elseif ( false !== stripos( $slug, 'database' ) || false !== stripos( $slug, 'table' ) ) {
						++$db_modifications;
					}
				}
			} catch ( \Exception $e ) {
				error_log( 'WPShadow: Error running diagnostic ' . $slug . ': ' . $e->getMessage() );
				continue;
			}
		}

		// Add theme analysis.
		$theme_data     = self::analyze_themes();
		$custom_themes += $theme_data['custom_count'];

		// Add plugin analysis.
		$plugin_data     = self::analyze_plugins();
		$custom_plugins += $plugin_data['custom_count'];

		// Add database analysis.
		$db_data           = self::analyze_database();
		$db_modifications += $db_data['custom_tables'];

		// Custom content type analysis removed.

		// Calculate overall risk level.
		$overall_risk = self::calculate_risk_level(
			$total_issues,
			$custom_themes,
			$custom_plugins,
			$db_modifications
		);

		$report = array(
			'id'                => uniqid( 'audit_', true ),
			'timestamp'         => time(),
			'overall_risk'      => $overall_risk,
			'total_issues'      => $total_issues,
			'custom_themes'     => $custom_themes,
			'custom_plugins'    => $custom_plugins,
			'db_modifications'  => $db_modifications,
			'custom_post_types' => 0,
			'custom_taxonomies' => 0,
			'findings'          => $findings,
			'theme_details'     => $theme_data['details'],
			'plugin_details'    => $plugin_data['details'],
			'database_details'  => $db_data['details'],
		);

		return $report;
	}

	/**
	 * Analyze active themes for customizations.
	 *
	 * @since 0.6093.1200
	 * @return array Theme analysis data.
	 */
	private static function analyze_themes() {
		$theme        = wp_get_theme();
		$parent_theme = $theme->parent();

		$custom_count = 0;
		$details      = array();

		// Check for child theme.
		if ( $parent_theme ) {
			++$custom_count;
			$details[] = array(
				'name'   => $theme->get( 'Name' ),
				'type'   => 'child_theme',
				'parent' => $parent_theme->get( 'Name' ),
				'risk'   => 'low',
			);
		}

		// Check for custom theme (not from WordPress.org).
		$theme_uri = $theme->get( 'ThemeURI' );
		if ( empty( $theme_uri ) || false === stripos( $theme_uri, 'wordpress.org' ) ) {
			++$custom_count;
			$details[] = array(
				'name' => $theme->get( 'Name' ),
				'type' => 'custom_theme',
				'risk' => 'medium',
			);
		}

		// Check for theme modifications via functions.php.
		$functions_file = get_stylesheet_directory() . '/functions.php';
		if ( file_exists( $functions_file ) ) {
			$content = file_get_contents( $functions_file );
			$lines   = count( explode( "\n", $content ) );

			if ( $lines > 50 ) { // Significant customizations.
				$details[] = array(
					'name'  => $theme->get( 'Name' ),
					'type'  => 'functions_php_customization',
					'lines' => $lines,
					'risk'  => $lines > 200 ? 'high' : 'medium',
				);
			}
		}

		return array(
			'custom_count' => $custom_count,
			'details'      => $details,
		);
	}

	/**
	 * Analyze plugins for customizations.
	 *
	 * @since 0.6093.1200
	 * @return array Plugin analysis data.
	 */
	private static function analyze_plugins() {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$all_plugins  = get_plugins();
		$custom_count = 0;
		$details      = array();

		foreach ( $all_plugins as $plugin_file => $plugin_data ) {
			// Check if plugin is custom (not from WordPress.org).
			$plugin_uri = $plugin_data['PluginURI'] ?? '';
			$author_uri = $plugin_data['AuthorURI'] ?? '';

			$is_custom = empty( $plugin_uri ) ||
				( false === stripos( $plugin_uri, 'wordpress.org' ) &&
					false === stripos( $author_uri, 'wordpress.org' ) );

			if ( $is_custom ) {
				++$custom_count;
				$details[] = array(
					'name'   => $plugin_data['Name'],
					'file'   => $plugin_file,
					'type'   => 'custom_plugin',
					'active' => is_plugin_active( $plugin_file ),
					'risk'   => 'medium',
				);
			}
		}

		// Check for mu-plugins.
		$mu_plugins = get_mu_plugins();
		foreach ( $mu_plugins as $mu_plugin_file => $mu_plugin_data ) {
			++$custom_count;
			$details[] = array(
				'name'   => $mu_plugin_data['Name'],
				'file'   => $mu_plugin_file,
				'type'   => 'mu_plugin',
				'active' => true,
				'risk'   => 'high', // MU plugins always run.
			);
		}

		return array(
			'custom_count' => $custom_count,
			'details'      => $details,
		);
	}

	/**
	 * Analyze database for custom tables and modifications.
	 *
	 * @since 0.6093.1200
	 * @return array Database analysis data.
	 */
	private static function analyze_database() {
		return array(
			'custom_tables' => 0,
			'details'       => array(),
		);
	}

	/**
	 * Calculate overall risk level based on findings.
	 *
	 * @since 0.6093.1200
	 * @param  int $total_issues Total issues found.
	 * @param  int $custom_themes Custom themes count.
	 * @param  int $custom_plugins Custom plugins count.
	 * @param  int $db_modifications Database modifications count.
	 * @return string Risk level: 'low', 'medium', 'high'.
	 */
	private static function calculate_risk_level( $total_issues, $custom_themes, $custom_plugins, $db_modifications ) {
		$risk_score = 0;

		// Weight by category.
		$risk_score += $total_issues * 2;
		$risk_score += $custom_themes * 1;
		$risk_score += $custom_plugins * 3;
		$risk_score += $db_modifications * 5;

		if ( $risk_score >= 20 ) {
			return 'high';
		} elseif ( $risk_score >= 10 ) {
			return 'medium';
		} else {
			return 'low';
		}
	}

	/**
	 * Save report to history.
	 *
	 * @since 0.6093.1200
	 * @param  array $report Report data.
	 * @return bool True on success, false on failure.
	 */
	private static function save_report( $report ) {
		$reports = get_option( 'wpshadow_customization_audit_reports', array() );

		// Keep only last 10 reports.
		if ( count( $reports ) >= 10 ) {
			array_shift( $reports );
		}

		$reports[] = $report;

		return update_option( 'wpshadow_customization_audit_reports', $reports );
	}
}
