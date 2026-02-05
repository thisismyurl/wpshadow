<?php
/**
 * AJAX Technical Errors Treatment
 *
 * Detects when AJAX failures display raw technical error messages instead of user-friendly messages.
 *
 * @package    WPShadow
 * @subpackage Treatments\UX
 * @since      1.6035.2300
 */

declare(strict_types=1);

namespace WPShadow\Treatments\UX;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AJAX Technical Errors Treatment Class
 *
 * Checks if AJAX error handlers show technical errors to users instead of friendly messages.
 *
 * @since 1.6035.2300
 */
class Treatment_AJAX_Technical_Errors extends Treatment_Base {

	/**
	 * Treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'ajax-technical-errors';

	/**
	 * Treatment title
	 *
	 * @var string
	 */
	protected static $title = 'AJAX Failures Show Technical Errors';

	/**
	 * Treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Detects when AJAX error handlers display technical errors instead of user-friendly messages';

	/**
	 * Treatment family
	 *
	 * @var string
	 */
	protected static $family = 'ux';

	/**
	 * Run the treatment check
	 *
	 * @since  1.6035.2300
	 * @return array|null Finding array or null if no issues found.
	 */
	public static function check() {
		global $wp_scripts;

		$issues       = array();
		$checked_code = array();

		// Check enqueued JavaScript for error handling patterns.
		if ( ! empty( $wp_scripts->registered ) ) {
			foreach ( $wp_scripts->registered as $handle => $script ) {
				if ( empty( $script->src ) ) {
					continue;
				}

				// Skip external scripts and WordPress core.
				if ( strpos( $script->src, site_url() ) === false || strpos( $script->src, '/wp-includes/' ) !== false ) {
					continue;
				}

				// Convert URL to file path.
				$file_path = str_replace( site_url(), ABSPATH, $script->src );
				$file_path = strtok( $file_path, '?' ); // Remove query string.

				if ( ! file_exists( $file_path ) || isset( $checked_code[ $file_path ] ) ) {
					continue;
				}

				$checked_code[ $file_path ] = true;
				$content                    = file_get_contents( $file_path );

				// Check for problematic error handling patterns.
				$bad_patterns = array(
					'alert(error)'                    => __( 'Using alert() with raw error object', 'wpshadow' ),
					'console.error(e)'                => __( 'Logging raw exception to console only', 'wpshadow' ),
					'error.responseText'              => __( 'Displaying raw response text', 'wpshadow' ),
					'xhr.statusText'                  => __( 'Showing technical status text', 'wpshadow' ),
					'JSON.parse(response)'            => __( 'Parsing JSON without try/catch', 'wpshadow' ),
					'throw new Error'                 => __( 'Throwing uncaught errors', 'wpshadow' ),
				);

				$found_issues = array();
				foreach ( $bad_patterns as $pattern => $description ) {
					if ( stripos( $content, $pattern ) !== false ) {
						$found_issues[] = $description;
					}
				}

				if ( ! empty( $found_issues ) ) {
					$issues[ basename( $file_path ) ] = $found_issues;
				}
			}
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'Your site shows technical error messages when things go wrong, confusing visitors instead of guiding them', 'wpshadow' ),
			'severity'     => 'medium',
			'threat_level' => 50,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/ajax-error-handling',
			'context'      => array(
				'files_with_issues' => $issues,
				'total_files'       => count( $issues ),
				'impact'            => __( 'Users see confusing error messages like "500 Internal Server Error" instead of "Oops! Something went wrong. Please try again."', 'wpshadow' ),
				'recommendation'    => array(
					__( 'Wrap AJAX calls in try/catch blocks', 'wpshadow' ),
					__( 'Create user-friendly error message translations', 'wpshadow' ),
					__( 'Map technical errors to friendly messages', 'wpshadow' ),
					__( 'Add error recovery suggestions (e.g., "Try refreshing the page")', 'wpshadow' ),
					__( 'Log technical details to console/server only', 'wpshadow' ),
				),
			),
		);
	}
}
