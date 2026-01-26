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
		
		// 2.5 Register AJAX handlers FIRST (before other plugins_loaded stuff)
		// This must be done early so AJAX endpoints work on the first request
		\WPShadow\Core\AJAX_Router::init();

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

		// 10. Load exit followup system
		self::load_exit_followup_system();

		// 11. Load content post types (KB, FAQ, etc.)
		self::load_content_types();

		// 12. Load pro addon integration
		self::load_pro_integration();

		// 13. Load WP-CLI commands
		self::load_cli_commands();

		// 14. Initialize visual comparator
		self::init_visual_comparator();

		// 15. Fire initialization complete hook
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
		// Note: Most gamification classes are static and don't require initialization
		// Only initialize if init() method exists
		
		// Achievement_System doesn't have init() method - uses static methods only
		// if ( class_exists( '\\WPShadow\\Gamification\\Achievement_System' ) && method_exists( '\\WPShadow\\Gamification\\Achievement_System', 'init' ) ) {
		// 	\WPShadow\Gamification\Achievement_System::init();
		// }

		// Streak_Tracker doesn't have init() method - uses static methods only
		// if ( class_exists( '\\WPShadow\\Gamification\\Streak_Tracker' ) && method_exists( '\\WPShadow\\Gamification\\Streak_Tracker', 'init' ) ) {
		// 	\WPShadow\Gamification\Streak_Tracker::init();
		// }

		// Leaderboard_Manager doesn't have init() method - uses static methods only
		// if ( class_exists( '\\WPShadow\\Gamification\\Leaderboard_Manager' ) && method_exists( '\\WPShadow\\Gamification\\Leaderboard_Manager', 'init' ) ) {
		// 	\WPShadow\Gamification\Leaderboard_Manager::init();
		// }

		// Badge_Manager doesn't have init() method - uses static methods only
		// if ( class_exists( '\\WPShadow\\Gamification\\Badge_Manager' ) && method_exists( '\\WPShadow\\Gamification\\Badge_Manager', 'init' ) ) {
		// 	\WPShadow\Gamification\Badge_Manager::init();
		// }

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
	 * Load exit followup system
	 *
	 * @return void
	 */
	private static function load_exit_followup_system() {
		/**
		 * Filters whether the exit followup system should load.
		 *
		 * @since 1.2601.2148
		 *
		 * @param bool $enabled Whether exit followups are enabled.
		 */
		$exit_followups_enabled = \apply_filters( 'wpshadow_exit_followups_enabled', false );

		if ( ! $exit_followups_enabled ) {
			return;
		}

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
