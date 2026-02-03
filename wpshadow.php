<?php

/**
 * Plugin Name: WPShadow
 * Description: Minimal bootstrap to show WPShadow menu and Settings link.
 * Version: 1.26033.0900
 * Author: thisismyurl
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WPSHADOW_VERSION', '1.26033.0900' );
define( 'WPSHADOW_FILE', __FILE__ );
define( 'WPSHADOW_BASENAME', plugin_basename( __FILE__ ) );
define( 'WPSHADOW_PATH', plugin_dir_path( __FILE__ ) );
define( 'WPSHADOW_URL', plugin_dir_url( __FILE__ ) );

/**
 * Load Composer autoloader (PSR-4)
 *
 * Enables automatic class loading for PSR-4 compliant files.
 * Coexists with manual requires for WordPress-style named files.
 */
if ( file_exists( WPSHADOW_PATH . 'vendor/autoload.php' ) ) {
	require_once WPSHADOW_PATH . 'vendor/autoload.php';
}

/**
 * Load essential base classes (required by all other systems)
 *
 * These are loaded here rather than in Plugin_Bootstrap because they're
 * dependencies for other classes that might be loaded before plugins_loaded.
 */
require_once WPSHADOW_PATH . 'includes/core/class-security-validator.php';
require_once WPSHADOW_PATH . 'includes/core/class-secret-manager.php';
require_once WPSHADOW_PATH . 'includes/core/class-secret-audit-log.php';
require_once WPSHADOW_PATH . 'includes/core/class-ajax-handler-base.php';
require_once WPSHADOW_PATH . 'includes/core/class-treatment-interface.php';
require_once WPSHADOW_PATH . 'includes/core/class-treatment-base.php';
require_once WPSHADOW_PATH . 'includes/core/class-diagnostic-base.php';
require_once WPSHADOW_PATH . 'includes/core/class-activity-logger.php';
require_once WPSHADOW_PATH . 'includes/core/class-error-handler.php';
require_once WPSHADOW_PATH . 'includes/core/class-settings-registry.php';
require_once WPSHADOW_PATH . 'includes/core/class-database-migrator.php';
require_once WPSHADOW_PATH . 'includes/core/class-form-param-helper.php';
require_once WPSHADOW_PATH . 'includes/core/class-options-manager.php';
require_once WPSHADOW_PATH . 'includes/core/class-abstract-registry.php';
require_once WPSHADOW_PATH . 'includes/core/class-upgrade-path-helper.php';
require_once WPSHADOW_PATH . 'includes/diagnostics/class-diagnostic-registry.php';
require_once WPSHADOW_PATH . 'includes/core/functions-treatment.php';
require_once WPSHADOW_PATH . 'includes/core/class-utm-link-manager.php';
require_once WPSHADOW_PATH . 'includes/helpers/form-controls.php';
require_once WPSHADOW_PATH . 'includes/helpers/html-fetcher-helpers.php';
require_once WPSHADOW_PATH . 'includes/views/functions-page-layout.php';
require_once WPSHADOW_PATH . 'includes/views/menu-stubs.php';
require_once WPSHADOW_PATH . 'includes/views/dashboard-page.php';
require_once WPSHADOW_PATH . 'includes/core/class-finding-utils.php';
require_once WPSHADOW_PATH . 'includes/core/functions-category-metadata.php';
require_once WPSHADOW_PATH . 'includes/monitoring/recovery/class-backup-manager.php';
require_once WPSHADOW_PATH . 'includes/monitoring/recovery/class-backup-scheduler.php';

/**
 * Initialize error handler early
 *
 * This must run before plugins_loaded so fatal errors during bootstrap are handled.
 */

/**
 * Initialize error handler early
 */
\WPShadow\Core\Error_Handler::init();

/**
 * Load translations as early as possible after plugins load.
 *
 * Prevents just-in-time translation loading warnings.
 */
add_action(
	'plugins_loaded',
	function () {
		load_plugin_textdomain( 'wpshadow', false, dirname( WPSHADOW_BASENAME ) . '/languages' );
	},
	0
);

/**
 * Register settings on init.
 */
add_action(
	'init',
	function () {
		\WPShadow\Core\Settings_Registry::register();
	},
	5
);

/**
 * Load bootstrap and initialize all systems on plugins_loaded
 *
 * This gives all plugins a chance to load their hooks before WPShadow initializes.
 */
require_once WPSHADOW_PATH . 'includes/core/class-menu-manager.php';
require_once WPSHADOW_PATH . 'includes/core/class-ajax-router.php';
require_once WPSHADOW_PATH . 'includes/core/class-hooks-initializer.php';
require_once WPSHADOW_PATH . 'includes/core/class-plugin-bootstrap.php';

// Load monitoring/tracking classes (activity logging)
require_once WPSHADOW_PATH . 'includes/monitoring/class-wordpress-hooks-tracker.php';

// Load privacy classes (required by AJAX handlers)
require_once WPSHADOW_PATH . 'includes/privacy/class-consent-preferences.php';
require_once WPSHADOW_PATH . 'includes/privacy/class-first-run-consent.php';

// Load AJAX handlers (requires privacy classes to be loaded first)
require_once WPSHADOW_PATH . 'includes/admin/ajax/ajax-handlers-loader.php';

// Load Post Types Manager
require_once WPSHADOW_PATH . 'includes/content/class-post-types-manager.php';
require_once WPSHADOW_PATH . 'includes/admin/class-post-types-page.php';
require_once WPSHADOW_PATH . 'includes/admin/ajax/class-ajax-toggle-post-type.php';
require_once WPSHADOW_PATH . 'includes/content/class-post-types-blocks.php';
require_once WPSHADOW_PATH . 'includes/content/block-category.php';
require_once WPSHADOW_PATH . 'includes/content/class-cpt-custom-fields.php';
require_once WPSHADOW_PATH . 'includes/content/class-cpt-schema-markup.php';
require_once WPSHADOW_PATH . 'includes/content/class-sample-content-generator.php';
require_once WPSHADOW_PATH . 'includes/content/class-cpt-block-patterns.php';
require_once WPSHADOW_PATH . 'includes/content/class-cpt-drag-drop-ordering.php';
require_once WPSHADOW_PATH . 'includes/content/class-cpt-live-preview.php';
require_once WPSHADOW_PATH . 'includes/content/class-cpt-conditional-display.php';
require_once WPSHADOW_PATH . 'includes/content/class-cpt-analytics-dashboard.php';
require_once WPSHADOW_PATH . 'includes/content/class-cpt-inline-editing.php';
require_once WPSHADOW_PATH . 'includes/content/class-cpt-block-presets.php';
require_once WPSHADOW_PATH . 'includes/content/class-cpt-ai-content.php';
require_once WPSHADOW_PATH . 'includes/content/class-cpt-multi-language.php';
require_once WPSHADOW_PATH . 'includes/content/class-cpt-version-history.php';
\WPShadow\Content\Post_Types_Manager::init();
\WPShadow\Admin\Post_Types_Page::init();
\WPShadow\Content\Post_Types_Blocks::init();
\WPShadow\Content\CPT_Custom_Fields::init();
\WPShadow\Content\CPT_Schema_Markup::init();
\WPShadow\Content\Sample_Content_Generator::init();
\WPShadow\Content\CPT_Block_Patterns::init();
\WPShadow\Content\CPT_Drag_Drop_Ordering::init();
\WPShadow\Content\CPT_Live_Preview::init();
\WPShadow\Content\CPT_Conditional_Display::init();
\WPShadow\Content\CPT_Analytics_Dashboard::init();
\WPShadow\Content\CPT_Inline_Editing::init();
\WPShadow\Content\CPT_Block_Presets::init();
\WPShadow\Content\CPT_AI_Content::init();
\WPShadow\Content\CPT_Multi_Language::init();
\WPShadow\Content\CPT_Version_History::init();

// Load Modal System (CPT + Block + Rules Engine)
require_once WPSHADOW_PATH . 'includes/content/class-modal-post-type.php';
require_once WPSHADOW_PATH . 'includes/content/class-modal-block.php';
\WPShadow\Content\Modal_Post_Type::init();
\WPShadow\Content\Modal_Block::init();

// Load custom Gutenberg blocks
require_once WPSHADOW_PATH . 'includes/blocks/class-block-registry.php';
require_once WPSHADOW_PATH . 'includes/blocks/class-pricing-table-block.php';
require_once WPSHADOW_PATH . 'includes/blocks/class-faq-accordion-block.php';
require_once WPSHADOW_PATH . 'includes/blocks/class-cta-block.php';
require_once WPSHADOW_PATH . 'includes/blocks/class-icon-box-block.php';
require_once WPSHADOW_PATH . 'includes/blocks/class-timeline-block.php';
require_once WPSHADOW_PATH . 'includes/blocks/class-before-after-block.php';
require_once WPSHADOW_PATH . 'includes/blocks/class-stats-counter-block.php';
require_once WPSHADOW_PATH . 'includes/blocks/class-logo-grid-block.php';
require_once WPSHADOW_PATH . 'includes/blocks/class-countdown-timer-block.php';
require_once WPSHADOW_PATH . 'includes/blocks/class-content-tabs-block.php';
require_once WPSHADOW_PATH . 'includes/blocks/class-alert-notice-block.php';
require_once WPSHADOW_PATH . 'includes/blocks/class-progress-bar-block.php';
\WPShadow\Blocks\Block_Registry::init();

// Load Magic Link Manager (for expiry notifications)
require_once WPSHADOW_PATH . 'includes/utils/class-magic-link-manager.php';
\WPShadow\Utils\Magic_Link_Manager::init();

// Load auto-deploy feature (only active if WPSHADOW_AUTO_DEPLOY is true)
require_once WPSHADOW_PATH . 'includes/admin/class-auto-deploy.php';
\WPShadow\Admin\Auto_Deploy::init();

// Load Guardian inactive notice
require_once WPSHADOW_PATH . 'includes/admin/class-guardian-inactive-notice.php';
\WPShadow\Admin\Guardian_Inactive_Notice::init();

// Load Health History Analytics
require_once WPSHADOW_PATH . 'includes/analytics/class-health-history.php';
require_once WPSHADOW_PATH . 'includes/admin/class-health-history-page.php';
require_once WPSHADOW_PATH . 'includes/admin/class-health-history-widget.php';
require_once WPSHADOW_PATH . 'includes/admin/ajax/class-ajax-get-health-history.php';
\WPShadow\Analytics\Health_History::init();
\WPShadow\Admin\Health_History_Page::init();
\WPShadow\Admin\Health_History_Widget::init();

add_action(
	'init',
	function () {
		\WPShadow\Core\Plugin_Bootstrap::init();
	},
	20
);

/**
 * Prevent caching on WPShadow admin pages during testing.
 *
 * Ensures page/object/db caches are bypassed and sends no-cache headers
 * on WPShadow admin screens to avoid stale admin UI while we test.
 *
 * @return void
 */
function wpshadow_disable_admin_page_cache() {
	if ( ! is_admin() ) {
		return;
	}

	$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';

	if ( empty( $page ) || 0 !== strpos( $page, 'wpshadow' ) ) {
		return;
	}

	if ( ! defined( 'DONOTCACHEPAGE' ) ) {
		define( 'DONOTCACHEPAGE', true );
	}

	if ( ! defined( 'DONOTCACHEOBJECT' ) ) {
		define( 'DONOTCACHEOBJECT', true );
	}

	if ( ! defined( 'DONOTCACHEDB' ) ) {
		define( 'DONOTCACHEDB', true );
	}

	if ( function_exists( 'nocache_headers' ) ) {
		nocache_headers();
	}
}

add_action( 'admin_init', 'wpshadow_disable_admin_page_cache', 1 );
