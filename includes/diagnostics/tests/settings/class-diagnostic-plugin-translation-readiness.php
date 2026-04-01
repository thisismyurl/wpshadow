<?php
/**
 * Plugin Translation Readiness Diagnostic
 *
 * Checks if plugins declare text domains and localization paths.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin Translation Readiness Diagnostic Class
 *
 * Flags active plugins missing TextDomain metadata.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Plugin_Translation_Readiness extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'plugin-translation-readiness';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Plugin Translation Readiness';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if plugins declare a translation text domain';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'plugins';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$active_plugins = get_option( 'active_plugins', array() );
		$all_plugins = get_plugins();
		$missing_text_domain = array();

		foreach ( $active_plugins as $plugin_file ) {
			if ( ! isset( $all_plugins[ $plugin_file ] ) ) {
				continue;
			}

			$text_domain = $all_plugins[ $plugin_file ]['TextDomain'] ?? '';
			$domain_path = $all_plugins[ $plugin_file ]['DomainPath'] ?? '';

			if ( '' === trim( $text_domain ) && '' === trim( $domain_path ) ) {
				$missing_text_domain[] = $all_plugins[ $plugin_file ]['Name'];
			}
		}

		if ( ! empty( $missing_text_domain ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Some active plugins do not declare a translation text domain. This can limit localization support.', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 20,
				'auto_fixable' => false,
				'details'      => array(
					'plugins' => array_slice( $missing_text_domain, 0, 10 ),
				),
				'kb_link'      => 'https://wpshadow.com/kb/plugin-translation-readiness?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
