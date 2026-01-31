<?php
/**
 * Envira Gallery Lightbox Security Diagnostic
 *
 * Envira Gallery lightbox vulnerable.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.490.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Envira Gallery Lightbox Security Diagnostic Class
 *
 * @since 1.490.0000
 */
class Diagnostic_EnviraGalleryLightboxSecurity extends Diagnostic_Base {

	protected static $slug = 'envira-gallery-lightbox-security';
	protected static $title = 'Envira Gallery Lightbox Security';
	protected static $description = 'Envira Gallery lightbox vulnerable';
	protected static $family = 'security';

	public static function check() {
		if ( ! class_exists( 'Envira_Gallery' ) ) {
			return null;
		}
		
		// Check if Envira Gallery is active
		if ( ! class_exists( 'Envira_Gallery' ) && ! function_exists( 'envira_gallery_init' ) ) {
			return null;
		}

		$issues = array();
		$threat_level = 0;

		global $wpdb;

		// Check galleries
		$galleries = $wpdb->get_results(
			"SELECT ID, post_content FROM {$wpdb->posts} WHERE post_type = 'envira'"
		);

		if ( empty( $galleries ) ) {
			return null;
		}

		// Check caption sanitization
		$caption_settings = get_option( 'envira_gallery_settings', array() );
		$allow_html = isset( $caption_settings['allow_html_captions'] ) ? $caption_settings['allow_html_captions'] : 1;
		if ( $allow_html ) {
			$issues[] = 'html_captions_enabled';
			$threat_level += 30;
		}

		// Check lightbox configuration
		$lightbox_theme = get_option( 'envira_gallery_lightbox_theme', 'base' );
		$escape_captions = get_option( 'envira_escape_lightbox_captions', 0 );
		if ( ! $escape_captions ) {
			$issues[] = 'caption_escaping_disabled';
			$threat_level += 30;
		}

		// Check iframe embed security
		$allow_iframe = get_option( 'envira_allow_iframe_lightbox', 0 );
		if ( $allow_iframe ) {
			$iframe_sandbox = get_option( 'envira_iframe_sandbox', 0 );
			if ( ! $iframe_sandbox ) {
				$issues[] = 'iframe_sandbox_disabled';
				$threat_level += 25;
			}
		}

		// Check external URLs
		$external_urls = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->postmeta} 
			 WHERE meta_key = '_eg_image_url' 
			 AND meta_value LIKE 'http%'"
		);
		if ( $external_urls > 0 ) {
			$validate_urls = get_option( 'envira_validate_external_urls', 0 );
			if ( ! $validate_urls ) {
				$issues[] = 'external_url_validation_disabled';
				$threat_level += 20;
			}
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %s: list of security issues */
				__( 'Envira Gallery lightbox has security vulnerabilities: %s. This enables XSS attacks and content injection.', 'wpshadow' ),
				implode( ', ', array_map( function( $issue ) {
					return ucwords( str_replace( '_', ' ', $issue ) );
				}, $issues ) )
			);

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => $description,
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/envira-gallery-lightbox-security',
			);
		}
		
		return null;
	}
}
