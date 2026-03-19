<?php
/**
 * Media Workflow Approval Process Diagnostic
 *
 * Tests editorial workflow for media approvals.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Media Workflow Approval Process Diagnostic Class
 *
 * Verifies editorial workflow for media approvals,
 * including pending/approved status tracking and permissions.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Media_Workflow_Approval_Process extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-workflow-approval-process';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Media Workflow Approval Process';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests editorial workflow for media approvals';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check for editorial workflow plugins.
		$workflow_plugins = array(
			'edit-flow/edit-flow.php',
			'publishpress/publishpress.php',
			'oasis-workflow/oasiswf.php',
			'workflow/workflow.php',
		);

		$has_workflow = false;
		foreach ( $workflow_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_workflow = true;
				break;
			}
		}

		if ( ! $has_workflow ) {
			$issues[] = __( 'No editorial workflow plugin detected for media approvals', 'wpshadow' );
		}

		// Check if attachment post status supports pending/draft.
		$attachment_post_type = get_post_type_object( 'attachment' );
		if ( empty( $attachment_post_type ) ) {
			$issues[] = __( 'Attachment post type is not registered', 'wpshadow' );
		} else {
			// Check if custom statuses are registered.
			$post_statuses = get_post_stati( array( 'show_in_admin_all_list' => false ) );
			$has_pending = in_array( 'pending', $post_statuses, true );

			if ( ! $has_pending ) {
				// No pending status available.
				$issues[] = __( 'Pending post status is not available for media workflow', 'wpshadow' );
			}
		}

		// Check for approval capability.
		$can_approve = current_user_can( 'publish_posts' ) || current_user_can( 'edit_others_posts' );
		if ( ! $can_approve && is_user_logged_in() ) {
			// Logged in user can't approve media.
			$issues[] = __( 'Current user lacks media approval capabilities', 'wpshadow' );
		}

		// Check for workflow status filters.
		$has_status_filter = has_filter( 'wp_insert_attachment_data' );
		if ( ! $has_status_filter ) {
			$issues[] = __( 'No attachment status filter detected for workflow integration', 'wpshadow' );
		}

		// Check for approval notifications.
		$has_notification = has_action( 'transition_post_status' );
		if ( ! $has_notification ) {
			$issues[] = __( 'No post status transition actions detected for approval notifications', 'wpshadow' );
		}

		// Check for media moderation queue.
		if ( ! function_exists( 'wp_count_attachments' ) ) {
			$issues[] = __( 'Attachment counting functions not available for moderation queue', 'wpshadow' );
		}

		// Check for custom media statuses.
		$has_custom_status = has_filter( 'display_post_states' );
		if ( ! $has_custom_status ) {
			// No visual indicators for media approval status.
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'low',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/media-workflow-approval-process',
			);
		}

		return null;
	}
}
