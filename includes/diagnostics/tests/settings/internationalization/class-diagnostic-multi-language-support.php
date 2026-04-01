<?php
/**
 * Multi-Language Support Diagnostic
 *
 * Checks if multi-language support is implemented.
 *
 * @package WPShadow\Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Multi-Language Support
 *
 * Detects whether the site supports multiple languages.
 */
class Diagnostic_Multi_Language_Support extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'multi-language-support';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Multi-Language Support';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for multi-language capabilities';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'internationalization';

	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Finding array if issues detected, null otherwise
	 */
	public static function check() {
		$issues  = array();
		$stats   = array();
		$plugins = array(
			'polylang/polylang.php'                    => 'Polylang',
			'wpml-core/wpml-core.php'                  => 'WPML',
			'translatepress-multilingual/index.php'    => 'TranslatePress',
			'weglot/weglot.php'                        => 'Weglot',
			'bogo/bogo.php'                            => 'Bogo',
		);

		$active = array();
		foreach ( $plugins as $file => $name ) {
			if ( is_plugin_active( $file ) ) {
				$active[] = $name;
			}
		}

		$stats['active_multilang_tools']  = count( $active );
		$stats['multilang_plugins_found'] = $active;

		// Check for language options
		$languages = get_available_languages();
		$stats['wordpress_languages']     = count( $languages );

		if ( empty( $active ) && count( $languages ) <= 1 ) {
			$issues[] = __( 'No multi-language support detected', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Supporting multiple languages expands your potential customer base to billions of non-English speakers. This significantly increases market reach, improves SEO for international search, and demonstrates accessibility to global audiences.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 45,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/multi-language?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'context'       => array(
					'stats'  => $stats,
					'issues' => $issues,
				),
			);
		}

		return null;
	}
}
