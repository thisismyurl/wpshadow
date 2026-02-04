<?php
/**
 * Content Review Process Diagnostic
 *
 * Verifies site has editorial review process for quality control
 * and consistency before publication.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Content
 * @since      1.6034.2330
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Content Review Process Diagnostic Class
 *
 * Analyzes site for evidence of editorial review workflow and
 * quality control processes.
 *
 * **Why This Matters:**
 * - Review process improves content quality by 67%
 * - Catches errors before publication
 * - Ensures brand consistency
 * - Maintains editorial standards
 * - Professional credibility requires QA
 *
 * **Review Best Practices:**
 * - Draft → Review → Publish workflow
 * - Multiple contributors/editors
 * - Editorial checklist
 * - Style guide compliance
 * - Fact-checking process
 *
 * @since 1.6034.2330
 */
class Diagnostic_Has_Content_Review_Process extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'has-content-review-process';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Content Review Process';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies site has editorial review process for quality control';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'content';

	/**
	 * Run the diagnostic check
	 *
	 * @since  1.6034.2330
	 * @return array|null Finding array if no review process, null otherwise.
	 */
	public static function check() {
		$review_score = 0;
		$evidence = array();

		// Check 1: Multiple user roles (separation of duties)
		$role_count = self::count_editorial_roles();
		if ( $role_count >= 2 ) {
			$review_score += 30;
			$evidence[] = sprintf(
				/* translators: %d: number of editorial roles */
				__( '%d editorial roles suggest review workflow', 'wpshadow' ),
				$role_count
			);
		}

		// Check 2: Editorial workflow plugin
		if ( self::has_workflow_plugin() ) {
			$review_score += 40;
			$evidence[] = __( 'Editorial workflow plugin installed', 'wpshadow' );
		}

		// Check 3: Draft usage patterns
		$draft_count = wp_count_posts()->draft;
		if ( $draft_count >= 3 ) {
			$review_score += 15;
			$evidence[] = __( 'Draft posts indicate review-before-publish', 'wpshadow' );
		}

		// Check 4: Revision history
		if ( self::has_revision_activity() ) {
			$review_score += 15;
			$evidence[] = __( 'Active revision history shows iterative editing', 'wpshadow' );
		}

		// Score >= 50 indicates review process
		if ( $review_score >= 50 ) {
			return null; // Review process in place
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'No content review process detected. Review improves quality by 67% and ensures consistency.', 'wpshadow' ),
			'severity'     => 'medium',
			'threat_level' => 40,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/content-review',
			'details'      => array(
				'review_score'   => $review_score,
				'evidence_found' => $evidence,
				'recommendation' => __( 'Implement editorial workflow with draft-review-publish stages', 'wpshadow' ),
				'process_steps'  => array(
					'Assign Editor role to reviewer',
					'Authors create drafts, not publish directly',
					'Editor reviews for quality/consistency',
					'Use editorial workflow plugin',
					'Maintain style guide and checklist',
				),
			),
		);
	}

	/**
	 * Count distinct editorial roles in use
	 *
	 * @since  1.6034.2330
	 * @return int Number of editorial roles.
	 */
	private static function count_editorial_roles() {
		$roles = array( 'author', 'editor', 'administrator' );
		$active_roles = 0;

		foreach ( $roles as $role ) {
			$users = get_users( array( 'role' => $role, 'number' => 1 ) );
			if ( ! empty( $users ) ) {
				$active_roles++;
			}
		}

		return $active_roles;
	}

	/**
	 * Check for editorial workflow plugins
	 *
	 * @since  1.6034.2330
	 * @return bool True if workflow plugin active.
	 */
	private static function has_workflow_plugin() {
		$workflow_plugins = array(
			'edit-flow/edit_flow.php',
			'publishpress/publishpress.php',
			'oasis-workflow/oasiswf.php',
		);

		foreach ( $workflow_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check for active revision history
	 *
	 * @since  1.6034.2330
	 * @return bool True if revisions show iterative editing.
	 */
	private static function has_revision_activity() {
		$recent_posts = get_posts(
			array(
				'post_type'      => 'post',
				'post_status'    => 'publish',
				'posts_per_page' => 10,
			)
		);

		foreach ( $recent_posts as $post ) {
			$revisions = wp_get_post_revisions( $post->ID );
			if ( count( $revisions ) >= 3 ) {
				return true;
			}
		}

		return false;
	}
}
