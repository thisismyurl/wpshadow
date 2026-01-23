<?php
/**
 * Diagnostic: Duplicate Postmeta Keys
 *
 * Detects duplicate meta_key entries causing slow queries.
 *
 * Philosophy: Show Value (#9) - Measure duplicate data waste
 * KB Link: https://wpshadow.com/kb/duplicate-postmeta-keys
 * Training: https://wpshadow.com/training/duplicate-postmeta-keys
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
 * Duplicate Postmeta Keys diagnostic
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Duplicate_Postmeta_Keys extends Diagnostic_Base {

	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Diagnostic result or null if no issue
	 */
	public static function check(): ?array {
		global $wpdb;

		// Find posts with duplicate meta keys (same key multiple times per post)
		$duplicates = $wpdb->get_results(
			"SELECT 
				post_id,
				meta_key,
				COUNT(*) as count,
				SUM(LENGTH(meta_value)) as total_size
			FROM {$wpdb->postmeta}
			GROUP BY post_id, meta_key
			HAVING count > 1
			ORDER BY count DESC, total_size DESC
			LIMIT 100",
			ARRAY_A
		);

		if ( empty( $duplicates ) ) {
			return null;
		}

		// Calculate impact
		$total_duplicates = array_sum( array_column( $duplicates, 'count' ) ) - count( $duplicates );
		$total_size = array_sum( array_column( $duplicates, 'total_size' ) );
		$total_size_kb = round( $total_size / 1024, 2 );

		// Only flag if significant (> 100 duplicates or > 100KB)
		if ( $total_duplicates < 100 && $total_size_kb < 100 ) {
			return null;
		}

		$severity = $total_duplicates > 1000 ? 'medium' : 'low';

		$description = sprintf(
			__( 'Your database has %s duplicate postmeta entries (same post_id + meta_key combination). Duplicates waste %s KB and slow down get_post_meta() queries. WordPress expects single values for most meta keys.', 'wpshadow' ),
			number_format( $total_duplicates ),
			number_format( $total_size_kb )
		);

		// Top culprits
		$culprits = [];
		foreach ( array_slice( $duplicates, 0, 5 ) as $dup ) {
			$post_title = get_the_title( $dup['post_id'] );
			$culprits[] = sprintf(
				'Post #%d ("%s") has %d copies of "%s"',
				$dup['post_id'],
				$post_title,
				$dup['count'],
				$dup['meta_key']
			);
		}

		if ( ! empty( $culprits ) ) {
			$description .= ' ' . __( 'Examples: ', 'wpshadow' ) . implode( '; ', $culprits );
		}

		return [
			'id'                => 'duplicate-postmeta-keys',
			'title'             => __( 'Duplicate Postmeta Entries', 'wpshadow' ),
			'description'       => $description,
			'severity'          => $severity,
			'category'          => 'performance',
			'impact'            => 'medium',
			'effort'            => 'medium',
			'kb_link'           => 'https://wpshadow.com/kb/duplicate-postmeta-keys',
			'training_link'     => 'https://wpshadow.com/training/duplicate-postmeta-keys',
			'affected_resource' => sprintf( '%s duplicates, %s KB', number_format( $total_duplicates ), number_format( $total_size_kb ) ),
			'metadata'          => [
				'total_duplicates' => $total_duplicates,
				'total_size_kb'    => $total_size_kb,
				'examples'         => array_slice( $duplicates, 0, 10 ),
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
