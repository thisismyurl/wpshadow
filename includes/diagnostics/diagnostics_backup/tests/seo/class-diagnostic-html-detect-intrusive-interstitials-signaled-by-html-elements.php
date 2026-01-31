<?php
/**
 * HTML Detect Intrusive Interstitials Signaled By HTML Elements Diagnostic
 *
 * Detects intrusive interstitials that hurt user experience.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\HTML
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * HTML Detect Intrusive Interstitials Signaled By HTML Elements Diagnostic Class
 *
 * Identifies pages with intrusive popups, modals, or interstitials that
 * Google marks as problematic for mobile UX and SEO.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Html_Detect_Intrusive_Interstitials_Signaled_By_Html_Elements extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'html-detect-intrusive-interstitials-signaled-by-html-elements';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Intrusive Interstitials Detected';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects intrusive popups and modals that harm user experience';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'ux';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if ( is_admin() ) {
			return null;
		}

		$interstitials = array();

		// Check scripts for intrusive popup patterns.
		global $wp_scripts;

		if ( ! empty( $wp_scripts ) && isset( $wp_scripts->registered ) ) {
			foreach ( $wp_scripts->registered as $handle => $script_obj ) {
				if ( isset( $script_obj->extra['data'] ) ) {
					$data = (string) $script_obj->extra['data'];

					// Check for common intrusive interstitial patterns.
					$patterns = array(
						'/interstitial|overlay|modal.*popup|popup.*modal/i' => 'Modal/Overlay Popup',
						'/subscribe.*form|email.*capture|join.*list/i'    => 'Subscribe/Email Capture',
						'/exit.*intent|mouse.*leave/i'                    => 'Exit Intent Popup',
						'/age.*verify|over.*18|confirm.*age/i'            => 'Age Verification Modal',
						'/class=["\'].*overlay["\']|class=["\'].*modal["\']/' => 'Overlay/Modal Element',
					);

					foreach ( $patterns as $pattern => $label ) {
						if ( preg_match( $pattern, $data ) ) {
							$interstitials[] = array(
								'handle' => $handle,
								'type'   => $label,
								'issue'  => sprintf(
									__( 'Likely %s detected (may impact SEO and UX)', 'wpshadow' ),
									$label
								),
							);

							break;
						}
					}
				}
			}
		}

		if ( empty( $interstitials ) ) {
			return null;
		}

		$items_list = '';
		$max_items  = 5;

		foreach ( array_slice( $interstitials, 0, $max_items ) as $item ) {
			$items_list .= sprintf(
				"\n- %s: %s",
				esc_html( $item['handle'] ),
				esc_html( $item['issue'] )
			);
		}

		if ( count( $interstitials ) > $max_items ) {
			$items_list .= sprintf(
				/* translators: %d: count */
				__( "\n...and %d more interstitial types", 'wpshadow' ),
				count( $interstitials ) - $max_items
			);
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: 1: count, 2: list */
				__( 'Found %1$d intrusive interstitial(s) detected. Google penalizes pages with intrusive popups/modals that obscure main content, especially on mobile. Consider: using less intrusive sticky headers, sidebar ads, or in-line CTAs instead. Allow easy dismissal if using modals.%2$s', 'wpshadow' ),
				count( $interstitials ),
				$items_list
			),
			'severity'     => 'medium',
			'threat_level' => 30,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/html-detect-intrusive-interstitials-signaled-by-html-elements',
			'meta'         => array(
				'interstitials' => $interstitials,
			),
		);
	}
}
