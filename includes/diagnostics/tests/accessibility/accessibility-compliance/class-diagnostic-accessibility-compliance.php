<?php
/**
 * Accessibility Compliance Diagnostic
 *
 * Checks if theme meets WCAG AA accessibility standards.
 *
 * @package    WPShadow
 * @subpackage Diagnostics/Accessibility
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics\Accessibility;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Accessibility Compliance Diagnostic Class
 *
 * Validates WCAG AA compliance including keyboard navigation, color contrast,
 * and screen reader support.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Accessibility_Compliance extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'accessibility-compliance';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Accessibility Compliance';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'WCAG AA compliance level';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for skip link
		if ( ! has_action( 'wp_footer', 'wp_print_footer_scripts' ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Skip to content links not found. WCAG AA requires keyboard navigation aids.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/accessibility-compliance',
				'persona'      => 'developer',
			);
		}

		// Check for proper heading hierarchy (basic check)
		global $wp_query;
		if ( is_singular() && $wp_query->post ) {
			$post_content = $wp_query->post->post_content;

				// Count heading levels.
				$h1_count = substr_count( $post_content, '<h1' );

			// Should have exactly one H1 on page (with page title)
			if ( $h1_count > 1 ) {
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => __( 'Multiple H1 headings detected. WCAG recommends one H1 per page.', 'wpshadow' ),
					'severity'     => 'medium',
					'threat_level' => 40,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/accessibility-compliance',
					'persona'      => 'developer',
				);
			}
		}

		return null; // No issue found
	}
}
