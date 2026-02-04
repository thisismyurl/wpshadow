<?php
/**
 * No ARIA Landmark Roles Diagnostic
 *
 * Detects when ARIA landmarks are missing,
 * making navigation difficult for screen reader users.
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
 * Diagnostic: No ARIA Landmark Roles
 *
 * Checks whether ARIA landmarks are implemented
 * for screen reader navigation.
 *
 * @since 1.6035.2148
 */
class Diagnostic_No_ARIA_Landmark_Roles extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-aria-landmark-roles';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'ARIA Landmark Roles';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether ARIA landmarks exist';

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
		// Check homepage for ARIA landmarks
		$homepage = wp_remote_get( home_url() );
		if ( is_wp_error( $homepage ) ) {
			return null;
		}

		$body = wp_remote_retrieve_body( $homepage );
		
		// Check for common landmarks
		$has_landmarks = preg_match( '/<(header|nav|main|aside|footer)|role=["\'](?:banner|navigation|main|complementary|contentinfo)["\']/i', $body );

		if ( ! $has_landmarks ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __(
					'ARIA landmarks are missing, which makes navigation hard for screen readers. Landmarks let screen reader users jump between: header (banner), navigation, main content, sidebar (complementary), footer (contentinfo). Without landmarks, users must tab through entire page linearly. Implementation: use HTML5 semantic elements (<header>, <nav>, <main>, <aside>, <footer>) OR add ARIA roles to divs. Modern themes include these, but verify with accessibility checker.',
					'wpshadow'
				),
				'severity'      => 'high',
				'threat_level'  => 65,
				'auto_fixable'  => false,
				'business_impact' => array(
					'metric'         => 'Screen Reader Navigation',
					'potential_gain' => 'Enable efficient page navigation for 2% of users',
					'roi_explanation' => 'ARIA landmarks enable screen reader users to jump directly to page sections instead of linear navigation.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/aria-landmark-roles',
			);
		}

		return null;
	}
}
