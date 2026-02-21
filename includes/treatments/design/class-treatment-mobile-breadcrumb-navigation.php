<?php
/**
 * Mobile Breadcrumb Navigation Treatment
 *
 * Validates that breadcrumb navigation is mobile-friendly with proper
 * sizing, structured data, and accessibility.
 *
 * @package    WPShadow
 * @subpackage Treatments\Mobile
 * @since      1.602.1235
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Breadcrumb Navigation Treatment Class
 *
 * Checks breadcrumb implementation for mobile usability, structured data,
 * and accessibility compliance.
 *
 * @since 1.602.1235
 */
class Treatment_Mobile_Breadcrumb_Navigation extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-breadcrumb-navigation';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Breadcrumb Navigation';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validates breadcrumb navigation is mobile-friendly with proper sizing and structured data';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'mobile';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.602.1235
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Mobile_Breadcrumb_Navigation' );
	}

	/**
	 * Check breadcrumb implementation.
	 *
	 * @since  1.602.1235
	 * @return array Check results.
	 */
	private static function check_breadcrumbs() {
		$issues = array();

		// Check for common breadcrumb plugins.
		$active_plugins    = get_option( 'active_plugins', array() );
		$has_breadcrumbs   = false;
		$breadcrumb_source = '';

		// Yoast SEO.
		if ( defined( 'WPSEO_VERSION' ) ) {
			$yoast_breadcrumbs = get_option( 'wpseo_internallinks', array() );
			if ( isset( $yoast_breadcrumbs['breadcrumbs-enable'] ) && $yoast_breadcrumbs['breadcrumbs-enable'] ) {
				$has_breadcrumbs   = true;
				$breadcrumb_source = 'Yoast SEO';
			}
		}

		// Rank Math.
		if ( defined( 'RANK_MATH_VERSION' ) ) {
			$rm_breadcrumbs = get_option( 'rank-math-options-general', array() );
			if ( isset( $rm_breadcrumbs['breadcrumbs'] ) && $rm_breadcrumbs['breadcrumbs'] ) {
				$has_breadcrumbs   = true;
				$breadcrumb_source = 'Rank Math';
			}
		}

		// Breadcrumb NavXT.
		foreach ( $active_plugins as $plugin ) {
			if ( strpos( $plugin, 'breadcrumb-navxt' ) !== false ) {
				$has_breadcrumbs   = true;
				$breadcrumb_source = 'Breadcrumb NavXT';
				break;
			}
		}

		// Check HTML for breadcrumbs.
		$test_url = get_permalink( get_option( 'page_for_posts' ) ?: 1 );
		if ( $test_url ) {
			$html = self::capture_page_html( $test_url );
			if ( ! empty( $html ) ) {
				// Look for breadcrumb patterns.
				if ( preg_match( '/<nav[^>]*(?:breadcrumb|aria-label=["\']breadcrumb["\'])/i', $html ) ||
					 preg_match( '/itemtype=["\']https?:\/\/schema\.org\/BreadcrumbList["\']/', $html ) ) {
					$has_breadcrumbs   = true;
					$breadcrumb_source = 'Custom Implementation';
				}
			}
		}

		// If no breadcrumbs detected.
		if ( ! $has_breadcrumbs ) {
			$issues[] = array(
				'issue_type'  => 'no_breadcrumbs',
				'severity'    => 'medium',
				'description' => 'No breadcrumb navigation detected',
				'impact'      => 'Poor mobile navigation, missed SEO opportunity',
			);
			return array( 'issues' => $issues );
		}

		// Check if breadcrumbs have structured data.
		if ( $has_breadcrumbs && ! empty( $html ) ) {
			if ( ! preg_match( '/itemtype=["\']https?:\/\/schema\.org\/BreadcrumbList["\']/', $html ) ) {
				$issues[] = array(
					'issue_type'  => 'no_structured_data',
					'severity'    => 'low',
					'description' => 'Breadcrumbs lack Schema.org structured data',
					'impact'      => 'Won\'t appear as rich snippets in Google search',
					'source'      => $breadcrumb_source,
				);
			}

			// Check mobile sizing (font-size should be 14px+).
			if ( preg_match( '/<style[^>]*>.*breadcrumb.*font-size:\s*([0-9]+)px/is', $html, $size_match ) ) {
				$font_size = (int) $size_match[1];
				if ( $font_size < 14 ) {
					$issues[] = array(
						'issue_type'  => 'text_too_small',
						'severity'    => 'low',
						'description' => sprintf( 'Breadcrumb font-size is %dpx (recommend 14px+ for mobile)', $font_size ),
					);
				}
			}
		}

		return array( 'issues' => $issues );
	}

	/**
	 * Capture page HTML.
	 *
	 * @since  1.602.1235
	 * @param  string $url Page URL.
	 * @return string HTML content.
	 */
	private static function capture_page_html( $url ) {
		$response = wp_remote_get(
			$url,
			array(
				'timeout'    => 10,
				'user-agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X)',
			)
		);

		if ( is_wp_error( $response ) ) {
			return '';
		}

		return wp_remote_retrieve_body( $response );
	}
}
