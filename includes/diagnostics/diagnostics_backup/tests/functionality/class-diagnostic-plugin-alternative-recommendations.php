<?php
/**
 * Plugin Alternative Recommendations Diagnostic
 *
 * Recommends alternative plugins for common use cases.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2308
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin Alternative Recommendations Diagnostic Class
 *
 * Recommends alternative plugins when better options exist.
 *
 * @since 1.2601.2308
 */
class Diagnostic_Plugin_Alternative_Recommendations extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'plugin-alternative-recommendations';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Plugin Alternative Recommendations';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Recommends alternative plugins when better options are available';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2308
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Map of plugins and their recommended alternatives
		$plugin_alternatives = array(
			'jetpack/jetpack.php' => array(
				'issue' => 'Consider if all Jetpack features are being used',
				'alternatives' => array(
					'akismet/akismet.php' => 'Spam protection',
					'wp-super-cache/wp-cache.php' => 'Caching',
					'wordfence/wordfence.php' => 'Security',
				),
			),
			'all-in-one-seo-pack/all_in_one_seo_pack.php' => array(
				'issue' => 'Heavy plugin - consider lighter alternatives',
				'alternatives' => array(
					'the-seo-framework/the-seo-framework.php' => 'Lighter SEO solution',
					'rank-math-seo/rank-math-seo.php' => 'Modern SEO alternative',
				),
			),
		);

		$active_plugins = get_option( 'active_plugins', array() );
		$recommendations = array();

		foreach ( $plugin_alternatives as $plugin_path => $alt_info ) {
			if ( in_array( $plugin_path, $active_plugins, true ) ) {
				$plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin_path );
				$recommendations[] = sprintf(
					'%s: %s',
					$plugin_data['Name'] ?? $plugin_path,
					$alt_info['issue']
				);
			}
		}

		if ( ! empty( $recommendations ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => sprintf(
					/* translators: %d: number of recommendations */
					__( '%d active plugins have recommended alternatives available', 'wpshadow' ),
					count( $recommendations )
				),
				'severity'      => 'low',
				'threat_level'  => 15,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/plugin-alternative-recommendations',
			);
		}

		return null;
	}
}
