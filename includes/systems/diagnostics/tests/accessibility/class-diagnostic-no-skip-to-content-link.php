<?php
/**
 * No Skip to Content Link Diagnostic
 *
 * Detects when skip navigation link is missing,
 * forcing keyboard users through repetitive navigation.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Accessibility
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: No Skip to Content Link
 *
 * Checks whether a skip navigation link exists
 * for keyboard and screen reader users.
 *
 * @since 0.6093.1200
 */
class Diagnostic_No_Skip_To_Content_Link extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-skip-to-content-link';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Skip to Content Link';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether skip navigation link exists';

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
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check homepage for skip link
		$homepage = wp_remote_get( home_url() );
		if ( is_wp_error( $homepage ) ) {
			return null;
		}

		$body = wp_remote_retrieve_body( $homepage );
		$has_skip_link = preg_match( '/skip[-\s]?to[-\s]?(main|content)/i', $body );

		if ( ! $has_skip_link ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __(
					'There\'s no "Skip to Content" link, which forces keyboard users to tab through every navigation item on every page load. Imagine pressing Tab 30 times just to reach the content you want. Skip links let users jump directly to main content, bypassing navigation. They\'re typically hidden until focused (visible when Tab is pressed). This is WCAG requirement and takes 5 minutes to add: a link at the top pointing to #main-content.',
					'wpshadow'
				),
				'severity'      => 'medium',
				'threat_level'  => 45,
				'auto_fixable'  => false,
				'business_impact' => array(
					'metric'         => 'Keyboard Navigation Efficiency',
					'potential_gain' => 'Save keyboard users 30+ tab presses per page',
					'roi_explanation' => 'Skip links enable keyboard users to bypass navigation, improving efficiency and meeting WCAG requirements.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/skip-to-content-link?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
