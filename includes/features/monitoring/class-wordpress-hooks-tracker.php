<?php
/**
 * WordPress Hooks Tracker
 *
 * Comprehensive activity tracking for all WordPress admin actions.
 * Logs user logins, account changes, content modifications, comments,
 * and settings updates to provide administrators with complete audit trail.
 *
 * @package    WPShadow
 * @subpackage Monitoring
 * @since      1.6030.212003
 */

declare(strict_types=1);

namespace WPShadow\Monitoring;

use WPShadow\Core\Activity_Logger;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WordPress Hooks Tracker Class
 *
 * Integrates with WordPress hooks to capture and log all admin activities.
 * Every action includes the user who performed it, timestamp, and relevant details.
 *
 * @since 1.6030.212003
 */
class WordPress_Hooks_Tracker {

	/**
	 * Initialize all hooks for WordPress activity tracking.
	 *
	 * Called during plugin initialization to register all hooks.
	 *
	 * @since 1.6030.212003
	 * @return void
	 */
	public static function init(): void {
		// User authentication hooks
		add_action( 'wp_login', array( self::class, 'log_user_login' ), 10, 2 );
		add_action( 'wp_logout', array( self::class, 'log_user_logout' ), 10 );
		add_action( 'wp_login_failed', array( self::class, 'log_login_failed' ), 10 );

		// User account lifecycle hooks
		add_action( 'user_register', array( self::class, 'log_user_created' ), 10, 1 );
		add_action( 'profile_update', array( self::class, 'log_user_updated' ), 10, 2 );
		add_action( 'delete_user', array( self::class, 'log_user_deleted' ), 10, 1 );

		// Role changes (needs to run before deletion).
		add_action( 'set_user_role', array( self::class, 'log_user_role_changed' ), 10, 3 );

		// Post, page, and custom post type hooks
		add_action( 'post_updated', array( self::class, 'log_post_updated' ), 10, 3 );
		add_action( 'publish_post', array( self::class, 'log_post_published' ), 10, 2 );
		add_action( 'wp_trash_post', array( self::class, 'log_post_trashed' ), 10, 1 );
		add_action( 'restore_post', array( self::class, 'log_post_restored' ), 10, 1 );
		add_action( 'delete_post', array( self::class, 'log_post_deleted' ), 10, 1 );
		add_action( 'transition_post_status', array( self::class, 'log_post_status_change' ), 10, 3 );

		// Comment hooks
		add_action( 'comment_post', array( self::class, 'log_comment_created' ), 10, 3 );
		add_action( 'edit_comment', array( self::class, 'log_comment_updated' ), 10, 1 );
		add_action( 'delete_comment', array( self::class, 'log_comment_deleted' ), 10, 1 );
		add_action( 'wp_set_comment_status', array( self::class, 'log_comment_status_changed' ), 10, 2 );
		add_action( 'spam_comment', array( self::class, 'log_comment_spammed' ), 10, 1 );
		add_action( 'unspam_comment', array( self::class, 'log_comment_unspammed' ), 10, 1 );

		// Settings hooks
		add_action( 'update_option', array( self::class, 'log_option_updated' ), 10, 3 );

		// WordPress core updates and maintenance
		add_action( 'upgrader_process_complete', array( self::class, 'log_plugin_update' ), 10, 1 );
		add_action( 'activated_plugin', array( self::class, 'log_plugin_activated' ), 10, 1 );
		add_action( 'deactivated_plugin', array( self::class, 'log_plugin_deactivated' ), 10, 1 );

		// Theme hooks
		add_action( 'switch_theme', array( self::class, 'log_theme_switched' ), 10, 3 );

		// Admin menu/customization
		add_action( 'wp_create_nav_menu', array( self::class, 'log_menu_created' ), 10, 1 );
		add_action( 'wp_update_nav_menu', array( self::class, 'log_menu_updated' ), 10, 1 );
		add_action( 'wp_delete_nav_menu', array( self::class, 'log_menu_deleted' ), 10, 1 );
	}

	// ========== User Authentication Hooks ==========

	/**
	 * Log successful user login.
	 *
	 * @since 1.6030.212003
	 * @param string $user_login The user login name.
	 * @param object $user The WP_User object of the logged-in user.
	 * @return void
	 */
	public static function log_user_login( string $user_login, object $user ): void {
		Activity_Logger::log(
			'user_login',
			sprintf( 'User logged in: %s (%s)', esc_html( $user->display_name ), esc_html( $user_login ) ),
			'user_authentication',
			array(
				'user_id'     => $user->ID,
				'user_login'  => $user_login,
				'user_role'   => self::get_user_role_string( $user->ID ),
				'ip_address'  => self::get_client_ip(),
				'user_agent'  => wp_unslash( $_SERVER['HTTP_USER_AGENT'] ?? '' ),
			)
		);
	}

	/**
	 * Log user logout.
	 *
	 * @since 1.6030.212003
	 * @return void
	 */
	public static function log_user_logout(): void {
		$user_id     = get_current_user_id();
		$user        = get_userdata( $user_id );
		$user_login  = $user ? $user->user_login : 'Unknown';
		$user_name   = $user ? $user->display_name : 'Unknown User';

		Activity_Logger::log(
			'user_logout',
			sprintf( 'User logged out: %s', esc_html( $user_name ) ),
			'user_authentication',
			array(
				'user_id'    => $user_id,
				'user_login' => $user_login,
				'ip_address' => self::get_client_ip(),
			)
		);
	}

	/**
	 * Log failed login attempts.
	 *
	 * @since 1.6030.212003
	 * @param string $username The username that failed to login.
	 * @return void
	 */
	public static function log_login_failed( string $username ): void {
		// Check if user exists
		$user = get_user_by( 'login', $username );

		Activity_Logger::log(
			'login_failed',
			sprintf( 'Failed login attempt: %s', esc_html( $username ) ),
			'user_authentication',
			array(
				'username'   => $username,
				'user_id'    => $user ? $user->ID : 0,
				'ip_address' => self::get_client_ip(),
			)
		);
	}

	// ========== User Account Lifecycle ==========

	/**
	 * Log new user creation.
	 *
	 * @since 1.6030.212003
	 * @param int $user_id The ID of the newly created user.
	 * @return void
	 */
	public static function log_user_created( int $user_id ): void {
		$user = get_userdata( $user_id );
		if ( ! $user ) {
			return;
		}

		$current_user = wp_get_current_user();

		Activity_Logger::log(
			'user_created',
			sprintf(
				'New user created: %s (%s) by %s',
				esc_html( $user->display_name ),
				esc_html( $user->user_login ),
				esc_html( $current_user->display_name )
			),
			'user_management',
			array(
				'user_id'       => $user_id,
				'user_login'    => $user->user_login,
				'user_email'    => $user->user_email,
				'user_role'     => self::get_user_role_string( $user_id ),
				'created_by'    => $current_user->ID,
				'created_by_name' => $current_user->display_name,
			)
		);
	}

	/**
	 * Log user profile/account updates.
	 *
	 * @since 1.6030.212003
	 * @param int    $user_id The ID of the updated user.
	 * @param object $old_user The WP_User object before the update.
	 * @return void
	 */
	public static function log_user_updated( int $user_id, object $old_user ): void {
		$user = get_userdata( $user_id );
		if ( ! $user ) {
			return;
		}

		$changes = self::get_user_changes( $old_user, $user );

		if ( empty( $changes ) ) {
			return; // No meaningful changes
		}

		$change_summary = implode( ', ', $changes );
		$current_user   = wp_get_current_user();

		Activity_Logger::log(
			'user_updated',
			sprintf(
				'User profile updated: %s - Changes: %s',
				esc_html( $user->display_name ),
				$change_summary
			),
			'user_management',
			array(
				'user_id'          => $user_id,
				'user_login'       => $user->user_login,
				'updated_by'       => $current_user->ID,
				'updated_by_name'  => $current_user->display_name,
				'changes'          => $changes,
				'email'            => $user->user_email,
			)
		);
	}

	/**
	 * Log user deletion.
	 *
	 * @since 1.6030.212003
	 * @param int $user_id The ID of the deleted user.
	 * @return void
	 */
	public static function log_user_deleted( int $user_id ): void {
		$user = get_userdata( $user_id );
		if ( ! $user ) {
			$user = (object) array(
				'ID'           => $user_id,
				'user_login'   => 'unknown',
				'display_name' => 'Deleted User',
			);
		}

		$current_user = wp_get_current_user();

		Activity_Logger::log(
			'user_deleted',
			sprintf(
				'User deleted: %s (%s) by %s',
				esc_html( $user->display_name ),
				esc_html( $user->user_login ),
				esc_html( $current_user->display_name )
			),
			'user_management',
			array(
				'user_id'       => $user_id,
				'user_login'    => $user->user_login,
				'deleted_by'    => $current_user->ID,
				'deleted_by_name' => $current_user->display_name,
			)
		);
	}

	/**
	 * Log user role changes.
	 *
	 * @since 1.6030.212003
	 * @param int    $user_id The user ID.
	 * @param string $new_role The new role.
	 * @param array  $old_roles The old roles.
	 * @return void
	 */
	public static function log_user_role_changed( int $user_id, string $new_role, array $old_roles ): void {
		$user         = get_userdata( $user_id );
		$current_user = wp_get_current_user();

		if ( ! $user ) {
			return;
		}

		$old_role_str = ! empty( $old_roles ) ? implode( ', ', $old_roles ) : 'None';

		Activity_Logger::log(
			'user_role_changed',
			sprintf(
				'User role changed: %s - From: %s, To: %s (Changed by: %s)',
				esc_html( $user->display_name ),
				esc_html( $old_role_str ),
				esc_html( $new_role ),
				esc_html( $current_user->display_name )
			),
			'user_management',
			array(
				'user_id'         => $user_id,
				'user_login'      => $user->user_login,
				'old_roles'       => $old_roles,
				'new_role'        => $new_role,
				'changed_by'      => $current_user->ID,
				'changed_by_name' => $current_user->display_name,
			)
		);
	}

	// ========== Post, Page, and Custom Post Type Hooks ==========

	/**
	 * Log post/page update.
	 *
	 * @since 1.6030.212003
	 * @param int    $post_id The post ID.
	 * @param object $post_after The post object after update.
	 * @param object $post_before The post object before update.
	 * @return void
	 */
	public static function log_post_updated( int $post_id, object $post_after, object $post_before ): void {
		// Don't log auto-saves or revisions
		if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
			return;
		}

		$post_type = get_post_type( $post_id );
		if ( ! $post_type || in_array( $post_type, array( 'nav_menu_item', 'attachment' ), true ) ) {
			return;
		}

		$changes       = self::get_post_changes( $post_before, $post_after );
		$current_user  = wp_get_current_user();
		$change_count  = count( $changes );
		$change_summary = implode( ', ', $changes );

		Activity_Logger::log(
			'post_updated',
			sprintf(
				'%s updated: "%s" (ID: %d) - %d changes: %s',
				ucfirst( $post_type ),
				esc_html( $post_after->post_title ),
				$post_id,
				$change_count,
				$change_summary
			),
			'content_management',
			array(
				'post_id'         => $post_id,
				'post_type'       => $post_type,
				'post_title'      => $post_after->post_title,
				'post_author'     => $post_after->post_author,
				'updated_by'      => $current_user->ID,
				'updated_by_name' => $current_user->display_name,
				'changes'         => $changes,
				'post_status'     => $post_after->post_status,
			)
		);
	}

	/**
	 * Log post publication.
	 *
	 * @since 1.6030.212003
	 * @param int    $post_id The post ID.
	 * @param object $post The post object.
	 * @return void
	 */
	public static function log_post_published( int $post_id, object $post ): void {
		if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
			return;
		}

		$post_type   = get_post_type( $post_id );
		$current_user = wp_get_current_user();

		Activity_Logger::log(
			'post_published',
			sprintf(
				'%s published: "%s" (ID: %d) by %s',
				ucfirst( $post_type ),
				esc_html( $post->post_title ),
				$post_id,
				esc_html( $current_user->display_name )
			),
			'content_management',
			array(
				'post_id'         => $post_id,
				'post_type'       => $post_type,
				'post_title'      => $post->post_title,
				'post_url'        => get_permalink( $post_id ),
				'published_by'    => $current_user->ID,
				'published_by_name' => $current_user->display_name,
			)
		);
	}

	/**
	 * Log post being moved to trash.
	 *
	 * @since 1.6030.212003
	 * @param int $post_id The post ID.
	 * @return void
	 */
	public static function log_post_trashed( int $post_id ): void {
		$post = get_post( $post_id );
		if ( ! $post ) {
			return;
		}

		$post_type    = get_post_type( $post_id );
		$current_user = wp_get_current_user();

		Activity_Logger::log(
			'post_trashed',
			sprintf(
				'%s moved to trash: "%s" (ID: %d) by %s',
				ucfirst( $post_type ),
				esc_html( $post->post_title ),
				$post_id,
				esc_html( $current_user->display_name )
			),
			'content_management',
			array(
				'post_id'       => $post_id,
				'post_type'     => $post_type,
				'post_title'    => $post->post_title,
				'trashed_by'    => $current_user->ID,
				'trashed_by_name' => $current_user->display_name,
			)
		);
	}

	/**
	 * Log post restoration from trash.
	 *
	 * @since 1.6030.212003
	 * @param int $post_id The post ID.
	 * @return void
	 */
	public static function log_post_restored( int $post_id ): void {
		$post = get_post( $post_id );
		if ( ! $post ) {
			return;
		}

		$post_type    = get_post_type( $post_id );
		$current_user = wp_get_current_user();

		Activity_Logger::log(
			'post_restored',
			sprintf(
				'%s restored from trash: "%s" (ID: %d) by %s',
				ucfirst( $post_type ),
				esc_html( $post->post_title ),
				$post_id,
				esc_html( $current_user->display_name )
			),
			'content_management',
			array(
				'post_id'        => $post_id,
				'post_type'      => $post_type,
				'post_title'     => $post->post_title,
				'restored_by'    => $current_user->ID,
				'restored_by_name' => $current_user->display_name,
			)
		);
	}

	/**
	 * Log permanent post deletion.
	 *
	 * @since 1.6030.212003
	 * @param int $post_id The post ID.
	 * @return void
	 */
	public static function log_post_deleted( int $post_id ): void {
		// Post data is already deleted at this point, so get from post meta
		$post_type = get_post_type( $post_id );
		if ( ! $post_type ) {
			return;
		}

		$current_user = wp_get_current_user();

		Activity_Logger::log(
			'post_deleted',
			sprintf(
				'%s permanently deleted (ID: %d) by %s',
				ucfirst( $post_type ),
				$post_id,
				esc_html( $current_user->display_name )
			),
			'content_management',
			array(
				'post_id'       => $post_id,
				'post_type'     => $post_type,
				'deleted_by'    => $current_user->ID,
				'deleted_by_name' => $current_user->display_name,
			)
		);
	}

	/**
	 * Log post status changes (draft, published, scheduled, etc).
	 *
	 * @since 1.6030.212003
	 * @param string $new_status The new status.
	 * @param string $old_status The old status.
	 * @param object $post The post object.
	 * @return void
	 */
	public static function log_post_status_change( string $new_status, string $old_status, object $post ): void {
		// Don't log auto-saves or revisions
		if ( wp_is_post_autosave( $post->ID ) || wp_is_post_revision( $post->ID ) ) {
			return;
		}

		// Don't log if status didn't actually change
		if ( $new_status === $old_status ) {
			return;
		}

		// Skip internal transitions (auto-draft to draft, etc)
		if ( in_array( $old_status, array( 'auto-draft', 'new' ), true ) ) {
			return;
		}

		$post_type    = get_post_type( $post->ID );
		$current_user = wp_get_current_user();

		Activity_Logger::log(
			'post_status_changed',
			sprintf(
				'%s status changed: "%s" (ID: %d) - From: %s, To: %s (by %s)',
				ucfirst( $post_type ),
				esc_html( $post->post_title ),
				$post->ID,
				esc_html( $old_status ),
				esc_html( $new_status ),
				esc_html( $current_user->display_name )
			),
			'content_management',
			array(
				'post_id'         => $post->ID,
				'post_type'       => $post_type,
				'post_title'      => $post->post_title,
				'old_status'      => $old_status,
				'new_status'      => $new_status,
				'changed_by'      => $current_user->ID,
				'changed_by_name' => $current_user->display_name,
			)
		);
	}

	// ========== Comment Hooks ==========

	/**
	 * Log comment creation.
	 *
	 * @since 1.6030.212003
	 * @param int    $comment_id The comment ID.
	 * @param int    $comment_approved Whether comment is approved (1), spam (spam), or pending (0).
	 * @param array  $commentdata Comment data array.
	 * @return void
	 */
	public static function log_comment_created( int $comment_id, $comment_approved, array $commentdata ): void {
		$comment = get_comment( $comment_id );
		if ( ! $comment ) {
			return;
		}

		$post = get_post( $comment->comment_post_ID );
		$post_title = $post ? $post->post_title : 'Unknown';
		$current_user = wp_get_current_user();

		$status = 1 === $comment_approved ? 'approved' : ( 'spam' === $comment_approved ? 'spam' : 'pending' );

		Activity_Logger::log(
			'comment_created',
			sprintf(
				'Comment created on "%s" (Post ID: %d) - Status: %s - By: %s',
				esc_html( $post_title ),
				$comment->comment_post_ID,
				esc_html( $status ),
				esc_html( $comment->comment_author )
			),
			'comment_management',
			array(
				'comment_id'      => $comment_id,
				'post_id'         => $comment->comment_post_ID,
				'post_title'      => $post_title,
				'comment_author'  => $comment->comment_author,
				'comment_author_email' => $comment->comment_author_email,
				'comment_status'  => $status,
				'created_by'      => $current_user->ID,
				'created_by_name' => $current_user->display_name,
			)
		);
	}

	/**
	 * Log comment updates.
	 *
	 * @since 1.6030.212003
	 * @param int $comment_id The comment ID.
	 * @return void
	 */
	public static function log_comment_updated( int $comment_id ): void {
		$comment = get_comment( $comment_id );
		if ( ! $comment ) {
			return;
		}

		$post = get_post( $comment->comment_post_ID );
		$post_title = $post ? $post->post_title : 'Unknown';
		$current_user = wp_get_current_user();

		Activity_Logger::log(
			'comment_updated',
			sprintf(
				'Comment updated on "%s" (ID: %d) by %s',
				esc_html( $post_title ),
				$comment_id,
				esc_html( $current_user->display_name )
			),
			'comment_management',
			array(
				'comment_id'       => $comment_id,
				'post_id'          => $comment->comment_post_ID,
				'post_title'       => $post_title,
				'updated_by'       => $current_user->ID,
				'updated_by_name'  => $current_user->display_name,
				'comment_author'   => $comment->comment_author,
			)
		);
	}

	/**
	 * Log comment deletion.
	 *
	 * @since 1.6030.212003
	 * @param int $comment_id The comment ID.
	 * @return void
	 */
	public static function log_comment_deleted( int $comment_id ): void {
		$comment = get_comment( $comment_id );
		if ( ! $comment ) {
			return;
		}

		$post = get_post( $comment->comment_post_ID );
		$post_title = $post ? $post->post_title : 'Unknown';
		$current_user = wp_get_current_user();

		Activity_Logger::log(
			'comment_deleted',
			sprintf(
				'Comment deleted from "%s" (ID: %d) by %s',
				esc_html( $post_title ),
				$comment_id,
				esc_html( $current_user->display_name )
			),
			'comment_management',
			array(
				'comment_id'      => $comment_id,
				'post_id'         => $comment->comment_post_ID,
				'post_title'      => $post_title,
				'deleted_by'      => $current_user->ID,
				'deleted_by_name' => $current_user->display_name,
				'comment_author'  => $comment->comment_author,
			)
		);
	}

	/**
	 * Log comment status changes (approve, unapprove, spam, unspam).
	 *
	 * @since 1.6030.212003
	 * @param int    $comment_id The comment ID.
	 * @param string $new_status The new status.
	 * @return void
	 */
	public static function log_comment_status_changed( int $comment_id, $new_status ): void {
		$comment = get_comment( $comment_id );
		if ( ! $comment ) {
			return;
		}

		$post = get_post( $comment->comment_post_ID );
		$post_title = $post ? $post->post_title : 'Unknown';
		$current_user = wp_get_current_user();

		Activity_Logger::log(
			'comment_status_changed',
			sprintf(
				'Comment status changed on "%s" (ID: %d) - To: %s - By: %s',
				esc_html( $post_title ),
				$comment_id,
				esc_html( $new_status ),
				esc_html( $current_user->display_name )
			),
			'comment_management',
			array(
				'comment_id'       => $comment_id,
				'post_id'          => $comment->comment_post_ID,
				'post_title'       => $post_title,
				'new_status'       => $new_status,
				'changed_by'       => $current_user->ID,
				'changed_by_name'  => $current_user->display_name,
				'comment_author'   => $comment->comment_author,
			)
		);
	}

	/**
	 * Log comment marked as spam.
	 *
	 * @since 1.6030.212003
	 * @param int $comment_id The comment ID.
	 * @return void
	 */
	public static function log_comment_spammed( int $comment_id ): void {
		$comment = get_comment( $comment_id );
		if ( ! $comment ) {
			return;
		}

		$post = get_post( $comment->comment_post_ID );
		$post_title = $post ? $post->post_title : 'Unknown';
		$current_user = wp_get_current_user();

		Activity_Logger::log(
			'comment_spammed',
			sprintf(
				'Comment marked as spam on "%s" (ID: %d) by %s',
				esc_html( $post_title ),
				$comment_id,
				esc_html( $current_user->display_name )
			),
			'comment_management',
			array(
				'comment_id'      => $comment_id,
				'post_id'         => $comment->comment_post_ID,
				'post_title'      => $post_title,
				'marked_by'       => $current_user->ID,
				'marked_by_name'  => $current_user->display_name,
				'comment_author'  => $comment->comment_author,
			)
		);
	}

	/**
	 * Log comment unmarked as spam.
	 *
	 * @since 1.6030.212003
	 * @param int $comment_id The comment ID.
	 * @return void
	 */
	public static function log_comment_unspammed( int $comment_id ): void {
		$comment = get_comment( $comment_id );
		if ( ! $comment ) {
			return;
		}

		$post = get_post( $comment->comment_post_ID );
		$post_title = $post ? $post->post_title : 'Unknown';
		$current_user = wp_get_current_user();

		Activity_Logger::log(
			'comment_unspammed',
			sprintf(
				'Comment unmarked as spam on "%s" (ID: %d) by %s',
				esc_html( $post_title ),
				$comment_id,
				esc_html( $current_user->display_name )
			),
			'comment_management',
			array(
				'comment_id'      => $comment_id,
				'post_id'         => $comment->comment_post_ID,
				'post_title'      => $post_title,
				'marked_by'       => $current_user->ID,
				'marked_by_name'  => $current_user->display_name,
				'comment_author'  => $comment->comment_author,
			)
		);
	}

	// ========== Settings Hooks ==========

	/**
	 * Log option/setting updates.
	 *
	 * Filters to exclude options that change frequently or are not admin-relevant.
	 *
	 * @since 1.6030.212003
	 * @param string $option The option name.
	 * @param mixed  $old_value The old option value.
	 * @param mixed  $new_value The new option value.
	 * @return void
	 */
	public static function log_option_updated( string $option, $old_value, $new_value ): void {
		// List of options to exclude from logging (too noisy or not relevant)
		$excluded_options = array(
			'transient_',
			'_transient',
			'_cache',
			'db_version',
			'template',
			'stylesheet',
			'cron',
			'_site_transient',
			'nonce_',
			'update_core',
			'update_plugins',
			'update_themes',
		);

		// Check if option should be excluded
		foreach ( $excluded_options as $excluded ) {
			if ( strpos( $option, $excluded ) === 0 ) {
				return;
			}
		}

		// Skip if old and new values are the same
		if ( $old_value === $new_value ) {
			return;
		}

		// Skip serialized data comparisons that didn't really change
		if ( maybe_serialize( $old_value ) === maybe_serialize( $new_value ) ) {
			return;
		}

		$current_user = wp_get_current_user();
		$value_display = self::get_option_display_value( $new_value );
		$old_display = self::get_option_display_value( $old_value );

		Activity_Logger::log(
			'option_updated',
			sprintf(
				'Setting updated: %s - Changed by: %s',
				esc_html( $option ),
				esc_html( $current_user->display_name )
			),
			'settings_management',
			array(
				'option'          => $option,
				'old_value'       => $old_display,
				'new_value'       => $value_display,
				'changed_by'      => $current_user->ID,
				'changed_by_name' => $current_user->display_name,
			)
		);
	}

	// ========== Plugin and Theme Hooks ==========

	/**
	 * Log plugin updates.
	 *
	 * @since 1.6030.212003
	 * @param object $upgrader The upgrader object.
	 * @return void
	 */
	public static function log_plugin_update( object $upgrader ): void {
		if ( ! isset( $upgrader->result ) || ! is_array( $upgrader->result ) ) {
			return;
		}

		$result = $upgrader->result;
		if ( ! isset( $result['destination_name'] ) ) {
			return;
		}

		$current_user = wp_get_current_user();

		Activity_Logger::log(
			'plugin_updated',
			sprintf(
				'Plugin updated: %s by %s',
				esc_html( $result['destination_name'] ),
				esc_html( $current_user->display_name )
			),
			'plugin_management',
			array(
				'plugin'          => $result['destination_name'],
				'updated_by'      => $current_user->ID,
				'updated_by_name' => $current_user->display_name,
			)
		);
	}

	/**
	 * Log plugin activation.
	 *
	 * @since 1.6030.212003
	 * @param string $plugin The plugin basename.
	 * @return void
	 */
	public static function log_plugin_activated( string $plugin ): void {
		$plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin );
		$plugin_name = $plugin_data['Name'] ?? $plugin;
		$current_user = wp_get_current_user();

		Activity_Logger::log(
			'plugin_activated',
			sprintf(
				'Plugin activated: %s by %s',
				esc_html( $plugin_name ),
				esc_html( $current_user->display_name )
			),
			'plugin_management',
			array(
				'plugin'          => $plugin,
				'plugin_name'     => $plugin_name,
				'activated_by'    => $current_user->ID,
				'activated_by_name' => $current_user->display_name,
			)
		);
	}

	/**
	 * Log plugin deactivation.
	 *
	 * @since 1.6030.212003
	 * @param string $plugin The plugin basename.
	 * @return void
	 */
	public static function log_plugin_deactivated( string $plugin ): void {
		$plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin );
		$plugin_name = $plugin_data['Name'] ?? $plugin;
		$current_user = wp_get_current_user();

		Activity_Logger::log(
			'plugin_deactivated',
			sprintf(
				'Plugin deactivated: %s by %s',
				esc_html( $plugin_name ),
				esc_html( $current_user->display_name )
			),
			'plugin_management',
			array(
				'plugin'           => $plugin,
				'plugin_name'      => $plugin_name,
				'deactivated_by'   => $current_user->ID,
				'deactivated_by_name' => $current_user->display_name,
			)
		);
	}

	// ========== Theme Hooks ==========

	/**
	 * Log theme switches.
	 *
	 * @since 1.6030.212003
	 * @param string $new_name Name of the new theme.
	 * @param object $new_theme WP_Theme object of the new theme.
	 * @param object $old_theme WP_Theme object of the old theme.
	 * @return void
	 */
	public static function log_theme_switched( string $new_name, object $new_theme, object $old_theme ): void {
		$current_user = wp_get_current_user();
		$old_name = $old_theme->get( 'Name' );

		Activity_Logger::log(
			'theme_switched',
			sprintf(
				'Theme changed from "%s" to "%s" by %s',
				esc_html( $old_name ),
				esc_html( $new_name ),
				esc_html( $current_user->display_name )
			),
			'theme_management',
			array(
				'old_theme'       => $old_name,
				'new_theme'       => $new_name,
				'changed_by'      => $current_user->ID,
				'changed_by_name' => $current_user->display_name,
			)
		);
	}

	// ========== Menu Hooks ==========

	/**
	 * Log menu creation.
	 *
	 * @since 1.6030.212003
	 * @param int $menu_id The menu ID.
	 * @return void
	 */
	public static function log_menu_created( int $menu_id ): void {
		$menu = wp_get_nav_menu_object( $menu_id );
		if ( ! $menu ) {
			return;
		}

		$current_user = wp_get_current_user();

		Activity_Logger::log(
			'menu_created',
			sprintf(
				'Menu created: %s (ID: %d) by %s',
				esc_html( $menu->name ),
				$menu_id,
				esc_html( $current_user->display_name )
			),
			'site_management',
			array(
				'menu_id'         => $menu_id,
				'menu_name'       => $menu->name,
				'created_by'      => $current_user->ID,
				'created_by_name' => $current_user->display_name,
			)
		);
	}

	/**
	 * Log menu updates.
	 *
	 * @since 1.6030.212003
	 * @param int $menu_id The menu ID.
	 * @return void
	 */
	public static function log_menu_updated( int $menu_id ): void {
		$menu = wp_get_nav_menu_object( $menu_id );
		if ( ! $menu ) {
			return;
		}

		$current_user = wp_get_current_user();

		Activity_Logger::log(
			'menu_updated',
			sprintf(
				'Menu updated: %s (ID: %d) by %s',
				esc_html( $menu->name ),
				$menu_id,
				esc_html( $current_user->display_name )
			),
			'site_management',
			array(
				'menu_id'         => $menu_id,
				'menu_name'       => $menu->name,
				'updated_by'      => $current_user->ID,
				'updated_by_name' => $current_user->display_name,
			)
		);
	}

	/**
	 * Log menu deletion.
	 *
	 * @since 1.6030.212003
	 * @param int $menu_id The menu ID.
	 * @return void
	 */
	public static function log_menu_deleted( int $menu_id ): void {
		$menu = wp_get_nav_menu_object( $menu_id );
		if ( ! $menu ) {
			return;
		}

		$current_user = wp_get_current_user();

		Activity_Logger::log(
			'menu_deleted',
			sprintf(
				'Menu deleted: %s (ID: %d) by %s',
				esc_html( $menu->name ),
				$menu_id,
				esc_html( $current_user->display_name )
			),
			'site_management',
			array(
				'menu_id'        => $menu_id,
				'menu_name'      => $menu->name,
				'deleted_by'     => $current_user->ID,
				'deleted_by_name' => $current_user->display_name,
			)
		);
	}

	// ========== Helper Methods ==========

	/**
	 * Get client IP address.
	 *
	 * @since 1.6030.212003
	 * @return string Client IP address.
	 */
	private static function get_client_ip(): string {
		// Check for shared internet
		if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			$ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_CLIENT_IP'] ) );
		} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			// Handle proxy
			$ips = explode( ',', sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) );
			$ip  = trim( $ips[0] );
		} else {
			$ip = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ?? '' ) );
		}

		return $ip;
	}

	/**
	 * Get user role as string.
	 *
	 * @since 1.6030.212003
	 * @param int $user_id User ID.
	 * @return string User role(s) as comma-separated string.
	 */
	private static function get_user_role_string( int $user_id ): string {
		$user = get_userdata( $user_id );
		if ( ! $user || empty( $user->roles ) ) {
			return 'none';
		}

		return implode( ', ', $user->roles );
	}

	/**
	 * Get user changes between old and new user data.
	 *
	 * @since 1.6030.212003
	 * @param object $old_user Old user object.
	 * @param object $new_user New user object.
	 * @return array Array of change descriptions.
	 */
	private static function get_user_changes( object $old_user, object $new_user ): array {
		$changes = array();

		// Compare key fields
		$fields_to_check = array(
			'user_email'    => 'Email',
			'display_name'  => 'Display Name',
			'user_url'      => 'Website',
			'user_nicename' => 'Nicename',
		);

		foreach ( $fields_to_check as $field => $label ) {
			if ( $old_user->{$field} !== $new_user->{$field} ) {
				$changes[] = sprintf(
					'%s: "%s" → "%s"',
					$label,
					esc_html( (string) $old_user->{$field} ),
					esc_html( (string) $new_user->{$field} )
				);
			}
		}

		return $changes;
	}

	/**
	 * Get post changes between old and new post data.
	 *
	 * @since 1.6030.212003
	 * @param object $old_post Old post object.
	 * @param object $new_post New post object.
	 * @return array Array of change descriptions.
	 */
	private static function get_post_changes( object $old_post, object $new_post ): array {
		$changes = array();

		$fields_to_check = array(
			'post_title'   => 'Title',
			'post_content' => 'Content',
			'post_excerpt' => 'Excerpt',
		);

		foreach ( $fields_to_check as $field => $label ) {
			if ( $old_post->{$field} !== $new_post->{$field} ) {
				$changes[] = $label;
			}
		}

		return $changes;
	}

	/**
	 * Get display-friendly representation of an option value.
	 *
	 * @since 1.6030.212003
	 * @param mixed $value The value to display.
	 * @return string Display-friendly string.
	 */
	private static function get_option_display_value( $value ): string {
		if ( is_array( $value ) || is_object( $value ) ) {
			return wp_json_encode( $value );
		}

		if ( is_bool( $value ) ) {
			return $value ? 'true' : 'false';
		}

		if ( is_null( $value ) ) {
			return 'null';
		}

		$string_value = (string) $value;

		// Truncate long values
		if ( strlen( $string_value ) > 100 ) {
			return substr( $string_value, 0, 100 ) . '...';
		}

		return esc_html( $string_value );
	}
}
