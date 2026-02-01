<?php
/**
 * Comment Blacklist Effectiveness Diagnostic
 *
 * Verifies comment blacklist/disallowed keys are properly configured to block spam.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26032.1755
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Comment Blacklist Effectiveness Diagnostic Class
 *
 * Checks comment blacklist configuration and effectiveness.
 *
 * @since 1.26032.1755
 */
class Diagnostic_Comment_Blacklist_Effectiveness extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'comment-blacklist-effectiveness';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Comment Blacklist Effectiveness';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies comment blacklist is effective';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'comments';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26032.1755
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check disallowed keys (blacklist).
		$disallowed_keys = get_option( 'disallowed_keys', get_option( 'blacklist_keys', '' ) );

		if ( empty( $disallowed_keys ) ) {
			$issues[] = __( 'No disallowed comment keys configured', 'wpshadow' );
		} else {
			$keys = array_filter( explode( "\n", $disallowed_keys ) );
			$key_count = count( $keys );

			if ( $key_count < 5 ) {
				$issues[] = sprintf(
					/* translators: %d: number of blacklist entries */
					__( 'Only %d disallowed keys configured - consider adding more common spam terms', 'wpshadow' ),
					$key_count
				);
			}

			// Check for common spam patterns.
			$has_url_pattern = false;
			$has_pharmacy = false;
			$has_casino = false;

			foreach ( $keys as $key ) {
				$key_lower = strtolower( trim( $key ) );
				if ( strpos( $key_lower, 'http' ) !== false || strpos( $key_lower, 'www' ) !== false ) {
					$has_url_pattern = true;
				}
				if ( strpos( $key_lower, 'pharm' ) !== false || strpos( $key_lower, 'viagra' ) !== false ) {
					$has_pharmacy = true;
				}
				if ( strpos( $key_lower, 'casino' ) !== false || strpos( $key_lower, 'poker' ) !== false ) {
					$has_casino = true;
				}
			}

			if ( ! $has_url_pattern ) {
				$issues[] = __( 'Consider adding URL patterns to blacklist (common in spam)', 'wpshadow' );
			}
			if ( ! $has_pharmacy && ! $has_casino ) {
				$issues[] = __( 'Consider adding common spam categories (pharmacy, casino) to blacklist', 'wpshadow' );
			}
		}

		// Check spam comment count.
		$spam_count = wp_count_comments();
		if ( isset( $spam_count->spam ) && $spam_count->spam > 1000 ) {
			$issues[] = sprintf(
				/* translators: %d: number of spam comments */
				__( 'High spam count (%d comments) - blacklist may need improvement', 'wpshadow' ),
				$spam_count->spam
			);
		}

		// Check if Akismet is active.
		if ( ! is_plugin_active( 'akismet/akismet.php' ) && ! class_exists( 'Akismet' ) ) {
			$issues[] = __( 'No spam protection plugin detected - consider installing Akismet', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/comment-blacklist-effectiveness',
			);
		}

		return null;
	}
}
