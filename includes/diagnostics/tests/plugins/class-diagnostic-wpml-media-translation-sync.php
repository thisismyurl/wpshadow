<?php
/**
 * Wpml Media Translation Sync Diagnostic
 *
 * Wpml Media Translation Sync misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1140.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wpml Media Translation Sync Diagnostic Class
 *
 * @since 1.1140.0000
 */
class Diagnostic_WpmlMediaTranslationSync extends Diagnostic_Base {

	protected static $slug = 'wpml-media-translation-sync';
	protected static $title = 'Wpml Media Translation Sync';
	protected static $description = 'Wpml Media Translation Sync misconfigured';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'ICL_SITEPRESS_VERSION' ) ) {
			return null;
		}

		$issues = array();
		$settings = get_option( 'wpml_media', array() );

		// Check 1: Verify media translation is enabled
		$media_translation = isset( $settings['translation'] ) ? (bool) $settings['translation'] : false;
		if ( ! $media_translation ) {
			$issues[] = 'Media translation not enabled';
		}

		// Check 2: Check for duplicate media sync
		$sync_media = isset( $settings['sync_media'] ) ? (bool) $settings['sync_media'] : false;
		if ( ! $sync_media ) {
			$issues[] = 'Media synchronization not enabled';
		}

		// Check 3: Verify featured image sync
		$sync_featured = isset( $settings['sync_featured'] ) ? (bool) $settings['sync_featured'] : false;
		if ( ! $sync_featured ) {
			$issues[] = 'Featured image synchronization not enabled';
		}

		// Check 4: Check for custom fields sync
		$sync_custom_fields = isset( $settings['sync_custom_fields'] ) ? (bool) $settings['sync_custom_fields'] : false;
		if ( ! $sync_custom_fields ) {
			$issues[] = 'Custom fields media sync not enabled';
		}

		// Check 5: Verify media translation batch size
		$batch_size = isset( $settings['batch_size'] ) ? (int) $settings['batch_size'] : 0;
		if ( $batch_size > 200 ) {
			$issues[] = 'Media sync batch size too large (over 200)';
		}

		// Check 6: Check for sync scheduling
		$sync_cron = wp_next_scheduled( 'wpml_media_sync' );
		if ( ! $sync_cron ) {
			$issues[] = 'Media sync schedule not configured';
		}

		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 40;
			$threat_multiplier = 6;
			$max_threat = 70;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d WPML media translation sync issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/wpml-media-translation-sync',
			);
		}

		return null;
	}
}
