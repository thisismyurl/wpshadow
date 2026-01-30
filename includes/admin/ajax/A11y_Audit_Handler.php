<?php
/**
 * A11y Audit AJAX Handler
 *
 * Uses diagnostic system for accessibility checks.
 *
 * @since   1.2601.2148
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;
use WPShadow\Core\Diagnostic_Registry;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Accessibility Audit Handler Class
 *
 * Refactored to use existing diagnostic system.
 * Provides WCAG 2.1 Level AA compliance checking.
 *
 * @since 1.2601.2148
 */
class A11y_Audit_Handler extends AJAX_Handler_Base {
	/**
	 * Register AJAX handler.
	 *
	 * @since 1.2601.2148
	 */
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_a11y_scan', array( __CLASS__, 'handle' ) );
	}

	/**
	 * Handle accessibility audit request using diagnostic system.
	 *
	 * @since 1.2601.2148
	 */
	public static function handle(): void {
		self::verify_request( 'wpshadow_a11y_scan', 'read', 'nonce' );

		$url = self::get_post_param( 'url', 'url', '' );
		if ( empty( $url ) ) {
			$url = home_url();
		}

		if ( ! wp_http_validate_url( $url ) ) {
			self::send_error( __( 'Please enter a valid URL (http/https).', 'wpshadow' ) );
		}

		// Validate same-site.
		$site_host = wp_parse_url( home_url(), PHP_URL_HOST );
		$check_host = wp_parse_url( $url, PHP_URL_HOST );
		
		if ( $site_host !== $check_host ) {
			self::send_error( __( 'You can only test your own site. Please enter a path from your domain.', 'wpshadow' ) );
		}

		// Use Diagnostic_Registry to get accessibility diagnostics.
		$diagnostics = Diagnostic_Registry::get_all();
		$a11y_checks = array();
		
		foreach ( $diagnostics as $slug => $class ) {
			// Check if class exists and has family property.
			if ( ! class_exists( $class ) ) {
				continue;
			}
			
			try {
				$reflection = new \ReflectionClass( $class );
				
				if ( ! $reflection->hasProperty( 'family' ) ) {
					continue;
				}
				
				$family_prop = $reflection->getProperty( 'family' );
				$family_prop->setAccessible( true );
				$family = $family_prop->getValue();
				
				// Only run accessibility family diagnostics.
				if ( 'accessibility' !== $family ) {
					continue;
				}
				
				if ( ! method_exists( $class, 'check' ) ) {
					continue;
				}
				
				$result = $class::check();
				
				if ( $result ) {
					// Convert diagnostic result to tool format.
					$a11y_checks[] = array(
						'label'   => $result['title'] ?? '',
						'status'  => self::map_severity_to_status( $result['severity'] ?? 'medium' ),
						'details' => $result['description'] ?? '',
					);
				} else {
					// Diagnostic passed - no issues found.
					$title_prop = $reflection->getProperty( 'title' );
					$title_prop->setAccessible( true );
					
					$a11y_checks[] = array(
						'label'   => $title_prop->getValue(),
						'status'  => 'pass',
						'details' => __( 'Check passed', 'wpshadow' ),
					);
				}
			} catch ( \ReflectionException $e ) {
				// Skip diagnostics that can't be reflected.
				continue;
			}
		}
		
		// Calculate summary.
		$summary = array(
			'pass' => 0,
			'warn' => 0,
			'fail' => 0,
		);
		
		foreach ( $a11y_checks as $check ) {
			$status = $check['status'] ?? 'pass';
			if ( isset( $summary[ $status ] ) ) {
				++$summary[ $status ];
			}
		}

		self::send_success(
			array(
				'url'     => $url,
				'summary' => $summary,
				'checks'  => $a11y_checks,
			)
		);
	}

	/**
	 * Map diagnostic severity to tool status.
	 *
	 * @since  1.2601.2148
	 * @param  string $severity Diagnostic severity level.
	 * @return string Tool status (pass, warn, fail).
	 */
	private static function map_severity_to_status( string $severity ): string {
		switch ( $severity ) {
			case 'critical':
			case 'high':
				return 'fail';
			case 'medium':
				return 'warn';
			default:
				return 'pass';
		}
	}
}

	/**
	 * Analyze HTML for accessibility issues.
	 *
	 * @param string $html The HTML content to analyze.
	 * @return array Array of accessibility checks.
	 */
	private static function analyze_a11y_html( $html ): array {
		$checks = array();

		// Check for images without alt text
		preg_match_all( '/<img[^>]*>/i', $html, $img_matches );
		$images_without_alt = 0;
		if ( ! empty( $img_matches[0] ) ) {
			foreach ( $img_matches[0] as $img_tag ) {
				if ( ! preg_match( '/alt\s*=\s*["\'][^"\']*["\']/i', $img_tag ) ) {
					++$images_without_alt;
				}
			}
		}
		
		$checks[] = array(
			'label'   => __( 'Image Alt Text', 'wpshadow' ),
			'status'  => $images_without_alt > 0 ? 'fail' : 'pass',
			'details' => $images_without_alt > 0 
				? sprintf( __( 'Found %d images without alt text. All images should have descriptive alt attributes for screen readers.', 'wpshadow' ), $images_without_alt )
				: __( 'All images have alt text.', 'wpshadow' ),
		);

		// Check for proper heading hierarchy
		preg_match_all( '/<h([1-6])[^>]*>/i', $html, $heading_matches );
		$has_h1 = false;
		$heading_issues = 0;
		if ( ! empty( $heading_matches[1] ) ) {
			$prev_level = 0;
			foreach ( $heading_matches[1] as $level ) {
				$level = (int) $level;
				if ( $level === 1 ) {
					$has_h1 = true;
				}
				if ( $prev_level > 0 && $level > $prev_level + 1 ) {
					++$heading_issues;
				}
				$prev_level = $level;
			}
		}
		
		$checks[] = array(
			'label'   => __( 'Heading Hierarchy', 'wpshadow' ),
			'status'  => ! $has_h1 || $heading_issues > 0 ? 'warn' : 'pass',
			'details' => ! $has_h1 
				? __( 'Page should have one H1 heading.', 'wpshadow' )
				: ( $heading_issues > 0 
					? __( 'Heading hierarchy has gaps (e.g., H1 to H3 without H2).', 'wpshadow' )
					: __( 'Heading hierarchy is properly structured.', 'wpshadow' ) ),
		);

		// Check for ARIA labels on forms
		preg_match_all( '/<(form|button|input|select|textarea)[^>]*>/i', $html, $form_matches );
		$forms_without_labels = 0;
		if ( ! empty( $form_matches[0] ) ) {
			foreach ( $form_matches[0] as $form_element ) {
				if ( ! preg_match( '/aria-label\s*=|aria-labelledby\s*=/i', $form_element ) 
					&& ! preg_match( '/<label[^>]*for\s*=/i', $html ) ) {
					++$forms_without_labels;
				}
			}
		}
		
		$checks[] = array(
			'label'   => __( 'Form Labels & ARIA', 'wpshadow' ),
			'status'  => $forms_without_labels > 5 ? 'warn' : 'pass',
			'details' => $forms_without_labels > 5
				? __( 'Some form elements may be missing proper labels or ARIA attributes.', 'wpshadow' )
				: __( 'Form elements appear to have proper labeling.', 'wpshadow' ),
		);

		// Check for language attribute
		$has_lang = preg_match( '/<html[^>]*lang\s*=\s*["\'][^"\']+["\']/i', $html );
		$checks[] = array(
			'label'   => __( 'Language Attribute', 'wpshadow' ),
			'status'  => $has_lang ? 'pass' : 'fail',
			'details' => $has_lang 
				? __( 'HTML lang attribute is set.', 'wpshadow' )
				: __( 'HTML should have a lang attribute for screen readers.', 'wpshadow' ),
		);

		// Check for skip links
		$has_skip_link = preg_match( '/href\s*=\s*["\']#[^"\']*content[^"\']*["\']/i', $html ) 
			|| preg_match( '/href\s*=\s*["\']#[^"\']*main[^"\']*["\']/i', $html );
		$checks[] = array(
			'label'   => __( 'Skip to Content Link', 'wpshadow' ),
			'status'  => $has_skip_link ? 'pass' : 'warn',
			'details' => $has_skip_link 
				? __( 'Skip to content link detected.', 'wpshadow' )
				: __( 'Consider adding a "skip to content" link for keyboard navigation.', 'wpshadow' ),
		);

		return $checks;
	}
}
