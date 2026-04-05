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
 * @package WPShadow
 * @since   0.7056.0100
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

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
			'wpshadow_blog_public_prev',
			__( 'Search engine visibility is already enabled. No changes made.', 'wpshadow' ),
			__( 'Search engine visibility enabled. WordPress will stop discouraging indexing for this site.', 'wpshadow' )
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
			'wpshadow_blog_public_prev',
			__( 'No previous search visibility setting was stored.', 'wpshadow' ),
			static function ( $previous ): string {
				return '0' === (string) $previous
					? __( 'Search engine visibility restored to discouraged.', 'wpshadow' )
					: __( 'Search engine visibility restored to enabled.', 'wpshadow' );
			}
		);
	}
}