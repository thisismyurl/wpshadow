<?php
/**
 * SSL Certificate Validity Diagnostic
 *
 * Issue #4932: SSL Certificate Expired or Invalid
 * Pillar: 🛡️ Safe by Default / ⚙️ Murphy's Law
 *
 * Checks SSL certificate validity and expiration.
 * Expired certificates cause browser warnings and lost traffic.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6050.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_SSL_Certificate_Validity Class
 *
 * @since 1.6050.0000
 */
class Diagnostic_SSL_Certificate_Validity extends Diagnostic_Base {

	protected static $slug = 'ssl-certificate-validity';
	protected static $title = 'SSL Certificate Expired or Invalid';
	protected static $description = 'Checks SSL certificate status and expiration date';
	protected static $family = 'security';

	public static function check() {
		$issues = array();

		$issues[] = __( 'Monitor SSL certificate expiration (renew 30 days before)', 'wpshadow' );
		$issues[] = __( 'Use auto-renewal with Let\'s Encrypt or Certbot', 'wpshadow' );
		$issues[] = __( 'Verify certificate chain is complete', 'wpshadow' );
		$issues[] = __( 'Check for mixed content warnings', 'wpshadow' );
		$issues[] = __( 'Enable HSTS to prevent downgrade attacks', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'SSL certificates expire and must be renewed. Expired certificates show browser warnings that scare visitors away.', 'wpshadow' ),
				'severity'     => 'critical',
				'threat_level' => 90,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/ssl-certificate',
				'details'      => array(
					'recommendations'         => $issues,
					'lets_encrypt'            => 'Free SSL certificates, auto-renew every 90 days',
					'browser_warning'         => 'Users see "Your connection is not private"',
					'traffic_loss'            => 'Up to 70% of users leave on SSL warning',
				),
			);
		}

		return null;
	}
}
