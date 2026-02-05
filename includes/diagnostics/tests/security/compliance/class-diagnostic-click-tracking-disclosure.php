<?php
/**
 * Click Tracking Disclosure Diagnostic
 *
 * Issue #4954: No Disclosure of Click Tracking
 * Pillar: #10: Beyond Pure (Privacy) / 🌐 Culturally Respectful
 *
 * Checks if click tracking is disclosed to users.
 * GDPR/CCPA require transparency about tracking.
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
 * Diagnostic_Click_Tracking_Disclosure Class
 *
 * @since 1.6050.0000
 */
class Diagnostic_Click_Tracking_Disclosure extends Diagnostic_Base {

	protected static $slug = 'click-tracking-disclosure';
	protected static $title = 'No Disclosure of Click Tracking';
	protected static $description = 'Checks if tracking mechanisms are disclosed in privacy policy';
	protected static $family = 'compliance';

	public static function check() {
		$issues = array();

		$issues[] = __( 'Disclose all tracking in privacy policy', 'wpshadow' );
		$issues[] = __( 'List tracking tools: Google Analytics, Facebook Pixel, etc', 'wpshadow' );
		$issues[] = __( 'Explain what data is collected (clicks, pages, time)', 'wpshadow' );
		$issues[] = __( 'Explain why data is collected (analytics, marketing)', 'wpshadow' );
		$issues[] = __( 'Provide opt-out mechanism', 'wpshadow' );
		$issues[] = __( 'Get consent before tracking (GDPR requirement)', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'GDPR and CCPA require transparency about tracking. Users must know what data you collect, why, and how to opt out.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/tracking-disclosure',
				'details'      => array(
					'recommendations'         => $issues,
					'gdpr_fine'               => 'Up to €20 million or 4% of annual turnover',
					'ccpa_fine'               => 'Up to $7,500 per intentional violation',
					'commandment'             => 'Commandment #10: Beyond Pure (Privacy First)',
				),
			);
		}

		return null;
	}
}
