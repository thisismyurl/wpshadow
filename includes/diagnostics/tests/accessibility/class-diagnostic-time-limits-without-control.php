<?php
/**
 * Time Limits Without Control Diagnostic
 *
 * Checks if users can extend or disable time limits.
 *
 * @since 1.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Time Limits Diagnostic Class
 *
 * Validates that time limits can be turned off, adjusted, or extended.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Time_Limits_Without_Control extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'time-limits-without-control';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Time Limits Without User Control';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if users can extend or disable time limits';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check for aggressive session timeout.
		$session_timeout = (int) get_option( 'session_timeout', 0 );
		if ( $session_timeout > 0 && $session_timeout < 1800 ) { // Less than 30 minutes.
			$issues[] = sprintf(
				/* translators: %d: session timeout in minutes */
				__( 'Session timeout is %d minutes without warning or extension option', 'wpshadow' ),
				floor( $session_timeout / 60 )
			);
		}

		// Check JavaScript files for timers.
		$js_files = array();
		$js_dirs  = array(
			get_template_directory() . '/js',
			get_template_directory() . '/assets/js',
			get_template_directory() . '/dist/js',
		);

		foreach ( $js_dirs as $js_dir ) {
			if ( is_dir( $js_dir ) ) {
				$files = glob( $js_dir . '/*.js' );
				if ( is_array( $files ) ) {
					$js_files = array_merge( $js_files, $files );
				}
			}
		}

		$has_timer = false;
		$has_warning = false;
		$has_extend_option = false;

		foreach ( $js_files as $js_file ) {
			if ( ! file_exists( $js_file ) ) {
				continue;
			}

			$content = file_get_contents( $js_file );

			// Check for setTimeout/setInterval with timeouts.
			if ( preg_match( '/setTimeout|setInterval|countdown|timer/i', $content ) ) {
				$has_timer = true;

				// Check for warning before timeout.
				if ( preg_match( '/warning|alert|notify|expire|timeout.*dialog/i', $content ) ) {
					$has_warning = true;
				}

				// Check for extend/adjust options.
				if ( preg_match( '/extend|continue|adjust|disable.*timer/i', $content ) ) {
					$has_extend_option = true;
				}
			}
		}

		if ( $has_timer && ! $has_warning ) {
			$issues[] = __( 'Timer detected but no warning before expiration', 'wpshadow' );
		}

		if ( $has_timer && ! $has_extend_option ) {
			$issues[] = __( 'Timer detected but no option to extend time limit', 'wpshadow' );
		}

		// Check for quiz/test plugins with time limits.
		$timed_plugins = array(
			'wp-pro-quiz' => 'WP Pro Quiz',
			'learnpress'  => 'LearnPress',
			'learndash'   => 'LearnDash',
		);

		$active_plugins = get_option( 'active_plugins', array() );
		foreach ( $active_plugins as $plugin ) {
			foreach ( $timed_plugins as $plugin_slug => $plugin_name ) {
				if ( strpos( $plugin, $plugin_slug ) !== false ) {
					$issues[] = sprintf(
						/* translators: %s: plugin name */
						__( 'Quiz plugin "%s" may have time limits without extension options', 'wpshadow' ),
						$plugin_name
					);
					break;
				}
			}
		}

		// Check for checkout timeout.
		if ( class_exists( 'WooCommerce' ) ) {
			$hold_stock_minutes = (int) get_option( 'woocommerce_hold_stock_minutes', 0 );
			if ( $hold_stock_minutes > 0 && $hold_stock_minutes < 60 ) {
				$issues[] = sprintf(
					/* translators: %d: hold stock minutes */
					__( 'WooCommerce holds cart for %d minutes without clear warning or extension', 'wpshadow' ),
					$hold_stock_minutes
				);
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Your site has countdown timers users can\'t control—like being forced to finish a test before you\'re done reading the first question. Time limits disproportionately affect users with cognitive disabilities (processing takes longer), motor disabilities (interactions take longer), and screen reader users (navigating takes longer). WCAG requires time limits be: (1) turned off, (2) adjusted to at least 10x the default, or (3) extended with 20-second warning before expiring. Example: shopping cart timeouts should allow extension.', 'wpshadow' ) . ' ' . implode( ' ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/time-limits-control',
			);
		}

		return null;
	}
}
