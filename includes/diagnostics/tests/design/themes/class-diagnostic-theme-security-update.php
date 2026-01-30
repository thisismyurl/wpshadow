<?php
/**
 * Theme Security Update Available Diagnostic
 *
 * Detects themes with available security patches.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5029.1700
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme Security Update Class
 *
 * Monitors theme updates for security-related patches.
 * Theme vulnerabilities can expose entire site.
 *
 * @since 1.5029.1700
 */
class Diagnostic_Theme_Security_Update extends Diagnostic_Base {

	protected static $slug        = 'theme-security-update';
	protected static $title       = 'Theme Security Update Available';
	protected static $description = 'Detects themes with security patches';
	protected static $family      = 'themes';

	public static function check() {
		$cache_key = 'wpshadow_theme_security_updates';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		// Trigger theme update check.
		wp_update_themes();

		$update_themes = get_site_transient( 'update_themes' );
		
		if ( empty( $update_themes ) || empty( $update_themes->response ) ) {
			set_transient( $cache_key, null, 12 * HOUR_IN_SECONDS );
			return null;
		}

		$security_updates = array();

		foreach ( $update_themes->response as $theme_slug => $theme_data ) {
			// Check if update mentions security.
			$api_url = "https://api.wordpress.org/themes/info/1.1/?action=theme_information&request[slug]={$theme_slug}";
			$response = wp_remote_get( $api_url, array( 'timeout' => 5 ) );

			if ( ! is_wp_error( $response ) ) {
				$body = wp_remote_retrieve_body( $response );
				$data = json_decode( $body, true );

				if ( ! empty( $data ) ) {
					$description = $data['sections']['description'] ?? '';
					$changelog = $data['sections']['changelog'] ?? '';
					
					// Check for security keywords.
					if ( preg_match( '/(security|vulnerability|xss|exploit|patch|fix)/i', $changelog . $description ) ) {
						$theme_obj = wp_get_theme( $theme_slug );
						
						$security_updates[] = array(
							'name' => $theme_obj->get( 'Name' ),
							'slug' => $theme_slug,
							'current_version' => $theme_obj->get( 'Version' ),
							'new_version' => $theme_data['new_version'] ?? 'Unknown',
							'security_note' => 'Security fix mentioned',
						);
					}
				}
			}
		}

		if ( ! empty( $security_updates ) ) {
			$result = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of themes */
					__( '%d themes have security updates available. Update immediately!', 'wpshadow' ),
					count( $security_updates )
				),
				'severity'     => 'critical',
				'threat_level' => 80,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/security-theme-updates',
				'data'         => array(
					'security_updates' => $security_updates,
					'total_updates' => count( $security_updates ),
				),
			);

			set_transient( $cache_key, $result, 6 * HOUR_IN_SECONDS );
			return $result;
		}

		set_transient( $cache_key, null, 12 * HOUR_IN_SECONDS );
		return null;
	}
}
