<?php
/**
 * Contributor Workflow Controls Diagnostic
 *
 * Validates that contributor role has appropriate workflow controls
 * including moderation requirements and content restrictions.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6032.1300
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Contributor Workflow Controls Diagnostic Class
 *
 * Checks contributor role workflow configuration.
 *
 * @since 1.6032.1300
 */
class Diagnostic_Contributor_Workflow_Controls extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'contributor-workflow-controls';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Contributor Workflow Controls';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates contributor role workflow restrictions';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6032.1300
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$roles  = wp_roles()->roles;

		// Check if contributor role exists.
		if ( ! isset( $roles['contributor'] ) ) {
			return null; // No contributors configured.
		}

		$contributor_caps = $roles['contributor']['capabilities'];

		// Contributors should NOT be able to publish.
		if ( ! empty( $contributor_caps['publish_posts'] ) ) {
			$issues[] = __( 'Contributors can publish posts directly (bypasses moderation)', 'wpshadow' );
		}

		// Contributors should NOT be able to upload files (security risk).
		if ( ! empty( $contributor_caps['upload_files'] ) ) {
			$issues[] = __( 'Contributors can upload files (security risk)', 'wpshadow' );
		}

		// Contributors should NOT be able to edit others' posts.
		if ( ! empty( $contributor_caps['edit_others_posts'] ) ) {
			$issues[] = __( 'Contributors can edit other users\' posts', 'wpshadow' );
		}

		// Contributors should be able to edit their own posts.
		if ( empty( $contributor_caps['edit_posts'] ) ) {
			$issues[] = __( 'Contributors cannot edit their own posts (workflow broken)', 'wpshadow' );
		}

		// Contributors should be able to delete their own posts.
		if ( empty( $contributor_caps['delete_posts'] ) ) {
			$issues[] = __( 'Contributors cannot delete their own posts', 'wpshadow' );
		}

		// Get contributors.
		$contributors = get_users(
			array(
				'role'   => 'contributor',
				'fields' => array( 'ID', 'user_login' ),
			)
		);

		if ( empty( $contributors ) ) {
			return null; // No contributors, no issues.
		}

		// Check for pending posts from contributors.
		global $wpdb;
		$pending_posts = $wpdb->get_results(
			"SELECT p.ID, p.post_title, p.post_author, p.post_date
			FROM {$wpdb->posts} p
			INNER JOIN {$wpdb->users} u ON p.post_author = u.ID
			INNER JOIN {$wpdb->usermeta} um ON u.ID = um.user_id
			WHERE p.post_status = 'pending'
			AND p.post_type = 'post'
			AND um.meta_key = '{$wpdb->prefix}capabilities'
			AND um.meta_value LIKE '%contributor%'
			ORDER BY p.post_date DESC"
		);

		if ( count( $pending_posts ) > 20 ) {
			$issues[] = sprintf(
				/* translators: %d: number of pending posts */
				__( '%d posts from contributors awaiting review (review backlog)', 'wpshadow' ),
				count( $pending_posts )
			);
		}

		// Check for old pending posts.
		$old_pending = array();
		foreach ( $pending_posts as $post ) {
			$post_age = ( time() - strtotime( $post->post_date ) ) / DAY_IN_SECONDS;
			if ( $post_age > 30 ) {
				$old_pending[] = array(
					'post_id'    => $post->ID,
					'post_title' => $post->post_title,
					'days_old'   => absint( $post_age ),
				);
			}
		}

		if ( count( $old_pending ) > 5 ) {
			$issues[] = sprintf(
				/* translators: %d: number of old pending posts */
				__( '%d contributor posts pending for over 30 days (requires attention)', 'wpshadow' ),
				count( $old_pending )
			);
		}

		// Check if editorial workflow plugin is active.
		$workflow_plugins = array(
			'edit-flow/edit_flow.php',
			'publishpress/publishpress.php',
		);

		$has_workflow_plugin = false;
		foreach ( $workflow_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_workflow_plugin = true;
				break;
			}
		}

		if ( count( $contributors ) > 5 && ! $has_workflow_plugin ) {
			$issues[] = sprintf(
				/* translators: %d: number of contributors */
				__( '%d contributors but no editorial workflow plugin active (consider Edit Flow or PublishPress)', 'wpshadow' ),
				count( $contributors )
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of contributor workflow issues */
					__( 'Found %d contributor workflow configuration issues.', 'wpshadow' ),
					count( $issues )
				),
				'severity'     => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'details'      => array(
					'issues'             => $issues,
					'contributor_count'  => count( $contributors ),
					'pending_posts'      => count( $pending_posts ),
					'old_pending'        => array_slice( $old_pending, 0, 10 ),
					'recommendation'     => __( 'Ensure contributors cannot publish directly, restrict file uploads, and consider workflow automation plugins.', 'wpshadow' ),
				),
			);
		}

		return null;
	}
}
