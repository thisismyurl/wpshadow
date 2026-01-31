<?php
/**
 * Multisite Asset Sharing Diagnostic
 *
 * Multisite Asset Sharing misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.965.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Multisite Asset Sharing Diagnostic Class
 *
 * @since 1.965.0000
 */
class Diagnostic_MultisiteAssetSharing extends Diagnostic_Base {

	protected static $slug = 'multisite-asset-sharing';
	protected static $title = 'Multisite Asset Sharing';
	protected static $description = 'Multisite Asset Sharing misconfigured';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! is_multisite() ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Site count
		$site_count = get_blog_count();
		if ( $site_count < 2 ) {
			return null;
		}
		
		// Check 2: Media library sharing
		$shared_media = get_site_option( 'ms_files_rewriting', 0 );
		if ( ! $shared_media && $site_count > 5 ) {
			$issues[] = __( 'Media libraries not shared (duplicate storage)', 'wpshadow' );
		}
		
		// Check 3: Upload directory structure
		$upload_dir = wp_upload_dir();
		$base_upload = $upload_dir['basedir'];
		
		if ( strpos( $base_upload, '/sites/' ) === false ) {
			$issues[] = __( 'Upload directory not site-specific (file conflicts)', 'wpshadow' );
		}
		
		// Check 4: Plugin sharing
		$shared_plugins = get_site_option( 'active_sitewide_plugins', array() );
		if ( count( $shared_plugins ) === 0 && $site_count > 3 ) {
			$issues[] = __( 'No network-activated plugins (management overhead)', 'wpshadow' );
		}
		
		// Check 5: Theme sharing
		$allowed_themes = get_site_option( 'allowedthemes' );
		if ( empty( $allowed_themes ) ) {
			$issues[] = __( 'All themes enabled network-wide (security/consistency risk)', 'wpshadow' );
		}
		
		// Check 6: Asset CDN
		$cdn_url = get_site_option( 'ms_cdn_url', '' );
		if ( empty( $cdn_url ) && $site_count > 10 ) {
			$issues[] = __( 'No CDN configured for shared assets (performance)', 'wpshadow' );
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
				/* translators: %s: list of asset sharing issues */
				__( 'Multisite asset sharing has %d configuration issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/multisite-asset-sharing',
		);
	}
}
