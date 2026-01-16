<?php
/**
 * Feature: Conditional Script Loading
 *
 * Load plugin scripts only on pages where they are needed.
 * Reduces page weight on pages that don't require specific functionality.
 *
 * @package WPShadow\CoreSupport
 * @since 1.2601.73001
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

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
				'name'               => __( 'Smart Resource Loading', 'plugin-wpshadow' ),
				'description'        => __( 'Loads plugin files only on pages that truly use them, so visitors do not download unnecessary scripts or styles across the entire site. Reduces bandwidth, speeds up the first view, and lowers JavaScript execution cost while keeping features available exactly where they are needed. Uses page context checks to decide when to load assets, leaving the admin experience unchanged.', 'plugin-wpshadow' ),
				'scope'              => 'core',
				'default_enabled'    => false,
				'version'            => '1.0.0',
				'widget_group'       => 'performance',
				'widget_label'       => __( 'Resource Optimization', 'plugin-wpshadow' ),
				'widget_description' => __( 'Optimize how resources are loaded and delivered', 'plugin-wpshadow' ),
			)
		);

		if ( method_exists( $this, 'register_sub_features' ) ) {
			$this->register_sub_features(
				array(
					'contact_forms'     => __( 'Conditional Contact Form Scripts', 'plugin-wpshadow' ),
					'woocommerce'       => __( 'Conditional WooCommerce Assets', 'plugin-wpshadow' ),
					'social_sharing'    => __( 'Conditional Social Sharing', 'plugin-wpshadow' ),
					'analytics'         => __( 'Conditional Analytics Scripts', 'plugin-wpshadow' ),
					'custom_rules'      => __( 'Enable Custom Loading Rules', 'plugin-wpshadow' ),
				)
			);
			if ( method_exists( $this, 'set_default_sub_features' ) ) {
				$this->set_default_sub_features(
					array(
						'contact_forms'     => true,
						'woocommerce'       => true,
						'social_sharing'    => true,
						'analytics'         => false,
						'custom_rules'      => false,
					)
				);
			}
		}

		$this->register_default_settings(
			array(
				'conditional_loading_rules' => array(),
			)
		);
		
		$this->log_activity( 'feature_initialized', 'Conditional Loading feature initialized', 'info' );
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

		add_action( 'wp_enqueue_scripts', array( $this, 'conditional_script_loading' ), 999 );
		
		// Add Site Health tests.
		add_filter( 'site_status_tests', array( $this, 'register_site_health_test' ) );
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

	/**
	 * Register Site Health test.
	 *
	 * @param array<string, mixed> $tests Site Health tests.
	 * @return array<string, mixed>
	 */
	public function register_site_health_test( array $tests ): array {
		$tests['direct']['WPSHADOW_conditional_loading'] = array(
			'label' => __( 'Conditional Loading', 'plugin-wpshadow' ),
			'test'  => array( $this, 'test_conditional_loading' ),
		);
		return $tests;
	}

	/**
	 * Site Health test for conditional loading.
	 *
	 * @return array<string, mixed>
	 */
	public function test_conditional_loading(): array {
		if ( ! $this->is_enabled() ) {
			return array(
				'label'       => __( 'Conditional Loading', 'plugin-wpshadow' ),
				'status'      => 'recommended',
				'badge'       => array(
					'label' => __( 'Performance', 'plugin-wpshadow' ),
					'color' => 'gray',
				),
				'description' => sprintf( '<p>%s</p>', __( 'Conditional Loading is not enabled. Enabling conditional loading can reduce page weight by loading scripts only where needed.', 'plugin-wpshadow' ) ),
				'actions'     => '',
				'test'        => 'WPSHADOW_conditional_loading',
			);
		}

		// Count enabled sub-features.
		$enabled_features = 0;
		if ( get_option( 'wpshadow_conditional-loading_contact_forms', true ) ) {
			++$enabled_features;
		}
		if ( get_option( 'wpshadow_conditional-loading_woocommerce', true ) ) {
			++$enabled_features;
		}
		if ( get_option( 'wpshadow_conditional-loading_social_sharing', true ) ) {
			++$enabled_features;
		}

		// Count custom rules.
		$rules = (array) $this->get_setting( 'conditional_loading_rules', array() );
		$rule_count = count( $rules );

		return array(
			'label'       => __( 'Conditional Loading', 'plugin-wpshadow' ),
			'status'      => 'good',
			'badge'       => array(
				'label' => __( 'Performance', 'plugin-wpshadow' ),
				'color' => 'blue',
			),
			'description' => sprintf(
				'<p>%s</p>',
				/* translators: 1: number of enabled features, 2: number of custom rules */
				sprintf(
					__( 'Conditional Loading is active with %1$d preset rules enabled and %2$d custom loading rules configured.', 'plugin-wpshadow' ),
					$enabled_features,
					$rule_count
				)
			),
			'actions'     => '',
			'test'        => 'WPSHADOW_conditional_loading',
		);
	}
}
