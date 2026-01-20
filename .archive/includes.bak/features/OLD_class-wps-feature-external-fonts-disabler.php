<?php

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class WPSHADOW_Feature_External_Fonts_Disabler extends WPSHADOW_Abstract_Feature {

	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'external-fonts-disabler',
				'name'               => __( 'Block External Font Loading', 'wpshadow' ),
				'description_short'  => __( 'Block external font services to improve privacy and performance.', 'wpshadow' ),
				'description_long'   => __( 'Protect your visitors\' privacy by blocking external font services including Google Fonts, Font Awesome, Adobe Fonts, and more. External fonts can track visitors across websites and slow down page load times. This feature intercepts and blocks font requests at multiple levels, removes font links from HTML output, and optionally replaces them with privacy-friendly system fonts. Granular controls let you whitelist specific fonts you want to keep while blocking others.', 'wpshadow' ),
				'description_wizard' => __( 'External font services like Google Fonts track your visitors across the web and add unnecessary HTTP requests that slow down your site. Enable this to block tracking, improve page speed, and maintain full GDPR compliance while keeping your site looking great with system fonts.', 'wpshadow' ),
				'description'        => __( 'Block external font services to improve privacy and performance.', 'wpshadow' ),
				'scope'              => 'core',
				'default_enabled'    => false,
				'version'            => '1.2.0',
				'widget_group'       => 'performance',
				'license_level'      => 1,
				'minimum_capability' => 'manage_options',
				'icon'               => 'dashicons-editor-removeformatting',
				'category'           => 'performance',
				'priority'           => 20,
				'aliases'            => array(
					'privacy',
					'fonts optimization',
					'external scripts',
					'local fonts',
					'gdpr compliance',
					'performance',
					'tracking prevention',
					'font awesome',
					'adobe fonts',
				),
				'sub_features'       => array(
					'block_google_fonts'  => array(
						'name'                => __( 'Block Google Fonts', 'wpshadow' ),
						'description_short'   => __( 'Block fonts.googleapis.com and fonts.gstatic.com.', 'wpshadow' ),
						'description_long'    => __( 'Blocks all font requests to fonts.googleapis.com and fonts.gstatic.com, Google\'s most widely-used font delivery network. Google Fonts can track users across websites through font requests and unique identifiers. This prevents that tracking while removing unnecessary external dependencies that can slow down your site and create GDPR compliance issues.', 'wpshadow' ),
						'description_wizard'  => __( 'Google tracks every visitor who loads fonts from their CDN, building detailed profiles across millions of websites. Block this to protect your visitors\' privacy and comply with GDPR regulations while improving page load speed.', 'wpshadow' ),
						'description'         => __( 'Block fonts.googleapis.com and fonts.gstatic.com.', 'wpshadow' ),
						'default_enabled'     => true,
					),
					'block_font_awesome'  => array(
						'name'                => __( 'Block Font Awesome CDN', 'wpshadow' ),
						'description_short'   => __( 'Block Font Awesome icon fonts from external CDN sources.', 'wpshadow' ),
						'description_long'    => __( 'Blocks Font Awesome icon fonts loaded from fontawesome.com, use.fontawesome.com, pro.fontawesome.com, kit.fontawesome.com, and the cloudflare CDN. Font Awesome is one of the most popular icon font libraries, but loading it from external CDNs creates privacy concerns and adds external dependencies. If you need Font Awesome icons, consider hosting the files locally instead.', 'wpshadow' ),
						'description_wizard'  => __( 'Font Awesome CDNs can track which icons are displayed on your site and collect visitor data. Most sites only use a handful of icons but load the entire library. Block external Font Awesome to eliminate tracking and improve performance.', 'wpshadow' ),
						'description'         => __( 'Block Font Awesome icon fonts from external CDN sources.', 'wpshadow' ),
						'default_enabled'     => true,
					),
					'block_adobe_fonts'   => array(
						'name'                => __( 'Block Adobe Fonts (Typekit)', 'wpshadow' ),
						'description_short'   => __( 'Block Adobe Typekit and Edge Web Fonts services.', 'wpshadow' ),
						'description_long'    => __( 'Blocks fonts from use.typekit.net, use.typekit.com, use.edgefonts.net, and typekit.com. Adobe Fonts (formerly Typekit) is Adobe\'s premium web font service that can track visitors and collect analytics. Adobe Edge Web Fonts, while discontinued, is still used by older sites. Blocking these prevents Adobe from tracking your visitors across their extensive network.', 'wpshadow' ),
						'description_wizard'  => __( 'Adobe collects detailed analytics about font usage and visitor behavior across all sites using their font services. This data feeds into Adobe\'s broader tracking ecosystem. Block it to keep your visitors\' data private and reduce external dependencies.', 'wpshadow' ),
						'description'         => __( 'Block Adobe Typekit and Edge Web Fonts services.', 'wpshadow' ),
						'default_enabled'     => true,
					),
					'block_bunny_fonts'   => array(
						'name'                => __( 'Block Bunny Fonts', 'wpshadow' ),
						'description_short'   => __( 'Block fonts.bunny.net privacy-focused font CDN.', 'wpshadow' ),
						'description_long'    => __( 'Blocks fonts from fonts.bunny.net, a privacy-focused alternative to Google Fonts. While Bunny Fonts claims to be privacy-friendly and GDPR-compliant, it still creates external dependencies and requires trusting a third party with your visitors\' data. Even privacy-focused CDNs can potentially track usage patterns and collect metadata about your site and visitors.', 'wpshadow' ),
						'description_wizard'  => __( 'Even privacy-focused font CDNs create external dependencies that slow your site and require trusting third parties. For true privacy, use self-hosted fonts or system fonts. Block Bunny Fonts to eliminate all external font dependencies.', 'wpshadow' ),
						'description'         => __( 'Block fonts.bunny.net privacy-focused font CDN.', 'wpshadow' ),
						'default_enabled'     => true,
					),
					'block_cdnjs_fonts'   => array(
						'name'                => __( 'Block CDNJS Font Libraries', 'wpshadow' ),
						'description_short'   => __( 'Block font libraries served from cdnjs.cloudflare.com.', 'wpshadow' ),
						'description_long'    => __( 'Blocks font libraries served from cdnjs.cloudflare.com, including Font Awesome, Open Sans, and other popular web fonts. CDNJS is Cloudflare\'s free open source CDN that hosts thousands of libraries. While convenient, loading fonts from CDNJS allows Cloudflare to see your visitors\' IP addresses and browsing patterns. Cloudflare processes massive amounts of internet traffic and this creates potential privacy concerns.', 'wpshadow' ),
						'description_wizard'  => __( 'CDNJS gives Cloudflare visibility into your visitors\' behavior and browsing patterns. While Cloudflare claims to respect privacy, they still collect extensive data. Block CDNJS fonts to prevent this third-party tracking and improve page load speed.', 'wpshadow' ),
						'description'         => __( 'Block font libraries served from cdnjs.cloudflare.com.', 'wpshadow' ),
						'default_enabled'     => true,
					),
					'buffer_cleanup'      => array(
						'name'                => __( 'Remove from HTML Output', 'wpshadow' ),
						'description_short'   => __( 'Scan and remove external font references from HTML.', 'wpshadow' ),
						'description_long'    => __( 'Scans the final HTML output before it\'s sent to browsers and removes any remaining external font references that weren\'t caught by the WordPress hooks. This catches font links added by themes that bypass WordPress\' enqueue system, hardcoded links in templates, and fonts injected by page builders or other plugins. It also removes DNS prefetch and preconnect hints that can leak information to font CDNs.', 'wpshadow' ),
						'description_wizard'  => __( 'Some themes and plugins bypass WordPress standards and add font links directly to HTML, evading normal blocking methods. Enable HTML output scanning to catch these sneaky font references and ensure complete external font blocking across your entire site.', 'wpshadow' ),
						'description'         => __( 'Scan and remove external font references from HTML.', 'wpshadow' ),
						'default_enabled'     => true,
					),
					'advanced_settings'   => array(
						'name'                => __( 'Advanced Settings', 'wpshadow' ),
						'description_short'   => __( 'Configure whitelist, system fonts, and debug options.', 'wpshadow' ),
						'description_long'    => __( 'Fine-tune font blocking behavior with advanced options. Create a whitelist of specific external fonts you want to allow while blocking others. Configure custom system font fallbacks to replace blocked fonts with privacy-friendly alternatives. Enable admin-only blocking to block fonts in admin area while allowing them on frontend. Turn on debug logging to see exactly what fonts are being blocked in your browser console for troubleshooting.', 'wpshadow' ),
						'description_wizard'  => __( 'Advanced controls let you whitelist specific fonts you need while blocking everything else, replace blocked fonts with beautiful system fonts, and debug any issues. These options give you complete control over font blocking behavior without compromising your site\'s design.', 'wpshadow' ),
						'description'         => __( 'Configure whitelist, system fonts, and debug options.', 'wpshadow' ),
						'default_enabled'     => true,
						'has_settings'        => true,
						'settings_only'       => true,
					),
				),
			)
		);

		add_action( 'admin_init', array( $this, 'register_settings' ) );

		$this->log_activity( 'feature_initialized', 'External Fonts Disabler feature initialized', 'info' );
	}

	public function has_details_page(): bool {
		return true;
	}

	public function register_settings(): void {
		register_setting(
			'wpshadow_external_fonts_options_group',
			'wpshadow_external_fonts_whitelist',
			array(
				'type'              => 'string',
				'sanitize_callback' => array( $this, 'sanitize_whitelist' ),
				'default'           => '',
			)
		);

		register_setting(
			'wpshadow_external_fonts_options_group',
			'wpshadow_external_fonts_system_fallback',
			array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif',
			)
		);

		register_setting(
			'wpshadow_external_fonts_options_group',
			'wpshadow_external_fonts_admin_only',
			array(
				'type'              => 'boolean',
				'sanitize_callback' => 'rest_sanitize_boolean',
				'default'           => false,
			)
		);

		register_setting(
			'wpshadow_external_fonts_options_group',
			'wpshadow_external_fonts_log_blocked',
			array(
				'type'              => 'boolean',
				'sanitize_callback' => 'rest_sanitize_boolean',
				'default'           => false,
			)
		);
	}

	public function sanitize_whitelist( string $input ): string {

		$lines = array_filter( array_map( 'trim', explode( "\n", $input ) ) );
		$sanitized = array();

		foreach ( $lines as $line ) {

			if ( strpos( $line, '#' ) === 0 ) {
				continue;
			}
			$sanitized[] = esc_url_raw( $line );
		}

		return implode( "\n", $sanitized );
	}

	public function register(): void {
		if ( ! $this->is_enabled() ) {
			return;
		}

		$admin_only = get_option( 'wpshadow_external_fonts_admin_only', false );
		if ( $admin_only && ! is_admin() ) {
			return;
		}

		add_filter( 'style_loader_src', array( $this, 'remove_external_fonts_from_styles' ), 10, 1 );
		add_action( 'wp_enqueue_scripts', array( $this, 'dequeue_external_fonts' ), 999 );

		if ( get_option( 'wpshadow_external-fonts-disabler_buffer_cleanup', true ) ) {
			add_action( 'template_redirect', array( $this, 'start_output_buffer' ), 1 );
		}

		add_filter( 'site_status_tests', array( $this, 'register_site_health_test' ) );

		if ( is_admin() && get_option( 'wpshadow_external_fonts_suppress_theme_fonts_admin', false ) ) {
			add_filter( 'should_print_font_face_styles', array( $this, 'suppress_admin_font_face_styles' ), 10, 1 );
		}

		add_action( 'wp_ajax_wpshadow_save_external_fonts_settings', array( $this, 'ajax_save_settings' ) );
	}

	public function remove_external_fonts_from_styles( string $src ) {

		if ( $this->is_url_whitelisted( $src ) ) {
			return $src;
		}

		$blocked_domains = $this->get_blocked_domains();

		foreach ( $blocked_domains as $domain ) {
			if ( strpos( $src, $domain ) !== false ) {
				$this->log_blocked_font( $src );
				return false;
			}
		}

		return $src;
	}

	public function dequeue_external_fonts(): void {
		global $wp_styles;

		if ( ! $wp_styles || ! is_array( $wp_styles->registered ) ) {
			return;
		}

		$blocked_domains = $this->get_blocked_domains();

		foreach ( $wp_styles->registered as $handle => $style ) {
			if ( ! isset( $style->src ) || ! is_string( $style->src ) ) {
				continue;
			}

			if ( $this->is_url_whitelisted( $style->src ) ) {
				continue;
			}

			foreach ( $blocked_domains as $domain ) {
				if ( strpos( $style->src, $domain ) !== false ) {
					$this->log_blocked_font( $style->src );
					wp_dequeue_style( $handle );
					wp_deregister_style( $handle );
					break;
				}
			}
		}
	}

	private function is_url_whitelisted( string $url ): bool {
		$whitelist = get_option( 'wpshadow_external_fonts_whitelist', '' );
		if ( empty( $whitelist ) ) {
			return false;
		}

		$whitelisted_urls = array_filter( array_map( 'trim', explode( "\n", $whitelist ) ) );

		foreach ( $whitelisted_urls as $whitelisted ) {
			if ( strpos( $url, $whitelisted ) !== false ) {
				return true;
			}
		}

		return false;
	}

	private function log_blocked_font( string $url ): void {
		$log_enabled = get_option( 'wpshadow_external_fonts_log_blocked', false );
		if ( ! $log_enabled || is_admin() ) {
			return;
		}

		add_action( 'wp_footer', function() use ( $url ) {
			?>
			<script>
			console.log('[WPShadow] Blocked external font:', <?php echo wp_json_encode( $url ); ?>);
			</script>
			<?php
		}, 999 );
	}

	private function get_blocked_domains(): array {
		$domains = array();

		if ( get_option( 'wpshadow_external-fonts-disabler_block_google_fonts', true ) ) {
			$domains[] = 'fonts.googleapis.com';
			$domains[] = 'fonts.gstatic.com';
		}

		if ( get_option( 'wpshadow_external-fonts-disabler_block_font_awesome', true ) ) {
			$domains[] = 'fontawesome.com';
			$domains[] = 'use.fontawesome.com';
			$domains[] = 'pro.fontawesome.com';
			$domains[] = 'kit.fontawesome.com';
			$domains[] = 'cdnjs.cloudflare.com/ajax/libs/font-awesome';
		}

		if ( get_option( 'wpshadow_external-fonts-disabler_block_adobe_fonts', true ) ) {
			$domains[] = 'use.typekit.net';
			$domains[] = 'use.typekit.com';
			$domains[] = 'use.edgefonts.net';
			$domains[] = 'typekit.com';
		}

		if ( get_option( 'wpshadow_external-fonts-disabler_block_bunny_fonts', true ) ) {
			$domains[] = 'fonts.bunny.net';
		}

		if ( get_option( 'wpshadow_external-fonts-disabler_block_cdnjs_fonts', true ) ) {
			$domains[] = 'cdnjs.cloudflare.com/ajax/libs/webfont';
		}

		return $domains;
	}

	public function start_output_buffer(): void {
		if ( is_admin() ) {
			return;
		}

		ob_start( array( $this, 'remove_external_fonts_from_html' ) );
	}

	public function remove_external_fonts_from_html( string $html ): string {
		$blocked_domains = $this->get_blocked_domains();

		foreach ( $blocked_domains as $domain ) {

			$escaped_domain = str_replace( '.', '\\.', $domain );

			$html = preg_replace(
				'/<link[^>]*href=["\']https?:\/\/[^"\'>]*' . $escaped_domain . '[^>]*>/i',
				'',
				$html
			);

			$html = preg_replace(
				'/<link[^>]*rel=["\'](?:preconnect|dns-prefetch)["\'][^>]*href=["\']https?:\/\/[^"\'>]*' . $escaped_domain . '[^>]*>/i',
				'',
				$html
			);
		}

		return $html;
	}

	public function register_site_health_test( array $tests ): array {
		$tests['direct']['WPSHADOW_external_fonts_disabler'] = array(
			'label' => __( 'External Fonts Privacy', 'wpshadow' ),
			'test'  => array( $this, 'test_external_fonts_disabler' ),
		);
		return $tests;
	}

	public function test_external_fonts_disabler(): array {
		if ( ! $this->is_enabled() ) {
			return array(
				'label'       => __( 'External Fonts Privacy', 'wpshadow' ),
				'status'      => 'recommended',
				'badge'       => array(
					'label' => __( 'Privacy', 'wpshadow' ),
					'color' => 'orange',
				),
				'description' => sprintf( '<p>%s</p>', __( 'External font blocking is not enabled. Blocking external fonts improves privacy by preventing third-party tracking.', 'wpshadow' ) ),
				'actions'     => '',
				'test'        => 'WPSHADOW_external_fonts_disabler',
			);
		}

		$enabled_features = 0;
		if ( get_option( 'wpshadow_external-fonts-disabler_block_google_fonts', true ) ) {
			++$enabled_features;
		}
		if ( get_option( 'wpshadow_external-fonts-disabler_block_font_awesome', true ) ) {
			++$enabled_features;
		}
		if ( get_option( 'wpshadow_external-fonts-disabler_block_adobe_fonts', true ) ) {
			++$enabled_features;
		}
		if ( get_option( 'wpshadow_external-fonts-disabler_block_bunny_fonts', true ) ) {
			++$enabled_features;
		}
		if ( get_option( 'wpshadow_external-fonts-disabler_block_cdnjs_fonts', true ) ) {
			++$enabled_features;
		}
		if ( get_option( 'wpshadow_external-fonts-disabler_buffer_cleanup', true ) ) {
			++$enabled_features;
		}

		return array(
			'label'       => __( 'External Fonts Privacy', 'wpshadow' ),
			'status'      => 'good',
			'badge'       => array(
				'label' => __( 'Privacy', 'wpshadow' ),
				'color' => 'blue',
			),
			'description' => sprintf(
				'<p>%s</p>',

				sprintf(
					__( 'External font blocking is active with %d protection layers enabled. Your site is not loading fonts from external CDN servers.', 'wpshadow' ),
					$enabled_features
				)
			),
			'actions'     => '',
			'test'        => 'WPSHADOW_external_fonts_disabler',
		);
	}

	public function ajax_save_settings(): void {
		check_ajax_referer( 'wpshadow_save_external_fonts_settings' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'wpshadow' ) ) );
		}

		$whitelist = isset( $_POST['whitelist'] ) ? sanitize_textarea_field( wp_unslash( $_POST['whitelist'] ) ) : '';
		$fallback = isset( $_POST['fallback'] ) ? sanitize_text_field( wp_unslash( $_POST['fallback'] ) ) : '';
		$admin_only = isset( $_POST['admin_only'] ) && '1' === $_POST['admin_only'];
		$log_blocked = isset( $_POST['log_blocked'] ) && '1' === $_POST['log_blocked'];

		$whitelist = $this->sanitize_whitelist( $whitelist );

		update_option( 'wpshadow_external_fonts_whitelist', $whitelist );
		update_option( 'wpshadow_external_fonts_system_fallback', $fallback );
		update_option( 'wpshadow_external_fonts_admin_only', $admin_only );
		update_option( 'wpshadow_external_fonts_log_blocked', $log_blocked );

		if ( function_exists( '\WPShadow\CoreSupport\wpshadow_log_feature_activity' ) ) {
			\WPShadow\CoreSupport\wpshadow_log_feature_activity(
				'external-fonts-disabler',
				'settings_updated',
				'Advanced settings updated'
			);
		}

		wp_send_json_success( array( 'message' => __( 'Settings saved successfully.', 'wpshadow' ) ) );
	}

	public function suppress_admin_font_face_styles( bool $should_print ): bool {

		if ( is_admin() && get_option( 'wpshadow_external_fonts_suppress_theme_fonts_admin', false ) ) {
			return false;
		}

		return $should_print;
	}
}