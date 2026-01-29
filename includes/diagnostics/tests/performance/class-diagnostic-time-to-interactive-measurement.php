<?php
/**
 * Time to Interactive (TTI) Measurement Diagnostic
 *
 * Measures when page becomes fully interactive and usable.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26029.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Time to Interactive (TTI) Measurement Class
 *
 * Tests TTI metric.
 *
 * @since 1.26029.0000
 */
class Diagnostic_Time_To_Interactive_Measurement extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'time-to-interactive-measurement';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Time to Interactive (TTI) Measurement';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Measures when page becomes fully interactive and usable';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26029.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$tti_check = self::check_tti_indicators();
		
		if ( $tti_check['has_issues'] ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( ' ', $tti_check['issues'] ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/time-to-interactive-measurement',
				'meta'         => array(
					'total_scripts'         => $tti_check['total_scripts'],
					'blocking_scripts'      => $tti_check['blocking_scripts'],
					'heavy_frameworks'      => $tti_check['heavy_frameworks'],
					'third_party_scripts'   => $tti_check['third_party_scripts'],
					'recommendations'       => $tti_check['recommendations'],
				),
			);
		}

		return null;
	}

	/**
	 * Check TTI indicators.
	 *
	 * @since  1.26029.0000
	 * @return array Check results.
	 */
	private static function check_tti_indicators() {
		global $wp_scripts;

		$check = array(
			'has_issues'          => false,
			'issues'              => array(),
			'total_scripts'       => 0,
			'blocking_scripts'    => 0,
			'heavy_frameworks'    => array(),
			'third_party_scripts' => array(),
			'recommendations'     => array(),
		);

		if ( empty( $wp_scripts->queue ) ) {
			return $check;
		}

		// Analyze enqueued scripts.
		foreach ( $wp_scripts->queue as $handle ) {
			if ( ! isset( $wp_scripts->registered[ $handle ] ) ) {
				continue;
			}

			$script = $wp_scripts->registered[ $handle ];
			$check['total_scripts']++;

			// Check if script is blocking.
			if ( ! isset( $script->extra['async'] ) && ! isset( $script->extra['defer'] ) ) {
				$check['blocking_scripts']++;
			}

			// Identify heavy frameworks.
			if ( ! empty( $script->src ) ) {
				// React.
				if ( false !== strpos( $script->src, 'react' ) || false !== strpos( $handle, 'react' ) ) {
					$check['heavy_frameworks'][] = 'React';
				}

				// Vue.
				if ( false !== strpos( $script->src, 'vue' ) || false !== strpos( $handle, 'vue' ) ) {
					$check['heavy_frameworks'][] = 'Vue';
				}

				// Angular.
				if ( false !== strpos( $script->src, 'angular' ) || false !== strpos( $handle, 'angular' ) ) {
					$check['heavy_frameworks'][] = 'Angular';
				}

				// Check for third-party scripts.
				$home_url = home_url();
				if ( 0 !== strpos( $script->src, $home_url ) && 0 !== strpos( $script->src, '/wp-includes/' ) ) {
					// External domain.
					if ( preg_match( '/https?:\/\/([^\/]+)/', $script->src, $matches ) ) {
						$domain = $matches[1];
						
						// Common third-party services.
						if ( false !== strpos( $domain, 'google-analytics' ) ||
						     false !== strpos( $domain, 'googletagmanager' ) ||
						     false !== strpos( $domain, 'facebook' ) ||
						     false !== strpos( $domain, 'doubleclick' ) ||
						     false !== strpos( $domain, 'twitter' ) ) {
							$check['third_party_scripts'][] = $domain;
						}
					}
				}
			}
		}

		// Deduplicate frameworks.
		$check['heavy_frameworks'] = array_unique( $check['heavy_frameworks'] );
		$check['third_party_scripts'] = array_unique( $check['third_party_scripts'] );

		// Detect issues.
		if ( $check['blocking_scripts'] > 8 ) {
			$check['has_issues'] = true;
			$check['issues'][] = sprintf(
				/* translators: %d: number of blocking scripts */
				__( '%d blocking JavaScript files detected (delays interactivity)', 'wpshadow' ),
				$check['blocking_scripts']
			);
			$check['recommendations'][] = __( 'Add defer or async attributes to non-critical scripts', 'wpshadow' );
		}

		if ( ! empty( $check['heavy_frameworks'] ) ) {
			$check['has_issues'] = true;
			$check['issues'][] = sprintf(
				/* translators: %s: framework names */
				__( 'Heavy JavaScript framework detected: %s (can block TTI for 1-3 seconds)', 'wpshadow' ),
				implode( ', ', $check['heavy_frameworks'] )
			);
			$check['recommendations'][] = __( 'Consider server-side rendering or lazy loading framework', 'wpshadow' );
		}

		if ( count( $check['third_party_scripts'] ) > 3 ) {
			$check['has_issues'] = true;
			$check['issues'][] = sprintf(
				/* translators: %d: number of third-party scripts */
				__( '%d third-party scripts detected (analytics, ads, social)', 'wpshadow' ),
				count( $check['third_party_scripts'] )
			);
			$check['recommendations'][] = __( 'Load third-party scripts asynchronously or with delay', 'wpshadow' );
		}

		// Check for jQuery in footer.
		if ( in_array( 'jquery', $wp_scripts->queue, true ) || in_array( 'jquery-core', $wp_scripts->queue, true ) ) {
			$jquery_in_footer = false;
			if ( isset( $wp_scripts->registered['jquery-core'] ) ) {
				$jquery_in_footer = isset( $wp_scripts->registered['jquery-core']->extra['group'] ) && 1 === $wp_scripts->registered['jquery-core']->extra['group'];
			}

			if ( ! $jquery_in_footer ) {
				$check['has_issues'] = true;
				$check['issues'][] = __( 'jQuery loaded in header (blocks TTI)', 'wpshadow' );
				$check['recommendations'][] = __( 'Move jQuery to footer or remove if not needed', 'wpshadow' );
			}
		}

		return $check;
	}
}
