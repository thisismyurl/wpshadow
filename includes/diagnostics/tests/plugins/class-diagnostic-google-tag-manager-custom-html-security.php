<?php
/**
 * Google Tag Manager Custom Html Security Diagnostic
 *
 * Google Tag Manager Custom Html Security misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1347.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Google Tag Manager Custom Html Security Diagnostic Class
 *
 * @since 1.1347.0000
 */
class Diagnostic_GoogleTagManagerCustomHtmlSecurity extends Diagnostic_Base {

	protected static $slug = 'google-tag-manager-custom-html-security';
	protected static $title = 'Google Tag Manager Custom Html Security';
	protected static $description = 'Google Tag Manager Custom Html Security misconfigured';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'GTM4WP_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: GTM container ID
		$container_id = get_option( 'gtm4wp-options' );
		if ( empty( $container_id ) || ! isset( $container_id['gtm-code'] ) ) {
			return null;
		}
		
		// Check 2: Custom HTML allowance
		$allow_custom_html = isset( $container_id['gtm-custom-html-allowed'] ) && $container_id['gtm-custom-html-allowed'];
		if ( $allow_custom_html ) {
			$issues[] = __( 'Custom HTML tags allowed (XSS risk)', 'wpshadow' );
		}
		
		// Check 3: User permissions
		$gtm_capability = get_option( 'gtm4wp_manage_tags_capability', 'manage_options' );
		if ( 'edit_posts' === $gtm_capability || 'edit_pages' === $gtm_capability ) {
			$issues[] = __( 'Low user capability for GTM management (security risk)', 'wpshadow' );
		}
		
		// Check 4: Content Security Policy
		$csp_enabled = get_option( 'gtm4wp_csp_enabled', false );
		if ( ! $csp_enabled && $allow_custom_html ) {
			$issues[] = __( 'No Content Security Policy for GTM (injection risk)', 'wpshadow' );
		}
		
		// Check 5: Tag validation
		$validate_tags = get_option( 'gtm4wp_validate_tags', false );
		if ( ! $validate_tags ) {
			$issues[] = __( 'Tag validation disabled (malicious code risk)', 'wpshadow' );
		}
		
		// Check 6: Environment (staging/production)
		$environment = get_option( 'gtm4wp_environment', 'production' );
		if ( 'production' !== $environment && defined( 'WP_ENV' ) && 'production' === WP_ENV ) {
			$issues[] = __( 'GTM environment mismatch (wrong tracking)', 'wpshadow' );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 50;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 62;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 56;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of GTM security issues */
				__( 'Google Tag Manager has %d security concerns: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/google-tag-manager-custom-html-security',
		);
	}
}
