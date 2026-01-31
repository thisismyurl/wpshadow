<?php
/**
 * Comment Blacklist/Whitelist Health Diagnostic
 *
 * Checks if comment blacklist and whitelist are properly configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2309
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Comment Blacklist/Whitelist Health Diagnostic Class
 *
 * Checks blacklist/whitelist configuration.
 *
 * @since 1.2601.2309
 */
class Diagnostic_Comment_Blacklist_Whitelist_Health extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'comment-blacklist-whitelist-health';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Comment Blacklist/Whitelist Health';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks comment blacklist and whitelist configuration';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2309
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Get blacklist and whitelist options
		$blacklist = get_option( 'blacklist_keys', '' );
		$whitelist = get_option( 'comment_whitelist', 0 );

		// Check if blacklist is configured
		if ( empty( $blacklist ) ) {
			$issues[] = __( 'No comment blacklist configured', 'wpshadow' );
		} else {
			// Count blacklist entries
			$blacklist_entries = count( array_filter( array_map( 'trim', explode( "\n", $blacklist ) ) ) );
			if ( $blacklist_entries > 100 ) {
				$issues[] = sprintf(
					/* translators: %d: number of blacklist entries */
					__( 'Blacklist is very large (%d entries) - may impact performance', 'wpshadow' ),
					$blacklist_entries
				);
			}
		}

		// Check whitelist configuration
		if ( ! $whitelist ) {
			$issues[] = __( 'Comment whitelist is disabled - previously approved comments will not be auto-approved', 'wpshadow' );
		}

		// Check for potentially ineffective regex patterns
		if ( ! empty( $blacklist ) ) {
			$lines = explode( "\n", $blacklist );
			$invalid_patterns = 0;
			foreach ( $lines as $line ) {
				$line = trim( $line );
				if ( empty( $line ) ) {
					continue;
				}
				// Check if looks like regex without delimiters
				if ( strpos( $line, '/' ) === false && strpos( $line, '#' ) === false ) {
					$invalid_patterns++;
				}
			}

			if ( $invalid_patterns > 10 ) {
				$issues[] = sprintf(
					/* translators: %d: number of patterns */
					__( '%d blacklist entries may be invalid or inefficient', 'wpshadow' ),
					$invalid_patterns
				);
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => sprintf(
					/* translators: %d: number of issues */
					__( 'Found %d comment filter configuration issues', 'wpshadow' ),
					count( $issues )
				),
				'severity'      => 'medium',
				'threat_level'  => 40,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/comment-blacklist-whitelist-health',
			);
		}

		return null;
	}
}
