<?php
/**
 * Workflow Block Registry - Defines available triggers and actions for visual builder
 *
 * @package WPShadow
 * @subpackage Workflow
 */

namespace WPShadow\Workflow;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registry for workflow blocks (triggers and actions)
 */
class Block_Registry {

	/**
	 * Get all available trigger blocks
	 */
	public static function get_triggers() {
		return array(
			'time_trigger' => array(
				'label'       => 'Time Trigger',
				'description' => 'Run when clock reaches specific time',
				'icon'        => 'dashicons-clock',
				'color'       => '#3b82f6',
				'fields'      => array(
					'time' => array(
						'label'   => 'Time (24-hour format)',
						'type'    => 'time',
						'default' => '02:00',
					),
					'days' => array(
						'label'   => 'Days to run',
						'type'    => 'checkbox_group',
						'options' => array(
							'monday'    => 'Monday',
							'tuesday'   => 'Tuesday',
							'wednesday' => 'Wednesday',
							'thursday'  => 'Thursday',
							'friday'    => 'Friday',
							'saturday'  => 'Saturday',
							'sunday'    => 'Sunday',
						),
						'default' => array( 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday' ),
					),
				),
			),
			'condition_trigger' => array(
				'label'       => 'Condition Trigger',
				'description' => 'Run when condition is met',
				'icon'        => 'dashicons-yes-alt',
				'color'       => '#8b5cf6',
				'fields'      => array(
					'condition_type' => array(
						'label'   => 'Condition Type',
						'type'    => 'select',
						'options' => array(
							'memory_low'          => 'Memory Usage is Low',
							'memory_high'         => 'Memory Usage is High',
							'plugins_outdated'    => 'Plugins are Outdated',
							'disk_space_low'      => 'Disk Space is Low',
							'ssl_invalid'         => 'SSL Certificate Invalid',
							'backup_missing'      => 'Backup Not Found',
							'debug_mode_enabled'  => 'Debug Mode Enabled',
							'custom_php'          => 'Custom PHP Condition',
						),
						'default' => 'memory_high',
					),
					'threshold' => array(
						'label'   => 'Threshold Value',
						'type'    => 'number',
						'default' => 80,
					),
					'custom_condition' => array(
						'label'   => 'Custom PHP Condition',
						'type'    => 'textarea',
						'default' => '',
					),
				),
			),
			'event_trigger' => array(
				'label'       => 'Event Trigger',
				'description' => 'Run when specific event happens',
				'icon'        => 'dashicons-bell',
				'color'       => '#ec4899',
				'fields'      => array(
					'event_type' => array(
						'label'   => 'Event Type',
						'type'    => 'select',
						'options' => array(
							'plugin_activated'   => 'Plugin Activated',
							'plugin_deactivated' => 'Plugin Deactivated',
							'theme_changed'      => 'Theme Changed',
							'user_registered'    => 'User Registered',
							'post_published'     => 'Post Published',
							'post_deleted'       => 'Post Deleted',
							'comment_posted'     => 'Comment Posted',
						),
						'default' => 'post_published',
					),
				),
			),
			'page_load_trigger' => array(
				'label'       => 'Page Load Trigger',
				'description' => 'Run on every page load (frontend or admin)',
				'icon'        => 'dashicons-update',
				'color'       => '#06b6d4',
				'fields'      => array(
					'page_context' => array(
						'label'   => 'Where to Run',
						'type'    => 'select',
						'options' => array(
							'all'              => 'All Pages (Frontend + Admin)',
							'frontend'         => 'All Frontend Pages',
							'admin'            => 'All Admin Pages',
							'frontend_pages'   => 'Frontend: Pages Only',
							'frontend_posts'   => 'Frontend: Posts Only',
							'frontend_single'  => 'Frontend: Single Posts/Pages',
							'frontend_archive' => 'Frontend: Archives/Categories',
							'frontend_category' => 'Frontend: Category Pages',
							'frontend_home'    => 'Frontend: Home/Front Page',
						),
						'default' => 'all',
					),
				),
			),
		);
	}

	/**
	 * Get all available action blocks
	 */
	public static function get_actions() {
		return array(
			'run_diagnostic' => array(
				'label'       => 'Run Diagnostic',
				'description' => 'Execute a health diagnostic scan',
				'icon'        => 'dashicons-clipboard',
				'color'       => '#10b981',
				'fields'      => array(
					'diagnostic_type' => array(
						'label'   => 'Diagnostic Type',
						'type'    => 'select',
						'options' => array(
							'full'       => 'Full Health Scan',
							'memory'     => 'Memory Check',
							'plugins'    => 'Plugin Audit',
							'ssl'        => 'SSL Check',
							'backup'     => 'Backup Verification',
							'performance' => 'Performance Audit',
							'specific'   => 'Specific Diagnostic',
						),
						'default' => 'full',
					),
					'specific_diagnostic' => array(
						'label'   => 'Specific Diagnostic (if selected above)',
						'type'    => 'select',
						'options' => array(
							''                    => 'Select a diagnostic...',
							'external_fonts'      => 'External Fonts',
							'memory_limit'        => 'Memory Limit',
							'backup'              => 'Backup Status',
							'permalinks'          => 'Permalinks',
							'ssl'                 => 'SSL Certificate',
							'outdated_plugins'    => 'Outdated Plugins',
							'debug_mode'          => 'Debug Mode',
							'plugin_count'        => 'Plugin Count',
							'inactive_plugins'    => 'Inactive Plugins',
							'hotlink_protection'  => 'Hotlink Protection',
							'head_cleanup'        => 'Head Cleanup',
							'iframe_busting'      => 'iFrame Busting',
							'image_lazy_load'     => 'Image Lazy Load',
							'plugin_auto_updates' => 'Plugin Auto Updates',
						),
						'default' => '',
					),
				),
			),
			'apply_treatment' => array(
				'label'       => 'Apply Treatment',
				'description' => 'Apply an automatic fix/treatment',
				'icon'        => 'dashicons-admin-tools',
				'color'       => '#8b5cf6',
				'fields'      => array(
					'specific_treatment' => array(
						'label'   => 'Treatment to Apply',
						'type'    => 'select',
						'options' => array(
							''                    => 'Select a treatment...',
							'external_fonts'      => 'Block External Fonts',
							'permalinks'          => 'Fix Permalinks',
							'memory_limit'        => 'Increase Memory Limit',
							'debug_mode'          => 'Disable Debug Mode',
							'ssl'                 => 'Fix SSL Issues',
							'inactive_plugins'    => 'Clean Inactive Plugins',
							'outdated_plugins'    => 'Update Outdated Plugins',
							'hotlink_protection'  => 'Enable Hotlink Protection',
							'head_cleanup'        => 'Clean WP Head',
							'iframe_busting'      => 'Enable iFrame Busting',
							'image_lazy_load'     => 'Enable Image Lazy Load',
							'plugin_auto_updates' => 'Enable Plugin Auto Updates',
						),
						'default' => '',
					),
					'halt_on_error' => array(
						'label'   => 'Stop workflow if this fails',
						'type'    => 'checkbox',
						'default' => false,
					),
				),
			),
			'send_email' => array(
				'label'       => 'Send Email',
				'description' => 'Send email notification',
				'icon'        => 'dashicons-email-alt',
				'color'       => '#f59e0b',
				'fields'      => array(
					'recipient' => array(
						'label'   => 'Send To',
						'type'    => 'select',
						'options' => array(
							'admin'  => 'Site Admin',
							'custom' => 'Custom Email',
						),
						'default' => 'admin',
					),
					'custom_email' => array(
						'label'   => 'Custom Email Address',
						'type'    => 'email',
						'default' => '',
					),
					'subject' => array(
						'label'   => 'Email Subject',
						'type'    => 'text',
						'default' => 'WPShadow Report',
					),
					'message' => array(
						'label'   => 'Email Message',
						'type'    => 'textarea',
						'default' => 'WPShadow automated report: {report_data}',
					),
					'include_report' => array(
						'label' => 'Include Diagnostic Report',
						'type'  => 'checkbox',
					),
				),
			),
			'autofix' => array(
				'label'       => 'Auto-Fix',
				'description' => 'Automatically fix detected issues',
				'icon'        => 'dashicons-hammer',
				'color'       => '#ef4444',
				'fields'      => array(
					'fix_type' => array(
						'label'   => 'What to Fix',
						'type'    => 'select',
						'options' => array(
							'all_findings'       => 'All Available Findings',
							'memory_issues'      => 'Memory Issues',
							'ssl_issues'         => 'SSL Issues',
							'plugin_updates'     => 'Plugin Updates',
							'debug_mode'         => 'Debug Mode',
						),
						'default' => 'all_findings',
					),
				),
			),
			'backup' => array(
				'label'       => 'Create Backup',
				'description' => 'Create database or full site backup',
				'icon'        => 'dashicons-cloud-saved',
				'color'       => '#06b6d4',
				'fields'      => array(
					'backup_type' => array(
						'label'   => 'Backup Type',
						'type'    => 'select',
						'options' => array(
							'database'  => 'Database Only',
							'files'     => 'Files Only',
							'full'      => 'Full Site Backup',
						),
						'default' => 'database',
					),
				),
			),
			'notification' => array(
				'label'       => 'Send Notification',
				'description' => 'Send in-app notification',
				'icon'        => 'dashicons-format-chat',
				'color'       => '#a855f7',
				'fields'      => array(
					'title' => array(
						'label'   => 'Notification Title',
						'type'    => 'text',
						'default' => 'WPShadow Notification',
					),
					'message' => array(
						'label'   => 'Notification Message',
						'type'    => 'textarea',
						'default' => '',
					),
					'type' => array(
						'label'   => 'Notification Type',
						'type'    => 'select',
						'options' => array(
							'info'    => 'Info',
							'success' => 'Success',
							'warning' => 'Warning',
							'error'   => 'Error',
						),
						'default' => 'info',
					),
				),
			),
			'slack' => array(
				'label'       => 'Send to Slack',
				'description' => 'Send message to Slack webhook',
				'icon'        => 'dashicons-share',
				'color'       => '#7c3aed',
				'fields'      => array(
					'webhook_url' => array(
						'label'   => 'Slack Webhook URL',
						'type'    => 'text',
						'default' => '',
					),
					'message' => array(
						'label'   => 'Message',
						'type'    => 'textarea',
						'default' => 'WPShadow workflow executed',
					),
				),
			),
			'log_action' => array(
				'label'       => 'Log Action',
				'description' => 'Log action to activity log',
				'icon'        => 'dashicons-editor-ul',
				'color'       => '#64748b',
				'fields'      => array(
					'log_message' => array(
						'label'   => 'Log Message',
						'type'    => 'textarea',
						'default' => 'Workflow action executed',
					),
				),
			),
		);
	}

	/**
	 * Get block by ID
	 */
	public static function get_block( $id, $type = 'action' ) {
		if ( 'trigger' === $type ) {
			$triggers = self::get_triggers();
			return $triggers[ $id ] ?? null;
		}

		$actions = self::get_actions();
		return $actions[ $id ] ?? null;
	}

	/**
	 * Validate block configuration
	 */
	public static function validate_block( $block ) {
		if ( empty( $block['id'] ) || empty( $block['type'] ) ) {
			return array( 'valid' => false, 'error' => 'Missing block ID or type' );
		}

		$id   = $block['id'];
		$type = $block['type'];

		if ( 'trigger' === $type ) {
			$triggers = self::get_triggers();
			if ( ! isset( $triggers[ $id ] ) ) {
				return array( 'valid' => false, 'error' => "Unknown trigger: {$id}" );
			}
		} elseif ( 'action' === $type ) {
			$actions = self::get_actions();
			if ( ! isset( $actions[ $id ] ) ) {
				return array( 'valid' => false, 'error' => "Unknown action: {$id}" );
			}
		} else {
			return array( 'valid' => false, 'error' => 'Invalid block type' );
		}

		return array( 'valid' => true );
	}
}
