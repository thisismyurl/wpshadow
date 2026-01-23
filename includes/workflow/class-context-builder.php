<?php

/**
 * Context Builder Utility
 *
 * Centralized context-building logic for workflow triggers.
 * Eliminates duplicate context creation code across handlers.
 *
 * @package WPShadow
 * @subpackage Workflow
 */

declare(strict_types=1);

namespace WPShadow\Workflow;

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Context Builder - DRY utility for workflow context arrays
 */
class Context_Builder
{

	/**
	 * Build frontend page load context
	 *
	 * @return array Context array with page type information
	 */
	public static function build_frontend_page_load(): array
	{
		return array(
			'trigger_type'  => 'page_load',
			'context'       => 'frontend',
			'is_admin'      => false,
			'post_type'     => get_post_type(),
			'is_single'     => is_single(),
			'is_page'       => is_page(),
			'is_archive'    => is_archive(),
			'is_category'   => is_category(),
			'is_tag'        => is_tag(),
			'is_home'       => is_home(),
			'is_front_page' => is_front_page(),
		);
	}

	/**
	 * Build admin page load context
	 *
	 * @return array Context array with admin screen information
	 */
	public static function build_admin_page_load(): array
	{
		return array(
			'trigger_type' => 'page_load',
			'context'      => 'admin',
			'is_admin'     => true,
			'screen'       => function_exists('get_current_screen') ? get_current_screen() : null,
		);
	}

	/**
	 * Build plugin state change context
	 *
	 * @param string $plugin Plugin basename.
	 * @param string $action Action performed (activated|deactivated).
	 * @return array Context array
	 */
	public static function build_plugin_state_changed(string $plugin, string $action): array
	{
		return array(
			'trigger_type'  => 'plugin_state_changed',
			'plugin'        => $plugin,
			'action'        => $action,
			'plugin_name'   => self::get_plugin_name($plugin),
			'timestamp'     => current_time('mysql'),
		);
	}

	/**
	 * Build theme switched context
	 *
	 * @param string $new_name  New theme name.
	 * @param object $new_theme New theme object.
	 * @param object $old_theme Old theme object.
	 * @return array Context array
	 */
	public static function build_theme_switched(string $new_name, $new_theme, $old_theme): array
	{
		return array(
			'trigger_type' => 'theme_switched',
			'new_theme'    => $new_name,
			'old_theme'    => is_object($old_theme) ? $old_theme->get('Name') : '',
			'timestamp'    => current_time('mysql'),
		);
	}

	/**
	 * Build user registered context
	 *
	 * @param int $user_id User ID.
	 * @return array Context array
	 */
	public static function build_user_registered(int $user_id): array
	{
		$user = get_userdata($user_id);

		return array(
			'trigger_type' => 'user_register',
			'user_id'      => $user_id,
			'user_login'   => $user ? $user->user_login : '',
			'user_email'   => $user ? $user->user_email : '',
			'timestamp'    => current_time('mysql'),
		);
	}

	/**
	 * Build post status changed context
	 *
	 * @param string $new_status New post status.
	 * @param string $old_status Old post status.
	 * @param object $post       Post object.
	 * @return array Context array
	 */
	public static function build_post_status_changed(string $new_status, string $old_status, $post): array
	{
		return array(
			'trigger_type' => 'post_status_changed',
			'post_id'      => $post->ID,
			'post_type'    => $post->post_type,
			'post_title'   => $post->post_title,
			'old_status'   => $old_status,
			'new_status'   => $new_status,
			'author_id'    => $post->post_author,
			'timestamp'    => current_time('mysql'),
		);
	}

	/**
	 * Build error log context
	 *
	 * @param string $error_level Error severity level.
	 * @param string $message     Error message.
	 * @return array Context array
	 */
	public static function build_error_logged(string $error_level, string $message): array
	{
		return array(
			'trigger_type' => 'error_log',
			'error_level'  => $error_level,
			'message'      => $message,
			'timestamp'    => current_time('mysql'),
		);
	}

	/**
	 * Build diagnostic run context
	 *
	 * @param array $activity Activity log data.
	 * @return array Context array
	 */
	public static function build_diagnostic_run(array $activity): array
	{
		return array(
			'trigger_type'   => 'diagnostic_run',
			'diagnostic_id'  => $activity['diagnostic_id'] ?? '',
			'result'         => $activity['result'] ?? '',
			'threat_level'   => $activity['threat_level'] ?? 0,
			'timestamp'      => $activity['timestamp'] ?? current_time('mysql'),
		);
	}

	/**
	 * Build time-based trigger context
	 *
	 * @return array Context array
	 */
	public static function build_time_trigger(): array
	{
		return array(
			'trigger_type' => 'time',
			'timestamp'    => current_time('mysql'),
		);
	}

	/**
	 * Build backup completion context
	 *
	 * @param array $backup_data Backup result data.
	 * @return array Context array
	 */
	public static function build_backup_completed(array $backup_data): array
	{
		return array(
			'trigger_type'   => 'backup_completion',
			'backup_success' => $backup_data['success'] ?? false,
			'backup_size'    => $backup_data['size'] ?? 0,
			'backup_path'    => $backup_data['path'] ?? '',
			'timestamp'      => current_time('mysql'),
		);
	}

	/**
	 * Get plugin name from basename
	 *
	 * @param string $plugin Plugin basename.
	 * @return string Plugin name
	 */
	private static function get_plugin_name(string $plugin): string
	{
		if (! function_exists('get_plugins')) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$plugins = get_plugins();
		return isset($plugins[$plugin]['Name']) ? $plugins[$plugin]['Name'] : $plugin;
	}
}
