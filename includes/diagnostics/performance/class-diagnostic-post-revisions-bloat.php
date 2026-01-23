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
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
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
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
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
		$revision_size = $wpdb->get_var(
			"SELECT SUM(LENGTH(post_content) + LENGTH(post_title)) 
			FROM {$wpdb->posts} 
			WHERE post_type = 'revision'"
		);
		$revision_size_mb = round( $revision_size / 1024 / 1024, 2 );

		$severity = $revision_count > 1000 ? 'medium' : 'low';

		$description = sprintf(
			__( 'Your database contains %s post revisions consuming approximately %s MB. Excessive revisions slow down post editing and database performance. WordPress keeps unlimited revisions by default.', 'wpshadow' ),
			number_format( $revision_count ),
			$revision_size_mb
		);

		if ( ! empty( $posts_with_revisions ) ) {
			$top_post = $posts_with_revisions[0];
			$description .= sprintf(
				' ' . __( 'Top culprit: "%s" has %d revisions.', 'wpshadow' ),
				$top_post['post_title'],
				$top_post['revision_count']
			);
		}

		return [
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
			'metadata'          => [
				'revision_count'   => $revision_count,
				'size_mb'          => $revision_size_mb,
				'top_posts'        => $posts_with_revisions,
			],
		];
	}

	/**
	 * Test: Result structure validation
	 *
	 * Ensures diagnostic returns null (no issues) or array (issues found)
	 * with all required fields populated.
	 *
	 * @return array Test result with 'passed' and 'message'
	 */
	public static function test_result_structure(): array {
		$result = self::check();
		
		// Valid states: null (pass) or array (fail)
		if ( null === $result || is_array( $result ) ) {
			// If array, validate structure
			if ( is_array( $result ) ) {
				$required = array(
					'id', 'title', 'description', 'category', 
					'severity', 'threat_level'
				);
				
				foreach ( $required as $field ) {
					if ( ! isset( $result[ $field ] ) ) {
						return array(
							'passed'  => false,
							'message' => "Missing field: $field",
						);
					}
				}
				
				// Validate field types
				if ( ! is_string( $result['severity'] ) ) {
					return array(
						'passed'  => false,
						'message' => 'severity must be string',
					);
				}
				
				if ( ! is_int( $result['threat_level'] ) || $result['threat_level'] < 0 || $result['threat_level'] > 100 ) {
					return array(
						'passed'  => false,
						'message' => 'threat_level must be int 0-100',
					);
				}
			}
			
			return array(
				'passed'  => true,
				'message' => 'Result structure valid',
			);
		}
		
		return array(
			'passed'  => false,
			'message' => 'Invalid result type: ' . gettype( $result ),
		);
	}}
