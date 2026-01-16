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
			'widget_label'       => __( 'Reporting', 'plugin-wpshadow' ),
				'widget_description' => __( 'Optimize how resources are loaded and delivered', 'plugin-wpshadow' ),
			)
		);
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
}
