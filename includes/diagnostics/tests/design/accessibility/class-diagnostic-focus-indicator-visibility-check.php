<?php
/**
 * Focus Indicator Visibility Check Diagnostic
 *
 * Tests keyboard focus indicators aren't removed by CSS.
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
 * Focus Indicator Visibility Check Class
 *
 * Tests focus indicator implementation.
 *
 * @since 1.26028.1905
 */
class Diagnostic_Focus_Indicator_Visibility_Check extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'focus-indicator-visibility-check';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Focus Indicator Visibility Check';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests keyboard focus indicators aren\'t removed by CSS';

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
		$focus_check = self::check_focus_indicators();
		
		if ( $focus_check['has_issues'] ) {
			$issues = array();
			
			if ( $focus_check['outline_removed_globally'] ) {
				$issues[] = __( 'outline:none applied globally on :focus (removes all focus indicators)', 'wpshadow' );
			}

			if ( $focus_check['outline_removed_count'] > 0 ) {
				$issues[] = sprintf(
					/* translators: %d: number of CSS rules */
					__( '%d CSS rules remove focus indicators', 'wpshadow' ),
					$focus_check['outline_removed_count']
				);
			}

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( ' ', $issues ),
				'severity'     => 'high',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/focus-indicator-visibility-check',
				'meta'         => array(
					'outline_removed_globally' => $focus_check['outline_removed_globally'],
					'outline_removed_count'    => $focus_check['outline_removed_count'],
					'files_checked'            => $focus_check['files_checked'],
				),
			);
		}

		return null;
	}

	/**
	 * Check focus indicator implementation.
	 *
	 * @since  1.26028.1905
	 * @return array Check results.
	 */
	private static function check_focus_indicators() {
		$check = array(
			'has_issues'               => false,
			'outline_removed_globally' => false,
			'outline_removed_count'    => 0,
			'files_checked'            => 0,
		);

		// Get theme stylesheet directory.
		$stylesheet_dir = get_stylesheet_directory();
		$css_files = array();

		// Find CSS files.
		if ( file_exists( $stylesheet_dir . '/style.css' ) ) {
			$css_files[] = $stylesheet_dir . '/style.css';
		}

		// Check for additional CSS files.
		$css_dir = $stylesheet_dir . '/css';
		if ( is_dir( $css_dir ) ) {
			$found_files = glob( $css_dir . '/*.css' );
			if ( ! empty( $found_files ) ) {
				$css_files = array_merge( $css_files, array_slice( $found_files, 0, 5 ) );
			}
		}

		// Analyze CSS files.
		foreach ( $css_files as $file ) {
			++$check['files_checked'];
			
			$css_content = file_get_contents( $file );
			
			// Check for outline:none or outline:0 on focus.
			$problematic_patterns = array(
				'/:focus\s*\{[^}]*outline\s*:\s*(none|0)/i',
				'/\*:focus\s*\{[^}]*outline\s*:\s*(none|0)/i',
				'/\*\s*\{[^}]*outline\s*:\s*(none|0)/i', // Global reset.
			);

			foreach ( $problematic_patterns as $pattern ) {
				if ( preg_match_all( $pattern, $css_content, $matches ) ) {
					$check['outline_removed_count'] += count( $matches[0] );
					$check['has_issues'] = true;

					// Check if it's a global reset.
					if ( false !== strpos( $pattern, '\*' ) ) {
						$check['outline_removed_globally'] = true;
					}
				}
			}
		}

		return $check;
	}
}
