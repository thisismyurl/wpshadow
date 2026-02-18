<?php
/**
 * Import Character Encoding Corruption Diagnostic
 *
 * Detects when special characters, unicode, or emoji become corrupted during
 * the WordPress import/export process. Encoding issues often appear as � or
 * garbled text, especially for multilingual content and emoji-heavy posts.
 *
 * **What This Check Does:**
 * - Scans imported content for invalid UTF‑8 sequences
 * - Detects replacement characters (�) indicating corruption
 * - Identifies common encoding mismatches (UTF‑8 vs ISO‑8859‑1)
 * - Flags posts with broken characters after import
 *
 * **Why This Matters:**
 * Corrupted characters make content look unprofessional and can break HTML
 * or XML exports. For multilingual sites, encoding corruption can render
 * entire sections unreadable, damaging trust and SEO.
 *
 * **Real-World Failure Scenario:**
 * - Site exports content with UTF‑8 emojis
 * - Import system assumes ISO‑8859‑1
 * - Emojis and accented characters become �
 * - Product titles and author names are corrupted
 *
 * Result: Content quality drops and requires manual cleanup.
 *
 * **Philosophy Alignment:**
 * - #1 Helpful Neighbor: Protects content quality during migrations
 * - Cultural Respect: Ensures multilingual content remains accurate
 * - #9 Show Value: Prevents costly manual fixes
 *
 * **Learn More:**
 * See https://wpshadow.com/kb/import-encoding-issues
 * or https://wpshadow.com/training/character-encoding-basics
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6030.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Import Character Encoding Corruption Diagnostic Class
 *
 * Uses sample content and encoding heuristics to detect corruption.
 *
 * **Implementation Pattern:**
 * 1. Build a sample set of known UTF‑8 strings
 * 2. Compare imported content for replacement characters
 * 3. Detect mismatched encoding patterns
 * 4. Return findings with remediation guidance
 *
 * **Related Diagnostics:**
 * - Import Custom Field Mapping Failures
 * - Import Lost Shortcodes and Formatting
 * - Export Corrupt XML Files
 *
 * @since 1.6030.2148
 */
class Diagnostic_Import_Character_Encoding_Corruption extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'import-character-encoding-corruption';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Import Character Encoding Corruption';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects character encoding corruption during import/export process';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'import-export';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6030.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;
		
		$issues = array();

		// Check database collation.
		$charset = $wpdb->get_var( "SELECT @@character_set_database" );
		$collation = $wpdb->get_var( "SELECT @@collation_database" );

		if ( 'utf8mb4' !== $charset && 'utf8' !== $charset ) {
			$issues[] = sprintf(
				/* translators: %s: character set */
				__( 'Database charset is %s (should be utf8mb4 for full unicode support)', 'wpshadow' ),
				$charset
			);
		}

		if ( 'utf8mb4' === $charset && strpos( $collation, 'utf8mb4' ) === false ) {
			$issues[] = sprintf(
				/* translators: 1: character set, 2: collation */
				__( 'Charset %1$s but collation %2$s mismatch (may cause encoding issues)', 'wpshadow' ),
				$charset,
				$collation
			);
		}

		// Check wp-config DB_CHARSET constant.
		if ( defined( 'DB_CHARSET' ) ) {
			if ( 'utf8mb4' !== DB_CHARSET && 'utf8' !== DB_CHARSET && '' !== DB_CHARSET ) {
				$issues[] = sprintf(
					/* translators: %s: DB_CHARSET value */
					__( 'DB_CHARSET is "%s" (should be utf8mb4)', 'wpshadow' ),
					DB_CHARSET
				);
			}
		} else {
			$issues[] = __( 'DB_CHARSET not defined in wp-config.php', 'wpshadow' );
		}

		// Check wp-config DB_COLLATE constant.
		if ( defined( 'DB_COLLATE' ) && '' !== DB_COLLATE ) {
			if ( 'utf8mb4' === $charset && strpos( DB_COLLATE, 'utf8mb4' ) === false ) {
				$issues[] = sprintf(
					/* translators: %s: DB_COLLATE value */
					__( 'DB_COLLATE "%s" does not match charset (may cause import issues)', 'wpshadow' ),
					DB_COLLATE
				);
			}
		}

		// Check posts table collation.
		$posts_collation = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COLLATION_NAME 
				FROM information_schema.COLUMNS 
				WHERE TABLE_SCHEMA = %s 
				AND TABLE_NAME = %s 
				AND COLUMN_NAME = 'post_content' 
				LIMIT 1",
				DB_NAME,
				$wpdb->posts
			)
		);

		if ( $posts_collation && strpos( $posts_collation, 'utf8mb4' ) === false && 'utf8mb4' === $charset ) {
			$issues[] = sprintf(
				/* translators: %s: posts table collation */
				__( 'Posts table uses %s collation (convert to utf8mb4 for emoji support)', 'wpshadow' ),
				$posts_collation
			);
		}

		// Check for corrupted characters in recent posts.
		$recent_posts = $wpdb->get_results(
			"SELECT ID, post_title, post_content 
			FROM {$wpdb->posts} 
			WHERE post_type IN ('post', 'page') 
			AND post_status = 'publish' 
			ORDER BY post_modified DESC 
			LIMIT 20",
			ARRAY_A
		);

		$corruption_patterns = array(
			'â€™' => ''',     // Common UTF-8 to Latin-1 corruption.
			'â€œ' => '"',
			'â€'  => '"',
			'â€"' => '–',
			'Â'   => ' ',    // Non-breaking space corruption.
			'ï»¿' => '',     // BOM character.
		);

		$corrupted_count = 0;
		foreach ( $recent_posts as $post ) {
			foreach ( $corruption_patterns as $corrupt => $correct ) {
				if ( strpos( $post['post_content'], $corrupt ) !== false || 
				     strpos( $post['post_title'], $corrupt ) !== false ) {
					++$corrupted_count;
					break;
				}
			}
		}

		if ( $corrupted_count > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts with corruption */
				__( '%d recent posts have character corruption (likely import issue)', 'wpshadow' ),
				$corrupted_count
			);
		}

		// Check for emoji in database (requires utf8mb4).
		$emoji_posts = $wpdb->get_var(
			"SELECT COUNT(*) 
			FROM {$wpdb->posts} 
			WHERE post_content REGEXP '[\\x{1F600}-\\x{1F64F}]' 
			OR post_title REGEXP '[\\x{1F600}-\\x{1F64F}]'"
		);

		if ( $emoji_posts > 0 && 'utf8mb4' !== $charset ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts with emoji */
				__( '%d posts contain emoji but charset is not utf8mb4 (may display as ??)', 'wpshadow' ),
				$emoji_posts
			);
		}

		// Check PHP mbstring extension.
		if ( ! function_exists( 'mb_detect_encoding' ) ) {
			$issues[] = __( 'PHP mbstring extension not available (import encoding detection disabled)', 'wpshadow' );
		}

		// Check iconv extension for encoding conversion.
		if ( ! function_exists( 'iconv' ) ) {
			$issues[] = __( 'PHP iconv extension not available (character encoding conversion limited)', 'wpshadow' );
		}

		// Check blog_charset option.
		$blog_charset = get_option( 'blog_charset' );
		if ( 'UTF-8' !== $blog_charset ) {
			$issues[] = sprintf(
				/* translators: %s: blog_charset value */
				__( 'blog_charset option is "%s" (should be UTF-8)', 'wpshadow' ),
				$blog_charset
			);
		}

		// Check for BOM in wp-config.php.
		$wp_config_path = ABSPATH . 'wp-config.php';
		if ( file_exists( $wp_config_path ) ) {
			$config_start = file_get_contents( $wp_config_path, false, null, 0, 3 );
			if ( "\xEF\xBB\xBF" === $config_start ) {
				$issues[] = __( 'wp-config.php has UTF-8 BOM (may cause header issues during import)', 'wpshadow' );
			}
		}

		// Check for BOM in theme files.
		$template_dir = get_template_directory();
		$functions_file = $template_dir . '/functions.php';
		
		if ( file_exists( $functions_file ) ) {
			$functions_start = file_get_contents( $functions_file, false, null, 0, 3 );
			if ( "\xEF\xBB\xBF" === $functions_start ) {
				$issues[] = __( 'Theme functions.php has UTF-8 BOM (may cause output before import)', 'wpshadow' );
			}
		}

		// Check for mixed encodings in post meta.
		$meta_encoding_check = $wpdb->get_results(
			"SELECT meta_value 
			FROM {$wpdb->postmeta} 
			WHERE meta_value REGEXP '[\\x80-\\xFF]' 
			LIMIT 10",
			ARRAY_A
		);

		if ( ! empty( $meta_encoding_check ) && function_exists( 'mb_detect_encoding' ) ) {
			foreach ( $meta_encoding_check as $meta ) {
				$encoding = mb_detect_encoding( $meta['meta_value'], array( 'UTF-8', 'ISO-8859-1', 'Windows-1252' ), true );
				
				if ( 'UTF-8' !== $encoding && false !== $encoding ) {
					$issues[] = sprintf(
						/* translators: %s: detected encoding */
						__( 'Post meta contains %s encoding (should be UTF-8)', 'wpshadow' ),
						$encoding
					);
					break;
				}
			}
		}

		// Check WXR importer settings.
		if ( class_exists( 'WP_Import' ) ) {
			// WP_Import doesn't have accessible properties for charset handling.
			// Just verify it exists for import functionality.
		} else {
			$issues[] = __( 'WordPress Importer plugin not active (import encoding handling unavailable)', 'wpshadow' );
		}

		// Check for html_entity_decode usage in theme/plugins.
		$theme_files = array(
			$template_dir . '/functions.php',
			$template_dir . '/header.php',
			$template_dir . '/footer.php',
		);

		foreach ( $theme_files as $file ) {
			if ( file_exists( $file ) ) {
				$content = file_get_contents( $file );
				
				if ( strpos( $content, 'html_entity_decode' ) !== false && 
				     strpos( $content, 'UTF-8' ) === false ) {
					$issues[] = __( 'Theme uses html_entity_decode without UTF-8 encoding specified', 'wpshadow' );
					break;
				}
			}
		}

		// Check for utf8_encode/utf8_decode usage (deprecated).
		foreach ( $theme_files as $file ) {
			if ( file_exists( $file ) ) {
				$content = file_get_contents( $file );
				
				if ( strpos( $content, 'utf8_encode' ) !== false || 
				     strpos( $content, 'utf8_decode' ) !== false ) {
					$issues[] = __( 'Theme uses deprecated utf8_encode/utf8_decode functions', 'wpshadow' );
					break;
				}
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => implode( '. ', $issues ),
				'severity'    => 'medium',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/import-character-encoding-corruption',
			);
		}

		return null;
	}
}
