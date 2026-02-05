<?php
/**
 * Custom Permalink Structure Treatment
 *
 * Detects posts using custom permalinks and analyzes potential conflicts.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6032.1745
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Custom Permalink Structure Treatment Class
 *
 * Checks for posts with custom permalinks set via post meta.
 *
 * @since 1.6032.1745
 */
class Treatment_Custom_Permalink_Structure extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'custom-permalink-structure';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Custom Permalink Structure';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Detects custom permalink usage';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'permalinks';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6032.1745
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$issues = array();

		// Check for custom permalinks plugin.
		$custom_permalink_plugins = array(
			'custom-permalinks/custom-permalinks.php',
			'custom-permalinks-extended/custom-permalinks-extended.php',
			'permalink-manager/permalink-manager.php',
		);

		$active_custom_plugin = false;
		foreach ( $custom_permalink_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_custom_plugin = true;
				break;
			}
		}

		if ( $active_custom_plugin ) {
			// Count posts with custom permalinks.
			$custom_count = $wpdb->get_var(
				"SELECT COUNT(*) FROM {$wpdb->postmeta} 
				WHERE meta_key IN ('custom_permalink', '_custom_permalink', 'permalink_manager_custom_uri')"
			);

			if ( $custom_count > 0 ) {
				$issues[] = sprintf(
					/* translators: %d: number of posts */
					_n(
						'%d post has a custom permalink',
						'%d posts have custom permalinks',
						(int) $custom_count,
						'wpshadow'
					),
					number_format_i18n( (int) $custom_count )
				);
			}
		}

		// Check for hardcoded redirects in .htaccess.
		if ( function_exists( 'got_mod_rewrite' ) && got_mod_rewrite() ) {
			$htaccess_file = get_home_path() . '.htaccess';
			if ( file_exists( $htaccess_file ) && is_readable( $htaccess_file ) ) {
				$htaccess_content = file_get_contents( $htaccess_file );
				$redirect_count   = substr_count( $htaccess_content, 'Redirect' );

				if ( $redirect_count > 10 ) {
					$issues[] = sprintf(
						/* translators: %d: number of redirects */
						__( 'Excessive redirects in .htaccess (%d found)', 'wpshadow' ),
						$redirect_count
					);
				}
			}
		}

		// Check for duplicate slugs.
		$duplicate_slugs = $wpdb->get_var(
			"SELECT COUNT(*) FROM (
				SELECT post_name, COUNT(*) as count 
				FROM {$wpdb->posts} 
				WHERE post_status = 'publish' 
				AND post_type IN ('post', 'page')
				GROUP BY post_name 
				HAVING count > 1
			) as dupes"
		);

		if ( $duplicate_slugs > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of duplicate slugs */
				_n(
					'%d duplicate URL slug found',
					'%d duplicate URL slugs found',
					(int) $duplicate_slugs,
					'wpshadow'
				),
				number_format_i18n( (int) $duplicate_slugs )
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/custom-permalink-structure',
			);
		}

		return null;
	}
}
