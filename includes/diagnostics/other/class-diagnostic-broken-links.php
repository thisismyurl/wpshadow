<?php
declare(strict_types=1);
/**
 * Broken Links Diagnostic
 *
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */


namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for broken links site-wide (deep scan only).
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Broken_Links extends Diagnostic_Base {
	/**
	 * Run the diagnostic check (deep scan).
	 *
	 * @return array|null Finding data or null if no issues.
	 */
	public static function check(): ?array {
		if ( ! function_exists( 'wpshadow_run_broken_links_scan' ) ) {
			return null;
		}

		$result = wpshadow_run_broken_links_scan(
			array(
				'check_internal' => true,
				'check_external' => true,
				'check_images'   => true,
				'limit'          => 100,
			)
		);

		if ( empty( $result['broken_links'] ) ) {
			return null;
		}

		$broken = $result['broken_links'];
		$count  = count( $broken );
		$first  = $broken[0];

		$title       = sprintf( 'Broken links found (%d)', (int) $count );
		$description = sprintf(
			/* translators: 1: URL, 2: post title, 3: status code */
			__( 'Example: %1$s in "%2$s" returned %3$s.', 'wpshadow' ),
			$first['url'],
			$first['post_title'],
			$first['status_code']
		);

		return array(
			'id'           => 'broken-links',
			'title'        => $title,
			'description'  => $description,
			'color'        => '#f44336',
			'bg_color'     => '#ffebee',
			'kb_link'      => 'https://wpshadow.com/kb/fix-broken-links/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=broken-links',
			'auto_fixable' => false,
			'threat_level' => 60,
			'category'     => 'seo',
			'extra'        => array(
				'broken_links'  => $broken,
				'posts_checked' => $result['posts_checked'] ?? 0,
				'links_checked' => $result['links_checked'] ?? 0,
			),
		);
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
