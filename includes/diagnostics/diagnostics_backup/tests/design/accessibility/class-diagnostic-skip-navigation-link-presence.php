<?php
/**
 * Skip Navigation Link Presence Diagnostic
 *
 * Verifies skip-to-content links exist for keyboard navigation.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26028.1905
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Skip Navigation Link Presence Class
 *
 * Tests skip link implementation.
 *
 * @since 1.26028.1905
 */
class Diagnostic_Skip_Navigation_Link_Presence extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'skip-navigation-link-presence';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Skip Navigation Link Presence';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies skip-to-content links exist for keyboard navigation';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26028.1905
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$skip_link_check = self::check_skip_link();
		
		if ( ! $skip_link_check['has_skip_link'] ) {
			$issues = array();
			
			if ( ! $skip_link_check['has_skip_link'] ) {
				$issues[] = __( 'No skip-to-content link found (keyboard navigation issue)', 'wpshadow' );
			}

			if ( $skip_link_check['skip_link_hidden_always'] ) {
				$issues[] = __( 'Skip link permanently hidden (not visible on focus)', 'wpshadow' );
			}

			if ( $skip_link_check['target_missing'] ) {
				$issues[] = __( 'Skip link target ID does not exist in page', 'wpshadow' );
			}

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( ' ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/skip-navigation-link-presence',
				'meta'         => array(
					'has_skip_link'           => $skip_link_check['has_skip_link'],
					'skip_link_hidden_always' => $skip_link_check['skip_link_hidden_always'],
					'target_missing'          => $skip_link_check['target_missing'],
				),
			);
		}

		return null;
	}

	/**
	 * Check skip link presence.
	 *
	 * @since  1.26028.1905
	 * @return array Check results.
	 */
	private static function check_skip_link() {
		$check = array(
			'has_skip_link'           => false,
			'skip_link_hidden_always' => false,
			'target_missing'          => false,
		);

		// Get homepage HTML.
		$response = wp_remote_get( get_home_url(), array( 'timeout' => 10 ) );
		
		if ( is_wp_error( $response ) ) {
			return $check;
		}

		$html = wp_remote_retrieve_body( $response );

		// Check for skip link patterns.
		$skip_patterns = array(
			'/href=["\']#(content|main|main-content|skip-content)["\'][^>]*>.*?skip/i',
			'/<a[^>]*class=["\'][^"\']*skip[^"\']*["\'][^>]*>/i',
		);

		foreach ( $skip_patterns as $pattern ) {
			if ( preg_match( $pattern, $html, $matches ) ) {
				$check['has_skip_link'] = true;

				// Extract the full link HTML.
				preg_match( '/<a[^>]*' . preg_quote( $matches[0], '/' ) . '[^>]*>.*?<\/a>/is', $html, $full_link );
				
				if ( ! empty( $full_link[0] ) ) {
					$link_html = $full_link[0];

					// Check if link target exists.
					preg_match( '/href=["\']#([^"\']+)["\']/i', $link_html, $target_match );
					if ( ! empty( $target_match[1] ) ) {
						$target_id = $target_match[1];
						if ( false === strpos( $html, "id=\"{$target_id}\"" ) ) {
							$check['target_missing'] = true;
						}
					}

					// Check if skip link is permanently hidden.
					if ( false !== strpos( $link_html, 'display:none' ) ||
					     false !== strpos( $link_html, 'visibility:hidden' ) ) {
						$check['skip_link_hidden_always'] = true;
					}
				}

				break;
			}
		}

		return $check;
	}
}
