<?php
/**
 * Malicious Comment Code Detection Diagnostic
 *
 * Checks for potentially malicious code patterns in comments.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2308
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Malicious Comment Code Detection Diagnostic Class
 *
 * Detects potentially malicious code patterns in comments.
 *
 * @since 1.2601.2308
 */
class Diagnostic_Malicious_Comment_Code_Detection extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'malicious-comment-code-detection';

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
	protected static $description = 'Scans comments for potentially malicious code patterns';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2308
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		// Dangerous patterns to look for in comments
		$malicious_patterns = array(
			'<script',
			'javascript:',
			'onerror=',
			'onload=',
			'onclick=',
			'eval(',
			'base64_decode',
			'system(',
			'shell_exec',
			'passthru(',
			'exec(',
			'proc_open',
			'popen(',
		);

		// Check for comments with potential malicious code
		$query = "SELECT COUNT(*) FROM {$wpdb->comments} WHERE comment_content LIKE %s OR comment_author_url LIKE %s";

		$dangerous_count = 0;
		foreach ( $malicious_patterns as $pattern ) {
			$like_pattern = '%' . $wpdb->esc_like( $pattern ) . '%';
			$result = $wpdb->get_var( $wpdb->prepare( $query, $like_pattern, $like_pattern ) );
			$dangerous_count += (int) $result;
		}

		if ( $dangerous_count > 0 ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => sprintf(
					/* translators: %d: number of suspicious comments */
					__( 'Found %d comments with potentially malicious code patterns', 'wpshadow' ),
					$dangerous_count
				),
				'severity'      => 'critical',
				'threat_level'  => 90,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/malicious-comment-code-detection',
			);
		}

		// Check if Akismet or spam protection is active
		$spam_protection_plugins = array(
			'akismet/akismet.php',
			'antispam-bee/antispam-bee.php',
		);

		$has_spam_protection = false;
		foreach ( $spam_protection_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_spam_protection = true;
				break;
			}
		}

		if ( ! $has_spam_protection ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'No comment spam protection plugin detected. Consider enabling Akismet or similar service.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 60,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/malicious-comment-code-detection',
			);
		}

		return null;
	}
}
