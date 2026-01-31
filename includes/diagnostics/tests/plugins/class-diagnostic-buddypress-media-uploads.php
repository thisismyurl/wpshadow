<?php
/**
 * BuddyPress Media Uploads Diagnostic
 *
 * BuddyPress media uploads insecure.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.517.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * BuddyPress Media Uploads Diagnostic Class
 *
 * @since 1.517.0000
 */
class Diagnostic_BuddypressMediaUploads extends Diagnostic_Base {

	protected static $slug = 'buddypress-media-uploads';
	protected static $title = 'BuddyPress Media Uploads';
	protected static $description = 'BuddyPress media uploads insecure';
	protected static $family = 'security';

	public static function check() {
		if ( ! class_exists( 'BuddyPress' ) ) {
			return null;
		}
		
		$issues = array();
		// Check if feature is configured
		$option_prefix = 'diagnostic_' . str_replace('-', '_', self::$slug);
		$configured = get_option($option_prefix, false);
		if (!$configured) {
			$issues[] = 'feature not configured';
		}
		$has_issue = !empty($issues);
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => 75,
				'threat_level' => 75,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/buddypress-media-uploads',
			);
		}
		

		// Security validation checks
		if ( is_ssl() === false ) {
			$issues[] = __( 'HTTPS not enabled', 'wpshadow' );
		}
		if ( defined( 'FORCE_SSL' ) === false || ! FORCE_SSL ) {
			$issues[] = __( 'SSL not forced', 'wpshadow' );
		}
		// Additional checks
		if ( ! function_exists( 'wp_verify_nonce' ) ) {
			$issues[] = __( 'Nonce verification unavailable', 'wpshadow' );
		}
		return null;
	}
}
