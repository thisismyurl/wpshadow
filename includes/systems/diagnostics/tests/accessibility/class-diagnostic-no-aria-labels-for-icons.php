<?php
/**
 * No ARIA Labels for Icons Diagnostic
 *
 * Detects when icon buttons lack ARIA labels,
 * making them inaccessible to screen reader users.
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
 * Diagnostic: No ARIA Labels for Icons
 *
 * Checks whether icon buttons and links have
 * proper ARIA labels for screen readers.
 *
 * @since 1.6093.1200
 */
class Diagnostic_No_ARIA_Labels_For_Icons extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-aria-labels-icons';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'ARIA Labels for Icon Buttons';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether icon buttons have ARIA labels';

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
		// Check homepage for unlabeled icons
		$homepage = wp_remote_get( home_url() );
		if ( is_wp_error( $homepage ) ) {
			return null;
		}

		$body = wp_remote_retrieve_body( $homepage );

		// Look for icon patterns (Font Awesome, dashicons, etc.)
		preg_match_all( '/<(?:button|a)[^>]*><i\s+class=["\'][^"\']*(?:fa-|dashicons-|icon-)[^"\']*["\'][^>]*><\/i><\/(?:button|a)>/i', $body, $icon_buttons );
		
		if ( ! empty( $icon_buttons[0] ) ) {
			// Check if they have aria-label
			$unlabeled_count = 0;
			foreach ( $icon_buttons[0] as $button ) {
				if ( strpos( $button, 'aria-label' ) === false ) {
					$unlabeled_count++;
				}
			}

			if ( $unlabeled_count > 0 ) {
				return array(
					'id'            => self::$slug,
					'title'         => self::$title,
					'description'   => sprintf(
						__(
							'About %d icon buttons don\'t have ARIA labels, which means screen reader users hear "button" or "link" without knowing what it does. Icon-only buttons (like a magnifying glass for search or hamburger menu) need aria-label to describe their purpose: aria-label="Search" or aria-label="Open menu". Without labels, blind users can\'t use these buttons. This is a quick fix that dramatically improves accessibility.',
							'wpshadow'
						),
						$unlabeled_count
					),
					'severity'      => 'medium',
					'threat_level'  => 50,
					'auto_fixable'  => false,
					'unlabeled_count' => $unlabeled_count,
					'business_impact' => array(
						'metric'         => 'Screen Reader Accessibility',
						'potential_gain' => 'Enable blind users to use navigation',
						'roi_explanation' => 'ARIA labels on icons make functionality accessible to 2% of users who are blind or use screen readers.',
					),
					'kb_link'       => 'https://wpshadow.com/kb/aria-labels-for-icons',
				);
			}
		}

		return null;
	}
}
