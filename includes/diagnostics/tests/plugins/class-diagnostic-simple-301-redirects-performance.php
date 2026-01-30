<?php
/**
 * Simple 301 Redirects Performance Diagnostic
 *
 * Simple 301 Redirects Performance issue found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1429.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Simple 301 Redirects Performance Diagnostic Class
 *
 * @since 1.1429.0000
 */
class Diagnostic_Simple301RedirectsPerformance extends Diagnostic_Base {

	protected static $slug = 'simple-301-redirects-performance';
	protected static $title = 'Simple 301 Redirects Performance';
	protected static $description = 'Simple 301 Redirects Performance issue found';
	protected static $family = 'performance';

	public static function check() {
		// Check for Simple 301 Redirects or similar plugins
		$has_redirects = function_exists( 'simple_301_redirects' ) ||
		                 get_option( '301_redirects', false ) ||
		                 class_exists( 'Simple301redirects' );
		
		if ( ! $has_redirects ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Redirect count
		$redirects = get_option( '301_redirects', array() );
		$redirect_count = is_array( $redirects ) ? count( $redirects ) : 0;
		
		if ( $redirect_count === 0 ) {
			return null;
		}
		
		if ( $redirect_count > 100 ) {
			$issues[] = sprintf( __( '%d redirects configured (consider .htaccess)', 'wpshadow' ), $redirect_count );
		}
		
		// Check 2: Wildcard redirects
		$wildcard_count = 0;
		foreach ( $redirects as $from => $to ) {
			if ( strpos( $from, '*' ) !== false || strpos( $from, '.*' ) !== false ) {
				$wildcard_count++;
			}
		}
		
		if ( $wildcard_count > 10 ) {
			$issues[] = sprintf( __( '%d wildcard redirects (regex overhead)', 'wpshadow' ), $wildcard_count );
		}
		
		// Check 3: Redirect caching
		$cache_redirects = get_option( '301_redirects_cache', false );
		if ( ! $cache_redirects && $redirect_count > 50 ) {
			$issues[] = __( 'Redirect caching disabled (database queries)', 'wpshadow' );
		}
		
		// Check 4: Redirect loop detection
		foreach ( $redirects as $from => $to ) {
			if ( isset( $redirects[ $to ] ) && $redirects[ $to ] === $from ) {
				$issues[] = sprintf(
					/* translators: %s: redirect URL */
					__( 'Redirect loop detected: %s', 'wpshadow' ),
					substr( $from, 0, 50 )
				);
				break;
			}
		}
		
		// Check 5: External redirects
		$external_count = 0;
		foreach ( $redirects as $from => $to ) {
			if ( strpos( $to, 'http://' ) === 0 || strpos( $to, 'https://' ) === 0 ) {
				$external_count++;
			}
		}
		
		if ( $external_count > 20 ) {
			$issues[] = sprintf( __( '%d external redirects (link juice loss)', 'wpshadow' ), $external_count );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 55;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 68;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 62;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of redirect performance issues */
				__( 'Simple 301 Redirects has %d performance issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/simple-301-redirects-performance',
		);
	}
}
