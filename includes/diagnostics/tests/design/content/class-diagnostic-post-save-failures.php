<?php
/**
 * Post Save Failures Diagnostic
 *
 * Detects posts failing to save properly. Monitors save operations and
 * identifies causes of failures (permissions, database, hooks).
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Tests
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Post Save Failures Diagnostic Class
 *
 * Checks for issues preventing posts from saving properly.
 * Examines database errors, permission issues, and problematic hooks.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Post_Save_Failures extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'post-save-failures';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Post Save Failures';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects posts failing to save properly due to permissions, database, or hook issues';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'posts';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$issues = array();

		// Check if posts table is writable by attempting a test query.
		$can_write = $wpdb->query( "SELECT 1 FROM {$wpdb->posts} LIMIT 1" );
		if ( false === $can_write ) {
			$issues[] = __( 'Database posts table may not be accessible', 'wpshadow' );
		}

		// Check for excessive hooks on save_post that might cause failures.
		global $wp_filter;
		$save_post_hooks = isset( $wp_filter['save_post'] ) ? count( $wp_filter['save_post']->callbacks ) : 0;
		if ( $save_post_hooks > 20 ) {
			$issues[] = sprintf(
				/* translators: %d: number of hooks */
				__( '%d hooks attached to save_post action (may cause timeouts)', 'wpshadow' ),
				$save_post_hooks
			);
		}

		// Check for posts with invalid post_author that would fail on save.
		$invalid_authors = $wpdb->get_var(
			"SELECT COUNT(*)
			FROM {$wpdb->posts} p
			LEFT JOIN {$wpdb->users} u ON p.post_author = u.ID
			WHERE p.post_author > 0
			AND u.ID IS NULL"
		);

		if ( $invalid_authors > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts */
				__( '%d posts have invalid authors (will fail on update)', 'wpshadow' ),
				$invalid_authors
			);
		}

		// Check for posts with excessively long content that might cause save failures.
		$long_posts = $wpdb->get_var(
			"SELECT COUNT(*)
			FROM {$wpdb->posts}
			WHERE LENGTH(post_content) > 65535
			AND post_status IN ('publish', 'draft', 'pending')"
		);

		if ( $long_posts > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts */
				__( '%d posts have very long content (may exceed limits)', 'wpshadow' ),
				$long_posts
			);
		}

		// Check PHP max_input_vars setting.
		$max_input_vars = ini_get( 'max_input_vars' );
		if ( $max_input_vars && $max_input_vars < 3000 ) {
			$issues[] = sprintf(
				/* translators: %d: current value */
				__( 'PHP max_input_vars is low (%d) - may fail saving complex posts', 'wpshadow' ),
				$max_input_vars
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'high',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/post-save-failures?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
