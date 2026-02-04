<?php
/**
 * Character Encoding Corruption During Import Diagnostic
 *
 * Detects when special characters become corrupted during import.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6033.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Character Encoding Corruption During Import Diagnostic Class
 *
 * Detects when special characters, unicode, or emoji become corrupted during import.
 *
 * @since 1.6033.0000
 */
class Diagnostic_Character_Encoding_Corruption_During_Import extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'character-encoding-corruption-during-import';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Character Encoding Corruption During Import';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects character encoding issues during import';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6033.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$issues = array();

		// Check database charset.
		$db_charset = $wpdb->get_charset_info();
		if ( empty( $db_charset ) || strpos( strtolower( $db_charset->charset ), 'utf' ) === false ) {
			$issues[] = __( 'Database is not using UTF-8 encoding', 'wpshadow' );
		}

		// Check for mojibake (corrupted characters) in posts.
		$suspicious_posts = $wpdb->get_results( "
			SELECT ID, post_title
			FROM {$wpdb->posts}
			WHERE post_title LIKE '%?%'
			OR post_content LIKE '%?%'
			LIMIT 5
		" );

		if ( ! empty( $suspicious_posts ) ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts with suspicious characters */
				__( '%d posts contain suspicious replacement characters (?)', 'wpshadow' ),
				count( $suspicious_posts )
			);
		}

		// Sample posts with emoji or special characters.
		$recent_posts = get_posts( array(
			'post_type'      => array( 'post', 'page' ),
			'posts_per_page' => 10,
			'orderby'        => 'modified',
			'order'          => 'DESC',
		) );

		if ( ! empty( $recent_posts ) ) {
			$encoding_issues = 0;

			foreach ( $recent_posts as $post ) {
				// Check for emoji (if they exist but are corrupted).
				if ( preg_match( '/[\x{1F300}-\x{1F9FF}]/u', $post->post_content ) ) {
					// Has emoji - check if valid UTF-8.
					if ( ! mb_check_encoding( $post->post_content, 'UTF-8' ) ) {
						$encoding_issues++;
					}
				}
			}

			if ( $encoding_issues > 0 ) {
				$issues[] = sprintf(
					/* translators: %d: number of posts with encoding issues */
					__( '%d posts have invalid UTF-8 encoding in content', 'wpshadow' ),
					$encoding_issues
				);
			}
		}

		// Check PHP settings.
		$php_charset = ini_get( 'default_charset' );
		if ( empty( $php_charset ) || strpos( strtolower( $php_charset ), 'utf' ) === false ) {
			$issues[] = sprintf(
				/* translators: %s: current charset */
				__( 'PHP default charset is not UTF-8 (currently: %s)', 'wpshadow' ),
				$php_charset ?: 'not set'
			);
		}

		// Check blog charset option.
		$blog_charset = get_option( 'blog_charset' );
		if ( empty( $blog_charset ) || strpos( strtolower( $blog_charset ), 'utf' ) === false ) {
			$issues[] = sprintf(
				/* translators: %s: current blog charset */
				__( 'Blog charset setting is not UTF-8 (currently: %s)', 'wpshadow' ),
				$blog_charset ?: 'not set'
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/character-encoding-corruption-during-import',
			);
		}

		return null;
	}
}
