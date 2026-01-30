<?php
/**
 * Digitalocean Spaces Offload Diagnostic
 *
 * Digitalocean Spaces Offload needs attention.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1015.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Digitalocean Spaces Offload Diagnostic Class
 *
 * @since 1.1015.0000
 */
class Diagnostic_DigitaloceanSpacesOffload extends Diagnostic_Base {

	protected static $slug = 'digitalocean-spaces-offload';
	protected static $title = 'Digitalocean Spaces Offload';
	protected static $description = 'Digitalocean Spaces Offload needs attention';
	protected static $family = 'functionality';

	public static function check() {
		// Check for DO Spaces plugins
		$has_do_spaces = defined( 'DO_SPACES_VERSION' ) ||
		                 get_option( 'do_spaces_key', '' ) !== '' ||
		                 get_option( 'as3cf_provider' ) === 'do';

		if ( ! $has_do_spaces ) {
			return null;
		}

		$issues = array();

		// Check 1: API credentials
		$access_key = get_option( 'do_spaces_access_key', '' );
		$secret_key = get_option( 'do_spaces_secret_key', '' );

		if ( empty( $access_key ) || empty( $secret_key ) ) {
			$issues[] = __( 'Missing API credentials', 'wpshadow' );
		}

		// Check 2: Bucket configuration
		$bucket = get_option( 'do_spaces_bucket', '' );
		if ( empty( $bucket ) ) {
			$issues[] = __( 'No bucket configured', 'wpshadow' );
		}

		// Check 3: CDN URL
		$cdn_url = get_option( 'do_spaces_cdn_url', '' );
		if ( empty( $cdn_url ) ) {
			$issues[] = __( 'No CDN URL (missing performance benefit)', 'wpshadow' );
		}

		// Check 4: Remove local files
		$remove_local = get_option( 'do_spaces_remove_local', 'no' );
		if ( 'no' === $remove_local ) {
			$issues[] = __( 'Local files kept (duplicate storage)', 'wpshadow' );
		}

		// Check 5: File type restrictions
		$file_types = get_option( 'do_spaces_file_types', array() );
		if ( empty( $file_types ) ) {
			$issues[] = __( 'All file types offloaded (security risk)', 'wpshadow' );
		}

		// Check 6: Backup before offload
		$backup_enabled = get_option( 'do_spaces_backup', 'no' );
		if ( 'no' === $backup_enabled ) {
			$issues[] = __( 'No backup before offload (data loss risk)', 'wpshadow' );
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
				__( 'DigitalOcean Spaces has %d configuration issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/digitalocean-spaces-offload',
		);
	}
}
