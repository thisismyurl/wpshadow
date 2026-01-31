<?php
/**
 * Malicious Comment Code Detection Diagnostic
 *
 * Detects SQL injection or XSS attempts in comment content,
 * protecting against code injection attacks.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5028.1630
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Malicious Comment Code Detection Class
 *
 * Scans comments for SQL injection, XSS, and other code injection attempts.
 * High threat level as these indicate active attack attempts.
 *
 * @since 1.5028.1630
 */
class Diagnostic_Malicious_Comment_Code extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'malicious-comment-code';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Malicious Comment Code Detection';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects SQL injection and XSS attempts in comments';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'comments';

	/**
	 * Run the diagnostic check.
	 *
	 * Scans recent comments for malicious code patterns using get_comments().
	 * Detects SQL injection, XSS, PHP code, and shell commands.
	 *
	 * @since  1.5028.1630
	 * @return array|null Finding array if malicious code detected, null otherwise.
	 */
	public static function check() {
		$cache_key = 'wpshadow_malicious_comment_code_check';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		// Use get_comments() API (NO $wpdb).
		$recent_comments = get_comments(
			array(
				'status'  => 'all',
				'number'  => 200,
				'orderby' => 'comment_date',
				'order'   => 'DESC',
			)
		);

		if ( empty( $recent_comments ) ) {
			set_transient( $cache_key, null, 12 * HOUR_IN_SECONDS );
			return null;
		}

		$malicious_comments = array();
		$attack_patterns    = self::get_attack_patterns();

		foreach ( $recent_comments as $comment ) {
			$detected_attacks = array();
			$content          = $comment->comment_content;
			$author           = $comment->comment_author;
			$author_email     = $comment->comment_author_email;
			$author_url       = $comment->comment_author_url;

			// Check all fields for malicious patterns.
			$fields_to_check = array(
				'content' => $content,
				'author'  => $author,
				'email'   => $author_email,
				'url'     => $author_url,
			);

			foreach ( $fields_to_check as $field_name => $field_value ) {
				if ( empty( $field_value ) ) {
					continue;
				}

				foreach ( $attack_patterns as $attack_type => $pattern ) {
					if ( preg_match( $pattern, $field_value ) ) {
						$detected_attacks[] = "{$field_name}:{$attack_type}";
					}
				}
			}

			// If any malicious pattern detected, flag it.
			if ( ! empty( $detected_attacks ) ) {
				$malicious_comments[] = array(
					'comment_id' => $comment->comment_ID,
					'date'       => $comment->comment_date,
					'status'     => $comment->comment_approved,
					'attacks'    => $detected_attacks,
				);
			}
		}

		// If any malicious attempts found, flag high threat.
		if ( ! empty( $malicious_comments ) ) {
			$result = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of malicious comments */
					__( 'Detected %d comments containing malicious code (SQL injection, XSS, or code injection attempts). Immediate action required.', 'wpshadow' ),
					count( $malicious_comments )
				),
				'severity'     => 'critical',
				'threat_level' => 85,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/security-malicious-comment-code',
				'data'         => array(
					'malicious_count'    => count( $malicious_comments ),
					'malicious_comments' => array_slice( $malicious_comments, 0, 20 ),
					'total_analyzed'     => count( $recent_comments ),
				),
			);

			set_transient( $cache_key, $result, 6 * HOUR_IN_SECONDS );
			return $result;
		}

		set_transient( $cache_key, null, 12 * HOUR_IN_SECONDS );
		return null;
	}

	/**
	 * Get malicious code detection patterns.
	 *
	 * @since  1.5028.1630
	 * @return array Regex patterns for attack detection.
	 */
	private static function get_attack_patterns() {
		return array(
			'sql-injection'    => '/(\bUNION\b.*\bSELECT\b|\bDROP\b.*\bTABLE\b|\bINSERT\b.*\bINTO\b|\bDELETE\b.*\bFROM\b|\'.*OR.*\'.*=.*\'|\bEXEC\b.*\()/i',
			'xss-script'       => '/<script[^>]*>.*<\/script>/is',
			'xss-onerror'      => '/onerror\s*=\s*["\']?[^"\'>\s]+/i',
			'xss-onload'       => '/onload\s*=\s*["\']?[^"\'>\s]+/i',
			'php-code'         => '/<\?php|<\?=|\beval\s*\(|\bbase64_decode\s*\(/i',
			'shell-command'    => '/\b(system|exec|shell_exec|passthru|popen|proc_open)\s*\(/i',
			'file-inclusion'   => '/\b(include|require|include_once|require_once)\s*\(/i',
			'javascript-eval'  => '/\beval\s*\(/i',
			'data-uri'         => '/data:text\/html|data:application/i',
		);
	}
}
