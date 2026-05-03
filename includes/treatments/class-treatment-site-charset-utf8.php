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
 * @package ThisIsMyURL\Shadow
 * @since   0.6095
 */

declare(strict_types=1);

namespace ThisIsMyURL\Shadow\Treatments;

use ThisIsMyURL\Shadow\Core\Treatment_Base;

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
			return static::apply_option_with_backup(
				'blog_charset',
				'UTF-8',
				'thisismyurl_shadow_site_charset_prev',
				__( 'Site charset is already UTF-8. No changes made.', 'thisismyurl-shadow' ),
				__( 'Site charset is already UTF-8. No changes made.', 'thisismyurl-shadow' )
			);
		}

		return static::apply_option_with_backup(
			'blog_charset',
			'UTF-8',
			'thisismyurl_shadow_site_charset_prev',
			__( 'Site charset is already UTF-8. No changes made.', 'thisismyurl-shadow' ),
			sprintf(
				/* translators: %s: previous charset value */
				__( 'Site charset updated from "%s" to UTF-8. Note: this changes the WordPress option only - it does not re-encode database content. Verify that your database collation is utf8 or utf8mb4 to ensure consistent encoding throughout.', 'thisismyurl-shadow' ),
				esc_html( $current )
			)
		);
	}

	/**
	 * Restore the previous charset value.
	 *
	 * @return array
	 */
	public static function undo(): array {
		return static::restore_option_from_backup(
			'blog_charset',
			'thisismyurl_shadow_site_charset_prev',
			__( 'No previous charset value found to restore.', 'thisismyurl-shadow' ),
			static function ( $prev ): string {
				return sprintf(
					/* translators: %s: restored charset value */
					__( 'Site charset restored to "%s".', 'thisismyurl-shadow' ),
					esc_html( (string) $prev )
				);
			}
		);
	}
}
