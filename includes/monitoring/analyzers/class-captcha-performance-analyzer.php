<?php
declare(strict_types=1);

namespace WPShadow\Guardian;

/**
 * Captcha Performance Analyzer
 *
 * Monitors CAPTCHA implementation and performance impact.
 * Identifies slow CAPTCHA services affecting form submission experience.
 *
 * Philosophy: Show value (#9) - Balance security with user experience.
 *
 * @package WPShadow
 * @subpackage Guardian
 * @since 1.2601.2200
 */
class Captcha_Performance_Analyzer {

	/**
	 * Known CAPTCHA services
	 *
	 * @var array
	 */
	private static $captcha_services = array(
		'google.com/recaptcha' => 'reCAPTCHA',
		'hcaptcha.com'         => 'hCaptcha',
		'funcaptcha.com'       => 'FunCaptcha',
		'captcha.com'          => 'Captcha.com',
		'securimage'           => 'Securimage',
		'simplecaptcha'        => 'Simple CAPTCHA',
	);

	/**
	 * Analyze CAPTCHA performance
	 *
	 * @return array Analysis results
	 */
	public static function analyze(): array {
		// Check cache first (hourly)
		$cached = \WPShadow\Core\Cache_Manager::get( 'captcha_performance', 'wpshadow_monitoring' );
		if ( $cached && is_array( $cached ) ) {
			return $cached;
		}

		$results = array(
			'has_captcha'            => false,
			'captcha_service'        => '',
			'total_scripts'          => 0,
			'estimated_load_time_ms' => 0,
			'is_blocking'            => false,
			'forms_with_captcha'     => array(),
		);

		// Get enqueued scripts
		global $wp_scripts;

		if ( ! isset( $wp_scripts ) || ! ( $wp_scripts instanceof \WP_Scripts ) ) {
			\WPShadow\Core\Cache_Manager::set( 'captcha_performance', $results, 'wpshadow_monitoring', HOUR_IN_SECONDS );
			return $results;
		}

		// Check scripts for CAPTCHA services
		$captcha_scripts = array();
		$is_blocking     = false;

		foreach ( $wp_scripts->registered as $handle => $script ) {
			if ( ! is_string( $script->src ) || empty( $script->src ) ) {
				continue;
			}

			foreach ( self::$captcha_services as $domain => $service ) {
				if ( stripos( $script->src, $domain ) !== false ) {
					$results['has_captcha']     = true;
					$results['captcha_service'] = $service;

					$captcha_scripts[] = array(
						'handle'  => $handle,
						'service' => $service,
						'src'     => $script->src,
					);

					// Check if blocking (in head)
					if ( empty( $script->extra['group'] ) || $script->extra['group'] !== 1 ) {
						$is_blocking = true;
					}
				}
			}
		}

		$results['total_scripts'] = count( $captcha_scripts );
		$results['is_blocking']   = $is_blocking;

		// Estimate load time
		// CAPTCHA scripts typically add 300-800ms
		if ( $results['has_captcha'] ) {
			$base_time                         = 500; // Base CAPTCHA load time
			$additional                        = count( $captcha_scripts ) * 200; // Additional scripts
			$results['estimated_load_time_ms'] = $base_time + $additional;
		}

		// Check forms for CAPTCHA implementation
		$results['forms_with_captcha'] = self::detect_captcha_forms();

		// Cache for 1 hour
		\WPShadow\Core\Cache_Manager::set( 'captcha_performance', $results, 'wpshadow_monitoring', HOUR_IN_SECONDS );

		return $results;
	}

	/**
	 * Detect forms with CAPTCHA
	 *
	 * @return array Forms with CAPTCHA
	 */
	private static function detect_captcha_forms(): array {
		$forms = array();

		// Check for common form plugins
		$form_plugins = array(
			'contact-form-7/wp-contact-form-7.php' => 'Contact Form 7',
			'wpforms-lite/wpforms.php'             => 'WPForms',
			'ninja-forms/ninja-forms.php'          => 'Ninja Forms',
			'gravityforms/gravityforms.php'        => 'Gravity Forms',
			'formidable/formidable.php'            => 'Formidable Forms',
		);

		foreach ( $form_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				// Check if plugin has CAPTCHA enabled
				if ( self::plugin_has_captcha_enabled( $plugin ) ) {
					$forms[] = $name;
				}
			}
		}

		// Check WooCommerce
		if ( class_exists( 'WooCommerce' ) ) {
			if ( get_option( 'woocommerce_enable_recaptcha' ) ) {
				$forms[] = 'WooCommerce Checkout';
			}
		}

		// Check login forms
		if ( get_option( 'wpshadow_login_captcha_enabled' ) ) {
			$forms[] = 'Login Form';
		}

		return $forms;
	}

	/**
	 * Check if form plugin has CAPTCHA enabled
	 *
	 * @param string $plugin Plugin path
	 * @return bool True if CAPTCHA enabled
	 */
	private static function plugin_has_captcha_enabled( string $plugin ): bool {
		// Contact Form 7
		if ( strpos( $plugin, 'contact-form-7' ) !== false ) {
			return function_exists( 'wpcf7_recaptcha' ) && wpcf7_recaptcha()->is_active();
		}

		// WPForms
		if ( strpos( $plugin, 'wpforms' ) !== false ) {
			$settings = get_option( 'wpforms_settings', array() );
			return ! empty( $settings['recaptcha-site-key'] );
		}

		// Ninja Forms
		if ( strpos( $plugin, 'ninja-forms' ) !== false ) {
			return get_option( 'ninja_forms_recaptcha_site_key' ) ? true : false;
		}

		// Gravity Forms
		if ( strpos( $plugin, 'gravityforms' ) !== false ) {
			$settings = get_option( 'rg_gforms_captcha_public_key' );
			return ! empty( $settings );
		}

		return false;
	}

	/**
	 * Get summary
	 *
	 * @return array Summary data
	 */
	public static function get_summary(): array {
		$results = \WPShadow\Core\Cache_Manager::get( 'captcha_performance', 'wpshadow_monitoring' );
		return is_array( $results ) ? $results : array(
			'has_captcha'            => false,
			'captcha_service'        => '',
			'estimated_load_time_ms' => 0,
			'is_blocking'            => false,
		);
	}

	/**
	 * Clear cached data
	 *
	 * @return void
	 */
	public static function clear_cache(): void {
		\WPShadow\Core\Cache_Manager::delete( 'captcha_performance', 'wpshadow_monitoring' );
	}
}
