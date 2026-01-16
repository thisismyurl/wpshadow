<?php
/**
 * Feature: Script Optimization Analyzer
 *
 * Analyze enqueued scripts and provide optimization recommendations.
 * Helps identify opportunities for conditional loading and performance improvements.
 *
 * @package WPShadow\CoreSupport
 * @since 1.2601.73001
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

/**
 * WPSHADOW_Feature_Script_Optimizer
 *
 * Analyze and suggest script optimizations.
 */
final class WPSHADOW_Feature_Script_Optimizer extends WPSHADOW_Abstract_Feature {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'script-optimizer',
				'name'               => __( 'Script Optimization Analyzer', 'plugin-wpshadow' ),
				'description'        => __( 'Analyzes scripts loaded on your pages, highlights heavy or duplicated assets, and offers practical suggestions like deferring, combining, or limiting them to specific pages. Helps reduce render-blocking files, cut load time, and lower conflict risk by showing which plugins or themes add each script. Provides a simple action list so you can improve speed without guesswork.', 'plugin-wpshadow' ),
				'scope'              => 'core',
				'default_enabled'    => true,
				'version'            => '1.0.0',
			'widget_group'       => 'reporting',
				'license_level'      => 2,
				'minimum_capability' => 'manage_options',
				'icon'               => 'dashicons-chart-bar',
				'category'           => 'diagnostics',
				'priority'           => 40,
			)
		);
		
		if ( method_exists( $this, 'register_sub_features' ) ) {
			$this->register_sub_features(
				array(
					'analyze_scripts'    => __( 'Analyze Script Loading', 'plugin-wpshadow' ),
					'detect_conflicts'   => __( 'Detect Script Conflicts', 'plugin-wpshadow' ),
					'identify_heavy'     => __( 'Identify Heavy Scripts', 'plugin-wpshadow' ),
					'suggest_defer'      => __( 'Suggest Defer Candidates', 'plugin-wpshadow' ),
					'track_changes'      => __( 'Track Script Changes', 'plugin-wpshadow' ),
				)
			);
			if ( method_exists( $this, 'set_default_sub_features' ) ) {
				$this->set_default_sub_features(
					array(
						'analyze_scripts'    => true,
						'detect_conflicts'   => true,
						'identify_heavy'     => true,
						'suggest_defer'      => true,
						'track_changes'      => true,
					)
				);
			}
		}
		
		$this->log_activity( 'feature_initialized', 'Script Optimizer feature initialized', 'info' );
	}

	/**
	 * Indicate this feature has a details page.
	 *
	 * @return bool
	 */
	public function has_details_page(): bool {
		return true;
	}

	/**
	 * Register hooks when feature is enabled.
	 *
	 * @return void
	 */
	public function register(): void {
		if ( ! $this->is_enabled() ) {
			return;
		}

		// Hook to collect script data.
		add_action( 'wp_enqueue_scripts', array( $this, 'analyze_scripts' ), 9999 );
		
		// Add Site Health tests.
		add_filter( 'site_status_tests', array( $this, 'register_site_health_test' ) );
	}

	/**
	 * Analyze enqueued scripts and store data for suggestions.
	 *
	 * @return void
	 */
	public function analyze_scripts(): void {
		// Only analyze on frontend for logged-in admins.
		if ( is_admin() || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		global $wp_scripts, $wp_styles;

		$script_data = array();

		if ( $wp_scripts && is_array( $wp_scripts->queue ) ) {
			foreach ( $wp_scripts->queue as $handle ) {
				if ( isset( $wp_scripts->registered[ $handle ] ) ) {
					$script                 = $wp_scripts->registered[ $handle ];
					$script_data[ $handle ] = array(
						'type'   => 'script',
						'src'    => $script->src ?? null,
						'deps'   => $script->deps ?? array(),
						'ver'    => $script->ver ?? '',
						'plugin' => $this->detect_plugin_from_handle( $handle ),
					);
				}
			}
		}

		// Store for later analysis.
		$stored_data = get_transient( 'wpshadow_script_analysis_data' );
		if ( ! is_array( $stored_data ) ) {
			$stored_data = array();
		}

		$page_id                 = get_queried_object_id();
		$stored_data[ $page_id ] = $script_data;

		// Keep data for 7 days.
		set_transient( 'wpshadow_script_analysis_data', $stored_data, 7 * DAY_IN_SECONDS );
	}

	/**
	 * Get optimization suggestions based on analyzed data.
	 *
	 * @return array<array<string, mixed>> List of suggestions.
	 */
	public function get_optimization_suggestions(): array {
		$suggestions = array();
		$stored_data = get_transient( 'wpshadow_script_analysis_data' );

		if ( ! is_array( $stored_data ) || empty( $stored_data ) ) {
			return $suggestions;
		}

		// Analyze script usage across pages.
		$script_usage = array();

		foreach ( $stored_data as $page_id => $scripts ) {
			foreach ( $scripts as $handle => $data ) {
				if ( ! isset( $script_usage[ $handle ] ) ) {
					$script_usage[ $handle ] = array(
						'pages'  => array(),
						'plugin' => $data['plugin'] ?? null,
						'data'   => $data,
					);
				}

				$script_usage[ $handle ]['pages'][] = $page_id;
			}
		}

		// Generate suggestions for scripts that appear on limited pages.
		$total_pages = count( $stored_data );

		foreach ( $script_usage as $handle => $usage ) {
			$pages_count = count( $usage['pages'] );

			// Skip core WordPress scripts.
			if ( in_array( $handle, array( 'jquery', 'jquery-core', 'jquery-migrate', 'wp-embed' ), true ) ) {
				continue;
			}

			// If script appears on less than 50% of pages, suggest conditional loading.
			if ( $pages_count < $total_pages * 0.5 && $pages_count > 0 ) {
				$suggestions[] = array(
					'type'           => 'conditional_loading',
					'handle'         => $handle,
					'plugin'         => $usage['plugin'] ?? __( 'Unknown', 'plugin-wpshadow' ),
					'pages_loaded'   => $pages_count,
					'total_pages'    => $total_pages,
					'recommendation' => sprintf(
						/* translators: 1: script handle, 2: number of pages, 3: total pages */
						__( 'Script "%1$s" loads on %2$d of %3$d analyzed pages. Consider conditional loading to reduce page weight.', 'plugin-wpshadow' ),
						$handle,
						$pages_count,
						$total_pages
					),
				);
			}
		}

		return $suggestions;
	}

	/**
	 * Detect plugin name from script handle.
	 *
	 * @param string $handle Script handle.
	 * @return string|null Plugin name or null if not detected.
	 */
	private function detect_plugin_from_handle( string $handle ): ?string {
		return WPSHADOW_Script_Utils::detect_plugin_from_handle( $handle );
	}

	/**
	 * Get statistics about current optimizations.
	 *
	 * @return array<string, mixed> Optimization statistics.
	 */
	public function get_optimization_stats(): array {
		global $wp_scripts;

		$stats = array(
			'total_scripts'     => 0,
			'deferred_scripts'  => 0,
			'external_scripts'  => 0,
			'estimated_savings' => 0,
		);

		if ( ! $wp_scripts || ! is_array( $wp_scripts->registered ) ) {
			return $stats;
		}

		$stats['total_scripts'] = count( $wp_scripts->registered );

		// Count deferred scripts.
		$defer_handles             = (array) $this->get_setting( 'wpshadow_defer_script_handles', array( ) );
		$stats['deferred_scripts'] = count( $defer_handles );

		// Count external scripts.
		foreach ( $wp_scripts->registered as $handle => $script ) {
			if ( isset( $script->src ) && ( strpos( $script->src, 'http' ) === 0 && strpos( $script->src, site_url() ) === false ) ) {
				++$stats['external_scripts'];
			}
		}

		// Estimate savings from conditional loading.
		$rules = (array) $this->get_setting( 'wpshadow_conditional_loading_rules', array( ) );
		foreach ( $rules as $rule ) {
			if ( isset( $rule['handles'] ) && is_array( $rule['handles'] ) ) {
				$stats['estimated_savings'] += count( $rule['handles'] ) * 15; // Estimate 15KB per script.
			}
		}

		return $stats;
	}

	/**
	 * Register Site Health test.
	 *
	 * @param array<string, mixed> $tests Site Health tests.
	 * @return array<string, mixed>
	 */
	public function register_site_health_test( array $tests ): array {
		$tests['direct']['WPSHADOW_script_optimizer'] = array(
			'label' => __( 'Script Optimization', 'plugin-wpshadow' ),
			'test'  => array( $this, 'test_script_optimizer' ),
		);
		return $tests;
	}

	/**
	 * Site Health test for script optimizer.
	 *
	 * @return array<string, mixed>
	 */
	public function test_script_optimizer(): array {
		if ( ! $this->is_enabled() ) {
			return array(
				'label'       => __( 'Script Optimization', 'plugin-wpshadow' ),
				'status'      => 'recommended',
				'badge'       => array(
					'label' => __( 'Performance', 'plugin-wpshadow' ),
					'color' => 'gray',
				),
				'description' => sprintf( '<p>%s</p>', __( 'Script Optimization Analyzer is not enabled. Enabling script analysis can help identify optimization opportunities.', 'plugin-wpshadow' ) ),
				'actions'     => '',
				'test'        => 'WPSHADOW_script_optimizer',
			);
		}

		// Get optimization statistics.
		$stats = $this->get_optimization_stats();
		$total_scripts = $stats['total_scripts'] ?? 0;
		$external_scripts = $stats['external_scripts'] ?? 0;

		// Count enabled sub-features.
		$enabled_features = 0;
		if ( get_option( 'wpshadow_script-optimizer_analyze_scripts', true ) ) {
			++$enabled_features;
		}
		if ( get_option( 'wpshadow_script-optimizer_detect_conflicts', true ) ) {
			++$enabled_features;
		}
		if ( get_option( 'wpshadow_script-optimizer_identify_heavy', true ) ) {
			++$enabled_features;
		}

		return array(
			'label'       => __( 'Script Optimization', 'plugin-wpshadow' ),
			'status'      => 'good',
			'badge'       => array(
				'label' => __( 'Performance', 'plugin-wpshadow' ),
				'color' => 'blue',
			),
			'description' => sprintf(
				'<p>%s</p>',
				/* translators: 1: total scripts, 2: external scripts, 3: enabled features */
				sprintf(
					__( 'Script Optimization is active. Analyzing %1$d total scripts (%2$d external) with %3$d analysis features enabled.', 'plugin-wpshadow' ),
					$total_scripts,
					$external_scripts,
					$enabled_features
				)
			),
			'actions'     => '',
			'test'        => 'WPSHADOW_script_optimizer',
		);
	}
}
