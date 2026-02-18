<?php
/**
 * Author Publishing Restrictions Diagnostic
 *
 * Validates that author role has appropriate publishing capabilities
 * while maintaining proper content moderation controls.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6032.1330
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Author Publishing Restrictions Diagnostic Class
 *
 * Checks author role capability configuration.
 *
 * @since 1.6032.1330
 */
class Diagnostic_Author_Publishing_Restrictions extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'author-publishing-restrictions';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Author Publishing Restrictions';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates author role publishing capabilities';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6032.1330
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$roles  = wp_roles()->roles;

		// Check if author role exists.
		if ( ! isset( $roles['author'] ) ) {
			return null; // No authors configured.
		}

		$author_caps = $roles['author']['capabilities'];

		// Authors should be able to publish their own posts.
		if ( empty( $author_caps['publish_posts'] ) ) {
			$issues[] = __( 'Authors cannot publish posts (workflow broken)', 'wpshadow' );
		}

		// Authors should be able to upload files.
		if ( empty( $author_caps['upload_files'] ) ) {
			$issues[] = __( 'Authors cannot upload files (limits content creation)', 'wpshadow' );
		}

		// Authors should NOT be able to edit others' posts.
		if ( ! empty( $author_caps['edit_others_posts'] ) ) {
			$issues[] = __( 'Authors can edit other users\' posts (security concern)', 'wpshadow' );
		}

		// Authors should NOT be able to delete others' posts.
		if ( ! empty( $author_caps['delete_others_posts'] ) ) {
			$issues[] = __( 'Authors can delete other users\' posts (security risk)', 'wpshadow' );
		}

		// Authors should NOT be able to publish pages.
		if ( ! empty( $author_caps['publish_pages'] ) ) {
			$issues[] = __( 'Authors can publish pages (consider restricting to editors)', 'wpshadow' );
		}

		// Authors should NOT be able to edit published posts by others.
		if ( ! empty( $author_caps['edit_published_posts'] ) && ! empty( $author_caps['edit_others_posts'] ) ) {
			$issues[] = __( 'Authors can edit published posts by others (editorial control issue)', 'wpshadow' );
		}

		// Check for dangerous custom capabilities.
		$dangerous_caps = array( 'manage_options', 'edit_users', 'delete_users', 'manage_categories', 'moderate_comments' );
		$has_dangerous  = array();

		foreach ( $dangerous_caps as $cap ) {
			if ( ! empty( $author_caps[ $cap ] ) ) {
				$has_dangerous[] = $cap;
			}
		}

		if ( ! empty( $has_dangerous ) ) {
			$issues[] = sprintf(
				/* translators: %s: comma-separated list of capabilities */
				__( 'Authors have dangerous capabilities: %s', 'wpshadow' ),
				implode( ', ', $has_dangerous )
			);
		}

		// Get authors.
		$authors = get_users(
			array(
				'role'   => 'author',
				'fields' => array( 'ID', 'user_login' ),
			)
		);

		if ( empty( $authors ) ) {
			return null; // No authors, no issues.
		}

		// Check for authors with excessive publish activity.
		global $wpdb;
		$prolific_authors = $wpdb->get_results(
			"SELECT post_author, COUNT(*) as post_count, u.user_login
			FROM {$wpdb->posts} p
			INNER JOIN {$wpdb->users} u ON p.post_author = u.ID
			INNER JOIN {$wpdb->usermeta} um ON u.ID = um.user_id
			WHERE p.post_status = 'publish'
			AND p.post_type = 'post'
			AND p.post_date > DATE_SUB(NOW(), INTERVAL 30 DAY)
			AND um.meta_key = '{$wpdb->prefix}capabilities'
			AND um.meta_value LIKE '%author%'
			GROUP BY post_author
			HAVING post_count > 50
			ORDER BY post_count DESC"
		);

		if ( ! empty( $prolific_authors ) ) {
			$issues[] = sprintf(
				/* translators: %d: number of prolific authors */
				__( '%d authors published 50+ posts in the last 30 days (monitor for quality)', 'wpshadow' ),
				count( $prolific_authors )
			);
		}

		// Check for authors with unpublished drafts.
		$old_drafts = $wpdb->get_results(
			"SELECT COUNT(*) as draft_count, post_author, u.user_login
			FROM {$wpdb->posts} p
			INNER JOIN {$wpdb->users} u ON p.post_author = u.ID
			INNER JOIN {$wpdb->usermeta} um ON u.ID = um.user_id
			WHERE p.post_status = 'draft'
			AND p.post_type = 'post'
			AND p.post_modified < DATE_SUB(NOW(), INTERVAL 60 DAY)
			AND um.meta_key = '{$wpdb->prefix}capabilities'
			AND um.meta_value LIKE '%author%'
			GROUP BY post_author
			HAVING draft_count > 10"
		);

		if ( ! empty( $old_drafts ) ) {
			$issues[] = sprintf(
				/* translators: %d: number of authors with old drafts */
				__( '%d authors have 10+ drafts older than 60 days (content cleanup needed)', 'wpshadow' ),
				count( $old_drafts )
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of author restriction issues */
					__( 'Found %d author role configuration issues.', 'wpshadow' ),
					count( $issues )
				),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'details'      => array(
					'issues'           => $issues,
					'author_count'     => count( $authors ),
					'prolific_authors' => $prolific_authors,
					'recommendation'   => __( 'Ensure authors can publish their own posts but cannot edit or delete content by others.', 'wpshadow' ),
				),
			);
		}

		return null;
	}
}
