<?php
/**
 * HTML Detect HTML Comments With Sensitive Information Diagnostic
 *
 * Detects HTML comments containing sensitive information.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\HTML
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * HTML Detect HTML Comments With Sensitive Information Diagnostic Class
 *
 * Identifies HTML comments that contain sensitive information like
 * passwords, API keys, internal notes, or system paths.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Html_Detect_Html_Comments_With_Sensitive_Information extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'html-detect-html-comments-with-sensitive-information';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'HTML Comments With Sensitive Information';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects HTML comments containing passwords, API keys, or other sensitive data';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if ( is_admin() ) {
			return null;
		}

		$sensitive_comments = array();

		// Patterns to detect sensitive information in HTML comments.
		$sensitive_patterns = array(
			'/password\s*[:=]|pwd\s*[:=]/i'                => 'Password',
			'/api[_-]?key|api[_-]?secret|apikey|apisecret/i' => 'API Key',
			'/secret|token|bearer|oauth|authorization/i'  => 'Token/Auth',
			'/database|db[_-]?name|db[_-]?user|db[_-]?pass/i' => 'Database Credentials',
			'/private[_-]?key|rsa|certificate/i'           => 'Private Key/Certificate',
			'/internal|confidential|todo|fixme|hack|kludge/i' => 'Internal Note',
			'/\/home\/|\/var\/|\/etc\/|\/opt\//i'         => 'File Path',
			'/aws[_-]?key|aws[_-]?secret|s3[_-]?key/i'    => 'AWS Credentials',
			'/google[_-]?key|google[_-]?oauth/i'          => 'Google Credentials',
			'/github[_-]?token|github[_-]?key/i'          => 'GitHub Credentials',
		);

		// Check scripts for HTML comments.
		global $wp_scripts;

		if ( ! empty( $wp_scripts ) && isset( $wp_scripts->registered ) ) {
			foreach ( $wp_scripts->registered as $handle => $script_obj ) {
				if ( isset( $script_obj->extra['data'] ) ) {
					$data = (string) $script_obj->extra['data'];

					// Find HTML comments.
					if ( preg_match_all( '/<!--\s*(.+?)\s*-->/s', $data, $comments ) ) {
						foreach ( $comments[1] as $comment ) {
							$comment = trim( $comment );

							// Check against sensitive patterns.
							foreach ( $sensitive_patterns as $pattern => $type ) {
								if ( preg_match( $pattern, $comment ) ) {
									$sensitive_comments[] = array(
										'handle' => $handle,
										'type'   => $type,
										'comment' => substr( $comment, 0, 100 ),
										'length' => strlen( $comment ),
									);

									break;
								}
							}
						}
					}
				}
			}
		}

		if ( empty( $sensitive_comments ) ) {
			return null;
		}

		$items_list = '';
		$max_items  = 5;

		foreach ( array_slice( $sensitive_comments, 0, $max_items ) as $item ) {
			$items_list .= sprintf(
				"\n- %s (%s): \"%s...\"",
				esc_html( $item['handle'] ),
				esc_html( $item['type'] ),
				esc_html( substr( $item['comment'], 0, 40 ) )
			);
		}

		if ( count( $sensitive_comments ) > $max_items ) {
			$items_list .= sprintf(
				/* translators: %d: count */
				__( "\n...and %d more sensitive comments", 'wpshadow' ),
				count( $sensitive_comments ) - $max_items
			);
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: 1: count, 2: list */
				__( 'Found %1$d HTML comment(s) with potentially sensitive information. Passwords, API keys, database credentials, and internal notes should never appear in HTML comments as they are visible to anyone viewing page source.%2$s', 'wpshadow' ),
				count( $sensitive_comments ),
				$items_list
			),
			'severity'     => 'high',
			'threat_level' => 40,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/html-detect-html-comments-with-sensitive-information',
			'meta'         => array(
				'comments' => $sensitive_comments,
			),
		);
	}
}
