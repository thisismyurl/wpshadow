<?php
/**
 * Mobile Third-Party Script Impact
 *
 * Quantifies performance impact of third-party scripts.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Performance
 * @since      1.2602.1600
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Third-Party Script Impact
 *
 * Identifies external scripts (analytics, ads, widgets) and estimates
 * their performance impact on mobile loading.
 *
 * @since 1.2602.1600
 */
class Diagnostic_Mobile_Third_Party_Script_Impact extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-third-party-impact';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Third-Party Script Impact';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Quantifies performance impact of third-party scripts';

	/**
	 * The diagnostic family.
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2602.1600
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$scripts = self::analyze_third_party_scripts();

		if ( empty( $scripts['issues'] ) ) {
			return null; // No problematic third-party scripts
		}

		$total_impact = $scripts['total_impact'];
		$threat = 60;
		if ( $total_impact > 800 ) {
			$threat = 85; // Critical - massive slowdown
		} elseif ( $total_impact > 500 ) {
			$threat = 75;
		}

		return array(
			'id'                  => self::$slug,
			'title'               => self::$title,
			'description'         => sprintf(
				/* translators: %dms: total blocking time */
				__( 'Third-party scripts block %dms of main thread', 'wpshadow' ),
				$total_impact
			),
			'severity'            => 'high',
			'threat_level'        => $threat,
			'total_impact_ms'     => $total_impact,
			'script_count'        => count( $scripts['issues'] ),
			'problematic_scripts' => array_slice( $scripts['issues'], 0, 5 ),
			'optimization_potential' => 'Remove or defer ' . count( $scripts['issues'] ) . ' scripts',
			'user_impact'         => __( 'Third-party scripts delay interaction by 200-800ms', 'wpshadow' ),
			'auto_fixable'        => false,
			'kb_link'             => 'https://wpshadow.com/kb/third-party-impact',
		);
	}

	/**
	 * Analyze third-party script impact.
	 *
	 * @since  1.2602.1600
	 * @return array Script analysis.
	 */
	private static function analyze_third_party_scripts(): array {
		global $wp_scripts;

		$info = array(
			'issues'         => array(),
			'total_impact'   => 0,
		);

		if ( ! isset( $wp_scripts ) || ! is_object( $wp_scripts ) ) {
			return $info;
		}

		// Known heavy third-party scripts
		$heavy_scripts = array(
			'google-analytics' => array(
				'patterns' => array( 'google-analytics', 'ga.js', 'analytics.js' ),
				'impact'   => 180,
			),
			'google-tag-manager' => array(
				'patterns' => array( 'google-tag-manager', 'gtm.js' ),
				'impact'   => 160,
			),
			'facebook-pixel' => array(
				'patterns' => array( 'facebook', 'fbevents', 'fb.js' ),
				'impact'   => 150,
			),
			'hotjar' => array(
				'patterns' => array( 'hotjar', 'hj.js' ),
				'impact'   => 140,
			),
			'intercom' => array(
				'patterns' => array( 'intercom', 'intercomcdn' ),
				'impact'   => 130,
			),
			'drift' => array(
				'patterns' => array( 'drift', 'cdn.drift' ),
				'impact'   => 120,
			),
			'adwords' => array(
				'patterns' => array( 'adwords', 'conversion_async' ),
				'impact'   => 110,
			),
			'twitter-pixel' => array(
				'patterns' => array( 'twitter', 'twtr', 'analytics.twitter' ),
				'impact'   => 100,
			),
		);

		foreach ( $wp_scripts->queue as $handle ) {
			if ( ! isset( $wp_scripts->registered[ $handle ] ) ) {
				continue;
			}

			$script = $wp_scripts->registered[ $handle ];
			if ( empty( $script->src ) ) {
				continue;
			}

			$src = $script->src;

			// Check each heavy script pattern
			foreach ( $heavy_scripts as $service => $data ) {
				foreach ( $data['patterns'] as $pattern ) {
					if ( false !== strpos( strtolower( $src ), strtolower( $pattern ) ) ) {
						$info['issues'][] = array(
							'handle'  => $handle,
							'service' => $service,
							'src'     => $src,
							'impact'  => $data['impact'],
							'unit'    => 'ms blocking time',
						);
						$info['total_impact'] += $data['impact'];
						break 2; // Move to next script
					}
				}
			}

			// Check for multiple trackers from same vendor
			if ( preg_match( '/google.*analytics|analytics.*google/i', $src ) ) {
				if ( count( preg_grep( '/google/i', $wp_scripts->queue ) ) > 1 ) {
					$info['issues'][] = array(
						'handle'  => $handle,
						'service' => 'duplicate-tracker',
						'src'     => $src,
						'impact'  => 50,
						'issue'   => 'Multiple Google Analytics instances',
					);
					$info['total_impact'] += 50;
				}
			}
		}

		return $info;
	}
}
