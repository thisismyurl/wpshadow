<?php
/**
 * No Skip Link Navigation Diagnostic
 *
 * Detects when skip links are missing,
 * forcing keyboard users through repetitive navigation.
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
 * Diagnostic: No Skip Link Navigation
 *
 * Checks whether skip links are implemented
 * for keyboard navigation efficiency.
 *
 * @since 1.6035.2148
 */
class Diagnostic_No_Skip_Link_Navigation extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-skip-link-navigation';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Skip Link Navigation';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether skip links exist';

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
		// Check homepage for skip links
		$homepage = wp_remote_get( home_url() );
		if ( is_wp_error( $homepage ) ) {
			return null;
		}

		$body = wp_remote_retrieve_body( $homepage );
		
		// Check for skip link patterns
		$has_skip_link = preg_match( '/skip.*content|skip.*navigation|skip.*main/i', $body );

		if ( ! $has_skip_link ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __(
					'Skip links are missing, which forces keyboard users through repetitive navigation. Skip links let users jump directly to: main content (skip header), main navigation, footer. Without skip links, keyboard users must Tab through 20-50 navigation links on every page. This is exhausting. Implementation: add hidden link at page top: <a href="#main-content" class="skip-link">Skip to main content</a>. Show on focus for keyboard users. WCAG AA requirement.',
					'wpshadow'
				),
				'severity'      => 'high',
				'threat_level'  => 65,
				'auto_fixable'  => false,
				'business_impact' => array(
					'metric'         => 'Keyboard Navigation Efficiency',
					'potential_gain' => 'Save keyboard users 20-50 tabs per page',
					'roi_explanation' => 'Skip links eliminate repetitive navigation for keyboard users, dramatically improving efficiency.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/skip-link-navigation',
			);
		}

		return null;
	}
}
