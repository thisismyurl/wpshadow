<?php
/**
 * Workflow Builder Wizard
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
	 * Cached trigger categories.
	 *
	 * @var array|null
	 */
	private static $trigger_categories = null;

	/**
	 * Cached actions list.
	 *
	 * @var array|null
	 */
	private static $all_actions = null;

	/**
	 * Get trigger categories and their triggers
	 *
	 * @return array Categorized triggers
	 */
	public static function get_trigger_categories() {
		if ( null !== self::$trigger_categories ) {
			return self::$trigger_categories;
		}

		self::$trigger_categories = array(
			'schedule'       => array(
				'label'       => 'Scheduled Tasks',
				'description' => 'Run tasks on a regular schedule - daily, weekly, or hourly',
				'icon'        => 'clock',
				'triggers'    => array(
					'time_daily' => array(
						'label'       => 'On a Schedule',
						'description' => 'Run at specific times - daily, weekly, or hourly',
						'icon'        => 'clock',
					),
				),
			),
			'content_events' => array(
				'label'       => 'Content Events',
				'description' => 'Respond to changes in your content and comments',
				'icon'        => 'edit',
				'triggers'    => array(
					'page_load_trigger'   => array(
						'label'       => 'When Page Loads',
						'description' => 'Run when specific pages or areas of your site load',
						'icon'        => 'desktop',
					),
					'pre_publish_review'  => array(
						'label'       => 'Pre Publish Review',
						'description' => 'Run after Publish is clicked but before content is published - perfect for final checks',
						'icon'        => 'yes-alt',
					),
					'post_status_changed' => array(
						'label'       => 'Post/Page Status Changed',
						'description' => 'When a post/page status changes (published, draft, scheduled, etc)',
						'icon'        => 'edit',
					),
					'comment_posted'      => array(
						'label'       => 'Comment Posted',
						'description' => 'When a comment is posted on your site',
						'icon'        => 'admin-comments',
					),
				),
			),
			'system_events'  => array(
				'label'       => 'System & Admin Events',
				'description' => 'Respond to plugin, theme, and user management events',
				'icon'        => 'admin-tools',
				'triggers'    => array(
					'plugin_update_trigger'     => array(
						'label'       => 'Plugin/Theme Update Available',
						'description' => 'When updates are detected for plugins or themes',
						'icon'        => 'update',
					),
					'backup_completion_trigger' => array(
						'label'       => 'Backup Completion',
						'description' => 'When a backup finishes (success or failure)',
						'icon'        => 'database',
					),
					'plugin_state_changed'      => array(
						'label'       => 'Plugin Changed',
						'description' => 'When any plugin is activated or deactivated',
						'icon'        => 'admin-plugins',
					),
					'theme_switched'            => array(
						'label'       => 'Theme Changed',
						'description' => 'When the active theme is changed',
						'icon'        => 'admin-appearance',
					),
					'user_login'                => array(
						'label'       => 'User Login',
						'description' => 'When a user logs in',
						'icon'        => 'admin-users',
					),
					'user_register'             => array(
						'label'       => 'User Registration',
						'description' => 'When a new user registers',
						'icon'        => 'admin-users',
					),
				),
			),
			'conditions'     => array(
				'label'       => 'System Conditions',
				'description' => 'Trigger on specific system issues or thresholds',
				'icon'        => 'shield',
				'triggers'    => array(
					'database_trigger'  => array(
						'label'       => 'Database Issues',
						'description' => 'When database size or integrity issues are detected',
						'icon'        => 'database',
					),
					'error_log_trigger' => array(
						'label'       => 'Error Log Activity',
						'description' => 'When warnings or errors appear in logs',
						'icon'        => 'warning',
					),
					'high_memory'       => array(
						'label'       => 'High Memory Usage',
						'description' => 'When memory usage exceeds a threshold',
						'icon'        => 'performance',
					),
					'debug_mode_on'     => array(
						'label'       => 'Debug Mode Enabled',
						'description' => 'When WP_DEBUG is enabled on production',
						'icon'        => 'warning',
					),
					'ssl_issue'         => array(
						'label'       => 'SSL Problem Detected',
						'description' => 'When SSL configuration issues are found',
						'icon'        => 'lock',
					),
					'ip_banned'         => array(
						'label'       => 'Banned IP Detected',
						'description' => 'When a banned IP tries to access the site',
						'icon'        => 'dismiss',
					),
				),
			),
			'diagnostics'    => array(
				'label'       => 'Diagnostics & Monitoring',
				'description' => 'React when diagnostics run (manual, Guardian, or scheduled)',
				'icon'        => 'visibility',
				'triggers'    => array(
					'diagnostic_run_trigger' => array(
						'label'       => 'When Diagnostics Run',
						'description' => 'Fire whenever any diagnostic executes, including Guardian scans',
						'icon'        => 'visibility',
					),
				),
			),
			'manual'         => array(
				'label'       => 'Manual & External',
				'description' => 'Trigger workflows externally via URL or API',
				'icon'        => 'admin-links',
				'triggers'    => array(
					'manual_cron_trigger' => array(
						'label'       => 'Manual or External Trigger',
						'description' => 'Trigger via URL - perfect for external CRON jobs, webhooks, and scheduled external services',
						'icon'        => 'admin-links',
					),
				),
			),
		);

		return self::$trigger_categories;
	}

	/**
	 * Get actions suitable for a specific trigger
	 *
	 * @param string $trigger_type Trigger type
	 * @return array Available actions filtered and ordered by relevance
	 */
	public static function get_available_actions( $trigger_type = '' ) {
		$all_actions = self::get_all_actions();

		// If no trigger specified, return all actions
		if ( empty( $trigger_type ) ) {
			return $all_actions;
		}

		// Normalize trigger ids to action filter keys.
		$trigger_map = array(
			'time_daily'                => 'schedule',
			'page_load_trigger'         => 'page_load',
			'post_status_changed'       => 'post_status',
			'pre_publish_review'        => 'pre_publish_review',
			'comment_posted'            => 'comment',
			'plugin_update_trigger'     => 'plugin_update',
			'backup_completion_trigger' => 'backup_completion',
			'plugin_state_changed'      => 'plugin',
			'theme_switched'            => 'theme',
			'user_login'                => 'user_login',
			'user_register'             => 'user_register',
			'high_memory'               => 'memory',
			'debug_mode_on'             => 'debug',
			'ssl_issue'                 => 'ssl',
			'too_many_plugins'          => 'plugins',
			'ip_banned'                 => 'ip_ban',
			'database_trigger'          => 'database',
			'error_log_trigger'         => 'error_log',
			'diagnostic_run_trigger'    => 'diagnostic_run',
			'manual_cron_trigger'       => 'manual',
		);

		$filter_key = isset( $trigger_map[ $trigger_type ] ) ? $trigger_map[ $trigger_type ] : $trigger_type;

		// Filter and order actions based on trigger type
		$filtered_actions = self::filter_actions_by_trigger( $all_actions, $filter_key );

		if ( empty( $filtered_actions ) ) {
			return $all_actions;
		}

		return $filtered_actions;
	}

	/**
	 * Get all available actions (unfiltered)
	 *
	 * @return array All actions
	 */
	private static function get_all_actions() {
		if ( null !== self::$all_actions ) {
			return self::$all_actions;
		}

		self::$all_actions = array(
			'treatments'              => array(
				'label'   => 'Treatments',
				'icon'    => 'admin-tools',
				'actions' => array(
					'run_treatment' => array(
						'label'       => 'Run a Treatment',
						'description' => 'Apply a specific fix from the treatment list',
						'icon'        => 'admin-tools',
					),
				),
			),
			'diagnostics'             => array(
				'label'   => 'Diagnostics',
				'icon'    => 'search',
				'actions' => array(
					'run_full_scan'        => array(
						'label'       => 'Run Full Health Scan',
						'description' => 'Run all available diagnostics',
						'icon'        => 'admin-tools',
					),
					'check_external_fonts' => array(
						'label'       => 'Check External Fonts',
						'description' => 'Scan for external font loading',
						'icon'        => 'admin-appearance',
					),
					'check_memory'         => array(
						'label'       => 'Check Memory Usage',
						'description' => 'Analyze PHP memory configuration',
						'icon'        => 'performance',
					),
					'check_ssl'            => array(
						'label'       => 'Check SSL Configuration',
						'description' => 'Verify SSL/HTTPS setup',
						'icon'        => 'lock',
					),
					'check_plugins'        => array(
						'label'       => 'Check Plugin Health',
						'description' => 'Scan for outdated or inactive plugins',
						'icon'        => 'admin-plugins',
					),
					'check_security'       => array(
						'label'       => 'Security Scan',
						'description' => 'Run security-focused diagnostics',
						'icon'        => 'shield',
					),
				),
			),
			'performance'             => array(
				'label'   => 'Performance',
				'icon'    => 'performance',
				'actions' => array(
					'external_fonts'  => array(
						'label'       => 'Block External Fonts',
						'description' => 'Disable external font loading for faster pages',
						'icon'        => 'admin-appearance',
					),
					'image_lazy_load' => array(
						'label'       => 'Enable Lazy Loading',
						'description' => 'Load images only when needed',
						'icon'        => 'format-image',
					),
					'html_cleanup'    => array(
						'label'       => 'Minify HTML',
						'description' => 'Remove whitespace and comments from HTML',
						'icon'        => 'editor-code',
					),
					'head_cleanup'    => array(
						'label'       => 'Clean Up Head',
						'description' => 'Remove unnecessary tags from HTML head',
						'icon'        => 'editor-removeformatting',
					),
					'embed_disable'   => array(
						'label'       => 'Disable Embeds',
						'description' => 'Remove WordPress embed scripts',
						'icon'        => 'format-video',
					),
					'css_classes'     => array(
						'label'       => 'Clean CSS Classes',
						'description' => 'Simplify body, post, and nav classes',
						'icon'        => 'admin-appearance',
					),
					'memory_limit'    => array(
						'label'       => 'Increase Memory Limit',
						'description' => 'Increase PHP memory limit',
						'icon'        => 'performance',
					),
				),
			),
			'content'                 => array(
				'label'   => 'Content Quality',
				'icon'    => 'edit',
				'actions' => array(
					'content_optimizer' => array(
						'label'       => 'Content Optimizer',
						'description' => 'Check SEO, readability, and quality',
						'icon'        => 'yes-alt',
					),
					'paste_cleanup'     => array(
						'label'       => 'Paste Cleanup',
						'description' => 'Remove inline styles from pasted content',
						'icon'        => 'editor-paste-text',
					),
				),
			),
			'accessibility'           => array(
				'label'   => 'Accessibility',
				'icon'    => 'universal-access',
				'actions' => array(
					'nav_aria' => array(
						'label'       => 'Navigation ARIA',
						'description' => 'Add ARIA attributes to navigation menus',
						'icon'        => 'menu',
					),
				),
			),
			'security'                => array(
				'label'   => 'Security',
				'icon'    => 'shield',
				'actions' => array(
					'debug_mode' => array(
						'label'       => 'Disable Debug Mode',
						'description' => 'Turn off WP_DEBUG',
						'icon'        => 'warning',
					),
					'ssl'        => array(
						'label'       => 'Fix SSL Issues',
						'description' => 'Correct SSL configuration',
						'icon'        => 'lock',
					),
				),
			),
			'maintenance'             => array(
				'label'   => 'Maintenance',
				'icon'    => 'admin-tools',
				'actions' => array(
					'inactive_plugins' => array(
						'label'       => 'Clean Inactive Plugins',
						'description' => 'Remove inactive plugins',
						'icon'        => 'admin-plugins',
					),
					'outdated_plugins' => array(
						'label'       => 'Update Plugins',
						'description' => 'Update outdated plugins',
						'icon'        => 'update',
					),
				),
			),
			'notifications'           => array(
				'label'   => 'Notifications',
				'icon'    => 'email',
				'actions' => array(
					'send_email'        => array(
						'label'       => 'Send Email',
						'description' => 'Send an email notification',
						'icon'        => 'email',
					),
					'send_notification' => array(
						'label'       => 'In-App Notification',
						'description' => 'Show notification in WordPress admin',
						'icon'        => 'bell',
					),
				),
			),
			'logging'                 => array(
				'label'   => 'Logging',
				'icon'    => 'list-view',
				'actions' => array(
					'log_activity'  => array(
						'label'       => 'Log Activity',
						'description' => 'Record action to activity log',
						'icon'        => 'list-view',
					),
					'create_backup' => array(
						'label'       => 'Create Backup Point',
						'description' => 'Save current state',
						'icon'        => 'backup',
					),
				),
			),
			'tools'                   => array(
				'label'   => 'WPShadow Tools',
				'icon'    => 'admin-tools',
				'actions' => array(
					'run_tool_a11y_audit'          => array(
						'label'       => 'Run Accessibility Audit',
						'description' => 'Execute WPShadow Accessibility Audit tool',
						'icon'        => 'universal-access',
					),
					'run_tool_broken_links'        => array(
						'label'       => 'Run Broken Link Checker',
						'description' => 'Execute WPShadow Broken Links tool',
						'icon'        => 'admin-links',
					),
					'run_tool_color_contrast'      => array(
						'label'       => 'Run Color Contrast Checker',
						'description' => 'Execute color contrast tool',
						'icon'        => 'art',
					),
					'run_tool_dark_mode'           => array(
						'label'       => 'Apply Dark Mode Preference',
						'description' => 'Set or sync dark mode preference',
						'icon'        => 'visibility',
					),
					'run_tool_mobile_friendliness' => array(
						'label'       => 'Run Mobile Friendliness Check',
						'description' => 'Execute mobile readiness scan',
						'icon'        => 'smartphone',
					),
					'run_tool_customization_audit' => array(
						'label'       => 'Run Customization Audit',
						'description' => 'Check customization best practices',
						'icon'        => 'admin-customizer',
					),
					'run_tool_timezone_alignment'  => array(
						'label'       => 'Run Timezone Alignment',
						'description' => 'Check timezone consistency',
						'icon'        => 'clock',
					),
					'run_tool_simple_cache'        => array(
						'label'       => 'Run WPShadow Cache Check',
						'description' => 'Validate WPShadow Cache settings',
						'icon'        => 'admin-settings',
					),
					'run_tool_magic_link_support'  => array(
						'label'       => 'Run Magic Link Support',
						'description' => 'Generate support magic link',
						'icon'        => 'admin-users',
					),
				),
			),
			'site_maintenance'        => array(
				'label'   => 'Site Maintenance',
				'icon'    => 'tools',
				'actions' => array(
					'clear_transients'        => array(
						'label'       => 'Clear Transients',
						'description' => 'Clear all temporary cached data',
						'icon'        => 'trash',
						'tier'        => 'free',
					),
					'purge_object_cache'      => array(
						'label'       => 'Purge Object Cache',
						'description' => 'Clear object cache (Redis/Memcached)',
						'icon'        => 'performance',
						'tier'        => 'free',
					),
					'optimize_database'       => array(
						'label'       => 'Optimize Database',
						'description' => 'Cleanup and optimize database tables',
						'icon'        => 'database',
						'tier'        => 'pro',
					),
					'remove_spam_comments'    => array(
						'label'       => 'Remove Spam Comments',
						'description' => 'Delete comments marked as spam',
						'icon'        => 'dismiss',
						'tier'        => 'pro',
					),
					'delete_revisions'        => array(
						'label'       => 'Clean Post Revisions',
						'description' => 'Remove old post revision history',
						'icon'        => 'history',
						'tier'        => 'pro',
					),
					'clean_orphaned_postmeta' => array(
						'label'       => 'Clean Orphaned Post Meta',
						'description' => 'Remove orphaned post metadata',
						'icon'        => 'admin-tools',
						'tier'        => 'pro',
					),
				),
			),
			'user_management'         => array(
				'label'   => 'User Management',
				'icon'    => 'admin-users',
				'actions' => array(
					'notify_inactive_users'     => array(
						'label'       => 'Notify Inactive Users',
						'description' => 'Send reminder emails to inactive users',
						'icon'        => 'email',
						'tier'        => 'pro',
					),
					'disable_inactive_accounts' => array(
						'label'       => 'Disable Inactive Accounts',
						'description' => 'Automatically disable accounts after period of inactivity',
						'icon'        => 'admin-users',
						'tier'        => 'pro',
					),
					'reset_user_passwords'      => array(
						'label'       => 'Force Password Reset',
						'description' => 'Require users to reset passwords on next login',
						'icon'        => 'lock',
						'tier'        => 'pro',
					),
				),
			),
			'security_hardening'      => array(
				'label'   => 'Security Hardening',
				'icon'    => 'shield',
				'actions' => array(
					'block_malicious_ips'  => array(
						'label'       => 'Block Malicious IPs',
						'description' => 'Auto-block IPs with suspicious activity',
						'icon'        => 'dismiss',
						'tier'        => 'pro',
					),
					'disable_file_editors' => array(
						'label'       => 'Disable File Editors',
						'description' => 'Prevent direct theme/plugin file editing',
						'icon'        => 'lock',
						'tier'        => 'free',
					),
					'enforce_two_factor'   => array(
						'label'       => 'Enforce 2FA for Admins',
						'description' => 'Require two-factor authentication for admin users',
						'icon'        => 'shield',
						'tier'        => 'pro',
					),
					'scan_malware'         => array(
						'label'       => 'Run Malware Scan',
						'description' => 'Scan site for malicious code and vulnerabilities',
						'icon'        => 'warning',
						'tier'        => 'pro',
					),
				),
			),
			'content_management'      => array(
				'label'   => 'Content Management',
				'icon'    => 'edit',
				'actions' => array(
					'publish_scheduled_posts' => array(
						'label'       => 'Publish Scheduled Posts',
						'description' => 'Auto-publish posts scheduled for specific time',
						'icon'        => 'calendar',
						'tier'        => 'pro',
					),
					'update_post_status'      => array(
						'label'       => 'Update Post Status Bulk',
						'description' => 'Change status of multiple posts at once',
						'icon'        => 'admin-post',
						'tier'        => 'pro',
					),
					'archive_old_posts'       => array(
						'label'       => 'Archive Old Posts',
						'description' => 'Automatically archive posts older than specified date',
						'icon'        => 'archive',
						'tier'        => 'pro',
					),
					'clear_draft_posts'       => array(
						'label'       => 'Delete Draft Posts',
						'description' => 'Remove draft posts older than specified period',
						'icon'        => 'trash',
						'tier'        => 'free',
					),
				),
			),
			'backup_recovery'         => array(
				'label'   => 'WPShadow Vault Light',
				'icon'    => 'backup',
				'actions' => array(
					'create_full_backup'   => array(
						'label'       => 'Create Full Site Backup',
						'description' => 'Backup database, files, and configuration',
						'icon'        => 'backup',
						'tier'        => 'pro',
					),
					'backup_database'      => array(
						'label'       => 'Backup Database',
						'description' => 'Backup WordPress database only',
						'icon'        => 'database',
						'tier'        => 'pro',
					),
					'sync_backups_offsite' => array(
						'label'       => 'Sync Backups Offsite',
						'description' => 'Push backups to AWS S3, Google Drive, or Dropbox',
						'icon'        => 'cloud',
						'tier'        => 'pro',
					),
				),
			),
			'monitoring_alerts'       => array(
				'label'   => 'Monitoring & Alerts',
				'icon'    => 'visibility',
				'actions' => array(
					'check_site_uptime'    => array(
						'label'       => 'Check Site Uptime',
						'description' => 'Verify site is online and responsive',
						'icon'        => 'thumbs-up',
						'tier'        => 'pro',
					),
					'monitor_ssl_cert'     => array(
						'label'       => 'Monitor SSL Certificate',
						'description' => 'Alert when SSL certificate nearing expiration',
						'icon'        => 'lock',
						'tier'        => 'pro',
					),
					'check_plugin_updates' => array(
						'label'       => 'Check Plugin Updates',
						'description' => 'Scan for available plugin and theme updates',
						'icon'        => 'update',
						'tier'        => 'free',
					),
					'check_php_version'    => array(
						'label'       => 'Check PHP Version',
						'description' => 'Alert if PHP version is outdated',
						'icon'        => 'admin-tools',
						'tier'        => 'free',
					),
					'monitor_disk_space'   => array(
						'label'       => 'Monitor Disk Space',
						'description' => 'Alert when disk usage exceeds threshold',
						'icon'        => 'storage',
						'tier'        => 'pro',
					),
				),
			),
			'wordpress_settings_core' => array(
				'label'   => 'WordPress Settings (Core)',
				'icon'    => 'admin-settings',
				'actions' => array(
					'set_site_title'          => array(
						'label'       => 'Set Site Title',
						'description' => 'Update blog name and tagline',
						'icon'        => 'edit',
						'tier'        => 'free',
					),
					'set_timezone'            => array(
						'label'       => 'Set Timezone',
						'description' => 'Update site timezone setting',
						'icon'        => 'clock',
						'tier'        => 'free',
					),
					'set_date_format'         => array(
						'label'       => 'Set Date Format',
						'description' => 'Update date and time display format',
						'icon'        => 'calendar',
						'tier'        => 'free',
					),
					'set_permalink_structure' => array(
						'label'       => 'Set Permalink Structure',
						'description' => 'Update URL structure for posts and pages',
						'icon'        => 'link',
						'tier'        => 'free',
					),
				),
			),
			'wordpress_settings_pro'  => array(
				'label'       => 'WordPress Settings (Pro)',
				'icon'        => 'admin-settings',
				'description' => 'Unlock with WPShadow Pro',
				'actions'     => array(
					'set_discussion_settings' => array(
						'label'       => 'Configure Comments & Discussions',
						'description' => 'Control comment moderation, notifications, threading',
						'icon'        => 'admin-comments',
						'tier'        => 'pro',
					),
					'set_media_settings'      => array(
						'label'       => 'Configure Media Settings',
						'description' => 'Set image sizes, thumbnails, media organization',
						'icon'        => 'format-image',
						'tier'        => 'pro',
					),
					'set_reading_settings'    => array(
						'label'       => 'Configure Reading Settings',
						'description' => 'Set blog visibility, posts per page, feed settings',
						'icon'        => 'book',
						'tier'        => 'pro',
					),
					'set_privacy_settings'    => array(
						'label'       => 'Configure Privacy & Policies',
						'description' => 'Set privacy policy page, GDPR compliance options',
						'icon'        => 'shield',
						'tier'        => 'pro',
					),
				),
			),
		);

		return self::$all_actions;
	}

	/**
	 * Filter and order actions based on trigger type
	 *
	 * @param array  $all_actions All available actions
	 * @param string $trigger_type The current trigger type
	 * @return array Filtered and prioritized actions
	 */
	private static function filter_actions_by_trigger( $all_actions, $trigger_type ) {
		// Define which action categories are relevant for each trigger
		$trigger_action_map = array(
			// Schedule: maintenance, diagnostics, backups, monitoring - things that should run automatically
			'schedule'                  => array(
				'priority'           => array(
					'treatments',
					'monitoring_alerts',
					'backup_recovery',
					'site_maintenance',
					'diagnostics',
					'wordpress_settings_core',
					'content_management',
				),
				'allowed'            => array(
					'treatments',
					'monitoring_alerts',
					'backup_recovery',
					'site_maintenance',
					'diagnostics',
					'wordpress_settings_core',
					'wordpress_settings_pro',
					'content_management',
					'security_hardening',
					'notifications',
					'logging',
				),
				'disallowed_actions' => array(
					'external_fonts',
					'image_lazy_load',
					'html_cleanup',
					'head_cleanup',
					'embed_disable',
					'css_classes',
					'nav_aria',
					'paste_cleanup',
					'content_optimizer',
					'check_external_fonts',
				),
			),
			// Page Load: frontend optimization, diagnostics, caching - things that run per-page
			'page_load'                 => array(
				'priority'           => array(
					'treatments',
					'performance',
					'diagnostics',
					'security',
					'accessibility',
					'content',
					'notifications',
				),
				'allowed'            => array(
					'treatments',
					'performance',
					'diagnostics',
					'security',
					'accessibility',
					'content',
					'notifications',
					'logging',
				),
				'disallowed_actions' => array(
					'clear_transients',
					'purge_object_cache',
					'optimize_database',
					'remove_spam_comments',
					'delete_revisions',
					'clean_orphaned_postmeta',
					'notify_inactive_users',
					'disable_inactive_accounts',
					'reset_user_passwords',
					'block_malicious_ips',
					'enforce_two_factor',
					'scan_malware',
					'publish_scheduled_posts',
					'update_post_status',
					'archive_old_posts',
					'clear_draft_posts',
					'create_full_backup',
					'backup_database',
					'sync_backups_offsite',
					'check_site_uptime',
					'monitor_ssl_cert',
					'monitor_disk_space',
					'check_plugin_updates',
					'check_php_version',
					'inactive_plugins',
					'outdated_plugins',
				),
			),
			// Plugin State Changed: maintenance, security, notifications
			'plugin_state_changed'      => array(
				'priority'           => array(
					'diagnostics',
					'security_hardening',
					'notifications',
					'site_maintenance',
					'logging',
				),
				'allowed'            => array(
					'diagnostics',
					'security_hardening',
					'notifications',
					'site_maintenance',
					'logging',
					'maintenance',
				),
				'disallowed_actions' => array(
					'external_fonts',
					'image_lazy_load',
					'html_cleanup',
					'head_cleanup',
					'embed_disable',
					'css_classes',
					'nav_aria',
					'paste_cleanup',
					'content_optimizer',
					'content_management',
					'user_management',
					'backup_recovery',
				),
			),
			// Theme Switched: maintenance, diagnostics, caching
			'theme_switched'            => array(
				'priority'           => array(
					'diagnostics',
					'site_maintenance',
					'security_hardening',
					'notifications',
				),
				'allowed'            => array(
					'diagnostics',
					'site_maintenance',
					'security_hardening',
					'notifications',
					'logging',
					'performance',
				),
				'disallowed_actions' => array(
					'user_management',
					'content_management',
					'backup_recovery',
					'user_login',
				),
			),
			// User Login: security, notifications, user management
			'user_login'                => array(
				'priority'           => array(
					'security_hardening',
					'notifications',
					'logging',
				),
				'allowed'            => array(
					'security_hardening',
					'notifications',
					'logging',
					'diagnostics',
					'security',
				),
				'disallowed_actions' => array(
					'performance',
					'content',
					'accessibility',
					'site_maintenance',
					'user_management',
					'content_management',
					'backup_recovery',
					'monitoring_alerts',
					'maintenance',
				),
			),
			// User Registration: notifications, user management, security
			'user_register'             => array(
				'priority'           => array(
					'notifications',
					'user_management',
					'security_hardening',
					'logging',
				),
				'allowed'            => array(
					'notifications',
					'user_management',
					'security_hardening',
					'logging',
					'diagnostics',
				),
				'disallowed_actions' => array(
					'performance',
					'content',
					'accessibility',
					'site_maintenance',
					'content_management',
					'backup_recovery',
					'monitoring_alerts',
					'maintenance',
				),
			),
			// Post Status Changed: content management, notifications, diagnostics
			'post_status_changed'       => array(
				'priority'           => array(
					'content_management',
					'notifications',
					'logging',
					'content',
				),
				'allowed'            => array(
					'content_management',
					'notifications',
					'logging',
					'content',
					'diagnostics',
				),
				'disallowed_actions' => array(
					'performance',
					'accessibility',
					'site_maintenance',
					'user_management',
					'security_hardening',
					'backup_recovery',
					'monitoring_alerts',
					'maintenance',
					'wordpress_settings_core',
					'wordpress_settings_pro',
				),
			),
			// Pre Publish Review: content validation, notifications, security checks
			'pre_publish_review'        => array(
				'priority'           => array(
					'content_management',
					'notifications',
					'security_hardening',
					'logging',
				),
				'allowed'            => array(
					'content_management',
					'notifications',
					'security_hardening',
					'logging',
					'content',
					'diagnostics',
				),
				'disallowed_actions' => array(
					'performance',
					'accessibility',
					'site_maintenance',
					'user_management',
					'backup_recovery',
					'monitoring_alerts',
					'maintenance',
					'wordpress_settings_core',
					'wordpress_settings_pro',
				),
			),
			// Comment Posted: content moderation, notifications, security
			'comment_posted'            => array(
				'priority'           => array(
					'notifications',
					'content',
					'security_hardening',
					'logging',
				),
				'allowed'            => array(
					'notifications',
					'content',
					'security_hardening',
					'logging',
					'diagnostics',
				),
				'disallowed_actions' => array(
					'performance',
					'accessibility',
					'site_maintenance',
					'user_management',
					'backup_recovery',
					'monitoring_alerts',
					'maintenance',
					'content_management',
					'wordpress_settings_core',
					'wordpress_settings_pro',
				),
			),
			// Updates and backups
			'plugin_update_trigger'     => array(
				'priority' => array(
					'notifications',
					'logging',
					'diagnostics',
				),
				'allowed'  => array(
					'notifications',
					'logging',
					'diagnostics',
					'maintenance',
				),
			),
			'backup_completion_trigger' => array(
				'priority' => array(
					'notifications',
					'logging',
					'diagnostics',
				),
				'allowed'  => array(
					'notifications',
					'logging',
					'diagnostics',
					'backup_recovery',
				),
			),
			// Conditions (memory, debug, ssl, plugins, IP): diagnostics, notifications, security
			'high_memory'               => array(
				'priority' => array(
					'diagnostics',
					'security_hardening',
					'notifications',
					'site_maintenance',
				),
				'allowed'  => array(
					'diagnostics',
					'security_hardening',
					'notifications',
					'site_maintenance',
					'logging',
					'maintenance',
				),
			),
			'debug_mode_on'             => array(
				'priority' => array(
					'security',
					'security_hardening',
					'notifications',
				),
				'allowed'  => array(
					'security',
					'security_hardening',
					'notifications',
					'logging',
				),
			),
			'ssl_issue'                 => array(
				'priority' => array(
					'diagnostics',
					'security',
					'security_hardening',
					'notifications',
				),
				'allowed'  => array(
					'diagnostics',
					'security',
					'security_hardening',
					'notifications',
					'logging',
				),
			),
			'too_many_plugins'          => array(
				'priority' => array(
					'maintenance',
					'diagnostics',
					'notifications',
				),
				'allowed'  => array(
					'maintenance',
					'diagnostics',
					'notifications',
					'logging',
				),
			),
			'ip_banned'                 => array(
				'priority' => array(
					'security_hardening',
					'security',
					'notifications',
					'logging',
				),
				'allowed'  => array(
					'security_hardening',
					'security',
					'notifications',
					'logging',
				),
			),
			'database_trigger'          => array(
				'priority' => array(
					'diagnostics',
					'logging',
					'notifications',
				),
				'allowed'  => array(
					'diagnostics',
					'logging',
					'notifications',
					'backup_recovery',
					'maintenance',
				),
			),
			'error_log_trigger'         => array(
				'priority' => array(
					'notifications',
					'logging',
				),
				'allowed'  => array(
					'notifications',
					'logging',
					'diagnostics',
				),
			),
			'diagnostic_run_trigger'    => array(
				'priority' => array(
					'notifications',
					'logging',
					'diagnostics',
				),
				'allowed'  => array(
					'notifications',
					'logging',
					'diagnostics',
					'maintenance',
				),
			),
		);

		// If no mapping exists for this trigger, return all actions
		if ( ! isset( $trigger_action_map[ $trigger_type ] ) ) {
			return $all_actions;
		}

		$config              = $trigger_action_map[ $trigger_type ];
		$priority_categories = isset( $config['priority'] ) ? $config['priority'] : array();
		$allowed_categories  = isset( $config['allowed'] ) ? $config['allowed'] : array();
		$disallowed_actions  = isset( $config['disallowed_actions'] ) ? $config['disallowed_actions'] : array();

		$result = array();

		// First, add categories in priority order
		foreach ( $priority_categories as $category ) {
			if ( isset( $all_actions[ $category ] ) ) {
				$filtered_category = self::filter_category_actions( $all_actions[ $category ], $disallowed_actions );
				if ( ! empty( $filtered_category['actions'] ) ) {
					$result[ $category ] = $filtered_category;
				}
			}
		}

		// Then add remaining allowed categories
		foreach ( $all_actions as $category => $data ) {
			if ( ! isset( $result[ $category ] ) && in_array( $category, $allowed_categories, true ) ) {
				$filtered_category = self::filter_category_actions( $data, $disallowed_actions );
				if ( ! empty( $filtered_category['actions'] ) ) {
					$result[ $category ] = $filtered_category;
				}
			}
		}

		return $result;
	}

	/**
	 * Filter individual actions in a category
	 *
	 * @param array $category Category data
	 * @param array $disallowed_actions Action IDs to exclude
	 * @return array Filtered category
	 */
	private static function filter_category_actions( $category, $disallowed_actions ) {
		if ( ! isset( $category['actions'] ) ) {
			return $category;
		}

		$filtered_actions = array();
		foreach ( $category['actions'] as $action_id => $action ) {
			if ( ! in_array( $action_id, $disallowed_actions, true ) ) {
				$filtered_actions[ $action_id ] = $action;
			}
		}

		$category['actions'] = $filtered_actions;
		return $category;
	}

	/**
	 * Get configuration fields for a specific trigger
	 *
	 * @param string $trigger_id Trigger ID
	 * @return array Configuration fields
	 */
	public static function get_trigger_config( $trigger_id ) {
		// Map trigger IDs to their config groups
		$trigger_to_config = array(
			'time_daily'                => 'schedule',
			'page_load_trigger'         => 'page_load',
			'post_status_changed'       => 'post_status',
			'pre_publish_review'        => 'pre_publish_review',
			'comment_posted'            => 'comment',
			'plugin_update_trigger'     => 'plugin_update',
			'backup_completion_trigger' => 'backup_completion',
			'plugin_state_changed'      => 'plugin',
			'theme_switched'            => 'theme',
			'user_login'                => 'user_login',
			'user_register'             => 'user_register',
			'high_memory'               => 'memory',
			'debug_mode_on'             => 'debug',
			'ssl_issue'                 => 'ssl',
			'too_many_plugins'          => 'plugins',
			'ip_banned'                 => 'ip_ban',
			'database_trigger'          => 'database',
			'error_log_trigger'         => 'error_log',
			'diagnostic_run_trigger'    => 'diagnostic_run',
			'manual_cron_trigger'       => 'manual',
		);

		// Get the config group key for this trigger
		$config_key   = isset( $trigger_to_config[ $trigger_id ] ) ? $trigger_to_config[ $trigger_id ] : $trigger_id;
		$manual_token = bin2hex( random_bytes( 16 ) );
		$manual_url   = add_query_arg( 'wpshadow_trigger', $manual_token, home_url( '/' ) );

		$configs = array(
			'schedule'          => array(
				array(
					'id'       => 'frequency',
					'type'     => 'select',
					'label'    => 'How often?',
					'options'  => array(
						'hourly' => 'Every Hour',
						'daily'  => 'Daily',
						'weekly' => 'Weekly',
					),
					'default'  => 'daily',
					'required' => true,
				),
				array(
					'id'          => 'time',
					'type'        => 'time',
					'label'       => 'What time?',
					'placeholder' => '14:00',
					'default'     => '02:00',
					'required'    => true,
					'show_if'     => array(
						'field' => 'frequency',
						'value' => array( 'daily', 'weekly' ),
					),
				),
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
					'show_if'  => array(
						'field' => 'frequency',
						'value' => 'weekly',
					),
				),
			),
			'page_load'         => array(
				array(
					'id'       => 'page_targets',
					'type'     => 'checkbox_group',
					'label'    => 'Which pages?',
					'options'  => self::get_page_load_options(),
					'default'  => array( 'all_frontend' ),
					'required' => true,
				),
			),
			'post_status'       => array(
				array(
					'id'       => 'post_types',
					'type'     => 'checkbox_group',
					'label'    => __( 'Which post types?', 'wpshadow' ),
					'options'  => self::get_public_post_type_options(),
					'required' => true,
				),
				array(
					'id'       => 'old_status',
					'type'     => 'select',
					'label'    => __( 'From status', 'wpshadow' ),
					'options'  => self::get_post_status_options(),
					'default'  => 'any',
					'required' => true,
				),
				array(
					'id'       => 'new_status',
					'type'     => 'select',
					'label'    => __( 'To status', 'wpshadow' ),
					'options'  => self::get_post_status_options(),
					'default'  => 'any',
					'required' => true,
				),
			),
			'plugin_update'     => array(
				array(
					'id'       => 'target_type',
					'type'     => 'select',
					'label'    => 'Which updates?',
					'options'  => array(
						'any'      => 'Any plugin or theme',
						'plugins'  => 'Plugins only',
						'themes'   => 'Themes only',
						'specific' => 'Specific slug',
					),
					'default'  => 'any',
					'required' => true,
				),
				array(
					'id'          => 'specific_slug',
					'type'        => 'text',
					'label'       => 'Specific plugin/theme slug',
					'placeholder' => 'e.g., wpshadow/wpshadow.php',
					'default'     => '',
					'show_if'     => array(
						'field' => 'target_type',
						'value' => 'specific',
					),
				),
			),
			'backup_completion' => array(
				array(
					'id'       => 'backup_status',
					'type'     => 'select',
					'label'    => 'When should this fire?',
					'options'  => array(
						'any'     => 'Any backup event',
						'success' => 'Only when successful',
						'failure' => 'Only when it fails',
					),
					'default'  => 'any',
					'required' => true,
				),
			),
			'user_login'        => array(
				array(
					'id'          => 'user_id',
					'type'        => 'user_search',
					'label'       => __( 'Specific user (optional)', 'wpshadow' ),
					'placeholder' => __( 'Search by name or email', 'wpshadow' ),
					'ajax_action' => 'wpshadow_user_search',
					'nonce'       => wp_create_nonce( 'wpshadow_user_search' ),
					'default'     => '',
					'required'    => false,
				),
				array(
					'id'       => 'user_role',
					'type'     => 'select',
					'label'    => 'User role? (optional)',
					'options'  => array(
						''              => 'Any role',
						'administrator' => 'Administrator',
						'editor'        => 'Editor',
						'author'        => 'Author',
						'contributor'   => 'Contributor',
						'subscriber'    => 'Subscriber',
					),
					'default'  => '',
					'required' => false,
				),
			),
			'high_memory'       => array(
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
			'too_many_plugins'  => array(
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
			'ip_banned'         => array(
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
			'database'          => array(
				array(
					'id'       => 'database_issue',
					'type'     => 'select',
					'label'    => 'Which database issue?',
					'options'  => array(
						'size_threshold' => 'Size exceeds threshold',
						'corruption'     => 'Corruption detected',
					),
					'default'  => 'size_threshold',
					'required' => true,
				),
				array(
					'id'       => 'size_mb',
					'type'     => 'number',
					'label'    => 'Database size threshold (MB)',
					'default'  => 500,
					'required' => true,
					'min'      => 10,
					'show_if'  => array(
						'field' => 'database_issue',
						'value' => 'size_threshold',
					),
				),
			),
			'error_log'         => array(
				array(
					'id'       => 'error_level',
					'type'     => 'select',
					'label'    => 'Minimum severity',
					'options'  => array(
						'any'      => 'Any',
						'warning'  => 'Warning+',
						'error'    => 'Error+',
						'critical' => 'Critical only',
					),
					'default'  => 'any',
					'required' => true,
				),
			),
			'manual'            => array(
				array(
					'id'       => 'trigger_url',
					'type'     => 'text',
					'label'    => __( 'Trigger URL', 'wpshadow' ),
					'default'  => $manual_url,
					'required' => true,
					'readonly' => true,
					'note'     => __( 'Copy this URL and use it in your CRON job or external service.', 'wpshadow' ),
				),
				array(
					'id'       => 'trigger_token',
					'type'     => 'text',
					'label'    => __( 'Trigger token', 'wpshadow' ),
					'default'  => $manual_token,
					'required' => true,
					'note'     => __( 'This is the security key used in the URL.', 'wpshadow' ),
				),
				array(
					'id'       => 'require_auth',
					'type'     => 'select',
					'label'    => __( 'Require login?', 'wpshadow' ),
					'options'  => array(
						'1' => __( 'Yes, only logged-in users', 'wpshadow' ),
						'0' => __( 'No, allow public requests', 'wpshadow' ),
					),
					'default'  => '1',
					'required' => true,
				),
				array(
					'id'          => 'allowed_ips',
					'type'        => 'textarea',
					'label'       => __( 'Allowed IPs (optional)', 'wpshadow' ),
					'placeholder' => __( '192.0.2.10, 203.0.113.7', 'wpshadow' ),
					'rows'        => 3,
					'note'        => __( 'Comma-separated list. Leave empty to allow any IP.', 'wpshadow' ),
				),
			),
			'diagnostic_run'    => array(
				array(
					'id'       => 'source',
					'type'     => 'select',
					'label'    => __( 'Which diagnostic runs?', 'wpshadow' ),
					'options'  => array(
						'any'        => __( 'Any source', 'wpshadow' ),
						'quick_scan' => __( 'Quick Scan', 'wpshadow' ),
						'deep_scan'  => __( 'Deep Scan', 'wpshadow' ),
						'guardian'   => __( 'Guardian', 'wpshadow' ),
						'manual'     => __( 'Manual', 'wpshadow' ),
					),
					'default'  => 'any',
					'required' => true,
				),
				array(
					'id'       => 'result',
					'type'     => 'select',
					'label'    => __( 'When should this run?', 'wpshadow' ),
					'options'  => array(
						'any'  => __( 'Pass or fail', 'wpshadow' ),
						'pass' => __( 'Only when it passes', 'wpshadow' ),
						'fail' => __( 'Only when it fails', 'wpshadow' ),
					),
					'default'  => 'any',
					'required' => true,
				),
				array(
					'id'          => 'specific_diagnostic',
					'type'        => 'diagnostic_search',
					'label'       => __( 'Choose a diagnostic (optional)', 'wpshadow' ),
					'default'     => '',
					'required'    => false,
					'placeholder' => __( 'Search diagnostics', 'wpshadow' ),
					'ajax_action' => 'wpshadow_workflow_search_diagnostics',
					'nonce'       => wp_create_nonce( 'wpshadow_workflow' ),
					'popular'     => self::get_popular_diagnostics(),
				),
				array(
					'id'      => 'issues_only',
					'type'    => 'checkbox',
					'label'   => 'Only when issues are found',
					'default' => false,
				),
			),
		);

		return isset( $configs[ $config_key ] ) ? $configs[ $config_key ] : array();
	}

	/**
	 * Get a short list of popular diagnostics for quick selection.
	 *
	 * @return array Array of popular diagnostic items.
	 */
	private static function get_popular_diagnostics(): array {
		$class_name = __NAMESPACE__ . '\\Workflow_Discovery';
		if ( ! class_exists( $class_name ) ) {
			$discovery_path = defined( 'WPSHADOW_PATH' ) ? WPSHADOW_PATH . 'includes/systems/workflow/class-workflow-discovery.php' : '';
			if ( $discovery_path && file_exists( $discovery_path ) ) {
				require_once $discovery_path;
			}
		}

		if ( ! class_exists( $class_name ) ) {
			return array();
		}

		$diagnostics = $class_name::discover_diagnostics();
		if ( empty( $diagnostics ) ) {
			return array();
		}

		uasort(
			$diagnostics,
			function ( $a, $b ) {
				$label_a = $a['label'] ?? '';
				$label_b = $b['label'] ?? '';
				return strcasecmp( $label_a, $label_b );
			}
		);

		$popular = array_slice( $diagnostics, 0, 6, true );
		$items   = array();
		foreach ( $popular as $slug => $diagnostic ) {
			$items[] = array(
				'slug'  => $slug,
				'label' => $diagnostic['label'] ?? $slug,
			);
		}

		return $items;
	}

	/**
	 * Build page load target options for the trigger config.
	 *
	 * @return array Page target options.
	 */
	private static function get_page_load_options() {
		$options = array(
			'all_frontend' => __( 'All frontend pages', 'wpshadow' ),
			'all_admin'    => __( 'All admin screens', 'wpshadow' ),
			'front_page'   => __( 'Homepage', 'wpshadow' ),
			'blog_index'   => __( 'Blog index', 'wpshadow' ),
			'search'       => __( 'Search results', 'wpshadow' ),
			'error_404'    => __( '404 not found page', 'wpshadow' ),
		);

		$post_types = get_post_types(
			array(
				'public' => true,
			),
			'objects'
		);

		foreach ( $post_types as $post_type => $object ) {
			if ( empty( $object->show_ui ) ) {
				continue;
			}

			$singular = $object->labels->singular_name ?? $object->label ?? $post_type;
			$plural   = $object->labels->name ?? $singular;

			$options[ 'single_' . $post_type ] = sprintf(
				/* translators: %s: post type singular label */
				__( 'Single %s', 'wpshadow' ),
				$singular
			);

			if ( ! empty( $object->has_archive ) ) {
				$options[ 'archive_' . $post_type ] = sprintf(
					/* translators: %s: post type plural label */
					__( '%s archive', 'wpshadow' ),
					$plural
				);
			}
		}

		return $options;
	}

	/**
	 * Build plugin options for trigger config.
	 *
	 * @return array
	 */
	private static function get_plugin_options() {
		$options = array(
			'any' => __( 'Any plugin', 'wpshadow' ),
		);

		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$plugins = get_plugins();
		if ( empty( $plugins ) || ! is_array( $plugins ) ) {
			return $options;
		}

		foreach ( $plugins as $plugin_file => $plugin_data ) {
			$plugin_name = isset( $plugin_data['Name'] ) ? $plugin_data['Name'] : $plugin_file;
			$label       = sanitize_text_field( $plugin_name );
			if ( $label !== $plugin_file ) {
				$label .= ' (' . $plugin_file . ')';
			}
			$options[ $plugin_file ] = $label;
		}

		return $options;
	}

	/**
	 * Get public post type options.
	 *
	 * @since 0.6093.1200
	 * @return array Post type options.
	 */
	private static function get_public_post_type_options(): array {
		$options    = array();
		$post_types = get_post_types(
			array(
				'public' => true,
			),
			'objects'
		);

		foreach ( $post_types as $post_type => $object ) {
			if ( empty( $object->show_ui ) ) {
				continue;
			}
			$label                 = $object->labels->singular_name ?? $object->label ?? $post_type;
			$options[ $post_type ] = $label;
		}

		return $options;
	}

	/**
	 * Get post status options.
	 *
	 * @since 0.6093.1200
	 * @return array Post status options.
	 */
	private static function get_post_status_options(): array {
		$options = array(
			'any' => __( 'Any status', 'wpshadow' ),
		);

		$statuses = get_post_stati( array( 'show_in_admin_all_list' => true ), 'objects' );
		foreach ( $statuses as $status => $object ) {
			$options[ $status ] = $object->label ?? $status;
		}

		return $options;
	}

	/**
	 * Get comment status options.
	 *
	 * @since 0.6093.1200
	 * @return array Comment status options.
	 */
	private static function get_comment_status_options(): array {
		return array(
			'any'   => __( 'Any status', 'wpshadow' ),
			'1'     => __( 'Approved', 'wpshadow' ),
			'0'     => __( 'Pending', 'wpshadow' ),
			'spam'  => __( 'Spam', 'wpshadow' ),
			'trash' => __( 'Trash', 'wpshadow' ),
		);
	}

	/**
	 * Get configuration fields for a specific action
	 *
	 * @param string $action_id Action ID
	 * @return array Configuration fields
	 */
	public static function get_action_config( $action_id ) {
		// Load the email recipient manager
		require_once WPSHADOW_PATH . 'includes/systems/workflow/class-email-recipient-manager.php';

		$configs = array(
			'send_email'                   => array(
				array(
					'id'       => 'recipient',
					'type'     => 'select',
					'label'    => 'Send to',
					'options'  => self::get_approved_email_options(),
					'default'  => 'admin',
					'required' => true,
					'note'     => 'Manage pre-approved recipients in Settings',
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
			'send_notification'            => array(
				array(
					'id'          => 'notification_title',
					'type'        => 'text',
					'label'       => __( 'Notification title', 'wpshadow' ),
					'placeholder' => __( 'WPShadow Notification', 'wpshadow' ),
					'default'     => __( 'WPShadow Notification', 'wpshadow' ),
					'required'    => true,
				),
				array(
					'id'          => 'notification_message',
					'type'        => 'text',
					'label'       => __( 'Notification message', 'wpshadow' ),
					'placeholder' => __( 'Action completed successfully', 'wpshadow' ),
					'default'     => '',
					'required'    => true,
				),
				array(
					'id'       => 'notification_type',
					'type'     => 'select',
					'label'    => __( 'Notification type', 'wpshadow' ),
					'options'  => array(
						'success' => __( 'Success', 'wpshadow' ),
						'warning' => __( 'Warning', 'wpshadow' ),
						'error'   => __( 'Error', 'wpshadow' ),
						'info'    => __( 'Info', 'wpshadow' ),
					),
					'default'  => 'success',
					'required' => true,
				),
			),
			'log_activity'                 => array(
				array(
					'id'          => 'log_message',
					'type'        => 'text',
					'label'       => 'Log message',
					'placeholder' => 'What happened?',
					'default'     => '',
					'required'    => true,
				),
			),
			'run_treatment'                => array(
				array(
					'id'          => 'specific_treatment',
					'type'        => 'treatment_search',
					'label'       => 'Choose a treatment',
					'placeholder' => 'Search treatments',
					'required'    => true,
					'ajax_action' => 'wpshadow_workflow_search_treatments',
					'nonce'       => wp_create_nonce( 'wpshadow_workflow' ),
				),
			),
			'block_ip'                     => array(
				array(
					'id'          => 'ip',
					'type'        => 'text',
					'label'       => 'IP address',
					'placeholder' => 'Leave blank to block current visitor',
					'default'     => '',
					'required'    => false,
				),
			),
			'set_site_title'               => array(
				array(
					'id'          => 'blog_name',
					'type'        => 'text',
					'label'       => 'Site Title',
					'placeholder' => get_option( 'blogname' ),
					'default'     => get_option( 'blogname' ),
					'required'    => false,
				),
				array(
					'id'          => 'blog_description',
					'type'        => 'text',
					'label'       => 'Site Tagline',
					'placeholder' => get_option( 'blogdescription' ),
					'default'     => get_option( 'blogdescription' ),
					'required'    => false,
				),
			),
			'set_timezone'                 => array(
				array(
					'id'       => 'timezone_string',
					'type'     => 'select',
					'label'    => 'Timezone',
					'options'  => self::get_timezone_options(),
					'default'  => get_option( 'timezone_string' ) ?: 'UTC',
					'required' => true,
				),
			),
			'set_date_format'              => array(
				array(
					'id'       => 'date_format',
					'type'     => 'select',
					'label'    => 'Date Format',
					'options'  => array(
						'F j, Y' => 'January 30, 2026',
						'Y-m-d'  => '2026-01-30',
						'j F Y'  => '30 January 2026',
						'd/m/Y'  => '30/01/2026',
						'm/d/Y'  => '01/30/2026',
					),
					'default'  => get_option( 'date_format' ) ?: 'F j, Y',
					'required' => true,
				),
				array(
					'id'       => 'time_format',
					'type'     => 'select',
					'label'    => 'Time Format',
					'options'  => array(
						'g:i a' => '2:30 pm',
						'H:i'   => '14:30',
						'g:i A' => '2:30 PM',
					),
					'default'  => get_option( 'time_format' ) ?: 'g:i a',
					'required' => true,
				),
			),
			'set_permalink_structure'      => array(
				array(
					'id'       => 'permalink_structure',
					'type'     => 'select',
					'label'    => 'Permalink Structure',
					'options'  => array(
						''                               => 'Plain (/?p=123)',
						'/%year%/%monthnum%/%postname%/' => 'Date and Name (/2026/01/sample-post/)',
						'/%postname%/'                   => 'Post Name (/sample-post/)',
						'/archives/%post_id%'            => 'Numeric (/archives/123/)',
					),
					'default'  => get_option( 'permalink_structure' ) ?: '',
					'required' => true,
				),
			),
			'set_discussion_settings'      => array(
				array(
					'id'       => 'default_comment_status',
					'type'     => 'select',
					'label'    => 'Default Comment Status',
					'options'  => array(
						'open'   => 'Allow Comments',
						'closed' => 'Disallow Comments',
					),
					'default'  => get_option( 'default_comment_status' ) ?: 'open',
					'required' => true,
					'note'     => 'Pro Feature',
				),
				array(
					'id'       => 'default_ping_status',
					'type'     => 'select',
					'label'    => 'Default Ping Status',
					'options'  => array(
						'open'   => 'Allow Pingbacks',
						'closed' => 'Disallow Pingbacks',
					),
					'default'  => get_option( 'default_ping_status' ) ?: 'open',
					'required' => true,
					'note'     => 'Pro Feature',
				),
				array(
					'id'       => 'comment_moderation',
					'type'     => 'select',
					'label'    => 'Comment Moderation',
					'options'  => array(
						'0' => 'No Moderation',
						'1' => 'Manual Review',
					),
					'default'  => get_option( 'comment_moderation' ) ? '1' : '0',
					'required' => true,
					'note'     => 'Pro Feature',
				),
			),
			'set_media_settings'           => array(
				array(
					'id'       => 'large_size_w',
					'type'     => 'number',
					'label'    => 'Large Image Width (px)',
					'default'  => get_option( 'large_size_w' ) ?: 1024,
					'required' => false,
					'note'     => 'Pro Feature',
				),
				array(
					'id'       => 'large_size_h',
					'type'     => 'number',
					'label'    => 'Large Image Height (px)',
					'default'  => get_option( 'large_size_h' ) ?: 1024,
					'required' => false,
					'note'     => 'Pro Feature',
				),
				array(
					'id'       => 'thumbnail_size_w',
					'type'     => 'number',
					'label'    => 'Thumbnail Width (px)',
					'default'  => get_option( 'thumbnail_size_w' ) ?: 150,
					'required' => false,
					'note'     => 'Pro Feature',
				),
			),
			'set_reading_settings'         => array(
				array(
					'id'       => 'posts_per_page',
					'type'     => 'number',
					'label'    => 'Posts Per Page',
					'default'  => get_option( 'posts_per_page' ) ?: 10,
					'required' => true,
					'note'     => 'Pro Feature',
				),
				array(
					'id'       => 'blog_public',
					'type'     => 'select',
					'label'    => 'Site Visibility',
					'options'  => array(
						'1' => 'Public (visible to search engines)',
						'0' => 'Private (ask search engines not to index)',
					),
					'default'  => get_option( 'blog_public' ) ?: '1',
					'required' => true,
					'note'     => 'Pro Feature',
				),
			),
			'set_privacy_settings'         => array(
				array(
					'id'       => 'wp_page_for_privacy_policy',
					'type'     => 'select',
					'label'    => 'Privacy Policy Page',
					'options'  => self::get_pages_options(),
					'default'  => get_option( 'wp_page_for_privacy_policy' ) ?: '',
					'required' => false,
					'note'     => 'Pro Feature',
				),
			),
			'clear_transients'             => array(
				array(
					'id'    => 'confirm',
					'type'  => 'checkbox',
					'label' => 'Confirm clearing all transients',
					'note'  => 'This removes all temporary cached data',
				),
			),
			'purge_object_cache'           => array(
				array(
					'id'    => 'confirm',
					'type'  => 'checkbox',
					'label' => 'Confirm clearing object cache',
					'note'  => 'Redis/Memcached will be cleared if available',
				),
			),
			'disable_file_editors'         => array(
				array(
					'id'    => 'confirm',
					'type'  => 'checkbox',
					'label' => 'Confirm disabling file editors',
					'note'  => 'This prevents direct editing of theme and plugin files',
				),
			),
			'clear_draft_posts'            => array(
				array(
					'id'       => 'days_old',
					'type'     => 'number',
					'label'    => 'Delete drafts older than (days)',
					'default'  => 30,
					'required' => true,
					'note'     => 'Drafts older than this will be permanently deleted',
				),
			),
			'check_plugin_updates'         => array(
				array(
					'id'    => 'notify_admin',
					'type'  => 'checkbox',
					'label' => 'Notify admin when updates available',
					'note'  => 'Send email to admin email address',
				),
			),
			'check_php_version'            => array(
				array(
					'id'       => 'minimum_version',
					'type'     => 'text',
					'label'    => __( 'Minimum PHP Version (e.g., 8.1)', 'wpshadow' ),
					'default'  => PHP_VERSION,
					'required' => true,
				),
				array(
					'id'    => 'alert_if_below',
					'type'  => 'checkbox',
					'label' => __( 'Alert if version is below minimum', 'wpshadow' ),
				),
				array(
					'id'       => 'notify_method',
					'type'     => 'select',
					'label'    => __( 'How should we notify you?', 'wpshadow' ),
					'options'  => array(
						'none'         => __( 'No notification', 'wpshadow' ),
						'email'        => __( 'Email', 'wpshadow' ),
						'notification' => __( 'In-app notice', 'wpshadow' ),
					),
					'default'  => 'email',
					'required' => true,
				),
				array(
					'id'       => 'notify_recipient',
					'type'     => 'select',
					'label'    => __( 'Email recipient', 'wpshadow' ),
					'options'  => self::get_approved_email_options(),
					'default'  => 'admin',
					'required' => false,
				),
				array(
					'id'          => 'notify_subject',
					'type'        => 'text',
					'label'       => __( 'Email subject', 'wpshadow' ),
					'placeholder' => __( 'PHP version alert', 'wpshadow' ),
					'default'     => __( 'PHP version alert', 'wpshadow' ),
					'required'    => false,
				),
				array(
					'id'          => 'notify_message',
					'type'        => 'textarea',
					'label'       => __( 'Notification message', 'wpshadow' ),
					'placeholder' => __( 'Your site is running PHP {php_version}. Recommended: {minimum_php_version}.', 'wpshadow' ),
					'rows'        => 4,
					'required'    => false,
				),
			),
			'optimize_database'            => array(
				array(
					'id'    => 'confirm',
					'type'  => 'checkbox',
					'label' => 'Confirm database optimization',
					'note'  => 'Pro Feature - Backs up database before optimizing',
				),
			),
			'remove_spam_comments'         => array(
				array(
					'id'       => 'older_than_days',
					'type'     => 'number',
					'label'    => 'Only remove spam older than (days)',
					'default'  => 0,
					'required' => false,
					'note'     => 'Pro Feature - Leave empty to remove all spam',
				),
			),
			'delete_revisions'             => array(
				array(
					'id'       => 'keep_recent',
					'type'     => 'number',
					'label'    => 'Keep most recent revisions',
					'default'  => 3,
					'required' => true,
					'note'     => 'Pro Feature - How many recent revisions to keep per post',
				),
			),
			'clean_orphaned_postmeta'      => array(
				array(
					'id'    => 'confirm',
					'type'  => 'checkbox',
					'label' => 'Confirm cleaning orphaned metadata',
					'note'  => 'Pro Feature - Removes meta for non-existent posts',
				),
			),
			'notify_inactive_users'        => array(
				array(
					'id'       => 'inactive_days',
					'type'     => 'number',
					'label'    => 'Inactive for (days)',
					'default'  => 90,
					'required' => true,
					'note'     => 'Pro Feature - Users not active for this long will be notified',
				),
				array(
					'id'          => 'message',
					'type'        => 'textarea',
					'label'       => 'Email message',
					'placeholder' => 'Your account has been inactive...',
					'rows'        => 4,
				),
			),
			'disable_inactive_accounts'    => array(
				array(
					'id'       => 'inactive_days',
					'type'     => 'number',
					'label'    => 'Disable after (days)',
					'default'  => 180,
					'required' => true,
					'note'     => 'Pro Feature - Users inactive this long will be disabled',
				),
			),
			'reset_user_passwords'         => array(
				array(
					'id'       => 'user_role',
					'type'     => 'select',
					'label'    => 'User role to reset',
					'options'  => array(
						'administrator' => 'Administrators',
						'editor'        => 'Editors',
						'author'        => 'Authors',
						'all'           => 'All users',
					),
					'default'  => 'administrator',
					'required' => true,
					'note'     => 'Pro Feature',
				),
			),
			'block_malicious_ips'          => array(
				array(
					'id'    => 'enable_auto_block',
					'type'  => 'checkbox',
					'label' => 'Auto-block after failed attempts',
					'note'  => 'Pro Feature - Block IPs after X failed login attempts',
				),
				array(
					'id'       => 'failed_attempts',
					'type'     => 'number',
					'label'    => 'Failed attempts before block',
					'default'  => 5,
					'required' => true,
				),
			),
			'enforce_two_factor'           => array(
				array(
					'id'       => 'grace_period_hours',
					'type'     => 'number',
					'label'    => 'Grace period (hours)',
					'default'  => 24,
					'required' => true,
					'note'     => 'Pro Feature - Time before 2FA is required',
				),
			),
			'scan_malware'                 => array(
				array(
					'id'    => 'quarantine_threats',
					'type'  => 'checkbox',
					'label' => 'Quarantine detected threats',
					'note'  => 'Pro Feature - Move suspicious files to quarantine',
				),
				array(
					'id'    => 'notify_on_threat',
					'type'  => 'checkbox',
					'label' => 'Email notification when threats found',
					'note'  => 'Send alert to admin email',
				),
			),
			'create_full_backup'           => array(
				array(
					'id'       => 'backup_location',
					'type'     => 'select',
					'label'    => 'Backup storage',
					'options'  => array(
						'local'   => 'Local server storage',
						's3'      => 'Amazon S3',
						'gdrive'  => 'Google Drive',
						'dropbox' => 'Dropbox',
					),
					'default'  => 'local',
					'required' => true,
					'note'     => 'Pro Feature',
				),
				array(
					'id'    => 'notify_on_complete',
					'type'  => 'checkbox',
					'label' => 'Email notification when backup completes',
				),
			),
			'backup_database'              => array(
				array(
					'id'       => 'compression',
					'type'     => 'select',
					'label'    => 'Compression type',
					'options'  => array(
						'none'  => 'None',
						'gzip'  => 'GZIP',
						'bzip2' => 'BZIP2',
					),
					'default'  => 'gzip',
					'required' => true,
					'note'     => 'Pro Feature',
				),
			),
			'sync_backups_offsite'         => array(
				array(
					'id'       => 'remote_provider',
					'type'     => 'select',
					'label'    => 'Remote provider',
					'options'  => array(
						's3'      => 'Amazon S3',
						'gdrive'  => 'Google Drive',
						'dropbox' => 'Dropbox',
						'b2'      => 'Backblaze B2',
					),
					'default'  => 's3',
					'required' => true,
					'note'     => 'Pro Feature',
				),
				array(
					'id'    => 'keep_local_backup',
					'type'  => 'checkbox',
					'label' => 'Keep local copy after sync',
				),
			),
			'check_site_uptime'            => array(
				array(
					'id'    => 'alert_on_down',
					'type'  => 'checkbox',
					'label' => 'Alert if site is down',
					'note'  => 'Pro Feature - Send notification if unreachable',
				),
				array(
					'id'       => 'timeout_seconds',
					'type'     => 'number',
					'label'    => 'Request timeout (seconds)',
					'default'  => 10,
					'required' => true,
				),
			),
			'monitor_ssl_cert'             => array(
				array(
					'id'       => 'alert_before_days',
					'type'     => 'number',
					'label'    => 'Alert before expiration (days)',
					'default'  => 30,
					'required' => true,
					'note'     => 'Pro Feature',
				),
			),
			'monitor_disk_space'           => array(
				array(
					'id'       => 'alert_threshold_percent',
					'type'     => 'number',
					'label'    => 'Alert when disk usage exceeds (%)',
					'default'  => 80,
					'required' => true,
					'note'     => 'Pro Feature',
				),
			),
			'publish_scheduled_posts'      => array(
				array(
					'id'    => 'confirm',
					'type'  => 'checkbox',
					'label' => 'Publish all scheduled posts',
					'note'  => 'Pro Feature - Posts scheduled before now will be published',
				),
			),
			'update_post_status'           => array(
				array(
					'id'       => 'status',
					'type'     => 'select',
					'label'    => 'Set post status to',
					'options'  => array(
						'publish' => 'Published',
						'draft'   => 'Draft',
						'private' => 'Private',
						'trash'   => 'Trash',
					),
					'default'  => 'draft',
					'required' => true,
					'note'     => 'Pro Feature',
				),
				array(
					'id'      => 'post_type',
					'type'    => 'select',
					'label'   => 'Post type',
					'options' => array(
						'post' => 'Posts',
						'page' => 'Pages',
						'all'  => 'All',
					),
					'default' => 'post',
				),
			),
			'archive_old_posts'            => array(
				array(
					'id'       => 'older_than_days',
					'type'     => 'number',
					'label'    => 'Archive posts older than (days)',
					'default'  => 365,
					'required' => true,
					'note'     => 'Pro Feature',
				),
				array(
					'id'          => 'archive_category',
					'type'        => 'text',
					'label'       => 'Archive category slug',
					'placeholder' => 'archive',
					'note'        => 'Posts will be moved to this category',
				),
			),
			'run_tool_a11y_audit'          => array(
				array(
					'id'      => 'tool',
					'type'    => 'hidden',
					'default' => 'a11y-audit',
				),
				array(
					'id'       => 'scan_mode',
					'type'     => 'select',
					'label'    => 'Scan Mode',
					'options'  => array(
						'specific' => 'Scan a specific URL',
						'cluster'  => 'Scan a cluster of URLs',
						'all'      => 'Scan all posts & pages (in batches)',
					),
					'default'  => 'specific',
					'required' => true,
					'note'     => 'Choose how many pages to scan',
				),
				array(
					'id'          => 'url',
					'type'        => 'text',
					'label'       => 'URL to scan',
					'placeholder' => 'https://example.com/about',
					'required'    => true,
					'show_if'     => array(
						'field' => 'scan_mode',
						'value' => 'specific',
					),
					'note'        => 'Must be from your own domain',
				),
				array(
					'id'          => 'urls',
					'type'        => 'textarea',
					'label'       => 'URLs to scan (one per line)',
					'placeholder' => 'https://example.com/about' . "\n" . 'https://example.com/contact',
					'rows'        => 5,
					'required'    => true,
					'show_if'     => array(
						'field' => 'scan_mode',
						'value' => 'cluster',
					),
					'note'        => 'All URLs must be from your own domain',
				),
				array(
					'id'       => 'batch_size',
					'type'     => 'number',
					'label'    => 'URLs per batch',
					'default'  => 10,
					'min'      => 1,
					'max'      => 50,
					'required' => true,
					'show_if'  => array(
						'field' => 'scan_mode',
						'value' => 'all',
					),
					'note'     => 'How many pages to scan at once',
				),
			),
			'run_tool_broken_links'        => array(
				array(
					'id'      => 'tool',
					'type'    => 'hidden',
					'default' => 'broken-links',
				),
				array(
					'id'       => 'scan_mode',
					'type'     => 'select',
					'label'    => 'Scan Mode',
					'options'  => array(
						'specific' => 'Scan a specific URL',
						'all'      => 'Scan all site content',
					),
					'default'  => 'all',
					'required' => true,
					'note'     => 'Scan a single page or entire site',
				),
				array(
					'id'          => 'url',
					'type'        => 'text',
					'label'       => 'URL to scan',
					'placeholder' => 'https://example.com/about',
					'required'    => false,
					'show_if'     => array(
						'field' => 'scan_mode',
						'value' => 'specific',
					),
					'note'        => 'Must be from your own domain',
				),
			),
			'run_tool_mobile_friendliness' => array(
				array(
					'id'      => 'tool',
					'type'    => 'hidden',
					'default' => 'mobile-friendliness',
				),
				array(
					'id'       => 'scan_mode',
					'type'     => 'select',
					'label'    => 'Scan Mode',
					'options'  => array(
						'specific' => 'Scan a specific URL',
						'all'      => 'Scan all posts & pages',
					),
					'default'  => 'specific',
					'required' => true,
					'note'     => 'Check mobile responsiveness',
				),
				array(
					'id'          => 'url',
					'type'        => 'text',
					'label'       => 'URL to scan',
					'placeholder' => 'https://example.com',
					'required'    => true,
					'show_if'     => array(
						'field' => 'scan_mode',
						'value' => 'specific',
					),
					'note'        => 'Must be from your own domain',
				),
			),
			'run_tool_simple_cache'        => array(
				array(
					'id'      => 'tool',
					'type'    => 'hidden',
					'default' => 'simple-cache',
				),
				array(
					'id'       => 'action',
					'type'     => 'select',
					'label'    => 'Cache Action',
					'options'  => array(
						'status'       => 'Check status',
						'clear'        => 'Clear cache',
						'save_options' => 'Save options',
					),
					'default'  => 'status',
					'required' => true,
				),
				array(
					'id'      => 'confirm',
					'type'    => 'checkbox',
					'label'   => 'Confirm clearing cache',
					'show_if' => array(
						'field' => 'action',
						'value' => 'clear',
					),
					'note'    => 'This will clear all cached pages',
				),
			),
			'run_tool_magic_link_support'  => array(
				array(
					'id'      => 'tool',
					'type'    => 'hidden',
					'default' => 'magic-link-support',
				),
				array(
					'id'       => 'action',
					'type'     => 'select',
					'label'    => 'Magic Link Action',
					'options'  => array(
						'create' => 'Create new link',
						'revoke' => 'Revoke link',
					),
					'default'  => 'create',
					'required' => true,
				),
				array(
					'id'       => 'expiry_hours',
					'type'     => 'number',
					'label'    => 'Expires in (hours)',
					'default'  => 24,
					'min'      => 1,
					'max'      => 720,
					'required' => true,
					'show_if'  => array(
						'field' => 'action',
						'value' => 'create',
					),
					'note'     => 'Link will automatically expire after this time',
				),
				array(
					'id'          => 'description',
					'type'        => 'text',
					'label'       => 'Link description',
					'placeholder' => 'Workflow-generated support link',
					'required'    => false,
					'show_if'     => array(
						'field' => 'action',
						'value' => 'create',
					),
				),
				array(
					'id'          => 'token',
					'type'        => 'text',
					'label'       => 'Token to revoke',
					'placeholder' => 'Paste the token to revoke',
					'required'    => true,
					'show_if'     => array(
						'field' => 'action',
						'value' => 'revoke',
					),
				),
			),
			'run_tool_dark_mode'           => array(
				array(
					'id'      => 'tool',
					'type'    => 'hidden',
					'default' => 'dark-mode',
				),
				array(
					'id'    => 'confirm',
					'type'  => 'checkbox',
					'label' => __( 'Confirm syncing dark mode preference', 'wpshadow' ),
				),
			),
		);

		if ( isset( $configs[ $action_id ] ) ) {
			return $configs[ $action_id ];
		}

		return self::get_default_action_config( $action_id );
	}

	/**
	 * Get a default action configuration when none is defined.
	 *
	 * @param string $action_id Action ID.
	 * @return array Default config fields.
	 */
	private static function get_default_action_config( $action_id ): array {
		$action = self::get_action_metadata( $action_id );
		$label  = isset( $action['label'] ) ? $action['label'] : $action_id;

		return array(
			array(
				'id'    => 'confirm',
				'type'  => 'checkbox',
				'label' => sprintf( __( 'Confirm "%s" should run', 'wpshadow' ), $label ),
				'note'  => __( 'This is a simple safety check to prevent accidental runs.', 'wpshadow' ),
			),
		);
	}

	/**
	 * Find action metadata by ID.
	 *
	 * @param string $action_id Action ID.
	 * @return array Action metadata.
	 */
	private static function get_action_metadata( $action_id ): array {
		$actions = self::get_all_actions();
		foreach ( $actions as $category ) {
			if ( empty( $category['actions'] ) ) {
				continue;
			}
			if ( isset( $category['actions'][ $action_id ] ) ) {
				return $category['actions'][ $action_id ];
			}
		}

		return array();
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
		$type   = $trigger['type'];
		$config = $trigger['config'];

		// Map wizard trigger types to executor trigger types
		$type_map = array(
			'time_daily'                => 'time_trigger',
			'time_weekly'               => 'time_trigger',
			'time_hourly'               => 'time_trigger',
			'page_load_all'             => 'page_load_trigger',
			'page_load_frontend'        => 'page_load_trigger',
			'page_load_admin'           => 'page_load_trigger',
			'page_load_single'          => 'page_load_trigger',
			'page_load_archive'         => 'page_load_trigger',
			'page_load_home'            => 'page_load_trigger',
			'plugin_update_trigger'     => 'plugin_update_trigger',
			'backup_completion_trigger' => 'backup_completion_trigger',
			'plugin_state_changed'      => 'event_trigger',
			'theme_switched'            => 'event_trigger',
			'user_login'                => 'event_trigger',
			'user_register'             => 'event_trigger',
			'post_status_changed'       => 'event_trigger',
			'comment_posted'            => 'event_trigger',
			'high_memory'               => 'condition_trigger',
			'debug_mode_on'             => 'condition_trigger',
			'ssl_issue'                 => 'condition_trigger',
			'too_many_plugins'          => 'condition_trigger',
			'ip_banned'                 => 'condition_trigger',
			'database_trigger'          => 'database_trigger',
			'error_log_trigger'         => 'error_log_trigger',
			'diagnostic_run_trigger'    => 'diagnostic_run_trigger',
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
		$type   = $action['type'];
		$config = $action['config'];

		// Map wizard action types to executor action types
		$type_map = array(
			'run_full_scan'        => 'run_diagnostic',
			'check_external_fonts' => 'run_diagnostic',
			'check_memory'         => 'run_diagnostic',
			'check_ssl'            => 'run_diagnostic',
			'check_plugins'        => 'run_diagnostic',
			'check_security'       => 'run_diagnostic',
			'run_treatment'        => 'apply_treatment',
			'block_external_fonts' => 'apply_treatment',
			'increase_memory'      => 'apply_treatment',
			'disable_debug'        => 'apply_treatment',
			'fix_ssl'              => 'apply_treatment',
			'cleanup_plugins'      => 'apply_treatment',
			'block_ip'             => 'apply_treatment',
			'send_email'           => 'email_action',
			'send_notification'    => 'notification_action',
			'log_activity'         => 'log_action',
			'create_backup'        => 'backup_action',
		);

		$executor_type = isset( $type_map[ $type ] ) ? $type_map[ $type ] : $type;

		return array(
			'type'   => $executor_type,
			'config' => $config,
		);
	}

	/**
	 * Get dynamically discovered treatment actions as a category
	 *
	 * @return array Discovered treatments organized by category
	 */
	public static function get_discovered_treatments(): array {
		$treatments = Workflow_Discovery::discover_treatments();

		if ( empty( $treatments ) ) {
			return array();
		}

		return array(
			'discovered_treatments' => array(
				'label'   => 'Available Fixes',
				'icon'    => 'admin-tools',
				'actions' => $treatments,
			),
		);
	}

	/**
	 * Get dynamically discovered diagnostic actions as a category
	 *
	 * @return array Discovered diagnostics organized by category
	 */
	public static function get_discovered_diagnostics(): array {
		$diagnostics = Workflow_Discovery::discover_diagnostics();

		if ( empty( $diagnostics ) ) {
			return array();
		}

		return array(
			'discovered_diagnostics' => array(
				'label'   => 'Available Checks',
				'icon'    => 'search',
				'actions' => $diagnostics,
			),
		);
	}

	/**
	 * Clear the discovery cache when files are updated
	 *
	 * Called by admin or when files are synced
	 */
	public static function refresh_discovery_cache(): void {
		Workflow_Discovery::clear_cache();
	}

	/**
	 * Get timezone options for dropdown
	 *
	 * @return array Timezone options
	 */
	private static function get_timezone_options(): array {
		$timezones = timezone_identifiers_list();
		$options   = array();

		foreach ( $timezones as $timezone ) {
			$options[ $timezone ] = $timezone;
		}

		return $options;
	}

	/**
	 * Get pages options for dropdown
	 *
	 * @return array Page ID => Title pairs
	 */
	private static function get_pages_options(): array {
		$pages = get_pages(
			array(
				'number'      => 100,
				'sort_column' => 'post_title',
			)
		);

		$options = array( '' => '- Select a Page -' );

		foreach ( $pages as $page ) {
			$options[ $page->ID ] = $page->post_title ?: '(no title)';
		}

		return $options;
	}

	/**
	 * Get approved email recipients as options for select field
	 *
	 * @return array Options array for select field
	 */
	private static function get_approved_email_options() {
		require_once WPSHADOW_PATH . 'includes/systems/workflow/class-email-recipient-manager.php';

		$recipients = Email_Recipient_Manager::get_approved_recipients();
		$options    = array();

		// Always include admin email
		$options['admin'] = 'Admin Email (' . get_option( 'admin_email' ) . ')';

		// Add approved recipients
		foreach ( $recipients as $email => $data ) {
			if ( isset( $data['approved'] ) && $data['approved'] ) {
				$options[ $email ] = $email;
			}
		}

		// If no approved recipients, at least show admin
		if ( count( $options ) === 1 ) {
			$options['_manage'] = '- Manage Approved Recipients -';
		}

		return $options;
	}
}
