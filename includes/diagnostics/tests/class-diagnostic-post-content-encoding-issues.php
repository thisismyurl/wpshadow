<?php
/**
 * Post Content Encoding Issues Diagnostic
 *
 * Detects character encoding problems in post content. Tests for UTF-8 issues,
 * special characters, and encoding corruption.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Post Content Encoding Issues Diagnostic Class
 *
 * Checks for character encoding problems in posts.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Post_Content_Encoding_Issues extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'post-content-encoding-issues';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Post Content Encoding Issues';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects character encoding problems and UTF-8 issues in post content';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'posts';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$issues = array();

		// Check database charset/collation.
		$db_charset = $wpdb->get_var( "SELECT @@character_set_database" );
		$db_collation = $wpdb->get_var( "SELECT @@collation_database" );

		if ( stripos( $db_charset, 'utf8' ) === false ) {
			$issues[] = sprintf(
				/* translators: %s: database charset */
				__( 'Database charset "%s" is not UTF-8 (may cause encoding issues)', 'wpshadow' ),
				esc_html( $db_charset )
			);
		}

		// Check posts table charset.
		$posts_table_status = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT TABLE_COLLATION
				FROM information_schema.TABLES
				WHERE TABLE_SCHEMA = %s
				AND TABLE_NAME = %s",
				DB_NAME,
				$wpdb->posts
			)
		);

		if ( $posts_table_status && stripos( $posts_table_status->TABLE_COLLATION, 'utf8' ) === false ) {
			$issues[] = sprintf(
				/* translators: %s: table collation */
				__( 'Posts table collation "%s" is not UTF-8 compatible', 'wpshadow' ),
				esc_html( $posts_table_status->TABLE_COLLATION )
			);
		}

		// Check for posts with invalid UTF-8 sequences.
		$invalid_utf8_posts = $wpdb->get_var(
			"SELECT COUNT(*)
			FROM {$wpdb->posts}
			WHERE post_status NOT IN ('trash', 'auto-draft')
			AND (
				post_content LIKE '%\xC3\x83\xC2%'
				OR post_content LIKE '%Ã‚%'
				OR post_content LIKE '%Ã¢â‚¬â„¢%'
				OR post_content LIKE '%â€%'
				OR post_title LIKE '%\xC3\x83\xC2%'
				OR post_title LIKE '%Ã‚%'
			)"
		);

		if ( $invalid_utf8_posts > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts with encoding issues */
				__( '%d posts contain double-encoded UTF-8 sequences (display issues)', 'wpshadow' ),
				$invalid_utf8_posts
			);
		}

		// Check for Windows-1252 characters (common encoding issue).
		$windows_chars = $wpdb->get_var(
			"SELECT COUNT(*)
			FROM {$wpdb->posts}
			WHERE post_status NOT IN ('trash', 'auto-draft')
			AND (
				post_content REGEXP '[\\x80-\\x9F]'
				OR post_content LIKE '%â€™%'
				OR post_content LIKE '%â€œ%'
				OR post_content LIKE '%â€�%'
				OR post_content LIKE '%â€"%'
			)"
		);

		if ( $windows_chars > 5 ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts with Windows-1252 chars */
				__( '%d posts contain Windows-1252 characters (smart quotes/dashes)', 'wpshadow' ),
				$windows_chars
			);
		}

		// Check for posts with null bytes.
		$null_byte_posts = $wpdb->get_var(
			"SELECT COUNT(*)
			FROM {$wpdb->posts}
			WHERE post_status NOT IN ('trash', 'auto-draft')
			AND (post_content LIKE '%\0%' OR post_title LIKE '%\0%')"
		);

		if ( $null_byte_posts > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts with null bytes */
				__( '%d posts contain null bytes (database corruption)', 'wpshadow' ),
				$null_byte_posts
			);
		}

		// Check for posts with non-printable characters.
		$non_printable_posts = $wpdb->get_var(
			"SELECT COUNT(*)
			FROM {$wpdb->posts}
			WHERE post_status NOT IN ('trash', 'auto-draft')
			AND (
				post_content REGEXP '[\\x00-\\x08\\x0B\\x0C\\x0E-\\x1F]'
				OR post_title REGEXP '[\\x00-\\x08\\x0B\\x0C\\x0E-\\x1F]'
			)"
		);

		if ( $non_printable_posts > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts with non-printable chars */
				__( '%d posts contain non-printable characters (copy/paste issues)', 'wpshadow' ),
				$non_printable_posts
			);
		}

		// Check for emoji storage issues (4-byte UTF-8).
		$emoji_posts = $wpdb->get_var(
			"SELECT COUNT(*)
			FROM {$wpdb->posts}
			WHERE post_status NOT IN ('trash', 'auto-draft')
			AND (post_content LIKE '%😀%' OR post_content REGEXP '[\\x{1F300}-\\x{1F9FF}]')"
		);

		if ( $emoji_posts > 10 ) {
			// Check if database supports utf8mb4.
			$table_charset = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT CCSA.character_set_name
					FROM information_schema.TABLES T,
					     information_schema.COLLATION_CHARACTER_SET_APPLICABILITY CCSA
					WHERE CCSA.collation_name = T.table_collation
					AND T.table_schema = %s
					AND T.table_name = %s",
					DB_NAME,
					$wpdb->posts
				)
			);

			if ( 'utf8mb4' !== $table_charset ) {
				$issues[] = sprintf(
					/* translators: %d: number of posts with emoji */
					__( '%d posts contain emoji but table charset is not utf8mb4 (data loss risk)', 'wpshadow' ),
					$emoji_posts
				);
			}
		}

		// Check for mixed encoding issues in titles.
		$mixed_encoding_titles = $wpdb->get_results(
			"SELECT ID, post_title
			FROM {$wpdb->posts}
			WHERE post_status NOT IN ('trash', 'auto-draft')
			AND post_title REGEXP '[\x80-\xFF]'
			AND LENGTH(post_title) != CHAR_LENGTH(post_title)
			LIMIT 20",
			ARRAY_A
		);

		if ( ! empty( $mixed_encoding_titles ) ) {
			$suspicious_count = 0;
			foreach ( $mixed_encoding_titles as $post ) {
				// Check if title has encoding issues.
				$title = $post['post_title'];
				if ( ! mb_check_encoding( $title, 'UTF-8' ) ) {
					++$suspicious_count;
				}
			}

			if ( $suspicious_count > 3 ) {
				$issues[] = sprintf(
					/* translators: %d: number of posts with encoding issues */
					__( '%d post titles have invalid UTF-8 encoding', 'wpshadow' ),
					$suspicious_count
				);
			}
		}

		// Check for HTML entities that should be decoded.
		$encoded_entities = $wpdb->get_var(
			"SELECT COUNT(*)
			FROM {$wpdb->posts}
			WHERE post_status NOT IN ('trash', 'auto-draft')
			AND (
				post_content LIKE '%&amp;amp;%'
				OR post_content LIKE '%&amp;#%'
				OR post_content LIKE '%&amp;lt;%'
				OR post_content LIKE '%&amp;gt;%'
			)"
		);

		if ( $encoded_entities > 5 ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts with double-encoded entities */
				__( '%d posts have double-encoded HTML entities (display corruption)', 'wpshadow' ),
				$encoded_entities
			);
		}

		// Check for broken multibyte characters at string boundaries.
		$truncated_chars = $wpdb->get_results(
			"SELECT ID, post_excerpt, post_title
			FROM {$wpdb->posts}
			WHERE post_status NOT IN ('trash', 'auto-draft')
			AND (
				post_excerpt REGEXP '[\x80-\xFF]$'
				OR post_title REGEXP '[\x80-\xFF]$'
			)
			LIMIT 50",
			ARRAY_A
		);

		$broken_boundaries = 0;
		foreach ( $truncated_chars as $post ) {
			if ( ! empty( $post['post_excerpt'] ) ) {
				$last_char = mb_substr( $post['post_excerpt'], -1, 1, 'UTF-8' );
				if ( empty( $last_char ) || '?' === $last_char ) {
					++$broken_boundaries;
				}
			}
		}

		if ( $broken_boundaries > 5 ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts with truncated characters */
				__( '%d posts have truncated multibyte characters (excerpt generation issues)', 'wpshadow' ),
				$broken_boundaries
			);
		}

		// Check for posts imported from other systems with encoding issues.
		$import_indicators = $wpdb->get_var(
			"SELECT COUNT(*)
			FROM {$wpdb->posts} p
			WHERE p.post_status NOT IN ('trash', 'auto-draft')
			AND (
				p.post_content LIKE '%ISO-8859-1%'
				OR p.post_content LIKE '%Latin-1%'
				OR p.post_content LIKE '%charset=windows-1252%'
			)"
		);

		if ( $import_indicators > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts with import encoding issues */
				__( '%d posts reference non-UTF-8 charsets (likely import issues)', 'wpshadow' ),
				$import_indicators
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => implode( '. ', $issues ),
				'severity'    => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/post-content-encoding-issues',
			);
		}

		return null;
	}
}
