<?php
/**
 * Mobile Text Zoom Capability
 *
 * Ensures text can be zoomed to 200% without horizontal scroll.
 *
 * @package    WPShadow
 * @subpackage Treatments\Typography
 * @since      1.602.1430
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Text Zoom Capability
 *
 * Validates that viewport allows zooming and content reflows properly at 200% zoom.
 * WCAG 1.4.4 Level AA requirement for accessibility.
 *
 * @since 1.602.1430
 */
class Treatment_Mobile_Text_Zoom extends Treatment_Base {

	/**
	 * The treatment slug.
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-text-zoom-blocked';

	/**
	 * The treatment title.
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Text Zoom Capability';

	/**
	 * The treatment description.
	 *
	 * @var string
	 */
	protected static $description = 'Ensures text can be zoomed to 200% without layout break';

	/**
	 * The treatment family.
	 *
	 * @var string
	 */
	protected static $family = 'typography';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.602.1430
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
			'kb_link'         => 'https://wpshadow.com/kb/text-zoom',
		);
	}

	/**
	 * Find zoom-related issues.
	 *
	 * @since  1.602.1430
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
	 * @since  1.602.1430
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
	 * @since  1.602.1430
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
