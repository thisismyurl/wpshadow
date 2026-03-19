<?php
/**
 * Poor Color Contrast Diagnostic
 *
 * Detects when text has insufficient color contrast,
 * making it hard to read for users with vision impairments.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Accessibility
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: Poor Color Contrast
 *
 * Checks whether text has sufficient color contrast
 * (WCAG AA: 4.5:1 for normal text, 3:1 for large text).
 *
 * @since 1.6093.1200
 */
class Diagnostic_Poor_Color_Contrast extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'poor-color-contrast';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Poor Color Contrast';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether text has sufficient color contrast';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Whether this diagnostic is auto-fixable
	 *
	 * @var bool
	 */
	protected static $auto_fixable = false;

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// This is a basic heuristic check (full testing requires specialized tools)
		$homepage = wp_remote_get( home_url() );
		if ( is_wp_error( $homepage ) ) {
			return null;
		}

		$body = wp_remote_retrieve_body( $homepage );

		// Look for common low-contrast patterns
		// Light gray text on white background
		$low_contrast_patterns = array(
			'color:\s*#[cdf][0-9a-f]{2}[0-9a-f]{2}',  // Light gray (cccccc, dddddd, eeeeee, etc.)
			'color:\s*#[89ab][0-9a-f]{2}[0-9a-f]{2}',  // Light colors
			'color:\s*rgba\(.*,\s*0\.[1-3]\)',         // Very transparent
		);

		$has_low_contrast = false;
		foreach ( $low_contrast_patterns as $pattern ) {
			if ( preg_match( '/' . $pattern . '/i', $body ) ) {
				$has_low_contrast = true;
				break;
			}
		}

		if ( $has_low_contrast ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __(
					'Your site may have poor color contrast in some areas. About 4-8% of people are colorblind, and 20% have low vision. WCAG AA requires 4.5:1 contrast ratio for normal text and 3:1 for large text. Light gray on white (like #999 on white) fails the requirement. Even people with normal vision appreciate high contrast. This is easy to fix: use darker text on light backgrounds or lighter text on dark backgrounds.',
					'wpshadow'
				),
				'severity'      => 'medium',
				'threat_level'  => 40,
				'auto_fixable'  => false,
				'business_impact' => array(
					'metric'         => 'Readability & Accessibility',
					'potential_gain' => 'Better readability for all users',
					'roi_explanation' => 'High contrast improves readability for colorblind users (4-8%), low-vision users (20%), and everyone in bright sunlight.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/color-contrast-accessibility',
			);
		}

		return null;
	}
}
