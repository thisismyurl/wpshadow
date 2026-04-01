<?php
/**
 * Anonymous User Data Collection Diagnostic
 *
 * Checks for unauthorized data collection from anonymous users.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Diagnostics\Helpers\Diagnostic_URL_And_Pattern_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Anonymous User Data Collection Diagnostic
 *
 * Validates data collection practices for anonymous users.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Anonymous_User_Data_Collection extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'anonymous-user-data-collection';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Anonymous User Data Collection';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for unauthorized data collection from anonymous users';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'privacy';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$data_collection = array();

		// Check for analytics plugins without proper consent
		$analytics_plugins = array(
			'google-analytics-for-wordpress/googleanalytics.php' => 'Google Analytics by MonsterInsights',
			'google-site-kit/google-site-kit.php'                => 'Google Site Kit',
			'jetpack/jetpack.php'                                => 'Jetpack (Stats)',
			'wp-statistics/wp-statistics.php'                   => 'WP Statistics',
			'clicky/clicky.php'                                 => 'Clicky',
		);

		$active_plugins = get_option( 'active_plugins', array() );

		foreach ( $analytics_plugins as $plugin => $name ) {
			if ( in_array( $plugin, $active_plugins, true ) ) {
				$data_collection[] = $name;

				// Check if consent is obtained
				$consent_option = apply_filters( 'wpshadow_analytics_consent', false );
				if ( ! $consent_option ) {
					$issues[] = sprintf(
						/* translators: %s: plugin name */
						__( '%s is active but user consent not verified', 'wpshadow' ),
						$name
					);
				}
			}
		}

		// Check for tracking pixels
		$pixel_plugins = array(
			'facebook-pixel/facebook-pixel.php',
			'pinterest-pixel/pin-pixel.php',
		);

		foreach ( $pixel_plugins as $plugin ) {
			if ( in_array( $plugin, $active_plugins, true ) ) {
				$data_collection[] = 'Tracking Pixel: ' . basename( dirname( $plugin ) );

				$consent_required = apply_filters( 'wpshadow_pixel_requires_consent', true );
				if ( $consent_required ) {
					$issues[] = sprintf(
						/* translators: %s: plugin slug */
						__( 'Pixel plugin %s requires user consent before firing', 'wpshadow' ),
						basename( dirname( $plugin ) )
					);
				}
			}
		}

		// Check for cookie consent banner
		$consent_plugins = array(
			'cookie-law-info/cookie-law-info.php',
			'cookiebot/cookiebot.php',
			'termly-cookie-consent/termly-cookie-consent.php',
			'cookie-notice/cookie-notice.php',
		);

		$has_consent_banner = false;
		foreach ( $consent_plugins as $plugin ) {
			if ( in_array( $plugin, $active_plugins, true ) ) {
				$has_consent_banner = true;
				break;
			}
		}

		if ( ! empty( $data_collection ) && ! $has_consent_banner ) {
			$issues[] = __( 'Data collection plugins detected but no consent banner found', 'wpshadow' );
		}

		// Check for email capture forms without disclosure
		global $wp_filter;

		$email_capture_count = 0;
		if ( isset( $wp_filter['wp_footer'] ) ) {
			$wp_footer_hook = $wp_filter['wp_footer'];
			if ( $wp_footer_hook instanceof \WP_Hook ) {
				$email_capture_count += is_array( $wp_footer_hook->callbacks ) ? count( $wp_footer_hook->callbacks ) : 0;
			} elseif ( is_array( $wp_footer_hook ) ) {
				$email_capture_count += count( $wp_footer_hook );
			}
		}

		if ( $email_capture_count > 5 ) {
			$issues[] = __( 'Multiple email capture hooks detected - verify compliance with privacy policy', 'wpshadow' );
		}

		// Check privacy policy link
		$privacy_policy_page_id = get_option( 'wp_page_for_privacy_policy' );
		if ( empty( $privacy_policy_page_id ) ) {
			$issues[] = __( 'Privacy policy page not set - required for data collection', 'wpshadow' );
		} else {
			// Check if privacy policy is public and accessible
			$privacy_post = get_post( $privacy_policy_page_id );
			if ( ! $privacy_post || 'publish' !== $privacy_post->post_status ) {
				$issues[] = __( 'Privacy policy page is not published', 'wpshadow' );
			}
		}

		// Check for third-party script inclusions
		$third_party_scripts = array(
			'facebook.com/en_US/sdk.js',
			'connect.facebook.net',
			'platform.linkedin.com',
			'platform.twitter.com',
		);

		// Check wp_enqueue_script calls
		if ( isset( $wp_filter['wp_enqueue_scripts'] ) ) {
			$enqueue_hook = $wp_filter['wp_enqueue_scripts'];
			$enqueue_callbacks = array();

			if ( $enqueue_hook instanceof \WP_Hook && is_array( $enqueue_hook->callbacks ) ) {
				$enqueue_callbacks = $enqueue_hook->callbacks;
			} elseif ( is_array( $enqueue_hook ) ) {
				$enqueue_callbacks = $enqueue_hook;
			}

			// Rough check for externally loaded scripts.
			foreach ( $enqueue_callbacks as $priority => $callbacks ) {
				foreach ( $callbacks as $callback ) {
					// This is a simplified check - real implementation would need more analysis
					foreach ( $third_party_scripts as $script ) {
						if ( false !== strpos( print_r( $callback, true ), $script ) ) {
							$script_domain = wp_parse_url( 'https://' . $script, PHP_URL_HOST );
							$issues[] = sprintf(
								/* translators: %s: script domain */
								__( 'Third-party script detected: %s', 'wpshadow' ),
								is_string( $script_domain ) ? $script_domain : $script
							);
						}
					}
				}
			}
		}

		// Check for GDPR compliance settings
		$gdpr_compliant = get_option( 'wpshadow_gdpr_compliant', false );
		if ( ! empty( $data_collection ) && ! $gdpr_compliant ) {
			$issues[] = __( 'Data collection active but GDPR compliance not configured', 'wpshadow' );
		}

		// Report findings
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Potential unauthorized user data collection detected', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 80,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/anonymous-user-data-collection?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'issues'             => $issues,
					'data_collection'    => $data_collection,
					'has_consent_banner' => $has_consent_banner,
					'recommendations'    => array(
						__( 'Install a cookie consent banner if collecting user data', 'wpshadow' ),
						__( 'Create and publish a privacy policy', 'wpshadow' ),
						__( 'Obtain explicit consent before analytics tracking', 'wpshadow' ),
						__( 'Implement GDPR compliance settings', 'wpshadow' ),
					),
				),
			);
		}

		return null;
	}
}
