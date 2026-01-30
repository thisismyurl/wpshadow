<?php
/**
 * Google Tag Manager Container Loading Diagnostic
 *
 * Google Tag Manager Container Loading misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1344.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Google Tag Manager Container Loading Diagnostic Class
 *
 * @since 1.1344.0000
 */
class Diagnostic_GoogleTagManagerContainerLoading extends Diagnostic_Base {

	protected static $slug = 'google-tag-manager-container-loading';
	protected static $title = 'Google Tag Manager Container Loading';
	protected static $description = 'Google Tag Manager Container Loading misconfigured';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'GTM4WP_VERSION' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Container ID
		$container_id = get_option( 'gtm4wp_container_id', '' );
		if ( empty( $container_id ) ) {
			$issues[] = __( 'No container ID set (GTM not active)', 'wpshadow' );
		}

		// Check 2: Loading method
		$load_method = get_option( 'gtm4wp_load_method', 'header' );
		if ( 'header' === $load_method ) {
			$issues[] = __( 'Loaded in header (render blocking)', 'wpshadow' );
		}

		// Check 3: Async loading
		$async = get_option( 'gtm4wp_async', 'no' );
		if ( 'no' === $async ) {
			$issues[] = __( 'Not loaded async (page speed impact)', 'wpshadow' );
		}

		// Check 4: Environment snippet
		$environment = get_option( 'gtm4wp_environment', '' );
		if ( ! empty( $environment ) && ! defined( 'WP_DEBUG' ) ) {
			$issues[] = __( 'Testing environment in production (data issues)', 'wpshadow' );
		}

		// Check 5: datalayer events
		$datalayer_events = get_option( 'gtm4wp_datalayer_events', array() );
		if ( count( $datalayer_events ) > 50 ) {
			$issues[] = sprintf( __( '%d dataLayer events (performance impact)', 'wpshadow' ), count( $datalayer_events ) );
		}

		// Check 6: Tag timeout
		$timeout = get_option( 'gtm4wp_tag_timeout', 2000 );
		if ( $timeout > 5000 ) {
			$issues[] = __( 'Tag timeout too long (slow page loads)', 'wpshadow' );
		}

		if ( empty( $issues ) ) {
			return null;
		}

		$threat_level = 55;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 67;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 61;
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				__( 'Google Tag Manager has %d performance issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/google-tag-manager-container-loading',
		);
	}
}
