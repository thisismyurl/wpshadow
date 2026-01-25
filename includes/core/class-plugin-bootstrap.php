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

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Service registry and plugin initialization orchestrator
 */
class Plugin_Bootstrap
{

	/**
	 * Initialize all WPShadow systems
	 *
	 * Called once from wpshadow.php on plugins_loaded hook
	 *
	 * @return void
	 */
	public static function init()
	{
		// 1. Load core base classes (required before everything else)
		self::load_core_classes();

		// 2. Register hooks (must be early, before other systems)
		Hooks_Initializer::init();

		// 3. Initialize menu system
		Menu_Manager::init();

		// 4. Load dashboard page
		self::load_dashboard_page();

		// 5. Load engage system (gamification)
		self::load_engage_system();

		// 5. Load performance optimizer
		self::load_performance_optimizer();

		// 6. Load onboarding system
		self::load_onboarding_system();

		// 7. Load privacy system
		self::load_privacy_system();

		// 8. Load content post types (KB, FAQ, etc.)
		self::load_content_types();

		// 9. Load pro addon integration
		self::load_pro_integration();

		// 10. Load WP-CLI commands
		self::load_cli_commands();

		// 11. Fire initialization complete hook
		do_action('wpshadow_core_initialized');
	}

	/**
	 * Load core base classes
	 *
	 * @return void
	 */
	private static function load_core_classes()
	{
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
		if (file_exists($core_path . 'class-kpi-tracker.php')) {
			require_once $core_path . 'class-kpi-tracker.php';
		}

		if (file_exists($core_path . 'class-finding-status-manager.php')) {
			require_once $core_path . 'class-finding-status-manager.php';
		}

		if (file_exists($core_path . 'class-tooltip-manager.php')) {
			require_once $core_path . 'class-tooltip-manager.php';
		}

		if (file_exists($core_path . 'class-dashboard-widgets.php')) {
			require_once $core_path . 'class-dashboard-widgets.php';
		}

		if (file_exists($core_path . 'class-site-health-explanations.php')) {
			require_once $core_path . 'class-site-health-explanations.php';
		}

		if (file_exists($core_path . 'class-treatment-hooks.php')) {
			require_once $core_path . 'class-treatment-hooks.php';
		}

		if (file_exists($core_path . 'class-trend-chart.php')) {
			require_once $core_path . 'class-trend-chart.php';
		}

		if (file_exists($core_path . 'class-abstract-registry.php')) {
			require_once $core_path . 'class-abstract-registry.php';
		}

		if (file_exists($core_path . 'class-category-metadata.php')) {
			require_once $core_path . 'class-category-metadata.php';
		}
	}

	/**
	 * Load dashboard page
	 *
	 * @return void
	 */
	private static function load_dashboard_page()
	{
		$dashboard_file = WPSHADOW_PATH . 'includes/views/dashboard-page.php';
		if (file_exists($dashboard_file)) {
			require_once $dashboard_file;
		}
		
		// Load dashboard widgets
		$widgets_path = WPSHADOW_PATH . 'includes/dashboard/widgets/';
		if (file_exists($widgets_path . 'class-setup-widget.php')) {
			require_once $widgets_path . 'class-setup-widget.php';
			
			if (class_exists('\\WPShadow\\Dashboard\\Widgets\\Setup_Widget')) {
				\WPShadow\Dashboard\Widgets\Setup_Widget::init();
			}
		}
	}

	/**
	 * Load engage system (gamification)
	 *
	 * @return void
	 */
	private static function load_engage_system()
	{
		$engage_path = WPSHADOW_PATH . 'includes/engage/';

		$engage_classes = array(
			'class-achievement.php',
			'class-streak.php',
			'class-leaderboard.php',
			'class-badge.php',
			'class-milestone.php',
		);

		foreach ($engage_classes as $file) {
			if (file_exists($engage_path . $file)) {
				require_once $engage_path . $file;
			}
		}

		// Initialize engage systems
		if (class_exists('\\WPShadow\\Engage\\Achievement')) {
			\WPShadow\Engage\Achievement::init();
		}

		if (class_exists('\\WPShadow\\Engage\\Streak')) {
			\WPShadow\Engage\Streak::init();
		}

		if (class_exists('\\WPShadow\\Engage\\Leaderboard')) {
			\WPShadow\Engage\Leaderboard::init();
		}

		if (class_exists('\\WPShadow\\Engage\\Badge')) {
			\WPShadow\Engage\Badge::init();
		}

		if (class_exists('\\WPShadow\\Engage\\Milestone')) {
			\WPShadow\Engage\Milestone::init();
		}
	}

	/**
	 * Load performance optimizer
	 *
	 * @return void
	 */
	private static function load_performance_optimizer()
	{
		$optimizer_path = WPSHADOW_PATH . 'includes/optimizer/';

		if (file_exists($optimizer_path . 'class-performance-optimizer.php')) {
			require_once $optimizer_path . 'class-performance-optimizer.php';

			if (class_exists('\\WPShadow\\Optimizer\\Performance_Optimizer')) {
				\WPShadow\Optimizer\Performance_Optimizer::init();
			}
		}
	}

	/**
	 * Load onboarding system
	 *
	 * @return void
	 */
	private static function load_onboarding_system()
	{
		$onboarding_path = WPSHADOW_PATH . 'includes/onboarding/';

		if (file_exists($onboarding_path . 'class-onboarding-wizard.php')) {
			require_once $onboarding_path . 'class-onboarding-wizard.php';

			if (class_exists('\\WPShadow\\Onboarding\\Onboarding_Wizard')) {
				\WPShadow\Onboarding\Onboarding_Wizard::init();
			}
		}
	}

	/**
	 * Load privacy system
	 *
	 * @return void
	 */
	private static function load_privacy_system()
	{
		$privacy_path = WPSHADOW_PATH . 'includes/privacy/';

		// Load privacy classes
		if (file_exists($privacy_path . 'class-consent-preferences.php')) {
			require_once $privacy_path . 'class-consent-preferences.php';
		}

		if (file_exists($privacy_path . 'class-first-run-consent.php')) {
			require_once $privacy_path . 'class-first-run-consent.php';
		}
	}

	/**
	 * Load content post types (KB, FAQ, etc.)
	 *
	 * @return void
	 */
	private static function load_content_types()
	{
		$content_path = WPSHADOW_PATH . 'includes/content/';

		// Load KB post type
		if (file_exists($content_path . 'class-kb-post-type.php')) {
			require_once $content_path . 'class-kb-post-type.php';
		}

		// Load FAQ post type
		if (file_exists($content_path . 'class-faq-post-type.php')) {
			require_once $content_path . 'class-faq-post-type.php';
		}
	}

	/**
	 * Load pro addon integration
	 *
	 * @return void
	 */
	private static function load_pro_integration()
	{
		// Load pro addon if available (separate plugin)
		do_action('wpshadow_load_pro_features');
	}

	/**
	 * Load WP-CLI commands
	 *
	 * @return void
	 */
	private static function load_cli_commands()
	{
		if (! defined('WP_CLI') || ! WP_CLI) {
			return;
		}

		$cli_path = WPSHADOW_PATH . 'includes/cli/';

		if (file_exists($cli_path . 'class-wpshadow-cli.php')) {
			require_once $cli_path . 'class-wpshadow-cli.php';

			// CLI will auto-register
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
	public static function get_status()
	{
		return array(
			'ready'  => true,
			'errors' => array(),
		);
	}
}
