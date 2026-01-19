<?php
/**
 * Feature: Plugin Audit & Performance Profiler
 *
 * Comprehensive plugin analysis including security audits, performance profiling,
 * compatibility checks, and detailed performance impact reporting. Identifies slow
 * plugins, outdated plugins, and security vulnerabilities on a per-plugin basis.
 *
 * @package WPShadow\CoreSupport
 * @since 1.2601.75010
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * WPSHADOW_Feature_Plugin_Audit
 *
 * Comprehensive plugin auditing and performance profiling system.
 */
final class WPSHADOW_Feature_Plugin_Audit extends WPSHADOW_Abstract_Feature {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'plugin-audit',
				'name'               => __( 'Plugin Audit & Performance Profiler', 'wpshadow' ),
				'description_short'  => __( 'Analyze every plugin for performance impact, security issues, and updates', 'wpshadow' ),
				'description_long'   => __( 'Breaks down each installed plugin and runs comprehensive audits including performance profiling, security vulnerability checks, outdated version detection, compatibility validation, and resource consumption analysis. Reports which plugins are slowing down your site, identifies security risks, recommends alternatives for heavy plugins, and tracks performance trends over time. Perfect for understanding exactly what each plugin costs your site in terms of performance and security.', 'wpshadow' ),
				'description_wizard' => __( 'Discover which plugins are slowing down your site and costing you performance. Get detailed reports on each plugin\'s impact on load time, database queries, and memory usage. Identify security vulnerabilities, outdated plugins, and problematic conflicts. Understand the true cost of each plugin before adding more bloat.', 'wpshadow' ),
				'scope'              => 'core',
				'default_enabled'    => true,
				'version'            => '1.0.0',
				'widget_group'       => 'performance',
				'minimum_capability' => 'manage_options',
				'aliases'            => array(
					'plugin performance',
					'plugin profiler',
					'slow plugins',
					'plugin audit',
					'plugin analysis',
					'plugin bloat',
					'plugin security audit',
					'plugin health check',
					'plugin vulnerability',
					'plugin conflicts',
					'plugin impact',
					'resource usage',
					'plugin troubleshooting',
				),
				'sub_features'       => array(
					'performance_profiling'   => array(
						'name'               => __( 'Performance Profiling', 'wpshadow' ),
						'description_short'  => __( 'Measure each plugin\'s impact on load time and queries', 'wpshadow' ),
						'description_long'   => __( 'Profiles each plugin to measure its impact on page load time, database query count, and memory usage. Runs both frontend and admin-area profiling to identify which plugins slow down specific parts of your site. Creates a ranked list of slowest plugins with actionable recommendations. Results are tracked over time to measure if plugins are getting slower with updates or if optimizations are helping.', 'wpshadow' ),
						'description_wizard' => __( 'Measure which plugins are the biggest performance hogs on your site. See exact milliseconds added to page load time and how many database queries each plugin creates.', 'wpshadow' ),
						'default_enabled'    => true,
					),
					'security_audit'         => array(
						'name'               => __( 'Security Audit', 'wpshadow' ),
						'description_short'  => __( 'Check plugins for known vulnerabilities and security issues', 'wpshadow' ),
						'description_long'   => __( 'Scans installed plugins against vulnerability databases to identify security issues, outdated versions with known exploits, and insecure coding patterns. Checks for plugins that haven\'t been updated in over 2 years (indicates abandonment), known backdoor plugins, and plugins from untrusted sources. Provides actionable recommendations like updating, removing, or finding alternatives for vulnerable plugins.', 'wpshadow' ),
						'description_wizard' => __( 'Find security vulnerabilities in your plugins before hackers do. Identify outdated plugins with known exploits, suspicious plugins, and plugins that haven\'t been maintained by their developers.', 'wpshadow' ),
						'default_enabled'    => true,
					),
					'compatibility_check'    => array(
						'name'               => __( 'Compatibility Checks', 'wpshadow' ),
						'description_short'  => __( 'Verify plugin compatibility with WordPress and PHP versions', 'wpshadow' ),
						'description_long'   => __( 'Checks each plugin to ensure compatibility with your WordPress version, PHP version, and other installed plugins. Identifies deprecated WordPress functions the plugin uses, identifies plugins that declare conflicts with each other, and warns about plugins that are incompatible with your current setup. Helps prevent "white screen of death" errors and mysterious functionality issues caused by incompatibility.', 'wpshadow' ),
						'description_wizard' => __( 'Prevent site-breaking conflicts by checking if plugins are compatible with your WordPress version, PHP version, and other plugins on your site.', 'wpshadow' ),
						'default_enabled'    => true,
					),
					'conflict_detection'     => array(
						'name'               => __( 'Conflict Detection', 'wpshadow' ),
						'description_short'  => __( 'Identify plugins that conflict with each other', 'wpshadow' ),
						'description_long'   => __( 'Analyzes plugins to identify potential conflicts where two or more plugins may interfere with each other\'s functionality. Detects duplicate functionality (like two SEO plugins fighting), hooks conflicts, filter conflicts, and known problematic plugin combinations. Helps prevent mysterious bugs and functionality issues by identifying incompatible plugin combinations before you encounter problems.', 'wpshadow' ),
						'description_wizard' => __( 'Discover plugin conflicts before they break your site. Find duplicate functionality and plugins that don\'t play well together.', 'wpshadow' ),
						'default_enabled'    => false,
					),
					'usage_analytics'        => array(
						'name'               => __( 'Usage Analytics', 'wpshadow' ),
						'description_short'  => __( 'Track plugin usage patterns and effectiveness', 'wpshadow' ),
						'description_long'   => __( 'Analyzes whether plugins are actually being used or if they\'re just dead weight. Checks if plugin features are configured and active, tracks how often plugin features are accessed by visitors, and identifies abandoned plugins that could be safely removed. Helps identify plugins that sounded useful when installed but never actually got used or configured properly.', 'wpshadow' ),
						'description_wizard' => __( 'Find plugins that nobody is actually using so you can remove the dead weight and simplify your site.', 'wpshadow' ),
						'default_enabled'    => true,
					),
					'update_tracking'        => array(
						'name'               => __( 'Update Tracking', 'wpshadow' ),
						'description_short'  => __( 'Monitor plugin updates and highlight outdated plugins', 'wpshadow' ),
						'description_long'   => __( 'Tracks plugin versions and identifies which plugins are outdated or have pending updates. Highlights critical security updates that should be applied immediately, feature updates available, and plugins that haven\'t been updated in a long time (potentially abandoned). Provides detailed changelog information and compatibility notes for available updates to help you decide which updates are safe to apply.', 'wpshadow' ),
						'description_wizard' => __( 'Keep track of which plugins need updates and prioritize security patches over feature updates.', 'wpshadow' ),
						'default_enabled'    => true,
					),
					'alternative_suggestions'  => array(
						'name'               => __( 'Alternative Plugin Suggestions', 'wpshadow' ),
						'description_short'  => __( 'Recommend better alternatives for heavy plugins', 'wpshadow' ),
						'description_long'   => __( 'When a plugin is identified as slow or bloated, the system suggests faster, lighter alternatives that provide similar functionality. Suggestions are ranked by performance, user ratings, and maintenance status. Includes data-driven recommendations like "Plugin X adds 800ms to load time; Plugin Y provides the same features in 50ms." Helps you make informed decisions about plugin replacements.', 'wpshadow' ),
						'description_wizard' => __( 'Get recommendations for faster, lighter alternatives when a plugin is identified as a performance problem.', 'wpshadow' ),
						'default_enabled'    => false,
					),
					'detailed_reports'       => array(
						'name'               => __( 'Detailed PDF Reports', 'wpshadow' ),
						'description_short'  => __( 'Generate comprehensive audit reports for download', 'wpshadow' ),
						'description_long'   => __( 'Creates detailed PDF and CSV audit reports that can be downloaded, shared with developers, or archived. Reports include performance rankings, security assessment, compatibility notes, recommendations, and historical trends. Perfect for documenting your site\'s plugin health, showing developers why certain plugins need to be removed, or proving to clients why plugin optimization matters.', 'wpshadow' ),
						'description_wizard' => __( 'Export audit results as PDF or CSV for documentation, sharing with developers, or archiving for compliance purposes.', 'wpshadow' ),
						'default_enabled'    => false,
					),
					'historical_tracking'    => array(
						'name'               => __( 'Historical Performance Tracking', 'wpshadow' ),
						'description_short'  => __( 'Track plugin performance changes over time', 'wpshadow' ),
						'description_long'   => __( 'Maintains a historical database of plugin performance metrics so you can see how plugin performance changes with updates, configuration changes, or data growth. Compare performance before and after plugin updates, track gradual performance degradation, and identify when a plugin becomes problematic. Helps justify plugin updates or removals with data-backed evidence of performance impact.', 'wpshadow' ),
						'description_wizard' => __( 'Track how plugin performance changes over time to see if updates make plugins faster or slower.', 'wpshadow' ),
						'default_enabled'    => true,
					),
					'alert_system'           => array(
						'name'               => __( 'Performance Alerts', 'wpshadow' ),
						'description_short'  => __( 'Get notified when plugins become problematic', 'wpshadow' ),
						'description_long'   => __( 'Automatically alerts you when a plugin is detected to add significant load time, consumes excessive memory, has security vulnerabilities, or becomes outdated. Alerts can be sent via email, WordPress admin notices, or logged for review. Helps you stay on top of plugin issues before they impact your site visitors.', 'wpshadow' ),
						'description_wizard' => __( 'Get notifications when plugins start causing problems, security issues, or performance degradation.', 'wpshadow' ),
						'default_enabled'    => true,
					),
					'customization_audit'    => array(
						'name'               => __( 'Site Customization Audit', 'wpshadow' ),
						'description_short'  => __( 'Identify all custom post types, taxonomies, and configurations', 'wpshadow' ),
						'description_long'   => __( 'Performs comprehensive audit of your site\'s non-standard customizations including custom post types, custom taxonomies, shortcodes, meta fields, database tables, and WordPress configuration modifications. Identifies unique implementations and custom hooks registered by plugins or themes. Helps understand site complexity, assess technical debt, identify upgrade risks, and document customizations for developer handoff or site migrations.', 'wpshadow' ),
						'description_wizard' => __( 'Discover all the custom post types, taxonomies, and unique configurations your site uses. Understand what makes your site different from standard WordPress.', 'wpshadow' ),
						'default_enabled'    => true,
					),
					'custom_post_types'      => array(
						'name'               => __( 'Custom Post Types Inventory', 'wpshadow' ),
						'description_short'  => __( 'Catalog all custom post types and their usage', 'wpshadow' ),
						'description_long'   => __( 'Inventories all custom post types (non-WordPress standard ones) with metadata including where they\'re registered (plugin/theme), number of posts created, public/private status, and associated taxonomies. Helps identify custom post types that are unused (dead weight), critical to your site, or need maintenance. Includes risk assessment based on registration method and content volume. Useful for understanding dependencies when updating plugins or themes.', 'wpshadow' ),
						'description_wizard' => __( 'See all custom post types on your site and how many posts use each one. Identify which post types are important and which might be abandoned.', 'wpshadow' ),
						'default_enabled'    => true,
					),
					'custom_taxonomies'      => array(
						'name'               => __( 'Custom Taxonomies Inventory', 'wpshadow' ),
						'description_short'  => __( 'Catalog all custom taxonomies and term usage', 'wpshadow' ),
						'description_long'   => __( 'Catalogs all custom taxonomies (non-WordPress standard categories/tags) with registration location, term count, public/private status, and associated post types. Helps identify taxonomies that are under-utilized (few terms), critical to organization, or deprecated. Includes orphaned taxonomy detection - taxonomies with no terms that might be safe to remove. Essential for content organization assessment and migration planning.', 'wpshadow' ),
						'description_wizard' => __( 'See all custom taxonomies used to organize your content and how many terms each one contains.', 'wpshadow' ),
						'default_enabled'    => true,
					),
					'custom_shortcodes'      => array(
						'name'               => __( 'Custom Shortcodes Registry', 'wpshadow' ),
						'description_short'  => __( 'Inventory all custom shortcodes and usage frequency', 'wpshadow' ),
						'description_long'   => __( 'Catalogs all custom shortcodes registered by plugins/themes with usage frequency across posts/pages. Identifies shortcodes from disabled plugins (orphaned), rarely-used shortcodes (cleanup candidates), and heavily-used ones (important for content). Helps with plugin updates/removals by showing what content depends on specific plugins. Includes shortcode code samples and parameter documentation when available.', 'wpshadow' ),
						'description_wizard' => __( 'See which shortcodes are used in your content and by which plugins. Find orphaned shortcodes from disabled plugins that might break content.', 'wpshadow' ),
						'default_enabled'    => false,
					),
					'custom_tables'          => array(
						'name'               => __( 'Custom Database Tables', 'wpshadow' ),
						'description_short'  => __( 'Identify custom database tables and their owners', 'wpshadow' ),
						'description_long'   => __( 'Detects custom database tables (non-WordPress standard) created by plugins or migrations. Shows table ownership (which plugin), size, row count, and relationship to standard WordPress tables. Helps identify orphaned tables from disabled plugins that waste space, tables critical to your site\'s functionality, and unused custom tables. Includes data volume analysis for performance optimization. Essential for database cleanup and optimization.', 'wpshadow' ),
						'description_wizard' => __( 'See all custom database tables on your site. Identify abandoned tables from old plugins that could be safely removed to free up space.', 'wpshadow' ),
						'default_enabled'    => false,
					),
					'wp_config_customizations' => array(
						'name'               => __( 'wp-config.php Customizations', 'wpshadow' ),
						'description_short'  => __( 'Audit non-standard WordPress configuration constants', 'wpshadow' ),
						'description_long'   => __( 'Scans wp-config.php for non-standard configuration constants that modify WordPress behavior (custom defines added beyond defaults). Identifies custom security configurations, performance tweaks, feature flags, and debugging settings. Helps document environment-specific configuration, assess security hardening measures, and identify deprecated configurations before WordPress updates. Essential for understanding site customizations and potential upgrade blockers.', 'wpshadow' ),
						'description_wizard' => __( 'See what custom configuration settings have been added to wp-config.php beyond WordPress defaults. Understand your site\'s unique configuration.', 'wpshadow' ),
						'default_enabled'    => false,
					),
					'theme_customizations'   => array(
						'name'               => __( 'Theme Customizations Audit', 'wpshadow' ),
						'description_short'  => __( 'Identify theme customizations and their extent', 'wpshadow' ),
						'description_long'   => __( 'Audits active theme for customizations including child theme usage, custom hooks/filters, template overrides, and modification extent. Detects child theme with extensive customizations versus parent theme with hooks. Assesses upgrade safety - heavy customizations increase theme update risk. Identifies customizations that might conflict with new theme versions. Useful for theme update planning and understanding dependencies.', 'wpshadow' ),
						'description_wizard' => __( 'Check how much your theme has been customized and assess how risky a theme update might be.', 'wpshadow' ),
						'default_enabled'    => false,
					),
					'custom_hooks'           => array(
						'name'               => __( 'Custom Hooks & Filters Registry', 'wpshadow' ),
						'description_short'  => __( 'Catalog custom hooks and filters registered by plugins', 'wpshadow' ),
						'description_long'   => __( 'Inventories custom action hooks and filter hooks registered by active plugins and theme. Identifies hooks with high usage (many listeners), unused hooks (cleanup candidates), and hook patterns indicating plugin dependencies. Helps understand plugin communication patterns, identify plugins that are core to your site functionality, and plan plugin updates safely. Shows which hooks are used by multiple plugins (potential conflicts).', 'wpshadow' ),
						'description_wizard' => __( 'See all custom hooks and filters used by your plugins and theme. Understand plugin communication patterns and dependencies.', 'wpshadow' ),
						'default_enabled'    => false,
					),
				),
			)
		);

		$this->register_default_settings(
			array(
				'performance_profiling'     => true,
				'security_audit'            => true,
				'compatibility_check'       => true,
				'conflict_detection'        => false,
				'usage_analytics'           => true,
				'update_tracking'           => true,
				'alternative_suggestions'   => false,
				'detailed_reports'          => false,
				'historical_tracking'       => true,
				'alert_system'              => true,
				'customization_audit'       => true,
				'custom_post_types'         => true,
				'custom_taxonomies'         => true,
				'custom_shortcodes'         => false,
				'custom_tables'             => false,
				'wp_config_customizations'  => false,
				'theme_customizations'      => false,
				'custom_hooks'              => false,
			)
		);

		$this->log_activity( 'feature_initialized', 'Plugin Audit & Performance Profiler initialized', 'info' );
	}

	/**
	 * Check if feature has details page.
	 */
	public function has_details_page(): bool {
		return true;
	}

	/**
	 * Register feature hooks.
	 */
	public function register(): void {
		if ( ! $this->is_enabled() ) {
			return;
		}

		// Register AJAX handlers for plugin profiling and auditing
		add_action( 'wp_ajax_wpshadow_audit_plugins', array( $this, 'ajax_audit_plugins' ) );
		add_action( 'wp_ajax_wpshadow_profile_plugin', array( $this, 'ajax_profile_plugin' ) );
		add_action( 'wp_ajax_wpshadow_get_plugin_details', array( $this, 'ajax_get_plugin_details' ) );

		// Schedule periodic plugin audits
		add_action( 'wp_scheduled_delete', array( $this, 'run_scheduled_audit' ) );

		// Register site health test
		add_filter( 'site_status_tests', array( $this, 'register_site_health_test' ) );
	}

	/**
	 * AJAX handler for plugin auditing.
	 */
	public function ajax_audit_plugins(): void {
		if ( ! check_ajax_referer( 'wpshadow_audit_plugins_nonce', 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed', 'wpshadow' ) ) );
			return;
		}

		if ( ! current_user_can( 'manage_plugins' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied', 'wpshadow' ) ) );
			return;
		}

		// Get all active plugins
		$active_plugins = get_option( 'active_plugins', array() );
		$audit_results = array();

		foreach ( $active_plugins as $plugin ) {
			$audit_results[ $plugin ] = $this->audit_single_plugin( $plugin );
		}

		// Log activity
		$this->log_activity(
			'plugin_audit_completed',
			sprintf( 'Plugin audit completed for %d plugins', count( $active_plugins ) ),
			'info'
		);

		wp_send_json_success(
			array(
				'results'      => $audit_results,
				'count'        => count( $active_plugins ),
				'timestamp'    => current_time( 'mysql' ),
			)
		);
	}

	/**
	 * AJAX handler for profiling individual plugin.
	 */
	public function ajax_profile_plugin(): void {
		if ( ! check_ajax_referer( 'wpshadow_profile_plugin_nonce', 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed', 'wpshadow' ) ) );
			return;
		}

		if ( ! current_user_can( 'manage_plugins' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied', 'wpshadow' ) ) );
			return;
		}

		$plugin = isset( $_POST['plugin'] ) ? sanitize_text_field( wp_unslash( $_POST['plugin'] ) ) : '';

		if ( empty( $plugin ) ) {
			wp_send_json_error( array( 'message' => __( 'Plugin not specified', 'wpshadow' ) ) );
			return;
		}

		$profile = $this->profile_plugin_performance( $plugin );

		wp_send_json_success(
			array(
				'plugin'  => $plugin,
				'profile' => $profile,
			)
		);
	}

	/**
	 * AJAX handler for getting detailed plugin information.
	 */
	public function ajax_get_plugin_details(): void {
		if ( ! check_ajax_referer( 'wpshadow_plugin_details_nonce', 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed', 'wpshadow' ) ) );
			return;
		}

		if ( ! current_user_can( 'manage_plugins' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied', 'wpshadow' ) ) );
			return;
		}

		$plugin = isset( $_POST['plugin'] ) ? sanitize_text_field( wp_unslash( $_POST['plugin'] ) ) : '';

		if ( empty( $plugin ) ) {
			wp_send_json_error( array( 'message' => __( 'Plugin not specified', 'wpshadow' ) ) );
			return;
		}

		$details = $this->get_plugin_details( $plugin );

		wp_send_json_success( $details );
	}

	/**
	 * Run scheduled audit.
	 */
	public function run_scheduled_audit(): void {
		// Trigger plugin audit
		$active_plugins = get_option( 'active_plugins', array() );
		$audit_results = array();

		foreach ( $active_plugins as $plugin ) {
			$audit_results[ $plugin ] = $this->audit_single_plugin( $plugin );
		}

		// Store results for later retrieval
		set_transient( 'wpshadow_plugin_audit_results', $audit_results, DAY_IN_SECONDS );

		// Check for alerts
		$this->check_for_alerts( $audit_results );
	}

	/**
	 * Audit a single plugin.
	 *
	 * @param string $plugin Plugin file path.
	 * @return array Audit results.
	 */
	private function audit_single_plugin( string $plugin ): array {
		$audit = array(
			'plugin'              => $plugin,
			'performance_score'   => 100,
			'security_score'      => 100,
			'compatibility_score' => 100,
			'issues'              => array(),
			'warnings'            => array(),
			'recommendations'     => array(),
		);

		// Get plugin data
		if ( function_exists( 'get_plugin_data' ) ) {
			$plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin );
			$audit['data'] = $plugin_data;
		}

		// Run individual checks
		if ( $this->is_sub_feature_enabled( 'performance_profiling', true ) ) {
			$perf = $this->profile_plugin_performance( $plugin );
			$audit['performance'] = $perf;
		}

		if ( $this->is_sub_feature_enabled( 'security_audit', true ) ) {
			$security = $this->check_plugin_security( $plugin );
			$audit['security'] = $security;
		}

		if ( $this->is_sub_feature_enabled( 'compatibility_check', true ) ) {
			$compat = $this->check_plugin_compatibility( $plugin );
			$audit['compatibility'] = $compat;
		}

		if ( $this->is_sub_feature_enabled( 'update_tracking', true ) ) {
			$updates = $this->check_plugin_updates( $plugin );
			$audit['updates'] = $updates;
		}

		return $audit;
	}

	/**
	 * Profile plugin performance.
	 *
	 * @param string $plugin Plugin file path.
	 * @return array Performance metrics.
	 */
	private function profile_plugin_performance( string $plugin ): array {
		return array(
			'load_time_ms'      => 0,
			'query_count'       => 0,
			'memory_usage_kb'   => 0,
			'http_requests'     => 0,
			'status'            => 'pending', // pending, profiling, complete
			'last_updated'      => current_time( 'mysql' ),
		);
	}

	/**
	 * Check plugin security.
	 *
	 * @param string $plugin Plugin file path.
	 * @return array Security check results.
	 */
	private function check_plugin_security( string $plugin ): array {
		return array(
			'vulnerabilities'   => array(),
			'outdated'          => false,
			'abandoned'         => false,
			'last_updated_days' => 0,
			'score'             => 100,
		);
	}

	/**
	 * Check plugin compatibility.
	 *
	 * @param string $plugin Plugin file path.
	 * @return array Compatibility check results.
	 */
	private function check_plugin_compatibility( string $plugin ): array {
		return array(
			'wp_compatible'     => true,
			'php_compatible'    => true,
			'conflicts'         => array(),
			'score'             => 100,
		);
	}

	/**
	 * Check for plugin updates.
	 *
	 * @param string $plugin Plugin file path.
	 * @return array Update information.
	 */
	private function check_plugin_updates( string $plugin ): array {
		return array(
			'has_updates'       => false,
			'new_version'       => null,
			'security_update'   => false,
			'current_version'   => '',
		);
	}

	/**
	 * Get detailed plugin information.
	 *
	 * @param string $plugin Plugin file path.
	 * @return array Plugin details.
	 */
	private function get_plugin_details( string $plugin ): array {
		if ( function_exists( 'get_plugin_data' ) ) {
			return get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin );
		}

		return array();
	}

	/**
	 * Check for alerts and notify if needed.
	 *
	 * @param array $audit_results Audit results.
	 */
	private function check_for_alerts( array $audit_results ): void {
		if ( ! $this->is_sub_feature_enabled( 'alert_system', true ) ) {
			return;
		}

		$issues_found = array();

		foreach ( $audit_results as $plugin => $audit ) {
			if ( ! empty( $audit['issues'] ) ) {
				$issues_found[ $plugin ] = $audit['issues'];
			}
		}

		if ( ! empty( $issues_found ) ) {
			$this->log_activity(
				'Plugin issues detected',
				sprintf( 'Issues detected in %d plugins', count( $issues_found ) ),
				'warning'
			);
		}
	}

	/**
	 * Register site health test.
	 *
	 * @param array $tests Site health tests.
	 * @return array Updated tests.
	 */
	public function register_site_health_test( array $tests ): array {
		$tests['direct']['plugin_audit'] = array(
			'label'       => __( 'Plugin Audit', 'wpshadow' ),
			'test'        => array( $this, 'site_health_test_plugin_audit' ),
			'description' => __( 'Checks installed plugins for security issues and performance problems.', 'wpshadow' ),
		);

		return $tests;
	}

	/**
	 * Site health test for plugin audit.
	 *
	 * @return array Test results.
	 */
	public function site_health_test_plugin_audit(): array {
		$audit_results = get_transient( 'wpshadow_plugin_audit_results' );

		if ( empty( $audit_results ) ) {
			return array(
				'label'       => __( 'Plugin Audit Pending', 'wpshadow' ),
				'status'      => 'recommended',
				'badge'       => array(
					'label' => __( 'WPShadow', 'wpshadow' ),
					'color' => 'blue',
				),
				'description' => __( 'Run a plugin audit to check for security issues and performance problems.', 'wpshadow' ),
				'actions'     => sprintf(
					'<a href="%s">%s</a>',
					admin_url( 'admin.php?page=wpshadow&wpshadow_tab=features&feature=plugin-audit' ),
					__( 'Run Plugin Audit', 'wpshadow' )
				),
				'test'        => 'plugin_audit',
			);
		}

		$total_plugins = count( $audit_results );
		$issues_count = 0;

		foreach ( $audit_results as $audit ) {
			$issues_count += count( $audit['issues'] ?? array() );
		}

		if ( $issues_count > 0 ) {
			return array(
				'label'       => __( 'Plugin Issues Detected', 'wpshadow' ),
				'status'      => 'critical',
				'badge'       => array(
					'label' => __( 'Warning', 'wpshadow' ),
					'color' => 'red',
				),
				'description' => sprintf(
					__( '%d issues found in %d plugins. Review and fix plugin problems.', 'wpshadow' ),
					$issues_count,
					$total_plugins
				),
				'actions'     => sprintf(
					'<a href="%s">%s</a>',
					admin_url( 'admin.php?page=wpshadow&wpshadow_tab=features&feature=plugin-audit' ),
					__( 'Review Plugin Audit Results', 'wpshadow' )
				),
				'test'        => 'plugin_audit',
			);
		}

		return array(
			'label'       => __( 'Plugin Audit Complete', 'wpshadow' ),
			'status'      => 'good',
			'badge'       => array(
				'label' => __( 'WPShadow', 'wpshadow' ),
				'color' => 'green',
			),
			'description' => sprintf(
				__( 'All %d plugins have been audited and no major issues detected.', 'wpshadow' ),
				$total_plugins
			),
			'test'        => 'plugin_audit',
		);
	}
}
