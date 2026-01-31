<?php
/**
 * Comment URL Validation Diagnostic
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26031.1400
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Diagnostic_Comment_URL_Validation extends Diagnostic_Base {
	protected static $slug = 'comment-url-validation';
	protected static $title = 'Comment URL Validation';
	protected static $description = 'Checks if comment URLs are validated for malware';
	protected static $family = 'security';

	public static function check() {
		// Check if URLs are being validated/sanitized.
		$has_url_filter = has_filter( 'pre_comment_author_url', 'esc_url_raw' );

		if ( ! $has_url_filter ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Comment URLs not being sanitized - may allow malicious links', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/comment-url-validation',
			);
		}

		// Check for suspicious URLs in recent comments.
		global $wpdb;
		$suspicious_urls = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->comments}
			WHERE comment_author_url LIKE '%bit.ly%'
			OR comment_author_url LIKE '%tinyurl%'
			OR comment_author_url LIKE '%.tk%'
			OR comment_author_url LIKE '%.ml%'
			OR comment_author_url REGEXP '[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}'
			LIMIT 20"
		);

		if ( $suspicious_urls > 10 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					__( 'Found %d comments with suspicious URLs (URL shorteners, free domains, or IP addresses)', 'wpshadow' ),
					$suspicious_urls
				),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/comment-url-validation',
			);
		}

		return null;
	}
}
