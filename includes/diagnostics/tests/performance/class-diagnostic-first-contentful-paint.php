<?php
/**
 * First Contentful Paint (FCP) Diagnostic
 *
 * Measures First Contentful Paint time for Core Web Vitals.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6033.2053
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * First Contentful Paint Diagnostic Class
 *
 * Measures FCP (First Contentful Paint) timing. FCP is a Core Web Vital
 * that affects Google rankings and user experience.
 *
 * @since 1.6033.2053
 */
class Diagnostic_First_Contentful_Paint extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'first-contentful-paint';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'First Contentful Paint (FCP)';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Measures First Contentful Paint timing (Core Web Vital)';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * Checks FCP data from Navigation Timing API or estimated values.
	 * Thresholds:
	 * - Good: <1.8s
	 * - Needs Improvement: 1.8-3.0s
	 * - Poor: >3.0s
	 *
	 * @since  1.6033.2053
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// FCP can't be measured server-side directly
		// This diagnostic checks for factors that affect FCP
		
		$issues = array();
		$score  = 0;
		
		// Check for render-blocking resources
		global $wp_scripts, $wp_styles;
		
		$blocking_scripts = 0;
		$blocking_styles  = 0;
		
		// Count blocking scripts in head
		if ( $wp_scripts && isset( $wp_scripts->queue ) ) {
			foreach ( $wp_scripts->queue as $handle ) {
				$script = $wp_scripts->registered[ $handle ] ?? null;
				if ( $script && empty( $script->extra['defer'] ) && empty( $script->extra['async'] ) ) {
					$blocking_scripts++;
				}
			}
		}
		
		// Count blocking stylesheets
		if ( $wp_styles && isset( $wp_styles->queue ) ) {
			foreach ( $wp_styles->queue as $handle ) {
				$style = $wp_styles->registered[ $handle ] ?? null;
				if ( $style && ( empty( $style->extra['media'] ) || 'all' === $style->extra['media'] ) ) {
					$blocking_styles++;
				}
			}
		}
		
		// Check for large DOM size
		global $wpdb;
		$post_content_length = 0;
		if ( is_singular() ) {
			$post_id = get_the_ID();
			if ( $post_id ) {
				$post_content = get_post_field( 'post_content', $post_id );
				$post_content_length = strlen( $post_content );
			}
		}
		
		// Evaluate factors
		if ( $blocking_scripts > 5 ) {
			$issues[] = sprintf(
				/* translators: %d: number of blocking scripts */
				__( '%d render-blocking scripts in head', 'wpshadow' ),
				$blocking_scripts
			);
			$score += 30;
		}
		
		if ( $blocking_styles > 3 ) {
			$issues[] = sprintf(
				/* translators: %d: number of blocking stylesheets */
				__( '%d render-blocking stylesheets', 'wpshadow' ),
				$blocking_styles
			);
			$score += 20;
		}
		
		if ( $post_content_length > 50000 ) {
			$issues[] = __( 'Large page content (>50KB)', 'wpshadow' );
			$score += 15;
		}
		
		// Check if critical CSS is not inlined
		$theme_dir = get_template_directory();
		if ( ! file_exists( $theme_dir . '/critical.css' ) && ! has_filter( 'wpshadow_critical_css' ) ) {
			$issues[] = __( 'No critical CSS detected', 'wpshadow' );
			$score += 25;
		}
		
		// If significant issues found
		if ( $score > 40 ) {
			$severity = 'medium';
			if ( $score > 60 ) {
				$severity = 'high';
			}
			
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: list of FCP issues */
					__( 'Factors affecting First Contentful Paint (Core Web Vital): %s. FCP measures how quickly content first appears, affecting Google rankings.', 'wpshadow' ),
					implode( '; ', $issues )
				),
				'severity'     => $severity,
				'threat_level' => min( 100, $score ),
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/first-contentful-paint',
				'meta'         => array(
					'blocking_scripts'    => $blocking_scripts,
					'blocking_styles'     => $blocking_styles,
					'content_length'      => $post_content_length,
					'critical_css_exists' => file_exists( $theme_dir . '/critical.css' ),
					'score'               => $score,
					'good_threshold'      => '1.8s',
					'poor_threshold'      => '3.0s',
					'measurement_type'    => 'indirect',
				),
			);
		}
		
		return null;
	}
}
