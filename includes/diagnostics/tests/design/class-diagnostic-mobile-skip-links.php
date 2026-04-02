<?php
/**
 * Mobile Skip Links Diagnostic
 *
 * Ensures skip-to-content link is visible on mobile.
 *
 * @since 1.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Skip Links Diagnostic Class
 *
 * Ensures skip-to-content link exists and is keyboard accessible,
 * allowing users to bypass navigation blocks (WCAG 2.4.1).
 *
 * @since 1.6093.1200
 */
class Diagnostic_Mobile_Skip_Links extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-skip-links';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Skip Links';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Ensure skip-to-content link is present and keyboard accessible (WCAG 2.4.1)';

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
		$issues = array();

		// Check if skip link exists in markup
		$skip_link_exists = apply_filters( 'wpshadow_skip_link_exists', false );
		if ( ! $skip_link_exists ) {
			$issues[] = __( 'Skip-to-content link not found; users must tab through navigation (WCAG 2.4.1)', 'wpshadow' );
		}

		// Check if skip link targets main content
		$skip_link_targets_main = apply_filters( 'wpshadow_skip_link_targets_main_content', false );
		if ( ! $skip_link_targets_main ) {
			$issues[] = __( 'Skip link should target main content area (id="main" or id="content")', 'wpshadow' );
		}

		// Check if skip link is keyboard accessible
		$skip_link_keyboard_accessible = apply_filters( 'wpshadow_skip_link_keyboard_accessible', false );
		if ( ! $skip_link_keyboard_accessible ) {
			$issues[] = __( 'Skip link should be keyboard focusable and trigger on Enter key', 'wpshadow' );
		}

		// Check if skip link appears on focus (not hidden)
		$skip_link_visible_on_focus = apply_filters( 'wpshadow_skip_link_visible_on_focus', false );
		if ( ! $skip_link_visible_on_focus ) {
			$issues[] = __( 'Skip link should become visible when focused (not hidden by display:none)', 'wpshadow' );
		}

		// Check for multiple skip links if needed
		$multi_skip_links = apply_filters( 'wpshadow_multiple_skip_links_if_needed', false );
		if ( ! $multi_skip_links ) {
			$issues[] = __( 'Consider multiple skip links for navigation, main content, and footer areas', 'wpshadow' );
		}

		// Check if skip link has clear focus indicator
		$skip_link_focus_indicator = apply_filters( 'wpshadow_skip_link_focus_indicator_visible', false );
		if ( ! $skip_link_focus_indicator ) {
			$issues[] = __( 'Skip link should have visible focus indicator (outline, border, etc)', 'wpshadow' );
		}

		// Check if skip link text is clear and descriptive
		$skip_link_text_clear = apply_filters( 'wpshadow_skip_link_text_descriptive', false );
		if ( ! $skip_link_text_clear ) {
			$issues[] = __( 'Skip link text should be descriptive (e.g., "Skip to main content")', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/mobile-skip-links',
			);
		}

		return null;
	}
}
