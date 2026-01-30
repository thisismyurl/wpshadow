<?php
/**
 * PeepSo Privacy Settings Diagnostic
 *
 * PeepSo privacy settings incomplete.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.519.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * PeepSo Privacy Settings Diagnostic Class
 *
 * @since 1.519.0000
 */
class Diagnostic_PeepsoPrivacySettings extends Diagnostic_Base {

	protected static $slug = 'peepso-privacy-settings';
	protected static $title = 'PeepSo Privacy Settings';
	protected static $description = 'PeepSo privacy settings incomplete';
	protected static $family = 'security';

	public static function check() {
		if ( ! class_exists( 'PeepSo' ) ) {
			return null;
		}
		
		// Check if PeepSo is active
		if ( ! class_exists( 'PeepSo' ) && ! defined( 'PEEPSO_VERSION' ) ) {
			return null;
		}

		$issues = array();
		$threat_level = 0;

		// Check default privacy level
		$default_privacy = get_option( 'peepso_site_privacy_default', '' );
		if ( $default_privacy === 'public' ) {
			$issues[] = 'default_privacy_too_permissive';
			$threat_level += 35;
		}

		// Check profile visibility
		$profile_public = get_option( 'peepso_site_profile_public', 1 );
		if ( $profile_public ) {
			$issues[] = 'profiles_publicly_visible';
			$threat_level += 30;
		}

		// Check guest access
		$guest_access = get_option( 'peepso_site_guests_access', 1 );
		if ( $guest_access ) {
			$issues[] = 'guest_access_enabled';
			$threat_level += 25;
		}

		// Check GDPR compliance
		$gdpr_enable = get_option( 'peepso_gdpr_enable', 0 );
		if ( ! $gdpr_enable ) {
			$issues[] = 'gdpr_compliance_disabled';
			$threat_level += 30;
		}

		// Check data export
		$data_export = get_option( 'peepso_gdpr_export_enable', 0 );
		if ( ! $data_export ) {
			$issues[] = 'data_export_disabled';
			$threat_level += 20;
		}

		// Check user content deletion
		$allow_delete = get_option( 'peepso_site_user_delete_content', 0 );
		if ( ! $allow_delete ) {
			$issues[] = 'user_content_deletion_disabled';
			$threat_level += 15;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %s: list of privacy issues */
				__( 'PeepSo privacy settings are incomplete: %s. This exposes user data and may violate privacy regulations.', 'wpshadow' ),
				implode( ', ', array_map( function( $issue ) {
					return ucwords( str_replace( '_', ' ', $issue ) );
				}, $issues ) )
			);

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => $description,
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/peepso-privacy-settings',
			);
		}
		
		return null;
	}
}
