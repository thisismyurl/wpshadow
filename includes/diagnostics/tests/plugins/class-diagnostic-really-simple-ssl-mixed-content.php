<?php
/**
 * Really Simple Ssl Mixed Content Diagnostic
 *
 * Really Simple Ssl Mixed Content issue found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1448.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Really Simple Ssl Mixed Content Diagnostic Class
 *
 * @since 1.1448.0000
 */
class Diagnostic_ReallySimpleSslMixedContent extends Diagnostic_Base {

	protected static $slug = 'really-simple-ssl-mixed-content';
	protected static $title = 'Really Simple Ssl Mixed Content';
	protected static $description = 'Really Simple Ssl Mixed Content issue found';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'REALLY_SIMPLE_SSL_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Mixed content detection
		$detect = get_option( 'rssl_mixed_content_detection_enabled', 0 );
		if ( ! $detect ) {
			$issues[] = 'Mixed content detection not enabled';
		}
		
		// Check 2: Auto-fix mixed content
		$autofix = get_option( 'rssl_auto_mixed_content_fix_enabled', 0 );
		if ( ! $autofix ) {
			$issues[] = 'Auto-fix for mixed content not enabled';
		}
		
		// Check 3: HTTPS enforcement
		$https = get_option( 'rssl_force_https_enabled', 0 );
		if ( ! $https ) {
			$issues[] = 'HTTPS enforcement not enabled';
		}
		
		// Check 4: SSL certificate validation
		$cert = get_option( 'rssl_ssl_certificate_validation_enabled', 0 );
		if ( ! $cert ) {
			$issues[] = 'SSL certificate validation not enabled';
		}
		
		// Check 5: HSTS header
		$hsts = get_option( 'rssl_hsts_header_enabled', 0 );
		if ( ! $hsts ) {
			$issues[] = 'HSTS header not enabled';
		}
		
		// Check 6: Redirect chain
		$redirect = get_option( 'rssl_redirect_chain_optimized', 0 );
		if ( ! $redirect ) {
			$issues[] = 'Redirect chain not optimized';
		}
		
		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 55;
			$threat_multiplier = 6;
			$max_threat = 85;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );
			
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d SSL/mixed content issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/really-simple-ssl-mixed-content',
			);
		}
		
		return null;
	}
}
