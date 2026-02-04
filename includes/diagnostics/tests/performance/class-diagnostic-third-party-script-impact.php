<?php
/**
 * Third-Party Script Impact Diagnostic
 *
 * Analyzes the performance impact of third-party scripts like analytics,
 * advertising, and chat widgets on page load performance.
 *
 * @since   1.6033.2082
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Third-Party Script Impact Diagnostic Class
 *
 * Detects third-party scripts and their impact:
 * - Analytics (Google Analytics, etc.)
 * - Advertising (Google Ads, AdSense)
 * - Chat widgets (Intercom, Drift, etc.)
 * - Tracking pixels
 * - Performance impact assessment
 *
 * @since 1.6033.2082
 */
class Diagnostic_Third_Party_Script_Impact extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'third-party-script-impact';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Third-Party Script Impact';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Analyzes performance impact of third-party scripts';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6033.2082
	 * @return array|null Finding array if issues found, null otherwise.
	 */
	public static function check() {
		global $wp_scripts;

		$third_party_count = 0;
		$blocking_count    = 0;
		$external_scripts  = array();

		// Common third-party domains
		$third_party_domains = array(
			'google-analytics.com'    => 'Google Analytics',
			'googletagmanager.com'    => 'Google Tag Manager',
			'facebook.com'            => 'Facebook',
			'intercom.io'             => 'Intercom',
			'drift.com'               => 'Drift',
			'segment.com'             => 'Segment',
			'freshchat.com'           => 'Freshchat',
			'tawk.to'                 => 'Tawk',
			'zopim.com'               => 'Zopim',
			'criteo.com'              => 'Criteo',
			'instagram.com'           => 'Instagram',
			'platform.twitter.com'    => 'Twitter',
		);

		// Analyze enqueued scripts
		if ( ! empty( $wp_scripts->queue ) ) {
			foreach ( $wp_scripts->queue as $handle ) {
				$script = $wp_scripts->registered[ $handle ] ?? null;
				if ( $script && ! empty( $script->src ) ) {
					$script_url = $script->src;

					// Check if script is external
					foreach ( $third_party_domains as $domain => $name ) {
						if ( stripos( $script_url, $domain ) !== false ) {
							$third_party_count++;
							$external_scripts[] = $name;

							// Check if script is render-blocking (no async/defer)
							if ( empty( $script->extra['async'] ) && empty( $script->extra['defer'] ) ) {
								$blocking_count++;
							}
							break;
						}
					}
				}
			}
		}

		if ( $third_party_count >= 3 || $blocking_count >= 2 ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => sprintf(
					/* translators: %d: number of third-party scripts, %d: number of blocking */
					__( 'Detected %d third-party scripts, with %d render-blocking. Third-party scripts can add 200-500ms+ to page load.', 'wpshadow' ),
					$third_party_count,
					$blocking_count
				),
				'severity'      => $blocking_count >= 2 ? 'high' : 'medium',
				'threat_level'  => $blocking_count >= 2 ? 60 : 40,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/third-party-script-impact',
				'meta'          => array(
					'third_party_count'    => $third_party_count,
					'blocking_count'       => $blocking_count,
					'detected_scripts'     => array_slice( array_unique( $external_scripts ), 0, 5 ),
					'recommendation'       => 'Add async or defer to third-party scripts. Consider deferring non-critical scripts.',
					'impact'               => 'Reducing third-party script blocking can improve TTI by 200-500ms',
					'optimization'         => array(
						'Add async/defer to external scripts',
						'Lazy-load non-critical scripts',
						'Use Web Workers for heavy JS',
						'Monitor script performance with DevTools',
					),
				),
			);
		}

		return null;
	}
}
