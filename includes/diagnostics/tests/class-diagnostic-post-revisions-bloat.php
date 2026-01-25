<?php

/**
 * Diagnostic: Post Revisions Bloat
 *
 * Detects excessive post revisions slowing down post editing.
 *
 * Philosophy: Show Value (#9) - Prove database bloat impact
 * KB Link: https://wpshadow.com/kb/post-revisions-bloat
 * Training: https://wpshadow.com/training/post-revisions-bloat
 *
 * @package WPShadow
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Loaded via Diagnostic_Registry
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Post Revisions Bloat diagnostic
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Loaded via Diagnostic_Registry
 */
class Diagnostic_Post_Revisions_Bloat extends Diagnostic_Base {


	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Diagnostic result or null if no issue
	 */
	public static function check(): ?array {
		global $wpdb;

		// Count total revisions
		$revision_count = (int) $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'revision'"
		);

		if ( $revision_count < 100 ) {
			return null; // Not significant enough
		}

		// Get posts with most revisions
		$posts_with_revisions = $wpdb->get_results(
			"SELECT p.post_title, p.ID, COUNT(r.ID) as revision_count
			FROM {$wpdb->posts} p
			INNER JOIN {$wpdb->posts} r ON p.ID = r.post_parent AND r.post_type = 'revision'
			GROUP BY p.ID
			ORDER BY revision_count DESC
			LIMIT 5",
			ARRAY_A
		);

		// Calculate database size impact (rough estimate)
		$revision_size    = $wpdb->get_var(
			"SELECT SUM(LENGTH(post_content) + LENGTH(post_title))
			FROM {$wpdb->posts}
			WHERE post_type = 'revision'"
		);
		$revision_size_mb = round( $revision_size / 1024 / 1024, 2 );

		$severity = $revision_count > 1000 ? 'medium' : 'low';

		$description = sprintf(
			__( 'Your database contains %1$s post revisions consuming approximately %2$s MB. Excessive revisions slow down post editing and database performance. WordPress keeps unlimited revisions by default.', 'wpshadow' ),
			number_format( $revision_count ),
			$revision_size_mb
		);

		if ( ! empty( $posts_with_revisions ) ) {
			$top_post     = $posts_with_revisions[0];
			$description .= sprintf(
				' ' . __( 'Top culprit: "%1$s" has %2$d revisions.', 'wpshadow' ),
				$top_post['post_title'],
				$top_post['revision_count']
			);
		}

		return array(
			'id'                => 'post-revisions-bloat',
			'title'             => __( 'Excessive Post Revisions', 'wpshadow' ),
			'description'       => $description,
			'severity'          => $severity,
			'category'          => 'performance',
			'impact'            => 'medium',
			'effort'            => 'low',
			'kb_link'           => 'https://wpshadow.com/kb/post-revisions-bloat',
			'training_link'     => 'https://wpshadow.com/training/post-revisions-bloat',
			'affected_resource' => sprintf( '%s revisions, %s MB', number_format( $revision_count ), $revision_size_mb ),
			'metadata'          => array(
				'revision_count' => $revision_count,
				'size_mb'        => $revision_size_mb,
				'top_posts'      => $posts_with_revisions,
			),
		);
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Post Revisions Bloat
	 * Slug: -post-revisions-bloat
	 * File: class-diagnostic-post-revisions-bloat.php
	 *
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Post Revisions Bloat
	 * Slug: -post-revisions-bloat
	 *
	 * TODO: Review the check() method to understand what constitutes a passing test.
	 * The test should verify that:
	 * - check() returns NULL when the diagnostic condition is NOT met (site is healthy)
	 * - check() returns an array when the diagnostic condition IS met (issue found)
	 *
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live__post_revisions_bloat(): array {
		global $wpdb;

		// Recompute actual revision count
		$revision_count = (int) $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'revision'"
		);

		$threshold = 100; // Must match check() threshold

		// Call diagnostic check
		$diagnostic_result = self::check();

		// Determine expected state
		$should_find_issue      = ( $revision_count >= $threshold );
		$diagnostic_found_issue = ( $diagnostic_result !== null );

		// Compare expected vs actual diagnostic result
		$test_passes = ( $should_find_issue === $diagnostic_found_issue );

		$message = sprintf(
			'Revision count: %d (threshold: %d). Expected diagnostic to %s issue. Diagnostic %s issue. Test: %s',
			$revision_count,
			$threshold,
			$should_find_issue ? 'FIND' : 'NOT find',
			$diagnostic_found_issue ? 'FOUND' : 'DID NOT find',
			$test_passes ? 'PASS' : 'FAIL'
		);

		return array(
			'passed'  => $test_passes,
			'message' => $message,
		);
	}
}
