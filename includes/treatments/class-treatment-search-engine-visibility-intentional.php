<?php
/**
 * Treatment: Re-enable search engine visibility
 *
 * When the "Discourage search engines from indexing this site" setting is
 * enabled, WordPress sets blog_public to 0. This treatment restores normal
 * indexing behavior by setting blog_public back to 1.
 *
 * Undo: restores the previous blog_public value.
 *
 * @package ThisIsMyURL\Shadow
 * @since   0.7056
 */

declare(strict_types=1);

namespace ThisIsMyURL\Shadow\Treatments;

use ThisIsMyURL\Shadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sets blog_public back to 1.
 */
class Treatment_Search_Engine_Visibility_Intentional extends Treatment_Base {

	/** @var string */
	protected static $slug = 'search-engine-visibility-intentional';

	/** @return string */
	public static function get_risk_level(): string {
		return 'safe';
	}

	/**
	 * Enable search engine visibility.
	 *
	 * @return array
	 */
	public static function apply(): array {
		return static::apply_option_with_backup(
			'blog_public',
			'1',
			'thisismyurl_shadow_blog_public_prev',
			__( 'Search engine visibility is already enabled. No changes made.', 'thisismyurl-shadow' ),
			__( 'Search engine visibility enabled. WordPress will stop discouraging indexing for this site.', 'thisismyurl-shadow' )
		);
	}

	/**
	 * Restore the previous blog_public value.
	 *
	 * @return array
	 */
	public static function undo(): array {
		return static::restore_option_from_backup(
			'blog_public',
			'thisismyurl_shadow_blog_public_prev',
			__( 'No previous search visibility setting was stored.', 'thisismyurl-shadow' ),
			static function ( $previous ): string {
				return '0' === (string) $previous
					? __( 'Search engine visibility restored to discouraged.', 'thisismyurl-shadow' )
					: __( 'Search engine visibility restored to enabled.', 'thisismyurl-shadow' );
			}
		);
	}
}