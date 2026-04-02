<?php
/**
 * Post Status Transitions Diagnostic
 *
 * Monitors post status changes (draft→pending→publish).
 * Detects stuck or failed transitions.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Tests
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Post Status Transitions Diagnostic Class
 *
 * Checks for issues preventing normal post status workflow
 * from functioning correctly.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Post_Status_Transitions extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'post-status-transitions';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Post Status Transitions';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Monitors post status changes and detects stuck or failed transitions';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'posts';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$issues = array();

		// Check for posts stuck in 'auto-draft' status (orphaned).
		$old_auto_drafts = $wpdb->get_var(
			"SELECT COUNT(*)
			FROM {$wpdb->posts}
			WHERE post_status = 'auto-draft'
			AND post_modified < DATE_SUB(NOW(), INTERVAL 7 DAY)"
		);

		if ( $old_auto_drafts > 50 ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts */
				__( '%d old auto-drafts stuck in database (should be cleaned)', 'wpshadow' ),
				$old_auto_drafts
			);
		}

		// Check for posts stuck in 'pending' for extended periods.
		$old_pending = $wpdb->get_var(
			"SELECT COUNT(*)
			FROM {$wpdb->posts}
			WHERE post_status = 'pending'
			AND post_modified < DATE_SUB(NOW(), INTERVAL 30 DAY)"
		);

		if ( $old_pending > 10 ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts */
				__( '%d posts pending review for over 30 days', 'wpshadow' ),
				$old_pending
			);
		}

		// Check for excessive transition_post_status hooks.
		global $wp_filter;
		$transition_hooks = isset( $wp_filter['transition_post_status'] ) ? count( $wp_filter['transition_post_status']->callbacks ) : 0;
		if ( $transition_hooks > 20 ) {
			$issues[] = sprintf(
				/* translators: %d: number of hooks */
				__( '%d hooks on transition_post_status (may block transitions)', 'wpshadow' ),
				$transition_hooks
			);
		}

		// Check for posts with future dates stuck in draft/pending.
		$future_drafts = $wpdb->get_var(
			"SELECT COUNT(*)
			FROM {$wpdb->posts}
			WHERE post_status IN ('draft', 'pending')
			AND post_date > NOW()"
		);

		if ( $future_drafts > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts */
				__( '%d posts have future dates but wrong status (should be "future")', 'wpshadow' ),
				$future_drafts
			);
		}

		// Check for posts with invalid status values.
		$valid_statuses     = get_post_stati();
		$valid_status_list  = "'" . implode( "','", array_map( 'esc_sql', $valid_statuses ) ) . "'";
		$invalid_status_posts = $wpdb->get_var(
			"SELECT COUNT(*)
			FROM {$wpdb->posts}
			WHERE post_status NOT IN ({$valid_status_list})
			AND post_type IN ('post', 'page')"
		);

		if ( $invalid_status_posts > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts */
				__( '%d posts have invalid status values', 'wpshadow' ),
				$invalid_status_posts
			);
		}

		// Check for posts that transitioned incorrectly (publish date before modified date).
		$bad_transitions = $wpdb->get_var(
			"SELECT COUNT(*)
			FROM {$wpdb->posts}
			WHERE post_status = 'publish'
			AND post_date > post_modified
			AND post_date < DATE_SUB(NOW(), INTERVAL 1 DAY)"
		);

		if ( $bad_transitions > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts */
				__( '%d posts have incorrect publish/modified dates', 'wpshadow' ),
				$bad_transitions
			);
		}

		// Check for workflow plugins that might interfere.
		if ( is_plugin_active( 'edit-flow/edit_flow.php' ) || 
		     is_plugin_active( 'publishpress/publishpress.php' ) ) {
			// Check if custom statuses are registered.
			$custom_statuses = get_post_stati( array( '_builtin' => false ) );
			if ( ! empty( $custom_statuses ) ) {
				$issues[] = __( 'Custom post statuses may cause transition issues', 'wpshadow' );
			}
		}

		// Check for capability issues that could prevent transitions.
		$current_user = wp_get_current_user();
		if ( $current_user->ID > 0 ) {
			$test_post = get_posts( array( 'post_status' => 'draft', 'numberposts' => 1, 'post_type' => 'post' ) );
			if ( ! empty( $test_post ) ) {
				if ( ! current_user_can( 'publish_posts' ) && ! current_user_can( 'publish_post', $test_post[0]->ID ) ) {
					$issues[] = __( 'User permission issues may prevent status transitions', 'wpshadow' );
				}
			}
		}

		// Check for posts with mismatched status and date.
		$mismatched_status = $wpdb->get_var(
			"SELECT COUNT(*)
			FROM {$wpdb->posts}
			WHERE (
				(post_status = 'future' AND post_date <= NOW())
				OR (post_status = 'publish' AND post_date > NOW())
			)"
		);

		if ( $mismatched_status > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts */
				__( '%d posts have status/date mismatches', 'wpshadow' ),
				$mismatched_status
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/post-status-transitions',
			);
		}

		return null;
	}
}
