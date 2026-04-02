<?php
/**
 * High Availability Setup Diagnostic
 *
 * Checks if load balancing and high availability infrastructure is configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * High Availability Setup Diagnostic Class
 *
 * Verifies that WordPress is deployed with high availability infrastructure including
 * load balancing, multiple web servers, and redundancy for enterprise operations.
 *
 * @since 1.6093.1200
 */
class Diagnostic_High_Availability_Setup extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'high-availability-setup';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'High Availability Setup';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if load balancing and high availability infrastructure is configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'enterprise';

	/**
	 * Run the high availability diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if HA gaps detected, null otherwise.
	 */
	public static function check() {
		$ha_components = array();
		$missing       = array();
		$warnings      = array();

		// Check for load balancer indicators.
		$behind_load_balancer = false;
		
		// Check HTTP headers that indicate load balancer presence.
		$lb_headers = array(
			'HTTP_X_FORWARDED_FOR',
			'HTTP_X_REAL_IP',
			'HTTP_X_FORWARDED_PROTO',
			'HTTP_X_FORWARDED_HOST',
			'HTTP_X_CLUSTER_CLIENT_IP',
		);

		foreach ( $lb_headers as $header ) {
			if ( isset( $_SERVER[ $header ] ) && ! empty( $_SERVER[ $header ] ) ) {
				$behind_load_balancer = true;
				break;
			}
		}

		// Check for AWS ELB/ALB headers.
		if ( isset( $_SERVER['HTTP_X_AMZN_TRACE_ID'] ) || isset( $_SERVER['HTTP_X_AMZ_CF_ID'] ) ) {
			$ha_components['load_balancer'] = 'AWS Load Balancer';
			$behind_load_balancer            = true;
		}

		// Check for CloudFlare.
		if ( isset( $_SERVER['HTTP_CF_RAY'] ) || isset( $_SERVER['HTTP_CF_CONNECTING_IP'] ) ) {
			$ha_components['cdn_lb'] = 'Cloudflare (CDN + LB)';
			$behind_load_balancer    = true;
		}

		// Check for other common load balancers.
		if ( isset( $_SERVER['HTTP_X_VARNISH'] ) ) {
			$ha_components['cache_lb'] = 'Varnish Cache';
		}

		if ( ! $behind_load_balancer ) {
			$missing[] = __( 'Load balancer not detected', 'wpshadow' );
		}

		// Check for persistent object cache (critical for HA).
		if ( wp_using_ext_object_cache() ) {
			$ha_components['object_cache'] = __( 'External object cache active', 'wpshadow' );
		} else {
			$missing[] = __( 'External object cache (Redis/Memcached) required for HA', 'wpshadow' );
		}

		// Check session handling for HA compatibility.
		$session_handler = ini_get( 'session.save_handler' );
		if ( 'files' === $session_handler ) {
			$warnings[] = __( 'File-based sessions incompatible with load-balanced setup', 'wpshadow' );
		} else {
			$ha_components['session_handler'] = sprintf(
				/* translators: %s: session handler type */
				__( 'Session handler: %s', 'wpshadow' ),
				$session_handler
			);
		}

		// Check for database replication awareness.
		global $wpdb;
		if ( defined( 'DB_HOST_SLAVE' ) || defined( 'DB_REPLICA_HOST' ) ) {
			$ha_components['db_replication'] = __( 'Database replication configured', 'wpshadow' );
		}

		// Check for shared uploads directory.
		$upload_dir = wp_upload_dir();
		if ( strpos( $upload_dir['basedir'], 'nfs' ) !== false || 
			 strpos( $upload_dir['basedir'], 'efs' ) !== false ||
			 strpos( $upload_dir['basedir'], 's3' ) !== false ||
			 defined( 'AS3CF_SETTINGS' ) ) {
			$ha_components['shared_storage'] = __( 'Shared/cloud storage configured', 'wpshadow' );
		} else {
			$warnings[] = __( 'Local uploads directory may cause inconsistency across servers', 'wpshadow' );
		}

		// Check for WordPress constants that indicate HA awareness.
		if ( defined( 'WP_CACHE' ) && WP_CACHE ) {
			$ha_components['page_cache'] = __( 'Page caching enabled', 'wpshadow' );
		}

		// Determine if this is an enterprise environment.
		$is_enterprise = self::is_enterprise_environment();

		// If enterprise and missing critical HA components.
		if ( $is_enterprise && count( $missing ) >= 2 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: list of missing components */
					__( 'High availability setup incomplete for enterprise environment. Missing: %s', 'wpshadow' ),
					implode( ', ', $missing )
				),
				'severity'     => 'high',
				'threat_level' => 80,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/high-availability-setup',
				'context'      => array(
					'ha_components' => $ha_components,
					'missing'       => $missing,
					'warnings'      => $warnings,
				),
			);
		}

		// If missing some components.
		if ( ! empty( $missing ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: list of missing components */
					__( 'High availability could be improved. Missing: %s', 'wpshadow' ),
					implode( ', ', $missing )
				),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/high-availability-setup',
				'context'      => array(
					'ha_components' => $ha_components,
					'missing'       => $missing,
					'warnings'      => $warnings,
				),
			);
		}

		// If HA is configured but has warnings.
		if ( ! empty( $warnings ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'High availability is configured but has recommendations: ', 'wpshadow' ) . implode( ', ', $warnings ),
				'severity'     => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/high-availability-setup',
				'context'      => array(
					'ha_components' => $ha_components,
					'warnings'      => $warnings,
				),
			);
		}

		return null; // HA is properly configured.
	}

	/**
	 * Determine if this is an enterprise environment.
	 *
	 * @since 1.6093.1200
	 * @return bool True if enterprise indicators detected, false otherwise.
	 */
	private static function is_enterprise_environment() {
		$enterprise_indicators = array(
			defined( 'WPCOM_IS_VIP_ENV' ) && WPCOM_IS_VIP_ENV,
			defined( 'WPE_CLUSTER_ID' ),
			defined( 'PANTHEON_ENVIRONMENT' ),
			is_multisite() && get_blog_count() > 50,
		);

		return in_array( true, $enterprise_indicators, true );
	}
}
