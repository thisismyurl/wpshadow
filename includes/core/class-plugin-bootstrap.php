<?php

/**
 * WPShadow Plugin Bootstrap
 *
 * Service registry and bootstrap orchestrator.
 * Initializes all core systems in the correct order.
 *
 * Philosophy: Commandment #7 (Ridiculously Good - obvious initialization order)
 *
 * @package WPShadow
 * @subpackage Core
 */

namespace WPShadow\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Service registry and plugin initialization orchestrator
 */
class Plugin_Bootstrap {


	/**
	 * Initialize all WPShadow systems
	 *
	 * Called once from wpshadow.php on plugins_loaded hook
	 *
	 * @return void
	 */
	public static function init() {
		// 1. Load core base classes (required before everything else)
		self::load_core_classes();

		// 2. Register hooks (must be early, before other systems)
		Hooks_Initializer::init();

		// 3. Initialize menu system
		Menu_Manager::init();

		// 4. Load dashboard page
		self::load_dashboard_page();

		// 5. Load workflow module
		self::load_workflow_module();

		// 6. Load engage system (gamification)
		self::load_engage_system();

		// 7. Load performance optimizer
		self::load_performance_optimizer();

		// 8. Load onboarding system
		self::load_onboarding_system();

		// 9. Load privacy system
		self::load_privacy_system();

		// 10. Load reporting and intelligence features
		self::load_reporting_intelligence();

		// 11. Load exit followup system
		self::load_exit_followup_system();

		// 12. Load content post types (KB, FAQ, etc.)
		self::load_content_types();

		// 13. Load AJAX handlers for utilities
		self::load_ajax_handlers();

		// 14. Load guided onboarding system
		self::load_guided_onboarding();

		// 15. Load usage analytics
		self::load_usage_analytics();

		// 16. Load workflow recipes
		self::load_workflow_recipes();

		// 17. Load smart recommendations
		self::load_smart_recommendations();

		// 17. Load smart recommendations
		self::load_smart_recommendations();

		// 18. Load pro addon integration
		self::load_pro_integration();

		// 19. Load WP-CLI commands
		self::load_cli_commands();

		// 20. Initialize visual comparator
		self::init_visual_comparator();

		// 21. Fire initialization complete hook
		do_action( 'wpshadow_core_initialized' );
	}

	/**
	 * Load core base classes
	 *
	 * @return void
	 */
	private static function load_core_classes() {
		$core_path = WPSHADOW_PATH . 'includes/core/';

		// Already loaded by wpshadow.php:
		// - class-treatment-base.php
		// - class-ajax-handler-base.php
		// - class-diagnostic-base.php
		// - class-color-utils.php
		// - class-theme-data-provider.php
		// - class-activity-logger.php
		// - class-error-handler.php

		// Load additional core classes
		if ( file_exists( $core_path . 'class-kpi-tracker.php' ) ) {
			require_once $core_path . 'class-kpi-tracker.php';
		}

		if ( file_exists( $core_path . 'class-finding-status-manager.php' ) ) {
			require_once $core_path . 'class-finding-status-manager.php';
		}

		if ( file_exists( $core_path . 'class-tooltip-manager.php' ) ) {
			require_once $core_path . 'class-tooltip-manager.php';
		}

		if ( file_exists( $core_path . 'class-dashboard-widgets.php' ) ) {
			require_once $core_path . 'class-dashboard-widgets.php';
		}

		if ( file_exists( $core_path . 'class-site-health-explanations.php' ) ) {
			require_once $core_path . 'class-site-health-explanations.php';
		}

		if ( file_exists( $core_path . 'class-treatment-hooks.php' ) ) {
			require_once $core_path . 'class-treatment-hooks.php';
		}

		if ( file_exists( $core_path . 'class-trend-chart.php' ) ) {
			require_once $core_path . 'class-trend-chart.php';
		}

		if ( file_exists( $core_path . 'class-abstract-registry.php' ) ) {
			require_once $core_path . 'class-abstract-registry.php';
		}

		if ( file_exists( $core_path . 'class-category-metadata.php' ) ) {
			require_once $core_path . 'class-category-metadata.php';
		}

		if ( file_exists( $core_path . 'class-visual-comparator.php' ) ) {
			require_once $core_path . 'class-visual-comparator.php';
		}
	}

	/**
	 * Load dashboard page
	 *
	 * @return void
	 */
	private static function load_dashboard_page() {
		$dashboard_file = WPSHADOW_PATH . 'includes/views/dashboard-page.php';
		if ( file_exists( $dashboard_file ) ) {
			require_once $dashboard_file;
		}

		// Load visual comparisons page
		$visual_comparisons_file = WPSHADOW_PATH . 'includes/views/visual-comparisons-page.php';
		if ( file_exists( $visual_comparisons_file ) ) {
			require_once $visual_comparisons_file;

			// Load dashboard widgets
			$widgets_path = WPSHADOW_PATH . 'includes/dashboard/widgets/';
			if ( file_exists( $widgets_path . 'class-setup-widget.php' ) ) {
				require_once $widgets_path . 'class-setup-widget.php';

				if ( class_exists( '\\WPShadow\\Dashboard\\Widgets\\Setup_Widget' ) ) {
					\WPShadow\Dashboard\Widgets\Setup_Widget::init();
				}
			}
		}
	}

	/**
	 * Load workflow module
	 *
	 * @since  1.2601.2148
	 * @return void
	 */
	private static function load_workflow_module() {
		$workflow_module_file = WPSHADOW_PATH . 'includes/workflow/workflow-module.php';
		if ( file_exists( $workflow_module_file ) ) {
			require_once $workflow_module_file;
		}
	}

	/**
	 * Load engage system (gamification)
	 *
	 * @return void
	 */
	private static function load_engage_system() {
		$engage_path = WPSHADOW_PATH . 'includes/engagement/';

		$engage_classes = array(
			'class-achievement-system.php',
			'class-streak-tracker.php',
			'class-leaderboard-manager.php',
			'class-badge-manager.php',
			'class-milestone-notifier.php',
			'class-exit-interview.php',
		);

		foreach ( $engage_classes as $file ) {
			if ( file_exists( $engage_path . $file ) ) {
				require_once $engage_path . $file;
			}
		}

		// Initialize gamification systems
		if ( class_exists( '\\WPShadow\\Gamification\\Achievement_System' ) && method_exists( '\\WPShadow\\Gamification\\Achievement_System', 'init' ) ) {
			\WPShadow\Gamification\Achievement_System::init();
		}

		if ( class_exists( '\\WPShadow\\Gamification\\Streak_Tracker' ) && method_exists( '\\WPShadow\\Gamification\\Streak_Tracker', 'init' ) ) {
			\WPShadow\Gamification\Streak_Tracker::init();
		}

		if ( class_exists( '\\WPShadow\\Gamification\\Leaderboard_Manager' ) && method_exists( '\\WPShadow\\Gamification\\Leaderboard_Manager', 'init' ) ) {
			\WPShadow\Gamification\Leaderboard_Manager::init();
		}

		if ( class_exists( '\\WPShadow\\Gamification\\Badge_Manager' ) && method_exists( '\\WPShadow\\Gamification\\Badge_Manager', 'init' ) ) {
			\WPShadow\Gamification\Badge_Manager::init();
		}

		if ( class_exists( '\\WPShadow\\Gamification\\Milestone_Notifier' ) && method_exists( '\\WPShadow\\Gamification\\Milestone_Notifier', 'init' ) ) {
			\WPShadow\Gamification\Milestone_Notifier::init();
		}

		// Initialize exit interview system
		if ( class_exists( '\\WPShadow\\Engagement\\Exit_Interview' ) && method_exists( '\\WPShadow\\Engagement\\Exit_Interview', 'init' ) ) {
			\WPShadow\Engagement\Exit_Interview::init();
		}
	}

	/**
	 * Load performance optimizer
	 *
	 * @return void
	 */
	private static function load_performance_optimizer() {
		$optimizer_path = WPSHADOW_PATH . 'includes/optimizer/';

		if ( file_exists( $optimizer_path . 'class-performance-optimizer.php' ) ) {
			require_once $optimizer_path . 'class-performance-optimizer.php';

			if ( class_exists( '\\WPShadow\\Optimizer\\Performance_Optimizer' ) ) {
				\WPShadow\Optimizer\Performance_Optimizer::init();
			}
		}
	}

	/**
	 * Load onboarding system
	 *
	 * @return void
	 */
	private static function load_onboarding_system() {
		$onboarding_path = WPSHADOW_PATH . 'includes/onboarding/';

		if ( file_exists( $onboarding_path . 'class-onboarding-wizard.php' ) ) {
			require_once $onboarding_path . 'class-onboarding-wizard.php';

			if ( class_exists( '\\WPShadow\\Onboarding\\Onboarding_Wizard' ) ) {
				\WPShadow\Onboarding\Onboarding_Wizard::init();
			}
		}
	}

	/**
	 * Load privacy system
	 *
	 * @return void
	 */
	private static function load_privacy_system() {
		$privacy_path = WPSHADOW_PATH . 'includes/privacy/';

		// Load privacy classes
		if ( file_exists( $privacy_path . 'class-consent-preferences.php' ) ) {
			require_once $privacy_path . 'class-consent-preferences.php';
		}

		if ( file_exists( $privacy_path . 'class-first-run-consent.php' ) ) {
			require_once $privacy_path . 'class-first-run-consent.php';
		}
	}

	/**
	 * Load reporting and intelligence features
	 *
	 * Loads advanced reporting capabilities including:
	 * - Predictive Analytics & Forecasting
	 * - Competitive Benchmarking
	 * - Real-Time Monitoring & Alerting
	 * - Visual Health Journey
	 * - Executive ROI Dashboard
	 * - Team Collaboration
	 *
	 * @since  1.2601.2200
	 * @return void
	 */
	private static function load_reporting_intelligence() {
		$reporting_path = WPSHADOW_PATH . 'includes/reporting/';
		$widgets_path   = WPSHADOW_PATH . 'includes/dashboard/widgets/';

		// Load reporting intelligence classes
		$reporting_classes = array(
			'class-predictive-analytics.php',
			'class-competitive-benchmarking.php',
			'class-realtime-monitoring.php',
			'class-visual-health-journey.php',
		);

		foreach ( $reporting_classes as $file ) {
			if ( file_exists( $reporting_path . $file ) ) {
				require_once $reporting_path . $file;
			}
		}

		// Load Phase 4 Infrastructure (Export, Snapshots, Integrations, Analytics)
		$phase4_classes = array(
			'class-report-export-manager.php',
			'class-report-snapshot-manager.php',
			'class-report-alert-manager.php',
			'class-report-integration-manager.php',
			'class-report-annotation-manager.php',
			'class-report-analytics-engine.php',
			'class-phase4-initializer.php',
		);

		foreach ( $phase4_classes as $file ) {
			if ( file_exists( $reporting_path . $file ) ) {
				require_once $reporting_path . $file;
			}
		}

		// Load Phase 4 Settings Page
		if ( file_exists( WPSHADOW_PATH . 'includes/screens/class-phase4-settings-page.php' ) ) {
			require_once WPSHADOW_PATH . 'includes/screens/class-phase4-settings-page.php';
		}

		// Load dashboard widgets
		$widget_classes = array(
			'class-executive-roi-widget.php',
			'class-team-collaboration-widget.php',
		);

		foreach ( $widget_classes as $file ) {
			if ( file_exists( $widgets_path . $file ) ) {
				require_once $widgets_path . $file;
			}
		}

		// Initialize real-time monitoring if enabled
		if ( class_exists( '\\WPShadow\\Reporting\\Realtime_Monitoring' ) ) {
			\WPShadow\Reporting\Realtime_Monitoring::init();
		}
	}

	/**
	 * Load exit followup system
	 *
	 * @return void
	 */
	private static function load_exit_followup_system() {
		$engagement_path = WPSHADOW_PATH . 'includes/engagement/';
		$screens_path    = WPSHADOW_PATH . 'includes/screens/';

		// Load exit followup classes
		if ( file_exists( $engagement_path . 'class-exit-followup-manager.php' ) ) {
			require_once $engagement_path . 'class-exit-followup-manager.php';
		}

		if ( file_exists( $engagement_path . 'class-exit-survey-builder.php' ) ) {
			require_once $engagement_path . 'class-exit-survey-builder.php';
		}

		// Load admin page
		if ( file_exists( $screens_path . 'class-exit-followups-page.php' ) ) {
			require_once $screens_path . 'class-exit-followups-page.php';
		}
	}

	/**
	 * Load content post types (KB, FAQ, etc.)
	 *
	 * @return void
	 */
	private static function load_content_types() {
		$content_path = WPSHADOW_PATH . 'includes/content/';

		// Load KB post type
		if ( file_exists( $content_path . 'class-kb-post-type.php' ) ) {
			require_once $content_path . 'class-kb-post-type.php';
		}

		// Load FAQ post type
		if ( file_exists( $content_path . 'class-faq-post-type.php' ) ) {
			require_once $content_path . 'class-faq-post-type.php';
		}
	}

	/**
	 * Load pro addon integration
	 *
	 * @return void
	 */
	private static function load_pro_integration() {
		// Load pro addon if available (separate plugin)
		do_action( 'wpshadow_load_pro_features' );
	}

	/**
	 * Load WP-CLI commands
	 *
	 * @return void
	 */
	private static function load_cli_commands() {
		if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
			return;
		}

		$cli_path = WPSHADOW_PATH . 'includes/cli/';

		if ( file_exists( $cli_path . 'class-wpshadow-cli.php' ) ) {
			require_once $cli_path . 'class-wpshadow-cli.php';

			// CLI will auto-register
		}
	}

	/**
	 * Initialize visual comparator
	 *
	 * @return void
	 */
	private static function init_visual_comparator() {
		if ( class_exists( '\\WPShadow\\Core\\Visual_Comparator' ) ) {
			\WPShadow\Core\Visual_Comparator::init();
		}
	}

	/**
	 * Load AJAX handlers for utilities
	 *
	 * @since 1.2601.2200
	 * @return void
	 */
	private static function load_ajax_handlers() {
		if ( ! is_admin() && ! wp_doing_ajax() ) {
			return;
		}

		$ajax_path = WPSHADOW_PATH . 'includes/admin/ajax/';

		// Utilities AJAX handlers
		$handlers = array(
			'create-clone-handler.php',
			'delete-clone-handler.php',
			'sync-clone-handler.php',
			'validate-snippet-handler.php',
			'save-snippet-handler.php',
			'toggle-snippet-handler.php',
			'delete-snippet-handler.php',
			'detect-plugin-conflict-handler.php',
			'bulk-find-replace-handler.php',
			'regenerate-thumbnails-handler.php',
		);

		foreach ( $handlers as $handler ) {
			if ( file_exists( $ajax_path . $handler ) ) {
				require_once $ajax_path . $handler;
			}
		}
	}

	/**
	 * Load guided onboarding system
	 *
	 * @since 1.2601.2200
	 * @return void
	 */
	private static function load_guided_onboarding() {
		$onboarding_path = WPSHADOW_PATH . 'includes/onboarding/';

		if ( file_exists( $onboarding_path . 'class-feature-tour.php' ) ) {
			require_once $onboarding_path . 'class-feature-tour.php';

			if ( class_exists( '\\WPShadow\\Onboarding\\Feature_Tour' ) ) {
				\WPShadow\Onboarding\Feature_Tour::init();
			}
		}
	}

	/**
	 * Load usage analytics system
	 *
	 * @since 1.2601.2200
	 * @return void
	 */
	private static function load_usage_analytics() {
		$analytics_path = WPSHADOW_PATH . 'includes/analytics/';

		if ( file_exists( $analytics_path . 'class-usage-tracker.php' ) ) {
			require_once $analytics_path . 'class-usage-tracker.php';

			if ( class_exists( '\\WPShadow\\Analytics\\Usage_Tracker' ) ) {
				\WPShadow\Analytics\Usage_Tracker::init();
			}
		}

		// Load dashboard widget
		if ( file_exists( $analytics_path . 'class-impact-dashboard-widget.php' ) ) {
			require_once $analytics_path . 'class-impact-dashboard-widget.php';
		}
	}

	/**
	 * Load workflow recipes system
	 *
	 * @since 1.2601.2200
	 * @return void
	 */
	private static function load_workflow_recipes() {
		$recipes_path = WPSHADOW_PATH . 'includes/workflow/';

		if ( file_exists( $recipes_path . 'class-recipe-manager.php' ) ) {
			require_once $recipes_path . 'class-recipe-manager.php';

			if ( class_exists( '\\WPShadow\\Workflow\\Recipe_Manager' ) ) {
				\WPShadow\Workflow\Recipe_Manager::init();
			}
		}
	}

	/**
	 * Load smart recommendations engine
	 *
	 * @since 1.2601.2200
	 * @return void
	 */
	private static function load_smart_recommendations() {
		$recommendations_path = WPSHADOW_PATH . 'includes/recommendations/';

		if ( file_exists( $recommendations_path . 'class-recommendation-engine.php' ) ) {
			require_once $recommendations_path . 'class-recommendation-engine.php';

			if ( class_exists( '\\WPShadow\\Recommendations\\Recommendation_Engine' ) ) {
				\WPShadow\Recommendations\Recommendation_Engine::init();
			}
		}
	}

	/**
	 * Get initialization status
	 *
	 * @return array {
	 *     @type bool $ready Is plugin ready
	 *     @type array $errors Any initialization errors
	 * }
	 */
	public static function get_status() {
		return array(
			'ready'  => true,
			'errors' => array(),
		);
	}
}
