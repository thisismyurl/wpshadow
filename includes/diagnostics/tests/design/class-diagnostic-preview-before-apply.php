<?php
/**
 * Preview Before Apply Diagnostic
 *
 * Checks whether changes can be previewed before being applied.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Design
 * @since      1.6035.0900
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Preview Before Apply Diagnostic Class
 *
 * Encourages preview workflows before committing changes.
 *
 * @since 1.6035.0900
 */
class Diagnostic_Preview_Before_Apply extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'preview-before-apply';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Changes Applied Without Preview First';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether users can preview major changes before applying them';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'design';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6035.0900
	 * @return array|null Finding array or null if no issues found.
	 */
	public static function check() {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$has_preview = false;
		$preview_sources = array();

		if ( function_exists( 'wp_is_block_theme' ) && wp_is_block_theme() ) {
			$has_preview = true;
			$preview_sources[] = __( 'Block theme editor preview', 'wpshadow' );
		}

		if ( has_action( 'customize_register' ) || current_theme_supports( 'customize-selective-refresh-widgets' ) ) {
			$has_preview = true;
			$preview_sources[] = __( 'Theme Customizer live preview', 'wpshadow' );
		}

		$preview_plugins = array(
			'preview-emails/preview-emails.php'   => 'Preview E-mails',
			'email-log/email-log.php'             => 'Email Log',
			'wp-mail-logging/wp-mail-logging.php' => 'WP Mail Logging',
		);

		foreach ( $preview_plugins as $plugin => $label ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_preview = true;
				$preview_sources[] = $label;
			}
		}

		if ( apply_filters( 'wpshadow_has_preview_mode', false ) ) {
			$has_preview = true;
			$preview_sources[] = __( 'Custom preview workflow', 'wpshadow' );
		}

		if ( $has_preview ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'Major design changes appear to apply immediately without a preview step. A preview helps users confirm changes before committing them.', 'wpshadow' ),
			'severity'     => 'medium',
			'threat_level' => 55,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/preview-before-apply',
			'meta'         => array(
				'preview_sources' => $preview_sources,
			),
		);
	}
}
