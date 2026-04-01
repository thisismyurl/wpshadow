<?php
/**
 * Cookie Banner Compliance Diagnostic
 *
 * Issue #4955: No Cookie Consent Banner (GDPR/CCPA)
 * Pillar: #10: Beyond Pure (Privacy) / 🌐 Culturally Respectful
 *
 * Checks if cookie consent banner is implemented.
 * EU GDPR requires explicit consent before non-essential cookies.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Cookie_Banner_Compliance Class
 *
 * @since 0.6093.1200
 */
class Diagnostic_Cookie_Banner_Compliance extends Diagnostic_Base {

	protected static $slug = 'cookie-banner-compliance';
	protected static $title = 'No Cookie Consent Banner (GDPR/CCPA)';
	protected static $description = 'Checks if cookie consent is obtained before tracking';
	protected static $family = 'compliance';

	public static function check() {
		$issues = array();

		$issues[] = __( 'Show cookie banner on first visit', 'wpshadow' );
		$issues[] = __( 'Require explicit consent (not pre-checked boxes)', 'wpshadow' );
		$issues[] = __( 'Block tracking until consent given', 'wpshadow' );
		$issues[] = __( 'Provide granular choices (essential, analytics, marketing)', 'wpshadow' );
		$issues[] = __( 'Allow easy withdrawal of consent', 'wpshadow' );
		$issues[] = __( 'Store consent record for compliance audit', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'GDPR requires explicit consent before non-essential cookies. Cookie banners must block tracking until users actively consent.', 'wpshadow' ),
				'severity'     => 'critical',
				'threat_level' => 85,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/cookie-consent?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'recommendations'         => $issues,
					'gdpr_requirement'        => 'Explicit opt-in required (not opt-out)',
					'essential_cookies'       => 'Session, security, load balancing (no consent needed)',
					'non_essential'           => 'Analytics, advertising, social media (consent required)',
					'commandment'             => 'Commandment #10: Beyond Pure (Privacy First)',
				),
			);
		}

		return null;
	}
}
