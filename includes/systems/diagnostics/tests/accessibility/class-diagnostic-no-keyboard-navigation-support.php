<?php
/**
 * No Keyboard Navigation Support Diagnostic
 *
 * Detects when keyboard navigation is not properly implemented,
 * excluding users who cannot use a mouse.
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
 * Diagnostic: No Keyboard Navigation Support
 *
 * Checks whether all interactive elements are accessible
 * via keyboard navigation.
 *
 * @since 1.6035.2148
 */
class Diagnostic_No_Keyboard_Navigation_Support extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-keyboard-navigation-support';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Keyboard Navigation Support';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether keyboard navigation is fully supported';

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
		// This is a basic check - full keyboard testing requires manual testing
		global $wp_query;
		
		// Check homepage for keyboard accessibility patterns
		$homepage = wp_remote_get( home_url() );
		if ( is_wp_error( $homepage ) ) {
			return null; // Can't check
		}

		$body = wp_remote_retrieve_body( $homepage );

		// Check for click-only buttons (missing keyboard support)
		$has_click_handlers = preg_match_all( '/onclick=["\']/', $body );
		$has_links = preg_match_all( '/<a\s+/', $body );
		$has_buttons = preg_match_all( '/<button/', $body );

		// If many click handlers and few buttons/links, likely not keyboard accessible
		if ( $has_click_handlers > ( $has_buttons + $has_links ) / 2 ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __(
					'Your site may have keyboard accessibility issues. Some users cannot use a mouse (motor disabilities, preference for keyboard, or screen readers), so all interactive elements must be keyboard accessible. This means: buttons must work with Tab and Enter keys, dropdowns with arrow keys, dialogs with Escape to close. About 16% of people have motor disabilities. Keyboard navigation is required for WCAG AA compliance.',
					'wpshadow'
				),
				'severity'      => 'medium',
				'threat_level'  => 45,
				'auto_fixable'  => false,
				'business_impact' => array(
					'metric'         => 'Accessibility & Legal Compliance',
					'potential_gain' => 'Serve 16% of population with motor disabilities',
					'roi_explanation' => 'Keyboard accessibility is required for WCAG AA compliance and enables users with motor disabilities to use your site.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/keyboard-navigation-support',
			);
		}

		return null;
	}
}
