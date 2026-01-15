<?php
/**
 * Feature: Conditional Script Loading
 *
 * Load plugin scripts only on pages where they are needed.
 * Reduces page weight on pages that don't require specific functionality.
 *
 * @package WPS\CoreSupport
 * @since 1.2601.73001
 */

declare(strict_types=1);

namespace WPS\CoreSupport;

/**
 * WPSHADOW_Feature_Conditional_Loading
 *
 * Conditionally load scripts and styles based on page context.
 */
final class WPSHADOW_Feature_Conditional_Loading extends WPSHADOW_Abstract_Feature {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'conditional-loading',
				'name'               => __( 'Conditional Script Loading', 'plugin-wpshadow' ),
				'description'        => __( 'Load plugin scripts only on pages where they are needed', 'plugin-wpshadow' ),
				'scope'              => 'core',
				'default_enabled'    => false,
				'version'            => '1.0.0',
				'widget_group'       => 'performance',
				'widget_label'       => __( 'Resource Optimization', 'plugin-wpshadow' ),
				'widget_description' => __( 'Optimize how resources are loaded and delivered', 'plugin-wpshadow' ),
			)
		);

		$this->register_default_settings(
			array(
				'conditional_loading_rules' => array(),
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

		add_action( 'wp_enqueue_scripts', array( $this, 'conditional_script_loading' ), 999 );
	}

	/**
	 * Apply conditional loading rules to dequeue scripts on non-relevant pages.
	 *
	 * @return void
	 */
	public function conditional_script_loading(): void {
		$rules = (array) $this->get_setting( 'conditional_loading_rules', array() );

		// Allow filtering of rules.
		$rules = apply_filters( 'wpshadow_conditional_loading_rules', $rules );

		foreach ( $rules as $rule ) {
			if ( ! is_array( $rule ) ) {
				continue;
			}

			// Rule structure: ['plugin' => 'contact-form-7', 'pages' => [1, 2, 3], 'handles' => ['contact-form-7']].
			$pages   = $rule['pages'] ?? array();
			$handles = $rule['handles'] ?? array();

			// Skip if no pages or handles defined.
			if ( empty( $pages ) || empty( $handles ) ) {
				continue;
			}

			// Check if we should load on current page.
			if ( ! $this->should_load_on_current_page( $pages ) ) {
				// Dequeue scripts and styles.
				foreach ( $handles as $handle ) {
					wp_dequeue_script( $handle );
					wp_deregister_script( $handle );
					wp_dequeue_style( $handle );
					wp_deregister_style( $handle );
				}
			}
		}
	}

	/**
	 * Check if scripts should be loaded on the current page.
	 *
	 * @param array<int|string> $allowed_pages List of allowed page IDs, slugs, or patterns.
	 * @return bool True if scripts should load on this page.
	 */
	private function should_load_on_current_page( array $allowed_pages ): bool {
		global $post;

		// Empty whitelist means load nowhere.
		if ( empty( $allowed_pages ) ) {
			return false;
		}

		// Special patterns.
		if ( in_array( '*', $allowed_pages, true ) ) {
			return true; // Load everywhere.
		}

		if ( in_array( 'shop', $allowed_pages, true ) && function_exists( 'is_woocommerce' ) && is_woocommerce() ) {
			return true;
		}

		if ( in_array( 'home', $allowed_pages, true ) && is_front_page() ) {
			return true;
		}

		if ( in_array( 'archive', $allowed_pages, true ) && is_archive() ) {
			return true;
		}

		if ( in_array( 'single', $allowed_pages, true ) && is_single() ) {
			return true;
		}

		if ( in_array( 'page', $allowed_pages, true ) && is_page() ) {
			return true;
		}

		// Check specific post/page IDs and slugs.
		if ( $post ) {
			if ( in_array( $post->ID, $allowed_pages, true ) ) {
				return true;
			}

			if ( in_array( $post->post_name, $allowed_pages, true ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Analyze enqueued scripts to suggest optimization opportunities.
	 *
	 * @return array<array<string, mixed>> List of optimization suggestions.
	 */
	public function analyze_optimization_opportunities(): array {
		global $wp_scripts, $wp_styles;

		$suggestions = array();

		if ( ! is_admin() && $wp_scripts && is_array( $wp_scripts->registered ) ) {
			foreach ( $wp_scripts->registered as $handle => $script ) {
				// Detect known plugin handles.
				$plugin = $this->detect_plugin_from_handle( $handle );

				if ( $plugin ) {
					$suggestions[] = array(
						'type'           => 'script',
						'handle'         => $handle,
						'plugin'         => $plugin,
						'recommendation' => sprintf(
							/* translators: 1: script handle, 2: plugin name */
							__( 'Consider loading %1$s (%2$s) only on relevant pages', 'plugin-wpshadow' ),
							$handle,
							$plugin
						),
					);
				}
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
}
