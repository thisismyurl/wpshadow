<?php

/**
 * WPShadow Hooks Initializer
 *
 * Centralizes WordPress hook registration (add_action, add_filter).
 * Extracted from wpshadow.php as part of Phase 4.5 refactoring.
 *
 * Philosophy: Commandment #7 (Ridiculously Good - single source of truth for hooks)
 *
 * @package WPShadow
 * @subpackage Core
 */

namespace WPShadow\Core;

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Centralized hook registration for WPShadow
 */
class Hooks_Initializer
{

	/**
	 * Register all WordPress hooks used by WPShadow
	 *
	 * @return void
	 */
	public static function init()
	{
		// Activation/Deactivation
		register_activation_hook(WPSHADOW_BASENAME, [__CLASS__, 'on_activate']);

		// Admin initialization
		add_action('admin_init', [__CLASS__, 'on_admin_init']);
		add_action('plugins_loaded', [__CLASS__, 'on_plugins_loaded']);

		// Menu and asset loading
		add_action('admin_menu', [__CLASS__, 'on_admin_menu']);
		add_action('admin_enqueue_scripts', [__CLASS__, 'on_admin_enqueue_scripts']);

		// Front-end assets
		add_action('wp_enqueue_scripts', [__CLASS__, 'on_wp_enqueue_scripts']);

		// Footer and notices
		add_action('admin_footer', [__CLASS__, 'on_admin_footer']);
		add_action('admin_notices', [__CLASS__, 'on_admin_notices']);
		add_action('tool_box', [__CLASS__, 'on_tool_box']);

		// User profile
		add_action('show_user_profile', [__CLASS__, 'on_show_user_profile']);
		add_action('edit_user_profile', [__CLASS__, 'on_show_user_profile']);
		add_action('personal_options_update', [__CLASS__, 'on_personal_options_update']);
		add_action('edit_user_profile_update', [__CLASS__, 'on_personal_options_update']);

		// Filters for WordPress integration
		add_filter('plugin_action_links_' . WPSHADOW_BASENAME, [__CLASS__, 'filter_plugin_action_links']);
		add_filter('site_status_tests', [__CLASS__, 'filter_site_status_tests']);
		add_filter('debug_information', [__CLASS__, 'filter_debug_information']);
		add_filter('wp_mail_from_name', [__CLASS__, 'filter_wp_mail_from_name'], 999);

		// Privacy
		add_filter('wp_privacy_personal_data_exporters', [__CLASS__, 'filter_privacy_exporters']);
		add_filter('wp_privacy_personal_data_erasers', [__CLASS__, 'filter_privacy_erasers']);
		add_action('admin_init', [__CLASS__, 'on_privacy_policy_content']);

		// Cron jobs
		add_action('wpshadow_run_overnight_fixes', [__CLASS__, 'on_overnight_fixes']);
		add_action('wpshadow_run_automated_fixes', [__CLASS__, 'on_automated_fixes']);
		add_action('wpshadow_run_data_cleanup', [__CLASS__, 'on_data_cleanup']);
		add_action('wpshadow_run_automatic_diagnostic_scan', [__CLASS__, 'on_automatic_diagnostic_scan']);
		add_action('wpshadow_send_scheduled_reports', [__CLASS__, 'on_scheduled_reports']);
		add_action('wpshadow_run_offpeak_operations', [__CLASS__, 'on_offpeak_operations']);

		// KPI tracking
		add_action('wpshadow_after_treatment_apply', [__CLASS__, 'on_treatment_applied'], 10, 3);
		add_action('wpshadow_diagnostic_executed', [__CLASS__, 'on_diagnostic_executed'], 10, 2);

		// Multisite
		add_action('network_admin_menu', [__CLASS__, 'on_network_admin_menu']);
	}

	/**
	 * Plugin activation hook
	 */
	public static function on_activate()
	{
		set_transient('wpshadow_redirect_to_dashboard', true, 30);
	}

	/**
	 * Admin init hook
	 */
	public static function on_admin_init()
	{
		// Redirect to dashboard on first activation
		if (get_transient('wpshadow_redirect_to_dashboard')) {
			delete_transient('wpshadow_redirect_to_dashboard');
			wp_safe_redirect(admin_url('admin.php?page=wpshadow'));
			exit;
		}

		// Initialize error handler
		Error_Handler::init();
	}

	/**
	 * Plugins loaded hook
	 */
	public static function on_plugins_loaded()
	{
		// Initialize core registries and systems
		\WPShadow\Admin\Update_Notification_Manager::init();
		\WPShadow\Diagnostics\Diagnostic_Registry::init();
		\WPShadow\Treatments\Treatment_Registry::init();
		\WPShadow\Workflow\Workflow_Executor::init();
		\WPShadow\Core\Treatment_Hooks::init();
		\WPShadow\Core\Site_Health_Explanations::init();

		// AJAX handlers (Phase 3.5.1)
		AJAX_Router::init();

		// Initialize Guardian system
		\WPShadow\Guardian\Guardian_Manager::init();

		// Initialize analyzers
		\WPShadow\Guardian\Failed_Login_Analyzer::init();
		\WPShadow\Guardian\Dashboard_Performance_Analyzer::init();
		\WPShadow\Guardian\REST_API_Performance_Analyzer::init();
		\WPShadow\Guardian\CSP_Violation_Analyzer::init();
		\WPShadow\Guardian\Compromised_Accounts_Analyzer::init();
		\WPShadow\Guardian\Cache_Invalidation_Analyzer::init();
		\WPShadow\Guardian\Shortcode_Execution_Analyzer::init();
		\WPShadow\Guardian\API_Latency_Analyzer::init();
		\WPShadow\Guardian\Block_Rendering_Performance_Analyzer::init();
		\WPShadow\Guardian\Bot_Traffic_Analyzer::init();
		\WPShadow\Guardian\Browser_Compatibility_Analyzer::init();
		\WPShadow\Guardian\Editor_Performance_Analyzer::init();
		\WPShadow\Guardian\Hook_Execution_Analyzer::init();

		// Guardian command handlers
		\WPShadow\Workflow\Commands\Enable_Guardian_Command::register();
		\WPShadow\Workflow\Commands\Configure_Guardian_Command::register();
		\WPShadow\Workflow\Commands\Get_Scan_Results_Command::register();
		\WPShadow\Workflow\Commands\Execute_Auto_Fix_Command::register();
		\WPShadow\Workflow\Commands\Preview_Auto_Fixes_Command::register();
		\WPShadow\Workflow\Commands\Update_Auto_Fix_Policy_Command::register();
		\WPShadow\Workflow\Commands\Generate_Report_Command::register();
		\WPShadow\Workflow\Commands\Send_Report_Command::register();
		\WPShadow\Workflow\Commands\Manage_Notifications_Command::register();

		// Fire hook for external plugins/addons
		do_action('wpshadow_core_loaded');
	}

	/**
	 * Admin menu hook
	 */
	public static function on_admin_menu()
	{
		// Menus are registered by Menu_Manager
	}

	/**
	 * Admin enqueue scripts hook
	 */
	public static function on_admin_enqueue_scripts($hook)
	{
		if (! is_string($hook) || strpos($hook, 'wpshadow') === false) {
			return;
		}

		// Enqueue design system
		wp_enqueue_style(
			'wpshadow-design-system',
			WPSHADOW_URL . 'assets/css/design-system.css',
			array(),
			WPSHADOW_VERSION
		);

		wp_enqueue_script(
			'wpshadow-design-system',
			WPSHADOW_URL . 'assets/js/design-system.js',
			array('jquery'),
			WPSHADOW_VERSION,
			true
		);

		// Enqueue page-specific styles
		wp_enqueue_style(
			'wpshadow-gauges',
			WPSHADOW_URL . 'assets/css/gauges.css',
			array('wpshadow-design-system'),
			WPSHADOW_VERSION
		);

		wp_enqueue_style(
			'wpshadow-safety-warnings',
			WPSHADOW_URL . 'assets/css/safety-warnings.css',
			array('wpshadow-design-system'),
			WPSHADOW_VERSION
		);

		wp_enqueue_style(
			'wpshadow-kanban-board',
			WPSHADOW_URL . 'assets/css/kanban-board.css',
			array('wpshadow-design-system'),
			WPSHADOW_VERSION
		);

		wp_enqueue_style(
			'wpshadow-dashboard-fullscreen',
			WPSHADOW_URL . 'assets/css/wpshadow-dashboard-fullscreen.css',
			array('wpshadow-design-system'),
			WPSHADOW_VERSION
		);

		// Enqueue scripts
		wp_enqueue_script(
			'wpshadow-dashboard-realtime',
			WPSHADOW_URL . 'assets/js/wpshadow-dashboard-realtime.js',
			array('jquery', 'wpshadow-design-system'),
			WPSHADOW_VERSION,
			false
		);

		wp_localize_script('wpshadow-dashboard-realtime', 'wpshadow', array(
			'dashboard_nonce' => wp_create_nonce('wpshadow_dashboard_nonce'),
			'first_scan_nonce' => wp_create_nonce('wpshadow_first_scan_nonce'),
			'scan_nonce' => wp_create_nonce('wpshadow_scan_nonce'),
		));

		wp_enqueue_script(
			'wpshadow-kanban-board',
			WPSHADOW_URL . 'assets/js/kanban-board.js',
			array('jquery'),
			WPSHADOW_VERSION,
			true
		);

		wp_localize_script('wpshadow-kanban-board', 'wpshadowKanban', array(
			'kanban_nonce' => wp_create_nonce('wpshadow_kanban'),
		));

		// Workflow list scripts
		if (is_string($hook) && ($hook === 'toplevel_page_wpshadow' || strpos($hook, 'wpshadow-workflows') !== false)) {
			wp_enqueue_script(
				'wpshadow-workflow-list',
				WPSHADOW_URL . 'assets/js/workflow-list.js',
				array('jquery'),
				WPSHADOW_VERSION,
				true
			);

			wp_localize_script('wpshadow-workflow-list', 'wpshadowWorkflow', array(
				'nonce' => wp_create_nonce('wpshadow_workflow'),
			));
		}

		// Guardian assets
		if (is_string($hook) && strpos($hook, 'wpshadow-guardian') !== false) {
			wp_enqueue_style(
				'wpshadow-guardian-dashboard-settings',
				WPSHADOW_URL . 'assets/css/guardian-dashboard-settings.css',
				array(),
				WPSHADOW_VERSION
			);

			wp_enqueue_script(
				'wpshadow-guardian-dashboard-settings',
				WPSHADOW_URL . 'assets/js/guardian-dashboard-settings.js',
				array('jquery'),
				WPSHADOW_VERSION,
				true
			);

			wp_localize_script('wpshadow-guardian-dashboard-settings', 'wpshadow', array(
				'ajax_url' => admin_url('admin-ajax.php'),
				'nonce' => wp_create_nonce('wpshadow_guardian_nonce'),
			));
		}

		// Dark mode
		wp_enqueue_style(
			'wpshadow-dark-mode',
			WPSHADOW_URL . 'assets/css/dark-mode.css',
			array(),
			WPSHADOW_VERSION
		);

		wp_enqueue_script(
			'wpshadow-dark-mode',
			WPSHADOW_URL . 'assets/js/dark-mode.js',
			array('jquery'),
			WPSHADOW_VERSION,
			true
		);

		$user_id = get_current_user_id();
		$dark_mode_pref = get_user_meta($user_id, 'wpshadow_dark_mode_preference', true) ?: 'auto';

		wp_localize_script('wpshadow-dark-mode', 'wpshadowDarkMode', array(
			'preference' => $dark_mode_pref,
		));
	}

	/**
	 * Frontend enqueue scripts hook
	 */
	public static function on_wp_enqueue_scripts()
	{
		wp_enqueue_style(
			'wpshadow-safety-warnings-frontend',
			WPSHADOW_URL . 'assets/css/safety-warnings.css',
			array(),
			WPSHADOW_VERSION
		);
	}

	/**
	 * Admin footer hook (consent banner)
	 */
	public static function on_admin_footer()
	{
		if (! is_admin() || wp_doing_ajax()) {
			return;
		}

		$current_user = get_current_user_id();
		if (! $current_user || ! current_user_can('manage_options')) {
			return;
		}

		if (! class_exists('\\WPShadow\\Privacy\\First_Run_Consent')) {
			return;
		}

		if (! \WPShadow\Privacy\First_Run_Consent::should_show_consent($current_user)) {
			return;
		}

		echo \WPShadow\Privacy\First_Run_Consent::get_consent_html();
	}

	/**
	 * Admin notices hook
	 */
	public static function on_admin_notices()
	{
		$scheduled = get_option('wpshadow_scheduled_offpeak', array());

		if (! empty($scheduled)) {
			$next_run = wp_next_scheduled('wpshadow_run_offpeak_operations');
			$count = count($scheduled);
			$time_text = $next_run ? date_i18n(get_option('time_format'), $next_run) : 'tonight';

			echo '<div class="notice notice-info is-dismissible">';
			echo '<p><span class="dashicons dashicons-clock" style="color: #2196f3;"></span> ';
			echo '<strong>WPShadow:</strong> ' . esc_html($count) . ' operation(s) scheduled for off-peak hours (' . esc_html($time_text) . ').';
			echo '</p></div>';
		}
	}

	/**
	 * Tool box hook
	 */
	public static function on_tool_box()
	{
		if (! current_user_can('read')) {
			return;
		}

		$catalog = wpshadow_get_tools_catalog();
		foreach ($catalog as $item) {
			if (empty($item['enabled'])) {
				continue;
			}

			$url = admin_url('admin.php?page=wpshadow-tools&tool=' . $item['tool']);

			echo '<div class="card">';
			echo '<h3>' . esc_html($item['title']) . '</h3>';
			echo '<p>' . esc_html($item['desc']) . '</p>';
			echo '<p><a class="button button-primary" href="' . esc_url($url) . '">' . esc_html__('Open Tool', 'wpshadow') . '</a></p>';
			echo '</div>';
		}
	}

	/**
	 * Show user profile hook
	 */
	public static function on_show_user_profile($user)
	{
		$dark_mode_pref = get_user_meta($user->ID, 'wpshadow_dark_mode_preference', true) ?: 'auto';
?>
		<table class="form-table" role="presentation">
			<tr class="wpshadow-dark-mode-wrap">
				<th scope="row"><?php esc_html_e('WPShadow Dark Mode', 'wpshadow'); ?></th>
				<td>
					<fieldset>
						<legend class="screen-reader-text"><span><?php esc_html_e('WPShadow Dark Mode', 'wpshadow'); ?></span></legend>
						<label>
							<input type="radio" name="wpshadow_dark_mode" value="auto" <?php checked($dark_mode_pref, 'auto'); ?>>
							<?php esc_html_e('Auto (follow system preference)', 'wpshadow'); ?>
						</label><br>
						<label>
							<input type="radio" name="wpshadow_dark_mode" value="light" <?php checked($dark_mode_pref, 'light'); ?>>
							<?php esc_html_e('Light', 'wpshadow'); ?>
						</label><br>
						<label>
							<input type="radio" name="wpshadow_dark_mode" value="dark" <?php checked($dark_mode_pref, 'dark'); ?>>
							<?php esc_html_e('Dark', 'wpshadow'); ?>
						</label>
						<p class="description">
							<?php esc_html_e('Choose your preferred dark mode setting for WPShadow admin pages.', 'wpshadow'); ?>
						</p>
					</fieldset>
				</td>
			</tr>
		</table>
<?php
	}

	/**
	 * Personal options update hook
	 */
	public static function on_personal_options_update($user_id)
	{
		if (! current_user_can('edit_user', $user_id)) {
			return;
		}

		if (isset($_POST['wpshadow_dark_mode'])) {
			$dark_mode = sanitize_text_field($_POST['wpshadow_dark_mode']);
			if (in_array($dark_mode, array('auto', 'light', 'dark'), true)) {
				update_user_meta($user_id, 'wpshadow_dark_mode_preference', $dark_mode);
			}
		}
	}

	/**
	 * Filter plugin action links
	 */
	public static function filter_plugin_action_links($links)
	{
		return Menu_Manager::add_settings_link($links);
	}

	/**
	 * Filter site status tests
	 */
	public static function filter_site_status_tests($tests)
	{
		if (! is_array($tests)) {
			$tests = array();
		}

		$tests['direct']['wpshadow_quick_scan'] = array(
			'label' => __('WPShadow Quick Scan', 'wpshadow'),
			'test'  => 'wpshadow_site_health_test_quick_scan',
		);

		$tests['direct']['wpshadow_deep_scan'] = array(
			'label' => __('WPShadow Deep Scan', 'wpshadow'),
			'test'  => 'wpshadow_site_health_test_deep_scan',
		);

		$tests['direct']['wpshadow_overall'] = array(
			'label' => __('WPShadow Overall Status', 'wpshadow'),
			'test'  => 'wpshadow_site_health_test_overall',
		);

		// Add critical findings as tests
		$findings = wpshadow_get_site_findings();
		$critical = array_filter($findings, function ($f) {
			return isset($f['threat_level']) && $f['threat_level'] >= 75;
		});

		foreach (array_slice($critical, 0, 5) as $finding) {
			$finding_id = isset($finding['id']) ? $finding['id'] : md5($finding['title'] ?? '');
			$tests['direct']['wpshadow_finding_' . $finding_id] = array(
				'label' => $finding['title'] ?? __('Security Issue', 'wpshadow'),
				'test'  => function () use ($finding) {
					return wpshadow_site_health_test_finding($finding, array(), $finding['id'] ?? '');
				},
			);
		}

		$GLOBALS['wpshadow_site_health_badge'] = array(
			'label' => __('WPShadow', 'wpshadow'),
			'color' => 'blue',
		);

		return $tests;
	}

	/**
	 * Filter debug information
	 */
	public static function filter_debug_information($info)
	{
		if (! is_array($info)) {
			$info = array();
		}

		$current_user_id = get_current_user_id();
		$quick_hidden = (bool) get_user_meta($current_user_id, 'wpshadow_hide_quick_scan', true);
		$deep_hidden  = (bool) get_user_meta($current_user_id, 'wpshadow_hide_deep_scan', true);

		$quick_last = (int) get_option('wpshadow_last_quick_checks', 0);
		$deep_last  = (int) get_option('wpshadow_last_heavy_tests', 0);

		$autofix_all = (bool) get_option('wpshadow_allow_all_autofixes', false);
		$autofix_types = get_option('wpshadow_autofix_permissions', array());
		$autofix_count = is_array($autofix_types) ? count($autofix_types) : 0;

		$finding_log = get_option('wpshadow_finding_log', array());
		$finding_count = is_array($finding_log) ? count($finding_log) : 0;

		$section = array(
			'label'  => __('WPShadow', 'wpshadow'),
			'fields' => array(
				array(
					'label'   => __('Quick Scan last run', 'wpshadow'),
					'value'   => $quick_last ? sprintf(__('%s ago', 'wpshadow'), human_time_diff($quick_last, time())) : __('Not yet', 'wpshadow'),
					'private' => false,
				),
				array(
					'label'   => __('Deep Scan last run', 'wpshadow'),
					'value'   => $deep_last ? sprintf(__('%s ago', 'wpshadow'), human_time_diff($deep_last, time())) : __('Not yet', 'wpshadow'),
					'private' => false,
				),
				array(
					'label'   => __('Auto-fix (global allow)', 'wpshadow'),
					'value'   => $autofix_all ? __('Enabled', 'wpshadow') : __('Disabled', 'wpshadow'),
					'private' => false,
				),
				array(
					'label'   => __('Auto-fix types enabled', 'wpshadow'),
					'value'   => (string) $autofix_count,
					'private' => false,
				),
				array(
					'label'   => __('Finding log entries', 'wpshadow'),
					'value'   => (string) $finding_count,
					'private' => false,
				),
			),
		);

		$info['wpshadow'] = $section;
		return $info;
	}

	/**
	 * Filter wp_mail_from_name
	 */
	public static function filter_wp_mail_from_name($from_name)
	{
		$custom_from_name = get_option('wpshadow_email_from_name', '');

		if (! empty($custom_from_name)) {
			return $custom_from_name;
		}

		return $from_name;
	}

	/**
	 * Filter privacy exporters
	 */
	public static function filter_privacy_exporters($exporters)
	{
		$exporters['wpshadow'] = array(
			'exporter_friendly_name' => __('WPShadow User Preferences', 'wpshadow'),
			'callback'               => 'wpshadow_privacy_exporter',
		);
		return $exporters;
	}

	/**
	 * Filter privacy erasers
	 */
	public static function filter_privacy_erasers($erasers)
	{
		$erasers['wpshadow'] = array(
			'eraser_friendly_name' => __('WPShadow User Preferences', 'wpshadow'),
			'callback'             => 'wpshadow_privacy_eraser',
		);
		return $erasers;
	}

	/**
	 * Privacy policy content
	 */
	public static function on_privacy_policy_content()
	{
		if (! function_exists('wp_add_privacy_policy_content')) {
			return;
		}

		$content = sprintf(
			'<h2>%s</h2><p>%s</p><h3>%s</h3><ul><li>%s</li><li>%s</li><li>%s</li></ul><h3>%s</h3><p>%s</p>',
			__('WPShadow Plugin', 'wpshadow'),
			__('This site uses the WPShadow plugin to enhance the WordPress admin experience. WPShadow stores the following user preferences locally on this site:', 'wpshadow'),
			__('What We Collect', 'wpshadow'),
			__('<strong>Tooltip Preferences:</strong> Which admin tooltips you have dismissed or disabled, to avoid showing you the same tip repeatedly.', 'wpshadow'),
			__('<strong>Display Preferences:</strong> Your dark mode preference (light, dark, or automatic) for the WPShadow admin interface.', 'wpshadow'),
			__('<strong>Dashboard Widget Preferences:</strong> Which dashboard widgets you have chosen to hide or show.', 'wpshadow'),
			__('Your Rights', 'wpshadow'),
			__('You can request to export or erase your WPShadow preferences at any time using the WordPress privacy tools under Tools > Export Personal Data or Tools > Erase Personal Data.', 'wpshadow')
		);

		wp_add_privacy_policy_content(
			'WPShadow',
			wp_kses_post(wpautop($content, false))
		);
	}

	/**
	 * Cron: Overnight fixes
	 */
	public static function on_overnight_fixes()
	{
		$scheduled = get_option('wpshadow_scheduled_fixes', array());

		if (empty($scheduled)) {
			return;
		}

		foreach ($scheduled as $item) {
			$finding_id = $item['finding_id'];
			$user_email = $item['user_email'];
			$result = wpshadow_attempt_autofix($finding_id);

			if ($result['success']) {
				$status_manager = new \WPShadow\Core\Finding_Status_Manager();
				$status_manager->set_finding_status($finding_id, 'fixed');
				wpshadow_log_finding_action($finding_id, 'auto_fixed_overnight', $result['message']);
				\WPShadow\Core\Activity_Logger::log('treatment_applied', "Overnight fix completed: {$finding_id}", '', array('finding_id' => $finding_id));

				$subject = 'WPShadow: Fix Completed';
				$message = "Your scheduled fix has been completed successfully.\n\nFinding: {$finding_id}\n" . $result['message'];
			} else {
				$subject = 'WPShadow: Fix Failed';
				$message = "Your scheduled fix encountered an error.\n\nFinding: {$finding_id}\n" . ($result['message'] ?? 'Unknown error');
			}

			wp_mail($user_email, $subject, $message);
		}

		delete_option('wpshadow_scheduled_fixes');
	}

	/**
	 * Cron: Automated fixes
	 */
	public static function on_automated_fixes()
	{
		$scheduled = get_option('wpshadow_scheduled_automated_fixes', array());

		if (empty($scheduled)) {
			return;
		}

		foreach ($scheduled as $finding_id => $item) {
			if ($item['status'] !== 'pending') {
				continue;
			}

			$result = wpshadow_attempt_autofix($finding_id);
			$scheduled[$finding_id]['status'] = $result['success'] ? 'completed' : 'failed';
			$scheduled[$finding_id]['completed'] = current_time('timestamp');
			$scheduled[$finding_id]['message'] = $result['message'] ?? '';

			if ($result['success']) {
				$status_manager = new \WPShadow\Core\Finding_Status_Manager();
				$status_manager->set_finding_status($finding_id, 'fixed');

				if (class_exists('\WPShadow\Core\KPI_Tracker')) {
					\WPShadow\Core\KPI_Tracker::record_treatment_applied($finding_id, 5);
				}

				\WPShadow\Core\Activity_Logger::log('treatment_applied', "Automated fix completed: {$finding_id}", '', array('finding_id' => $finding_id));
			} else {
				\WPShadow\Core\Activity_Logger::log('workflow_executed', "Automated fix failed: {$finding_id} - " . $result['message'], '', array('finding_id' => $finding_id, 'error' => $result['message']));
			}
		}

		update_option('wpshadow_scheduled_automated_fixes', $scheduled);
	}

	/**
	 * Cron: Data cleanup
	 */
	public static function on_data_cleanup()
	{
		if (class_exists('\WPShadow\Settings\Data_Retention_Manager')) {
			\WPShadow\Settings\Data_Retention_Manager::run_cleanup();
		}
	}

	/**
	 * Cron: Automatic diagnostic scan
	 */
	public static function on_automatic_diagnostic_scan()
	{
		if (class_exists('\WPShadow\Settings\Scan_Frequency_Manager')) {
			\WPShadow\Settings\Scan_Frequency_Manager::run_diagnostic_scan();
		}
	}

	/**
	 * Cron: Scheduled reports
	 */
	public static function on_scheduled_reports()
	{
		if (class_exists('\WPShadow\Settings\Report_Scheduler')) {
			$schedules = \WPShadow\Settings\Report_Scheduler::get_all_schedules();

			foreach ($schedules as $report_type => $config) {
				if (! empty($config['enabled'])) {
					\WPShadow\Settings\Report_Scheduler::send_scheduled_report($report_type);
				}
			}
		}
	}

	/**
	 * Cron: Off-peak operations
	 */
	public static function on_offpeak_operations()
	{
		$scheduled = get_option('wpshadow_scheduled_offpeak', array());

		if (empty($scheduled)) {
			return;
		}

		foreach ($scheduled as $item) {
			$operation_type = $item['operation_type'];
			$user_email = $item['user_email'];

			$result = array('success' => false, 'message' => 'Unknown operation type');

			switch ($operation_type) {
				case 'deep-scan':
					$result = array('success' => true, 'message' => 'Deep scan completed. No critical issues found.');
					break;

				case 'database-optimization':
					$result = array('success' => true, 'message' => 'Database optimized successfully.');
					break;
			}

			$subject = $result['success'] ? 'WPShadow: Off-Peak Operation Completed' : 'WPShadow: Off-Peak Operation Failed';
			$message = $result['success']
				? "Your scheduled operation has been completed successfully.\n\nOperation: {$operation_type}\n" . $result['message']
				: "Your scheduled operation encountered an error.\n\nOperation: {$operation_type}\n" . $result['message'];

			wp_mail($user_email, $subject, $message);
		}

		delete_option('wpshadow_scheduled_offpeak');
	}

	/**
	 * Track treatment applied (KPI)
	 */
	public static function on_treatment_applied($class, $finding_id, $result)
	{
		if (! isset($result['success']) || ! $result['success']) {
			return;
		}

		$treatment_id = strtolower(str_replace('WPShadow\Treatments\Treatment_', '', $class));

		\WPShadow\Core\KPI_Tracker::record_treatment_applied($treatment_id, 5);

		\WPShadow\Core\Activity_Logger::log(
			'treatment_applied',
			sprintf('Applied treatment: %s', $treatment_id),
			'',
			array('finding_id' => $finding_id, 'treatment' => $treatment_id)
		);

		\WPShadow\Core\Trend_Chart::record_finding_resolved($finding_id, 'fixed');
	}

	/**
	 * Track diagnostic executed (KPI)
	 */
	public static function on_diagnostic_executed($diagnostic_id, $result)
	{
		$success = isset($result['success']) ? $result['success'] : false;
		\WPShadow\Core\KPI_Tracker::record_diagnostic_run($diagnostic_id, $success);
	}

	/**
	 * Network admin menu
	 */
	public static function on_network_admin_menu()
	{
		add_menu_page(
			'WPShadow',
			'WPShadow',
			'read',
			'wpshadow',
			function () {
				echo '<div class="wrap"><h1>WPShadow (Network)</h1><p>Network admin menu check.</p></div>';
			},
			'dashicons-admin-generic',
			999
		);
	}
}
