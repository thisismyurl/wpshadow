<?php
/**
 * AIOSEO Security Diagnostic
 *
 * Checks security and access controls.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5029.1805
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AIOSEO Security Class
 *
 * Validates security settings and access controls.
 *
 * @since 1.5029.1805
 */
class Diagnostic_AIOSEO_Security extends Diagnostic_Base {

	protected static $slug        = 'aioseo-security';
	protected static $title       = 'AIOSEO Security and Access';
	protected static $description = 'Validates security settings';
	protected static $family      = 'plugins';

	public static function check() {
		if ( ! function_exists( 'aioseo' ) ) {
			return null;
		}

		$cache_key = 'wpshadow_aioseo_security';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		$issues = array();
		$options = get_option( 'aioseo_options', array() );

		// Check if non-admins can edit SEO.
		$editor_access = isset( $options['advanced']['truSeo']['editorRole'] ) 
			? $options['advanced']['truSeo']['editorRole'] 
			: 'editor';

		if ( 'editor' === $editor_access || 'author' === $editor_access ) {
			$issues[] = 'Non-administrators can edit SEO settings';
		}

		// Check if API is accessible.
		$rest_api_disabled = isset( $options['advanced']['restApiDisabled'] ) 
			? $options['advanced']['restApiDisabled'] 
			: false;

		if ( ! $rest_api_disabled ) {
			// Check if public can access REST API.
			$rest_url = rest_url( 'aioseo/v1/posts' );
			$response = wp_remote_get( $rest_url, array( 'timeout' => 5 ) );
			
			if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
				$issues[] = 'AIOSEO REST API is publicly accessible';
			}
		}

		// Check if uninstall removes data.
		$uninstall_delete_data = isset( $options['advanced']['uninstall'] ) 
			? $options['advanced']['uninstall'] 
			: false;

		if ( ! $uninstall_delete_data ) {
			$issues[] = 'Uninstall will not remove AIOSEO data';
		}

		// Check robots.txt rules.
		$robots_txt_enabled = isset( $options['tools']['robotsTxt']['enable'] ) 
			? $options['tools']['robotsTxt']['enable'] 
			: false;

		if ( $robots_txt_enabled ) {
			$robots_rules = isset( $options['tools']['robotsTxt']['rules'] ) 
				? $options['tools']['robotsTxt']['rules'] 
				: '';

			if ( strpos( $robots_rules, 'Disallow: /' ) !== false ) {
				$issues[] = 'robots.txt may be blocking all search engines';
			}
		}

		if ( ! empty( $issues ) ) {
			$result = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: count */
					__( '%d AIOSEO security concerns found.', 'wpshadow' ),
					count( $issues )
				),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/plugins-aioseo-security',
				'data'         => array(
					'security_issues' => $issues,
					'total_issues' => count( $issues ),
				),
			);

			set_transient( $cache_key, $result, 24 * HOUR_IN_SECONDS );
			return $result;
		}

		set_transient( $cache_key, null, 24 * HOUR_IN_SECONDS );
		return null;
	}
}
