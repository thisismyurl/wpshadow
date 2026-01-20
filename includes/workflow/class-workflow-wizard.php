<?php
/**
 * IFTTT-style Workflow Wizard
 *
 * @package WPShadow
 * @subpackage Workflow
 */

namespace WPShadow\Workflow;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Workflow Wizard class - step-by-step workflow creation
 */
class Workflow_Wizard {

	/**
	 * Get trigger categories and their triggers
	 *
	 * @return array Categorized triggers
	 */
	public static function get_trigger_categories() {
		return array(
			'schedule' => array(
				'label' => 'Schedule',
				'icon'  => 'clock',
				'triggers' => array(
					'time_daily' => array(
						'label' => 'Every Day',
						'description' => 'Run at a specific time every day',
						'icon' => 'clock',
					),
					'time_weekly' => array(
						'label' => 'Every Week',
						'description' => 'Run on specific days at a specific time',
						'icon' => 'calendar',
					),
					'time_hourly' => array(
						'label' => 'Every Hour',
						'description' => 'Run at the top of every hour',
						'icon' => 'backup',
					),
				),
			),
			'page_load' => array(
				'label' => 'Page Load',
				'icon'  => 'desktop',
				'triggers' => array(
					'page_load_all' => array(
						'label' => 'Every Page Load',
						'description' => 'Runs on every page load (frontend and admin)',
						'icon' => 'admin-site',
					),
					'page_load_frontend' => array(
						'label' => 'Frontend Page Load',
						'description' => 'Runs on frontend page loads only',
						'icon' => 'visibility',
					),
					'page_load_admin' => array(
						'label' => 'Admin Page Load',
						'description' => 'Runs on admin page loads only',
						'icon' => 'admin-settings',
					),
					'page_load_single' => array(
						'label' => 'Single Post/Page Load',
						'description' => 'Runs when viewing a single post or page',
						'icon' => 'admin-post',
					),
					'page_load_archive' => array(
						'label' => 'Archive Page Load',
						'description' => 'Runs on archive, category, or tag pages',
						'icon' => 'category',
					),
					'page_load_home' => array(
						'label' => 'Homepage Load',
						'description' => 'Runs only on the homepage',
						'icon' => 'admin-home',
					),
				),
			),
			'events' => array(
				'label' => 'Events',
				'icon'  => 'controls-play',
				'triggers' => array(
					'plugin_activated' => array(
						'label' => 'Plugin Activated',
						'description' => 'When any plugin is activated',
						'icon' => 'admin-plugins',
					),
					'plugin_deactivated' => array(
						'label' => 'Plugin Deactivated',
						'description' => 'When any plugin is deactivated',
						'icon' => 'admin-plugins',
					),
					'theme_switched' => array(
						'label' => 'Theme Changed',
						'description' => 'When the active theme is changed',
						'icon' => 'admin-appearance',
					),
					'user_login' => array(
						'label' => 'User Login',
						'description' => 'When a user logs in',
						'icon' => 'admin-users',
					),
					'user_register' => array(
						'label' => 'User Registration',
						'description' => 'When a new user registers',
						'icon' => 'admin-users',
					),
					'post_published' => array(
						'label' => 'Post Published',
						'description' => 'When a post is published',
						'icon' => 'edit',
					),
					'comment_posted' => array(
						'label' => 'Comment Posted',
						'description' => 'When a comment is posted',
						'icon' => 'admin-comments',
					),
				),
			),
			'conditions' => array(
				'label' => 'Conditions',
				'icon'  => 'shield',
				'triggers' => array(
					'high_memory' => array(
						'label' => 'High Memory Usage',
						'description' => 'When memory usage exceeds a threshold',
						'icon' => 'performance',
					),
					'debug_mode_on' => array(
						'label' => 'Debug Mode Enabled',
						'description' => 'When WP_DEBUG is enabled on production',
						'icon' => 'warning',
					),
					'ssl_issue' => array(
						'label' => 'SSL Problem Detected',
						'description' => 'When SSL configuration issues are found',
						'icon' => 'lock',
					),
					'too_many_plugins' => array(
						'label' => 'Too Many Plugins',
						'description' => 'When plugin count exceeds a threshold',
						'icon' => 'admin-plugins',
					),
					'ip_banned' => array(
						'label' => 'Banned IP Detected',
						'description' => 'When a banned IP tries to access the site',
						'icon' => 'dismiss',
					),
				),
			),
		);
	}

	/**
	 * Get actions suitable for a specific trigger
	 *
	 * @param string $trigger_type Trigger type
	 * @return array Available actions
	 */
	public static function get_available_actions( $trigger_type = '' ) {
		$all_actions = array(
			'diagnostics' => array(
				'label' => 'Diagnostics',
				'icon'  => 'search',
				'actions' => array(
					'run_full_scan' => array(
						'label' => 'Run Full Health Scan',
						'description' => 'Run all available diagnostics',
						'icon' => 'admin-tools',
					),
					'check_external_fonts' => array(
						'label' => 'Check External Fonts',
						'description' => 'Scan for external font loading',
						'icon' => 'admin-appearance',
					),
					'check_memory' => array(
						'label' => 'Check Memory Usage',
						'description' => 'Analyze PHP memory configuration',
						'icon' => 'performance',
					),
					'check_ssl' => array(
						'label' => 'Check SSL Configuration',
						'description' => 'Verify SSL/HTTPS setup',
						'icon' => 'lock',
					),
					'check_plugins' => array(
						'label' => 'Check Plugin Health',
						'description' => 'Scan for outdated or inactive plugins',
						'icon' => 'admin-plugins',
					),
					'check_security' => array(
						'label' => 'Security Scan',
						'description' => 'Run security-focused diagnostics',
						'icon' => 'shield',
					),
				),
			),
			'treatments' => array(
				'label' => 'Fixes',
				'icon'  => 'admin-tools',
				'actions' => array(
					'block_external_fonts' => array(
						'label' => 'Block External Fonts',
						'description' => 'Disable external font loading',
						'icon' => 'admin-appearance',
					),
					'increase_memory' => array(
						'label' => 'Increase Memory Limit',
						'description' => 'Increase PHP memory limit',
						'icon' => 'performance',
					),
					'disable_debug' => array(
						'label' => 'Disable Debug Mode',
						'description' => 'Turn off WP_DEBUG',
						'icon' => 'warning',
					),
					'fix_ssl' => array(
						'label' => 'Fix SSL Issues',
						'description' => 'Correct SSL configuration',
						'icon' => 'lock',
					),
					'cleanup_plugins' => array(
						'label' => 'Clean Inactive Plugins',
						'description' => 'Remove inactive plugins',
						'icon' => 'admin-plugins',
					),
					'block_ip' => array(
						'label' => 'Block IP Address',
						'description' => 'Block the current IP',
						'icon' => 'dismiss',
					),
				),
			),
			'notifications' => array(
				'label' => 'Notifications',
				'icon'  => 'email',
				'actions' => array(
					'send_email' => array(
						'label' => 'Send Email',
						'description' => 'Send an email notification',
						'icon' => 'email',
					),
					'send_notification' => array(
						'label' => 'In-App Notification',
						'description' => 'Show notification in WordPress admin',
						'icon' => 'bell',
					),
					'send_slack' => array(
						'label' => 'Send Slack Message',
						'description' => 'Post message to Slack channel',
						'icon' => 'share',
					),
				),
			),
			'logging' => array(
				'label' => 'Logging',
				'icon'  => 'list-view',
				'actions' => array(
					'log_activity' => array(
						'label' => 'Log Activity',
						'description' => 'Record action to activity log',
						'icon' => 'list-view',
					),
					'create_backup' => array(
						'label' => 'Create Backup Point',
						'description' => 'Save current state',
						'icon' => 'backup',
					),
				),
			),
		);

		// Filter actions based on trigger type (optional)
		// For now, return all actions
		return $all_actions;
	}

	/**
	 * Get configuration fields for a specific trigger
	 *
	 * @param string $trigger_id Trigger ID
	 * @return array Configuration fields
	 */
	public static function get_trigger_config( $trigger_id ) {
		$configs = array(
			'time_daily' => array(
				array(
					'id'          => 'time',
					'type'        => 'time',
					'label'       => 'What time?',
					'placeholder' => '14:00',
					'default'     => '02:00',
					'required'    => true,
				),
			),
			'time_weekly' => array(
				array(
					'id'       => 'days',
					'type'     => 'checkbox_group',
					'label'    => 'Which days?',
					'options'  => array(
						'monday'    => 'Monday',
						'tuesday'   => 'Tuesday',
						'wednesday' => 'Wednesday',
						'thursday'  => 'Thursday',
						'friday'    => 'Friday',
						'saturday'  => 'Saturday',
						'sunday'    => 'Sunday',
					),
					'default'  => array( 'sunday' ),
					'required' => true,
				),
				array(
					'id'          => 'time',
					'type'        => 'time',
					'label'       => 'What time?',
					'placeholder' => '14:00',
					'default'     => '14:00',
					'required'    => true,
				),
			),
			'page_load_frontend' => array(
				array(
					'id'       => 'post_types',
					'type'     => 'checkbox_group',
					'label'    => 'Which post types? (optional)',
					'options'  => array(
						'post' => 'Posts',
						'page' => 'Pages',
						'all'  => 'All Types',
					),
					'default'  => array( 'all' ),
					'required' => false,
				),
			),
			'page_load_single' => array(
				array(
					'id'       => 'post_types',
					'type'     => 'checkbox_group',
					'label'    => 'Which post types?',
					'options'  => array(
						'post' => 'Posts',
						'page' => 'Pages',
						'all'  => 'All Types',
					),
					'default'  => array( 'all' ),
					'required' => true,
				),
			),
			'user_login' => array(
				array(
					'id'          => 'user_id',
					'type'        => 'text',
					'label'       => 'Specific user ID? (optional)',
					'placeholder' => 'Leave blank for any user',
					'default'     => '',
					'required'    => false,
				),
				array(
					'id'          => 'user_role',
					'type'        => 'select',
					'label'       => 'User role? (optional)',
					'options'     => array(
						''              => 'Any role',
						'administrator' => 'Administrator',
						'editor'        => 'Editor',
						'author'        => 'Author',
						'contributor'   => 'Contributor',
						'subscriber'    => 'Subscriber',
					),
					'default'     => '',
					'required'    => false,
				),
			),
			'high_memory' => array(
				array(
					'id'          => 'threshold',
					'type'        => 'number',
					'label'       => 'Memory threshold (%)',
					'placeholder' => '85',
					'default'     => '85',
					'required'    => true,
					'min'         => 50,
					'max'         => 100,
				),
			),
			'too_many_plugins' => array(
				array(
					'id'          => 'count',
					'type'        => 'number',
					'label'       => 'Maximum plugins',
					'placeholder' => '20',
					'default'     => '20',
					'required'    => true,
					'min'         => 5,
					'max'         => 100,
				),
			),
			'ip_banned' => array(
				array(
					'id'          => 'ip_list',
					'type'        => 'textarea',
					'label'       => 'IP addresses to block',
					'placeholder' => 'One IP per line',
					'default'     => '',
					'required'    => true,
					'rows'        => 5,
				),
			),
		);

		return isset( $configs[ $trigger_id ] ) ? $configs[ $trigger_id ] : array();
	}

	/**
	 * Get configuration fields for a specific action
	 *
	 * @param string $action_id Action ID
	 * @return array Configuration fields
	 */
	public static function get_action_config( $action_id ) {
		$configs = array(
			'send_email' => array(
				array(
					'id'          => 'to',
					'type'        => 'text',
					'label'       => 'Send to',
					'placeholder' => 'admin@example.com',
					'default'     => get_option( 'admin_email' ),
					'required'    => true,
				),
				array(
					'id'          => 'subject',
					'type'        => 'text',
					'label'       => 'Subject',
					'placeholder' => 'WPShadow Alert',
					'default'     => 'WPShadow Workflow Notification',
					'required'    => true,
				),
				array(
					'id'          => 'message',
					'type'        => 'textarea',
					'label'       => 'Message',
					'placeholder' => 'Enter your message here...',
					'default'     => '',
					'required'    => true,
					'rows'        => 5,
				),
			),
			'send_notification' => array(
				array(
					'id'          => 'message',
					'type'        => 'text',
					'label'       => 'Notification message',
					'placeholder' => 'Action completed successfully',
					'default'     => '',
					'required'    => true,
				),
				array(
					'id'       => 'type',
					'type'     => 'select',
					'label'    => 'Notification type',
					'options'  => array(
						'success' => 'Success',
						'warning' => 'Warning',
						'error'   => 'Error',
						'info'    => 'Info',
					),
					'default'  => 'success',
					'required' => true,
				),
			),
			'send_slack' => array(
				array(
					'id'          => 'webhook_url',
					'type'        => 'text',
					'label'       => 'Slack Webhook URL',
					'placeholder' => 'https://hooks.slack.com/...',
					'default'     => '',
					'required'    => true,
				),
				array(
					'id'          => 'message',
					'type'        => 'textarea',
					'label'       => 'Message',
					'placeholder' => 'Enter your message here...',
					'default'     => '',
					'required'    => true,
					'rows'        => 3,
				),
			),
			'log_activity' => array(
				array(
					'id'          => 'message',
					'type'        => 'text',
					'label'       => 'Log message',
					'placeholder' => 'What happened?',
					'default'     => '',
					'required'    => true,
				),
			),
			'block_ip' => array(
				array(
					'id'          => 'ip',
					'type'        => 'text',
					'label'       => 'IP address',
					'placeholder' => 'Leave blank to block current visitor',
					'default'     => '',
					'required'    => false,
				),
			),
		);

		return isset( $configs[ $action_id ] ) ? $configs[ $action_id ] : array();
	}

	/**
	 * Convert wizard workflow to executor format
	 *
	 * @param array $wizard_data Wizard data from frontend
	 * @return array Workflow in executor format
	 */
	public static function convert_to_executor_format( $wizard_data ) {
		$workflow = array(
			'id'      => isset( $wizard_data['id'] ) ? $wizard_data['id'] : wp_generate_uuid4(),
			'name'    => $wizard_data['name'],
			'enabled' => true,
			'trigger' => self::convert_trigger( $wizard_data['trigger'] ),
			'actions' => array(),
		);

		// Convert actions
		if ( ! empty( $wizard_data['actions'] ) ) {
			foreach ( $wizard_data['actions'] as $action ) {
				$workflow['actions'][] = self::convert_action( $action );
			}
		}

		return $workflow;
	}

	/**
	 * Convert wizard trigger to executor format
	 *
	 * @param array $trigger Trigger data
	 * @return array Converted trigger
	 */
	private static function convert_trigger( $trigger ) {
		$type = $trigger['type'];
		$config = $trigger['config'];

		// Map wizard trigger types to executor trigger types
		$type_map = array(
			'time_daily'            => 'time_trigger',
			'time_weekly'           => 'time_trigger',
			'time_hourly'           => 'time_trigger',
			'page_load_all'         => 'page_load_trigger',
			'page_load_frontend'    => 'page_load_trigger',
			'page_load_admin'       => 'page_load_trigger',
			'page_load_single'      => 'page_load_trigger',
			'page_load_archive'     => 'page_load_trigger',
			'page_load_home'        => 'page_load_trigger',
			'plugin_activated'      => 'event_trigger',
			'plugin_deactivated'    => 'event_trigger',
			'theme_switched'        => 'event_trigger',
			'user_login'            => 'event_trigger',
			'user_register'         => 'event_trigger',
			'post_published'        => 'event_trigger',
			'comment_posted'        => 'event_trigger',
			'high_memory'           => 'condition_trigger',
			'debug_mode_on'         => 'condition_trigger',
			'ssl_issue'             => 'condition_trigger',
			'too_many_plugins'      => 'condition_trigger',
			'ip_banned'             => 'condition_trigger',
		);

		$executor_type = isset( $type_map[ $type ] ) ? $type_map[ $type ] : $type;

		return array(
			'type'   => $executor_type,
			'config' => $config,
		);
	}

	/**
	 * Convert wizard action to executor format
	 *
	 * @param array $action Action data
	 * @return array Converted action
	 */
	private static function convert_action( $action ) {
		$type = $action['type'];
		$config = $action['config'];

		// Map wizard action types to executor action types
		$type_map = array(
			'run_full_scan'         => 'run_diagnostic',
			'check_external_fonts'  => 'run_diagnostic',
			'check_memory'          => 'run_diagnostic',
			'check_ssl'             => 'run_diagnostic',
			'check_plugins'         => 'run_diagnostic',
			'check_security'        => 'run_diagnostic',
			'block_external_fonts'  => 'apply_treatment',
			'increase_memory'       => 'apply_treatment',
			'disable_debug'         => 'apply_treatment',
			'fix_ssl'               => 'apply_treatment',
			'cleanup_plugins'       => 'apply_treatment',
			'block_ip'              => 'apply_treatment',
			'send_email'            => 'email_action',
			'send_notification'     => 'notification_action',
			'send_slack'            => 'slack_action',
			'log_activity'          => 'log_action',
			'create_backup'         => 'backup_action',
		);

		$executor_type = isset( $type_map[ $type ] ) ? $type_map[ $type ] : $type;

		return array(
			'type'   => $executor_type,
			'config' => $config,
		);
	}
}
