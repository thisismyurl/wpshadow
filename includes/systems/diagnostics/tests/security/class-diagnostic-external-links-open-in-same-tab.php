<?php
/**
 * External Links Open in Same Tab Diagnostic
 *
 * Checks if external links open in a new tab (security & UX best practice).
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Security
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * External Links Target Blank Diagnostic
 *
 * Detects external links that don't open in new tabs. External links should always
 * open in new tabs (_blank or target="_blank") to keep visitors on your site.
 * This improves UX (visitors don't lose your site) and provides security (prevents
 * Referrer Policy leaks to external sites).
 *
 * @since 0.6093.1200
 */
class Diagnostic_External_Links_Open_In_Same_Tab extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'external-links-open-same-tab';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'External Links Open in New Tabs';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if external links open in new tabs (target="_blank") for better UX and security';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$external_links = self::check_external_links();

		if ( ! empty( $external_links ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %d: number of external links opening in same tab */
					__( 'Found %d external links that open in the same tab. Best practice: external links should use target="_blank" to keep visitors on your site. Also add rel="noopener noreferrer" for security (prevents Referrer Policy leaks and protects against window.opener attacks).', 'wpshadow' ),
					count( $external_links )
				),
				'severity'    => 'medium',
				'threat_level' => 30,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/external-links-security?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'     => array(
					'problematic_count' => count( $external_links ),
					'examples'          => array_slice( $external_links, 0, 5 ),
					'recommendation'    => __( 'Add target="_blank" rel="noopener noreferrer" to all external links', 'wpshadow' ),
				),
			);
		}

		return null; // No issue found
	}

	/**
	 * Check for external links not opening in new tabs
	 *
	 * @since 0.6093.1200
	 * @return array Array of problematic external links
	 */
	private static function check_external_links(): array {
		$problematic = array();

		// Get homepage content
		$response = wp_remote_get( home_url( '/' ) );

		if ( is_wp_error( $response ) ) {
			return array();
		}

		$body = wp_remote_retrieve_body( $response );
		$site_domain = wp_parse_url( home_url() );
		$site_domain = $site_domain['host'] ?? '';

		// Find all links
		if ( preg_match_all( '/<a\s+([^>]*)href\s*=\s*["\']?([^"\'\s>]+)["\']?([^>]*)>/i', $body, $matches ) ) {
			for ( $i = 0; $i < count( $matches[0] ); $i++ ) {
				$full_tag = $matches[0][ $i ];
				$before   = $matches[1][ $i ];
				$href     = $matches[2][ $i ];
				$after    = $matches[3][ $i ];

				// Check if this is an external link
				$link_domain = wp_parse_url( $href );
				$link_domain = $link_domain['host'] ?? '';

				if ( empty( $link_domain ) || $link_domain === $site_domain ) {
					continue; // Internal link, skip
				}

				// Check if it has target="_blank"
				if ( ! preg_match( '/target\s*=\s*["\']?_blank["\']?/i', $full_tag ) ) {
					$problematic[] = array(
						'href'   => esc_url( $href ),
						'issue'  => 'Missing target="_blank"',
						'fix'    => 'Add target="_blank" and rel="noopener noreferrer"',
					);
				} elseif ( ! preg_match( '/rel\s*=\s*["\']?[^"\']*noopener[^"\']*["\']?/i', $full_tag ) ) {
					// Has target="_blank" but missing security rel attribute
					$problematic[] = array(
						'href'   => esc_url( $href ),
						'issue'  => 'Missing rel="noopener noreferrer"',
						'fix'    => 'Add rel="noopener noreferrer" for security',
					);
				}
			}
		}

		return array_slice( $problematic, 0, 20 ); // Limit results
	}
}
