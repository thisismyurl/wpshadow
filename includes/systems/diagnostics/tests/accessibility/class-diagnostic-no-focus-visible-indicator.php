<?php
/**
 * No Focus Visible Indicator Diagnostic
 *
 * Detects when focus indicators are missing or hidden,
 * making keyboard navigation impossible to track.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Accessibility
 * @since      1.6035.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: No Focus Visible Indicator
 *
 * Checks whether focus indicators are visible
 * for keyboard navigation users.
 *
 * @since 1.6035.2148
 */
class Diagnostic_No_Focus_Visible_Indicator extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-focus-visible-indicator';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Focus Visible Indicators';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether focus indicators are visible';

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
	 * @since  1.6035.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check CSS for focus outline removal
		$homepage = wp_remote_get( home_url() );
		if ( is_wp_error( $homepage ) ) {
			return null;
		}

		$body = wp_remote_retrieve_body( $homepage );
		
		// Look for CSS that removes focus indicators
		$removes_outline = preg_match( '/outline:\s*(?:none|0)/i', $body ) ||
			preg_match( '/\*:focus\s*{\s*outline:\s*(?:none|0)/i', $body );

		if ( $removes_outline ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __(
					'Focus indicators have been removed with CSS, which makes keyboard navigation invisible. When someone presses Tab, they need to see where focus is (usually a blue outline). Your CSS has outline: none or outline: 0, which removes this. Keyboard-only users now can\'t see where they are. Solution: either keep default focus styles, or add custom visible focus styles. Never just remove them—that makes sites unusable for keyboard users.',
					'wpshadow'
				),
				'severity'      => 'high',
				'threat_level'  => 70,
				'auto_fixable'  => false,
				'business_impact' => array(
					'metric'         => 'Keyboard Navigation Usability',
					'potential_gain' => 'Enable 16% of users to navigate site',
					'roi_explanation' => 'Visible focus indicators are required for keyboard navigation. Without them, 16% of users can\'t use the site.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/focus-visible-indicators',
			);
		}

		return null;
	}
}
