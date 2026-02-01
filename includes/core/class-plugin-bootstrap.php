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

		// 7.5. Load notifications system (email alerts)
		self::load_notifications_system();
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

		// PHASE 1 OPTIMIZATION: Load database and cache management classes
		if ( file_exists( $core_path . 'class-database-indexes.php' ) ) {
			require_once $core_path . 'class-database-indexes.php';
		}

		if ( file_exists( $core_path . 'class-cache-manager.php' ) ) {
			require_once $core_path . 'class-cache-manager.php';
		}

		// PHASE 3 OPTIMIZATION: Load query optimization classes
		if ( file_exists( $core_path . 'class-query-batch-optimizer.php' ) ) {
			require_once $core_path . 'class-query-batch-optimizer.php';
		}

		// PHASE 3 OPTIMIZATION: Load dashboard page-level cache
		if ( file_exists( $core_path . 'class-dashboard-cache.php' ) ) {
			require_once $core_path . 'class-dashboard-cache.php';
		}

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

		// Load rollback/undo system for treatments
		$rollback_path = WPSHADOW_PATH . 'includes/treatments/class-rollback-manager.php';
		if ( file_exists( $rollback_path ) ) {
			require_once $rollback_path;
			if ( class_exists( '\\WPShadow\\Treatments\\Rollback_Manager' ) ) {
				\WPShadow\Treatments\Rollback_Manager::init();
			}
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
	private static function load_notifications_system() {
		// Initialize email notifications system
		$notifications_path = WPSHADOW_PATH . 'includes/notifications/class-email-notifier.php';
		if ( file_exists( $notifications_path ) ) {
			require_once $notifications_path;
			if ( class_exists( '\\WPShadow\\Notifications\\Email_Notifier' ) ) {
				\WPShadow\Notifications\Email_Notifier::init();
			}
		}
	}

	/**
	 * Load dashboard page
	 *
	 * NOTE: dashboard-page.php is now loaded in wpshadow.php so the 
	 * wpshadow_render_dashboard() function exists before admin_menu fires.
	 *
	 * @return void
	 */
	private static function load_dashboard_page() {
		// dashboard-page.php already loaded in wpshadow.php

		// PHASE 3 OPTIMIZATION: Load lazy widget loader
		$lazy_loader_file = WPSHADOW_PATH . 'includes/dashboard/class-lazy-widget-loader.php';
		if ( file_exists( $lazy_loader_file ) ) {
			require_once $lazy_loader_file;

			if ( class_exists( '\\WPShadow\\Dashboard\\Lazy_Widget_Loader' ) ) {
				\WPShadow\Dashboard\Lazy_Widget_Loader::init();
			}
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

		// PHASE 3 OPTIMIZATION: Initialize query batch optimizer
		if ( class_exists( '\\WPShadow\\Core\\Query_Batch_Optimizer' ) ) {
			\WPShadow\Core\Query_Batch_Optimizer::init();
		}

		// PHASE 3 OPTIMIZATION: Initialize dashboard cache system
		if ( class_exists( '\\WPShadow\\Core\\Dashboard_Cache' ) ) {
			\WPShadow\Core\Dashboard_Cache::init();
				// Initialize scheduled scans system
				$scan_scheduler_path = WPSHADOW_PATH . 'includes/guardian/class-scan-scheduler.php';
				if ( file_exists( $scan_scheduler_path ) ) {
					require_once $scan_scheduler_path;
					if ( class_exists( '\\WPShadow\\Guardian\\Scan_Scheduler' ) ) {
						\WPShadow\Guardian\Scan_Scheduler::init();
					}
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

		// Load Phase 5: Academy & Training Integration
		self::load_academy_training();

		// Load Phase 6: Privacy & Consent Excellence
		self::load_privacy_consent();

		// Load Phase 7: WPShadow Guardian (Cloud AI Scanning)
		self::load_guardian_integration();

		// Load Phase 8: Gamification System
		self::load_gamification_system();

		// Load Phase 9: WPShadow Vault (Backup & Restore)
		self::load_vault_system();

		// Load Phase 10: WPShadow Academy (Adaptive Learning)
		self::load_academy_system();

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
	 * Load Academy & Training Integration (Phase 5)
	 *
	 * Loads educational content systems including:
	 * - KB Article Management
	 * - Training Widget
	 * - Weekly Tips Widget
	 * - Post-Fix Education
	 * - Contextual Learning Tips
	 *
	 * @since 1.2604.0100
	 * @return void
	 */
	private static function load_academy_training() {
		$content_path = WPSHADOW_PATH . 'includes/content/';

		// Load KB Article Manager
		if ( file_exists( $content_path . 'class-kb-article-manager.php' ) ) {
			require_once $content_path . 'class-kb-article-manager.php';

			if ( class_exists( '\\WPShadow\\Content\\KB_Article_Manager' ) ) {
				\WPShadow\Content\KB_Article_Manager::init();
			}
		}

		// Load Training Widget
		if ( file_exists( $content_path . 'class-training-widget.php' ) ) {
			require_once $content_path . 'class-training-widget.php';

			if ( class_exists( '\\WPShadow\\Content\\Training_Widget' ) ) {
				\WPShadow\Content\Training_Widget::init();
			}
		}

		// Load Weekly Tips Widget
		if ( file_exists( $content_path . 'class-weekly-tips-widget.php' ) ) {
			require_once $content_path . 'class-weekly-tips-widget.php';

			if ( class_exists( '\\WPShadow\\Content\\Weekly_Tips_Widget' ) ) {
				\WPShadow\Content\Weekly_Tips_Widget::init();
			}
		}

		// Load Post-Fix Education
		if ( file_exists( $content_path . 'class-post-fix-education.php' ) ) {
			require_once $content_path . 'class-post-fix-education.php';

			if ( class_exists( '\\WPShadow\\Content\\Post_Fix_Education' ) ) {
				\WPShadow\Content\Post_Fix_Education::init();
			}
		}

		// Load existing KB Post Type if present
		if ( file_exists( $content_path . 'class-kb-post-type.php' ) ) {
			require_once $content_path . 'class-kb-post-type.php';

			if ( class_exists( '\\WPShadow\\KB\\KB_Post_Type' ) ) {
				\WPShadow\KB\KB_Post_Type::init();
			}
		}
	}

	/**
	 * Load Privacy & Consent Excellence (Phase 6)
	 *
	 * Loads privacy management systems including:
	 * - First Activation Welcome Modal
	 * - Privacy Dashboard Page
	 * - Phone Home Indicator
	 * - Privacy Policy Version Tracking
	 * - Consent Management
	 * - Data Export/Deletion Tools
	 *
	 * @since 1.2604.0200
	 * @return void
	 */
	private static function load_privacy_consent() {
		$privacy_path = WPSHADOW_PATH . 'includes/privacy/';
		$admin_path   = WPSHADOW_PATH . 'includes/admin/';

		// Load existing privacy infrastructure
		if ( file_exists( $privacy_path . 'class-consent-preferences.php' ) ) {
			require_once $privacy_path . 'class-consent-preferences.php';
		}

		if ( file_exists( $privacy_path . 'class-first-run-consent.php' ) ) {
			require_once $privacy_path . 'class-first-run-consent.php';
		}

		if ( file_exists( $privacy_path . 'class-privacy-policy-manager.php' ) ) {
			require_once $privacy_path . 'class-privacy-policy-manager.php';
		}

		// Load Phase 6 components
		// First Activation Welcome Modal
		if ( file_exists( $admin_path . 'class-first-activation-welcome.php' ) ) {
			require_once $admin_path . 'class-first-activation-welcome.php';

			if ( class_exists( '\\WPShadow\\Admin\\First_Activation_Welcome' ) ) {
				\WPShadow\Admin\First_Activation_Welcome::init();
			}
		}

		// Privacy Dashboard Page
		if ( file_exists( $admin_path . 'class-privacy-dashboard-page.php' ) ) {
			require_once $admin_path . 'class-privacy-dashboard-page.php';

			if ( class_exists( '\\WPShadow\\Admin\\Privacy_Dashboard_Page' ) ) {
				\WPShadow\Admin\Privacy_Dashboard_Page::init();
			}
		}

		// Phone Home Indicator
		if ( file_exists( $admin_path . 'class-phone-home-indicator.php' ) ) {
			require_once $admin_path . 'class-phone-home-indicator.php';

			if ( class_exists( '\\WPShadow\\Admin\\Phone_Home_Indicator' ) ) {
				\WPShadow\Admin\Phone_Home_Indicator::init();
			}
		}

		// Privacy Policy Version Tracker
		if ( file_exists( $privacy_path . 'class-privacy-policy-version-tracker.php' ) ) {
			require_once $privacy_path . 'class-privacy-policy-version-tracker.php';

			if ( class_exists( '\\WPShadow\\Privacy\\Privacy_Policy_Version_Tracker' ) ) {
				\WPShadow\Privacy\Privacy_Policy_Version_Tracker::init();
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
	 * Load Guardian integration (Phase 7).
	 *
	 * Loads WPShadow Guardian cloud AI scanning integration.
	 * Provides:
	 * - API client for Guardian cloud service
	 * - Token balance display in admin bar and dashboard
	 * - Scan interface for security, performance, SEO scans
	 * - Account connection management
	 * - Scan history and results viewing
	 *
	 * @since  1.2604.0300
	 * @return void
	 */
	private static function load_guardian_integration() {
		$guardian_path = WPSHADOW_PATH . 'includes/guardian/';

		// Core Guardian components
		$guardian_files = array(
			'class-guardian-manager.php',
			'class-guardian-api-client.php',
			'class-token-balance-widget.php',
			'class-guardian-scan-interface.php',
		);

		foreach ( $guardian_files as $file ) {
			if ( file_exists( $guardian_path . $file ) ) {
				require_once $guardian_path . $file;
			}
		}

		// Initialize Guardian components
		if ( class_exists( '\\WPShadow\\Guardian\\Token_Balance_Widget' ) ) {
			\WPShadow\Guardian\Token_Balance_Widget::init();
		}

		if ( class_exists( '\\WPShadow\\Guardian\\Guardian_Scan_Interface' ) ) {
			\WPShadow\Guardian\Guardian_Scan_Interface::init();
		}
	}

	/**
	 * Load Gamification System (Phase 8).
	 *
	 * Loads achievement, badge, points, leaderboard, and reward systems.
	 * Privacy-first design with opt-in leaderboards.
	 *
	 * Components:
	 * - Achievement Registry (23 achievements across 6 categories)
	 * - Badge System (tier + special badges)
	 * - Points System (earning, spending, history)
	 * - Leaderboard (opt-in only, privacy-first)
	 * - Reward System (Guardian credits, Vault storage, Pro subscriptions, digital swag)
	 * - Gamification Manager (central orchestrator)
	 * - Gamification UI (admin pages)
	 *
	 * @since  1.2604.0400
	 * @return void
	 */
	private static function load_gamification_system() {
		$gamification_path = WPSHADOW_PATH . 'includes/gamification/';

		// Core gamification components
		$gamification_files = array(
			'class-achievement-registry.php',
			'class-badge-system.php',
			'class-points-system.php',
			'class-leaderboard.php',
			'class-reward-system.php',
			'class-earn-actions.php',
			'class-gamification-manager.php',
			'class-gamification-ui.php',
		);

		foreach ( $gamification_files as $file ) {
			if ( file_exists( $gamification_path . $file ) ) {
				require_once $gamification_path . $file;
			}
		}

		// Initialize gamification components
		if ( class_exists( '\\WPShadow\\Gamification\\Achievement_Registry' ) ) {
			\WPShadow\Gamification\Achievement_Registry::init();
		}

		if ( class_exists( '\\WPShadow\\Gamification\\Badge_System' ) ) {
			\WPShadow\Gamification\Badge_System::init();
		}

		if ( class_exists( '\\WPShadow\\Gamification\\Leaderboard' ) ) {
			\WPShadow\Gamification\Leaderboard::init();
		}

		if ( class_exists( '\\WPShadow\\Gamification\\Reward_System' ) ) {
			\WPShadow\Gamification\Reward_System::init();
		}

		if ( class_exists( '\\WPShadow\\Gamification\\Gamification_Manager' ) ) {
			\WPShadow\Gamification\Gamification_Manager::init();
		}

		if ( class_exists( '\\WPShadow\\Gamification\\Gamification_UI' ) ) {
			\WPShadow\Gamification\Gamification_UI::init();
		}
	}

	/**
	 * Load WPShadow Vault (Backup & Restore) system
	 *
	 * Phase 9: Comprehensive backup and disaster recovery.
	 *
	 * Components:
	 * - Vault Manager (backup creation, storage, restore)
	 * - Vault Registration (free tier: 3 backups, 7-day retention)
	 * - Vault Dashboard Badge (Core dashboard integration)
	 * - Vault UI (admin pages for backup management)
	 *
	 * @since  1.6030.1850
	 * @return void
	 */
	private static function load_vault_system() {
		$vault_path = WPSHADOW_PATH . 'includes/vault/';

		// Core Vault components
		$vault_files = array(
			'class-vault-manager.php',
			'class-vault-registration.php',
			'class-vault-dashboard-badge.php',
			'class-vault-ui.php',
		);

		foreach ( $vault_files as $file ) {
			if ( file_exists( $vault_path . $file ) ) {
				require_once $vault_path . $file;
			}
		}

		// Initialize Vault components
		if ( class_exists( '\\WPShadow\\Vault\\Vault_Registration' ) ) {
			\WPShadow\Vault\Vault_Registration::init();
		}

		if ( class_exists( '\\WPShadow\\Vault\\Vault_Dashboard_Badge' ) ) {
			\WPShadow\Vault\Vault_Dashboard_Badge::init();
		}

		if ( class_exists( '\\WPShadow\\Vault\\Vault_UI' ) ) {
			\WPShadow\Vault\Vault_UI::init();
		}

		// Initialize Vault Manager singleton
		if ( class_exists( '\\WPShadow\\Vault\\Vault_Manager' ) ) {
			\WPShadow\Vault\Vault_Manager::get_instance();
		}
	}

	/**
	 * Load Academy system (Phase 10)
	 *
	 * Adaptive learning with KB articles, training videos, and courses.
	 *
	 * @since 1.6030.1930
	 */
	private static function load_academy_system() {
		// Load Academy classes.
		$classes = array(
			WPSHADOW_PATH . 'includes/academy/class-academy-manager.php',
			WPSHADOW_PATH . 'includes/academy/class-kb-article-registry.php',
			WPSHADOW_PATH . 'includes/academy/class-training-video-registry.php',
			WPSHADOW_PATH . 'includes/academy/class-course-registry.php',
			WPSHADOW_PATH . 'includes/academy/class-academy-ui.php',
		);

		foreach ( $classes as $class ) {
			if ( file_exists( $class ) ) {
				require_once $class;
			}
		}

		// Initialize registries.
		if ( class_exists( '\\WPShadow\\Academy\\KB_Article_Registry' ) ) {
			\WPShadow\Academy\KB_Article_Registry::init();
		}

		if ( class_exists( '\\WPShadow\\Academy\\Training_Video_Registry' ) ) {
			\WPShadow\Academy\Training_Video_Registry::init();
		}

		if ( class_exists( '\\WPShadow\\Academy\\Course_Registry' ) ) {
			\WPShadow\Academy\Course_Registry::init();
		}

		// Initialize Academy Manager.
		if ( class_exists( '\\WPShadow\\Academy\\Academy_Manager' ) ) {
			\WPShadow\Academy\Academy_Manager::init();
		}

		// Initialize Academy UI.
		if ( class_exists( '\\WPShadow\\Academy\\Academy_UI' ) ) {
			\WPShadow\Academy\Academy_UI::init();
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
