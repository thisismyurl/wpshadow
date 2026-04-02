<?php
/**
 * Treatment: Set site charset to UTF-8
 *
 * The WordPress `blog_charset` option controls the character encoding declared
 * in page `<meta charset>` tags and HTTP Content-Type headers. Sites migrated
 * from legacy hosting sometimes carry an ISO-8859-1 or other charset that
 * produces garbled special characters (mojibake) in content, RSS feeds, and
 * REST API responses.
 *
 * This treatment sets `blog_charset` to `UTF-8`.
 *
 * ⚠ Important: changing the charset option alone does not re-encode database
 * content. If the database tables are genuinely stored in a non-UTF-8
 * collation (e.g. latin1), changing this option may produce incorrect output
 * until the database is also migrated. Verify the database collation before
 * or shortly after applying this treatment. If in doubt, apply the change on a
 * staging site first.
 *
 * Undo: restores the previous charset value.
 *
 * @package WPShadow
 * @since   0.6093.1900
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sets the WordPress blog_charset option to UTF-8.
 */
class Treatment_Site_Charset_Utf8 extends Treatment_Base {

	/** @var string */
	protected static $slug = 'site-charset-utf8';

	/** @return string */
	public static function get_risk_level(): string {
		return 'moderate';
	}

	/**
	 * Set blog_charset to UTF-8 and store the previous value.
	 *
	 * @return array
	 */
	public static function apply(): array {
		$current = strtoupper( trim( (string) get_option( 'blog_charset', 'UTF-8' ) ) );

		if ( 'UTF-8' === $current ) {
			return array(
				'success' => true,
				'message' => __( 'Site charset is already UTF-8. No changes made.', 'wpshadow' ),
			);
		}

		update_option( 'wpshadow_site_charset_prev', $current, false );
		update_option( 'blog_charset', 'UTF-8' );

		return array(
			'success' => true,
			'message' => sprintf(
				/* translators: %s: previous charset value */
				__( 'Site charset updated from "%s" to UTF-8. Note: this changes the WordPress option only — it does not re-encode database content. Verify that your database collation is utf8 or utf8mb4 to ensure consistent encoding throughout.', 'wpshadow' ),
				esc_html( $current )
			),
		);
	}

	/**
	 * Restore the previous charset value.
	 *
	 * @return array
	 */
	public static function undo(): array {
		$prev = get_option( 'wpshadow_site_charset_prev' );

		if ( false === $prev ) {
			return array(
				'success' => false,
				'message' => __( 'No previous charset value found to restore.', 'wpshadow' ),
			);
		}

		update_option( 'blog_charset', $prev );
		delete_option( 'wpshadow_site_charset_prev' );

		return array(
			'success' => true,
			'message' => sprintf(
				/* translators: %s: restored charset value */
				__( 'Site charset restored to "%s".', 'wpshadow' ),
				esc_html( $prev )
			),
		);
	}
}
