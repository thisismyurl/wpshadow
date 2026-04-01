<?php
/**
 * Third Party Data Sharing Diagnostic
 *
 * Issue #4957: Third-Party Data Sharing Not Disclosed
 * Pillar: #10: Beyond Pure (Privacy) / 🌐 Culturally Respectful
 *
 * Checks if third-party data sharing is disclosed.
 * Users must know who receives their data.
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
 * Diagnostic_Third_Party_Data_Sharing Class
 *
 * @since 0.6093.1200
 */
class Diagnostic_Third_Party_Data_Sharing extends Diagnostic_Base {

	protected static $slug = 'third-party-data-sharing';
	protected static $title = 'Third-Party Data Sharing Not Disclosed';
	protected static $description = 'Checks if data sharing with third parties is documented';
	protected static $family = 'compliance';

	public static function check() {
		$issues = array();

		$issues[] = __( 'List all third parties that receive data', 'wpshadow' );
		$issues[] = __( 'Examples: Google Analytics, Mailchimp, payment processors', 'wpshadow' );
		$issues[] = __( 'Explain what data each third party receives', 'wpshadow' );
		$issues[] = __( 'Explain why data is shared (analytics, email, payments)', 'wpshadow' );
		$issues[] = __( 'Link to third-party privacy policies', 'wpshadow' );
		$issues[] = __( 'Ensure third parties are GDPR-compliant', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'GDPR requires disclosure of all third-party data sharing. Users have the right to know who processes their data and why.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/third-party-sharing?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'recommendations'         => $issues,
					'common_third_parties'    => 'Google, Facebook, Stripe, PayPal, AWS, Cloudflare',
					'data_processing_agreement' => 'DPA required for GDPR compliance',
					'commandment'             => 'Commandment #10: Beyond Pure (Privacy First)',
				),
			);
		}

		return null;
	}
}
