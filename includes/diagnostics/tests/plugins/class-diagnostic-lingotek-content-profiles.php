<?php
/**
 * Lingotek Content Profiles Diagnostic
 *
 * Lingotek Content Profiles misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1182.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Lingotek Content Profiles Diagnostic Class
 *
 * @since 1.1182.0000
 */
class Diagnostic_LingotekContentProfiles extends Diagnostic_Base {

	protected static $slug = 'lingotek-content-profiles';
	protected static $title = 'Lingotek Content Profiles';
	protected static $description = 'Lingotek Content Profiles misconfigured';
	protected static $family = 'functionality';

	public static function check() {
		$issues = array();

		// Check 1: Default profile configured
		$default = get_option( 'lingotek_default_profile_configured', 0 );
		if ( ! $default ) {
			$issues[] = 'Default content profile not configured';
		}

		// Check 2: API connection
		$api = get_option( 'lingotek_api_connection_verified', 0 );
		if ( ! $api ) {
			$issues[] = 'API connection not verified';
		}

		// Check 3: Translation workflow
		$workflow = get_option( 'lingotek_translation_workflow_enabled', 0 );
		if ( ! $workflow ) {
			$issues[] = 'Translation workflow not configured';
		}

		// Check 4: Target languages
		$languages = get_option( 'lingotek_target_languages_count', 0 );
		if ( $languages <= 0 ) {
			$issues[] = 'Target languages not configured';
		}

		// Check 5: Auto-upload enabled
		$upload = get_option( 'lingotek_auto_upload_enabled', 0 );
		if ( ! $upload ) {
			$issues[] = 'Auto-upload not enabled';
		}

		// Check 6: Sync preferences
		$sync = get_option( 'lingotek_sync_preferences_configured', 0 );
		if ( ! $sync ) {
			$issues[] = 'Synchronization preferences not configured';
		}

		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 35;
			$threat_multiplier = 6;
			$max_threat = 65;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d profile configuration issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/lingotek-content-profiles',
			);
		}

		return null;
	}
}
