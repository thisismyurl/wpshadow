<?php
/**
 * Site Kit Google Services Connection Status Diagnostic
 *
 * Checks if Site Kit by Google services are properly connected and tracking.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6031.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Site Kit Connection Status Diagnostic Class
 *
 * Verifies all Google services are connected and tracking properly.
 *
 * @since 1.6031.1200
 */
class Diagnostic_Site_Kit_Connection_Status extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'site-kit-connection-status';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Site Kit Google Services Connection Status';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies all Google services properly connected and tracking';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'plugins';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6031.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if Site Kit is active.
		if ( ! class_exists( 'Google\Site_Kit\Context' ) && ! class_exists( 'Google\Site_Kit\Plugin' ) ) {
			return null; // Plugin not active, no check needed.
		}

		$issues = array();

		// Check for Site Kit module status.
		if ( function_exists( 'googlesitekit_get_modules' ) ) {
			$modules = googlesitekit_get_modules();

			// Check Analytics connection.
			if ( isset( $modules['analytics'] ) && ! $modules['analytics']->is_connected() ) {
				$issues[] = array(
					'service'     => 'Google Analytics',
					'issue'       => 'disconnected',
					'description' => __( 'Google Analytics is not connected', 'wpshadow' ),
					'severity'    => 'high',
				);
			}

			// Check Search Console connection.
			if ( isset( $modules['search-console'] ) && ! $modules['search-console']->is_connected() ) {
				$issues[] = array(
					'service'     => 'Google Search Console',
					'issue'       => 'disconnected',
					'description' => __( 'Google Search Console is not connected', 'wpshadow' ),
					'severity'    => 'high',
				);
			}

			// Check PageSpeed Insights.
			if ( isset( $modules['pagespeed-insights'] ) && ! $modules['pagespeed-insights']->is_connected() ) {
				$issues[] = array(
					'service'     => 'PageSpeed Insights',
					'issue'       => 'disconnected',
					'description' => __( 'PageSpeed Insights is not enabled', 'wpshadow' ),
					'severity'    => 'low',
				);
			}

			// Check AdSense (if applicable).
			if ( isset( $modules['adsense'] ) && $modules['adsense']->is_active() && ! $modules['adsense']->is_connected() ) {
				$issues[] = array(
					'service'     => 'Google AdSense',
					'issue'       => 'disconnected',
					'description' => __( 'Google AdSense is activated but not connected', 'wpshadow' ),
					'severity'    => 'medium',
				);
			}

			// Check Tag Manager (if applicable).
			if ( isset( $modules['tagmanager'] ) && $modules['tagmanager']->is_active() && ! $modules['tagmanager']->is_connected() ) {
				$issues[] = array(
					'service'     => 'Google Tag Manager',
					'issue'       => 'disconnected',
					'description' => __( 'Google Tag Manager is activated but not connected', 'wpshadow' ),
					'severity'    => 'medium',
				);
			}
		} else {
			// Fallback: Check using options if functions not available.
			$connected = get_option( 'googlesitekit_connected', false );
			if ( ! $connected ) {
				$issues[] = array(
					'service'     => 'Site Kit',
					'issue'       => 'not_connected',
					'description' => __( 'Site Kit is not connected to Google Services', 'wpshadow' ),
					'severity'    => 'high',
				);
			}
		}

		// Check for authentication expiry.
		$auth_error = get_transient( 'googlesitekit_auth_error' );
		if ( $auth_error ) {
			$issues[] = array(
				'service'     => 'Site Kit Authentication',
				'issue'       => 'auth_expired',
				'description' => __( 'Site Kit authentication token has expired and needs reconnection', 'wpshadow' ),
				'severity'    => 'high',
			);
		}

		if ( empty( $issues ) ) {
			return null; // No issues found.
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: number of connection issues found */
				__( 'Found %d Site Kit connection issues', 'wpshadow' ),
				count( $issues )
			),
			'severity'     => 'high',
			'threat_level' => 80,
			'auto_fixable' => false,
			'details'      => $issues,
			'kb_link'      => 'https://wpshadow.com/kb/site-kit-connection-status',
		);
	}
}
