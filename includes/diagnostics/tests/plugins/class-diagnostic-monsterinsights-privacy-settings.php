<?php
/**
 * MonsterInsights Privacy Settings Diagnostic
 *
 * MonsterInsights privacy not configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.428.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * MonsterInsights Privacy Settings Diagnostic Class
 *
 * @since 1.428.0000
 */
class Diagnostic_MonsterinsightsPrivacySettings extends Diagnostic_Base {

	protected static $slug = 'monsterinsights-privacy-settings';
	protected static $title = 'MonsterInsights Privacy Settings';
	protected static $description = 'MonsterInsights privacy not configured';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'MONSTERINSIGHTS_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: IP anonymization enabled
		$anonymize_ip = get_option( 'monsterinsights_anonymize_ip', false );
		if ( ! $anonymize_ip ) {
			$issues[] = 'IP anonymization disabled';
		}
		
		// Check 2: Respect Do Not Track
		$dnt = get_option( 'monsterinsights_respect_dnt', false );
		if ( ! $dnt ) {
			$issues[] = 'Do Not Track not respected';
		}
		
		// Check 3: Cookie consent integration
		$cookie_consent = get_option( 'monsterinsights_cookie_consent', false );
		if ( ! $cookie_consent ) {
			$issues[] = 'Cookie consent not integrated';
		}
		
		// Check 4: Data retention configured
		$data_retention = get_option( 'monsterinsights_data_retention', 0 );
		if ( $data_retention <= 0 ) {
			$issues[] = 'Data retention period not configured';
		}
		
		// Check 5: User opt-out enabled
		$user_optout = get_option( 'monsterinsights_user_optout', false );
		if ( ! $user_optout ) {
			$issues[] = 'User opt-out not enabled';
		}
		
		// Check 6: GDPR compliance mode
		$gdpr_mode = get_option( 'monsterinsights_gdpr_compliance', false );
		if ( ! $gdpr_mode ) {
			$issues[] = 'GDPR compliance mode disabled';
		}
		
		if ( ! empty( $issues ) ) {
			$threat_level = min( 80, 50 + ( count( $issues ) * 5 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'MonsterInsights privacy settings issues: ' . implode( ', ', $issues ),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/monsterinsights-privacy-settings',
			);
		}
		// Additional checks
		if ( ! function_exists( 'wp_verify_nonce' ) ) {
			$issues[] = __( 'Nonce verification unavailable', 'wpshadow' );
		}
		return null;
	}
}
