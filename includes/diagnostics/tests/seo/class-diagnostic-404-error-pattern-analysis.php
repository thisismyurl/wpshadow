<?php
/**
 * 404 Error Pattern Analysis Diagnostic
 *
 * Tracks most common 404 errors and identifies patterns requiring fixes.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26029.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 404 Error Pattern Analysis Class
 *
 * Tests 404 error patterns.
 *
 * @since 1.26029.0000
 */
class Diagnostic_404_Error_Pattern_Analysis extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = '404-error-pattern-analysis';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = '404 Error Pattern Analysis';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tracks most common 404 errors and identifies patterns requiring fixes';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26029.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$error_check = self::check_404_patterns();
		
		if ( $error_check['has_issues'] ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( '404 error patterns detected (potential lost conversions and broken links)', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/404-error-pattern-analysis',
				'meta'         => array(
					'common_patterns'  => $error_check['common_patterns'],
					'recommendations'  => $error_check['recommendations'],
				),
			);
		}

		return null;
	}

	/**
	 * Check 404 error patterns.
	 *
	 * @since  1.26029.0000
	 * @return array Check results.
	 */
	private static function check_404_patterns() {
		$check = array(
			'has_issues'       => false,
			'common_patterns'  => array(),
			'recommendations'  => array(),
		);

		// Check for common 404-causing patterns.
		$test_urls = array(
			'/blog/',
			'/wp-content/uploads/',
			'/news/',
			'/category/',
			'/tag/',
			'/author/',
			'/?p=1',
		);

		foreach ( $test_urls as $url ) {
			$response = wp_remote_head( home_url( $url ), array(
				'timeout' => 5,
			) );

			if ( ! is_wp_error( $response ) ) {
				$status_code = wp_remote_retrieve_response_code( $response );
				
				if ( 404 === $status_code ) {
					$check['has_issues'] = true;
					$check['common_patterns'][] = $url;
				}
			}
		}

		// Check for redirect plugin (indicates 404 management).
		$redirect_plugins = array(
			'redirection/redirection.php',
			'simple-301-redirects/wp-simple-301-redirects.php',
			'safe-redirect-manager/safe-redirect-manager.php',
		);

		$has_redirect_plugin = false;
		
		foreach ( $redirect_plugins as $plugin_file ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$has_redirect_plugin = true;
				break;
			}
		}

		if ( $check['has_issues'] ) {
			if ( ! $has_redirect_plugin ) {
				$check['recommendations'][] = __( 'Install Redirection plugin to track and fix 404 errors', 'wpshadow' );
			}

			$check['recommendations'][] = __( 'Review common 404 URLs and create redirects to relevant pages', 'wpshadow' );
			$check['recommendations'][] = __( 'Check external sites linking to broken URLs', 'wpshadow' );
		}

		// Check permalink structure for potential issues.
		$permalink_structure = get_option( 'permalink_structure' );
		
		if ( empty( $permalink_structure ) || '/?p=%post_id%' === $permalink_structure ) {
			$check['has_issues'] = true;
			$check['recommendations'][] = __( 'Update permalink structure for better SEO (using default permalinks)', 'wpshadow' );
		}

		return $check;
	}
}
