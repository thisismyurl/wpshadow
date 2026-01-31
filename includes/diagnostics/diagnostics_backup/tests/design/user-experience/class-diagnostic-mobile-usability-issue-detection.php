<?php
/**
 * Diagnostic: Mobile Usability Issue Detection
 *
 * Tests for mobile-specific problems affecting 60% of traffic.
 * 60% of traffic is mobile but mobile conversion often 50% lower than desktop.
 * Small mobile problems have massive business impact.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\UX
 * @since      1.26028.1909
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Diagnostic_Mobile_Usability_Issue_Detection
 *
 * Tests mobile usability issues.
 *
 * @since 1.26028.1909
 */
class Diagnostic_Mobile_Usability_Issue_Detection extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-usability-issue-detection';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Usability Issue Detection';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Tests for mobile-specific problems affecting 60% of traffic';

	/**
	 * Diagnostic family.
	 *
	 * @var string
	 */
	protected static $family = 'user-experience';

	/**
	 * Check mobile usability issues.
	 *
	 * @since  1.26028.1909
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$issues = array();

		if ( ! self::has_viewport_meta() ) {
			$issues[] = __( 'Missing viewport meta tag - page will appear zoomed out on mobile', 'wpshadow' );
		}

		if ( self::uses_flash() ) {
			$issues[] = __( 'Site uses Flash (not supported on mobile)', 'wpshadow' );
		}

		if ( ! self::is_responsive() ) {
			$issues[] = __( 'Theme may not be responsive - check mobile display', 'wpshadow' );
		}

		$font_issues = self::check_font_sizes();
		if ( ! empty( $font_issues ) ) {
			$issues = array_merge( $issues, $font_issues );
		}

		if ( ! empty( $issues ) ) {
			$severity = count( $issues ) > 2 ? 'high' : 'medium';
			$threat_level = count( $issues ) > 2 ? 70 : 60;

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: 1: Number of issues, 2: List of issues */
					__( 'Detected %1$d mobile usability issue(s): %2$s. 60%% of traffic is mobile - these issues significantly hurt user experience and conversions.', 'wpshadow' ),
					count( $issues ),
					implode( '; ', $issues )
				),
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/mobile-usability-issue-detection',
				'meta'         => array(
					'issues'         => $issues,
					'recommendation' => 'Fix mobile usability issues for better conversion',
				),
			);
		}

		return null;
	}

	/**
	 * Check if viewport meta tag exists.
	 *
	 * @since  1.26028.1909
	 * @return bool True if viewport meta exists, false otherwise.
	 */
	private static function has_viewport_meta() {
		ob_start();
		wp_head();
		$head_content = ob_get_clean();

		return false !== strpos( $head_content, 'name="viewport"' ) ||
			   false !== strpos( $head_content, "name='viewport'" );
	}

	/**
	 * Check if site uses Flash.
	 *
	 * @since  1.26028.1909
	 * @return bool True if Flash detected, false otherwise.
	 */
	private static function uses_flash() {
		$flash_plugins = array(
			'flash-album-gallery/flag.php',
			'wordpress-flash-player/wp-flash-player.php',
		);

		foreach ( $flash_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if theme is responsive.
	 *
	 * @since  1.26028.1909
	 * @return bool True if likely responsive, false otherwise.
	 */
	private static function is_responsive() {
		$theme = wp_get_theme();

		$responsive_keywords = array( 'responsive', 'mobile', 'adaptive', 'fluid' );
		$theme_name = strtolower( $theme->get( 'Name' ) );
		$theme_desc = strtolower( $theme->get( 'Description' ) );

		foreach ( $responsive_keywords as $keyword ) {
			if ( false !== strpos( $theme_name, $keyword ) ||
				 false !== strpos( $theme_desc, $keyword ) ) {
				return true;
			}
		}

		if ( wp_is_block_theme() ) {
			return true;
		}

		$modern_themes = array(
			'twentytwentyfour',
			'twentytwentythree',
			'twentytwentytwo',
			'twentytwentyone',
			'twentytwenty',
			'generatepress',
			'astra',
			'kadence',
		);

		if ( in_array( strtolower( $theme->get_template() ), $modern_themes, true ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check for font size issues.
	 *
	 * @since  1.26028.1909
	 * @return array List of font issues.
	 */
	private static function check_font_sizes() {
		$issues = array();

		$theme = wp_get_theme();
		$template = strtolower( $theme->get_template() );

		$old_themes = array(
			'twentyeleven',
			'twentytwelve',
			'twentythirteen',
			'twentyfourteen',
			'twentyfifteen',
		);

		if ( in_array( $template, $old_themes, true ) ) {
			$issues[] = __( 'Using older theme - check mobile font sizes', 'wpshadow' );
		}

		return $issues;
	}
}
