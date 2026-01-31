<?php
/**
 * Comment Author IP Tracking Diagnostic
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26031.1500
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Diagnostic_Comment_Author_IP_Tracking extends Diagnostic_Base {
	protected static $slug = 'comment-author-ip-tracking';
	protected static $title = 'Comment Author IP Tracking';
	protected static $description = 'Checks if comment author IPs being stored securely';
	protected static $family = 'privacy';

	public static function check() {
		global $wpdb;

		// Check if IPs are being stored.
		$ip_count = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->comments} 
			WHERE comment_author_IP != '' AND comment_author_IP IS NOT NULL
			LIMIT 100"
		);

		if ( $ip_count > 0 ) {
			// Check if privacy policy mentions IP logging.
			$privacy_page_id = (int) get_option( 'wp_page_for_privacy_policy', 0 );
			$mentions_ip     = false;

			if ( $privacy_page_id > 0 ) {
				$privacy_page = get_post( $privacy_page_id );
				if ( $privacy_page ) {
					$content     = strtolower( $privacy_page->post_content );
					$mentions_ip = ( strpos( $content, 'ip address' ) !== false || strpos( $content, 'ip' ) !== false );
				}
			}

			if ( ! $mentions_ip ) {
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => __( 'Site stores commenter IP addresses but privacy policy does not disclose this', 'wpshadow' ),
					'severity'     => 'high',
					'threat_level' => 55,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/comment-author-ip-tracking',
				);
			}
		}

		return null;
	}
}
