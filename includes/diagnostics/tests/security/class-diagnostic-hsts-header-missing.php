<?php
/**
 * HSTS Header Configuration Diagnostic
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Diagnostic_HSTS_Header_Missing extends Diagnostic_Base {

	protected static $slug        = 'hsts-header-missing';
	protected static $title       = 'HSTS Header Configuration';
	protected static $description = 'Detects missing HSTS security header';
	protected static $family      = 'security';

	public static function check() {
		$cached = get_transient( 'wpshadow_diagnostic_hsts' );
		if ( false !== $cached ) {
			return $cached;
		}

		$has_hsts = self::check_hsts_header();
		if ( $has_hsts ) {
			set_transient( 'wpshadow_diagnostic_hsts', null, 24 * HOUR_IN_SECONDS );
			return null;
		}

		$finding = array(
			'id'             => self::$slug,
			'title'          => self::$title,
			'description'    => __( 'HSTS (HTTP Strict Transport Security) header is missing', 'wpshadow' ),
			'severity'       => 'medium',
			'threat_level'   => 60,
			'auto_fixable'   => false,
			'kb_link'        => 'https://wpshadow.com/kb/hsts-header',
			'details'        => array( __( 'HSTS prevents protocol downgrade attacks and cookie hijacking', 'wpshadow' ) ),
			'recommendations' => array(
				__( 'Add Strict-Transport-Security header via .htaccess or server config', 'wpshadow' ),
				__( 'Use: Strict-Transport-Security: max-age=31536000; includeSubDomains', 'wpshadow' ),
			),
		);

		set_transient( 'wpshadow_diagnostic_hsts', $finding, 24 * HOUR_IN_SECONDS );
		return $finding;
	}

	private static function check_hsts_header() {
		$response = wp_remote_head( home_url(), array( 'sslverify' => false ) );
		if ( is_wp_error( $response ) ) {
			return false;
		}

		$headers = wp_remote_retrieve_headers( $response );
		return ! empty( $headers['strict-transport-security'] );
	}
}
