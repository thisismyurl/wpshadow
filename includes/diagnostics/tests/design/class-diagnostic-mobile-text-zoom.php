<?php
/**
 * Mobile Text Zoom Capability
 *
 * Ensures text can be zoomed to 200% without horizontal scroll.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Typography
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Text Zoom Capability
 *
 * Validates that viewport allows zooming and content reflows properly at 200% zoom.
 * WCAG1.0 Level AA requirement for accessibility.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Mobile_Text_Zoom extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-text-zoom-blocked';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Text Zoom Capability';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Ensures text can be zoomed to 200% without layout break';

	/**
	 * The diagnostic family.
	 *
	 * @var string
	 */
	protected static $family = 'typography';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = self::find_zoom_issues();

		if ( empty( $issues ) ) {
			return null; // No issues found
		}

		return array(
			'id'              => self::$slug,
			'title'           => self::$title,
			'description'     => __( 'Viewport prevents zoom or 200% zoom causes horizontal scroll', 'wpshadow' ),
			'severity'        => 'critical',
			'threat_level'    => 80,
			'issues'          => $issues,
			'wcag_violation'  => '1.4.4 Resize Text (Level AA)',
			'affected_users'  => __( 'Low-vision users, elderly users', 'wpshadow' ),
			'user_impact'     => __( 'Cannot increase text size for readability', 'wpshadow' ),
			'auto_fixable'    => true,
			'kb_link'         => 'https://wpshadow.com/kb/text-zoom?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
		);
	}

	/**
	 * Find zoom-related issues.
	 *
	 * @since 0.6093.1200
	 * @return array List of issues.
	 */
	private static function find_zoom_issues(): array {
		$issues = array();

		// Check viewport meta tag
		$header_html = self::get_header_html();
		if ( $header_html ) {
			// Check for user-scalable=no
			if ( preg_match( '/user-scalable\s*=\s*no/i', $header_html ) ) {
				$issues[] = __( 'Viewport has user-scalable=no (disables zoom)', 'wpshadow' );
			}

			// Check for maximum-scale that limits zoom
			if ( preg_match( '/maximum-scale\s*=\s*1/i', $header_html ) ) {
				$issues[] = __( 'Viewport maximum-scale=1 (prevents zoom)', 'wpshadow' );
			}
		}

		// Check for fixed-width containers
		$css = self::get_stylesheet_content();
		if ( $css && preg_match( '/width\s*:\s*960px|width\s*:\s*1200px|max-width\s*:\s*100%[^}]*width\s*:\s*\d{3,4}px/i', $css ) ) {
			$issues[] = __( 'Fixed-width containers (960px/1200px) force horizontal scroll at zoom', 'wpshadow' );
		}

		// Check for lack of responsive design
		if ( $css && ! preg_match( '/@media\s*\(max-width/i', $css ) ) {
			$issues[] = __( 'No media queries detected - may not support zoom properly', 'wpshadow' );
		}

		return $issues;
	}

	/**
	 * Get header HTML for viewport check.
	 *
	 * @since 0.6093.1200
	 * @return string|null HTML content.
	 */
	private static function get_header_html(): ?string {
		// Get homepage and check viewport
		$response = wp_remote_get(
			home_url( '/' ),
			array(
				'timeout'   => 5,
				'sslverify' => false,
			)
		);

		if ( is_wp_error( $response ) ) {
			return null;
		}

		$body = wp_remote_retrieve_body( $response );
		// Extract head section
		if ( preg_match( '/<head>(.*?)<\/head>/is', $body, $matches ) ) {
			return $matches[1];
		}

		return null;
	}

	/**
	 * Get theme stylesheet content.
	 *
	 * @since 0.6093.1200
	 * @return string|null CSS content.
	 */
	private static function get_stylesheet_content(): ?string {
		$stylesheet = get_template_directory() . '/style.css';

		if ( file_exists( $stylesheet ) ) {
			return file_get_contents( $stylesheet );
		}

		return null;
	}
}
