<?php
/**
 * Color Contrast in Content Diagnostic
 *
 * Scans actual page content for WCAG color contrast violations.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26028.1905
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Color Contrast in Content Class
 *
 * Tests color contrast compliance.
 *
 * @since 1.26028.1905
 */
class Diagnostic_Color_Contrast_In_Content extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'color-contrast-in-content';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Color Contrast in Content';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Scans actual page content for WCAG color contrast violations';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26028.1905
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$contrast_check = self::check_color_contrast();
		
		if ( $contrast_check['violations_found'] > 0 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of contrast violations */
					__( '%d potential color contrast violations detected (WCAG AA requires 4.5:1 ratio)', 'wpshadow' ),
					$contrast_check['violations_found']
				),
				'severity'     => 'medium',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/color-contrast-in-content',
				'meta'         => array(
					'violations_found' => $contrast_check['violations_found'],
					'elements_tested'  => $contrast_check['elements_tested'],
					'common_issues'    => $contrast_check['common_issues'],
				),
			);
		}

		return null;
	}

	/**
	 * Check color contrast in content.
	 *
	 * @since  1.26028.1905
	 * @return array Check results.
	 */
	private static function check_color_contrast() {
		global $wpdb;

		$check = array(
			'violations_found' => 0,
			'elements_tested'  => 0,
			'common_issues'    => array(),
		);

		// Sample recent posts.
		$posts = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT post_content
				FROM {$wpdb->posts}
				WHERE post_status = %s
				AND post_type IN ('post', 'page')
				ORDER BY post_date DESC
				LIMIT 20",
				'publish'
			)
		);

		$problematic_patterns = array(
			'light_gray_text' => array(
				'pattern' => '/color\s*:\s*#([cdef][0-9a-f]{5}|[89ab][0-9a-f]{5})/i',
				'issue'   => __( 'Light gray text (common contrast violation)', 'wpshadow' ),
			),
			'colored_text'    => array(
				'pattern' => '/color\s*:\s*#[0-9a-f]{6}/i',
				'issue'   => __( 'Custom text colors (potential contrast issues)', 'wpshadow' ),
			),
		);

		foreach ( $posts as $post ) {
			$content = $post->post_content;

			// Check for inline styles with color.
			foreach ( $problematic_patterns as $key => $pattern_data ) {
				preg_match_all( $pattern_data['pattern'], $content, $matches );
				
				if ( ! empty( $matches[0] ) ) {
					$check['violations_found'] += count( $matches[0] );
					$check['elements_tested'] += count( $matches[0] );
					
					if ( ! in_array( $pattern_data['issue'], $check['common_issues'], true ) ) {
						$check['common_issues'][] = $pattern_data['issue'];
					}
				}
			}
		}

		return $check;
	}
}
