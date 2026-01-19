<?php declare(strict_types=1);
/**
 * Feature: DNS Prefetch & Resource Hints Management
 *
 * Control DNS prefetch and resource hints in page head.
 *
 * @package WPShadow\CoreSupport
 * @since 1.2601.75001
 */

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


final class WPSHADOW_Feature_Resource_Hints extends WPSHADOW_Abstract_Feature {

	public function __construct() {
		parent::__construct( array(
			'id'          => 'resource-hints',
			'name'        => __( 'Prepare External Connections', 'wpshadow' ),
			'description' => __( 'Start connecting to fonts, scripts, and services early so they load faster when needed.', 'wpshadow' ),
			'aliases'     => array( 'dns prefetch', 'preconnect', 'preload', 'resource hints', 'dns optimization', 'early hints', 'prefetch', 'preload fonts', 'preload scripts', 'connection optimization', 'link headers', 'performance hints' ),
			'sub_features' => array(
				'dns_prefetch'      => array(
					'name'               => __( 'DNS Prefetch', 'wpshadow' ),
					'description_short'  => __( 'Start resolving external domains early', 'wpshadow' ),
					'description_long'   => __( 'Tells browsers to start DNS lookups early for external domains your site uses (like CDNs, analytics, fonts). DNS resolution adds latency before content can load. By prefetching early, the domain is already resolved when content actually loads, reducing delays. Safe for any external domain you use.', 'wpshadow' ),
					'description_wizard' => __( 'Speed up external connections by resolving domain names early. Recommended for sites using external services and CDNs.', 'wpshadow' ),
					'default_enabled'    => true,
				),
				'preconnect'        => array(
					'name'               => __( 'Preconnect', 'wpshadow' ),
					'description_short'  => __( 'Establish connections to external services early', 'wpshadow' ),
					'description_long'   => __( 'Preconnects to important external domains like CDNs and font services by establishing full connections (DNS lookup, TCP handshake, TLS negotiation) before content loads. Much more powerful than DNS prefetch. Use sparingly for only the most important external resources to avoid slowing down other connections.', 'wpshadow' ),
					'description_wizard' => __( 'Establish connections to critical external services early. Use carefully - preconnecting uses resources. Best for your main CDN or font service.', 'wpshadow' ),
					'default_enabled'    => true,
				),
				'preload_fonts'     => array(
					'name'               => __( 'Preload Fonts', 'wpshadow' ),
					'description_short'  => __( 'Start loading fonts before they\'re needed', 'wpshadow' ),
					'description_long'   => __( 'Tells browsers to start loading important web fonts early before the CSS that references them is parsed. Fonts are critical for rendering text, so preloading them reduces text rendering delays and makes the page appear to load faster. Only preload fonts actually used on the page.', 'wpshadow' ),
					'description_wizard' => __( 'Speed up font loading so text appears sooner. Particularly helpful for custom fonts that affect page rendering time.', 'wpshadow' ),
					'default_enabled'    => false,
				),
				'preload_scripts'   => array(
					'name'               => __( 'Preload Scripts', 'wpshadow' ),
					'description_short'  => __( 'Start loading critical scripts early', 'wpshadow' ),
					'description_long'   => __( 'Tells browsers to start loading critical JavaScript files early. Useful for large scripts that are essential to page functionality and you want loaded as soon as possible. Disabled by default - only enable for truly critical scripts.', 'wpshadow' ),
					'description_wizard' => __( 'Load critical JavaScript early if it\'s important for core functionality. Use sparingly for best results.', 'wpshadow' ),
					'default_enabled'    => false,
				),
				'remove_s_w_org'    => array(
					'name'               => __( 'Remove WordPress.org DNS Prefetch', 'wpshadow' ),
					'description_short'  => __( 'Stop prefetch to WordPress.org', 'wpshadow' ),
					'description_long'   => __( 'Removes DNS prefetch for s.w.org that WordPress adds by default. This assumes your site will load resources from WordPress.org, but most sites don\'t. Removing it saves a DNS lookup and reduces external dependencies. Safe for any WordPress site that doesn\'t load critical resources from WordPress.org.', 'wpshadow' ),
					'description_wizard' => __( 'Most sites don\'t need WordPress.org resources. Remove this prefetch to save a DNS lookup.', 'wpshadow' ),
					'default_enabled'    => true,
				),
			),
		) );

		$this->register_default_settings( array(
			'dns_prefetch'      => true,
			'preconnect'        => true,
			'preload_fonts'     => false,
			'preload_scripts'   => false,
			'remove_s_w_org'    => true,
		) );
	}

	public function register(): void {
		if ( ! $this->is_enabled() ) {
			return;
		}

		add_filter( 'wp_resource_hints', array( $this, 'filter_resource_hints' ), 10, 2 );
		add_action( 'wp_head', array( $this, 'add_preload_headers' ), 2 );
		add_filter( 'site_status_tests', array( $this, 'register_site_health_test' ) );

		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			\WP_CLI::add_command( 'wpshadow resource-hints', array( $this, 'handle_cli_command' ) );
		}
	}

	/**
	 * Filter resource hints.
	 */
	public function filter_resource_hints( array $urls, string $relation_type ): array {
		// Handle DNS prefetch.
		if ( 'dns-prefetch' === $relation_type ) {
			// Remove WordPress.org DNS prefetch.
			if ( $this->is_sub_feature_enabled( 'remove_s_w_org', true ) ) {
				$urls = array_diff(
					$urls,
					array( 'https://s.w.org', '//s.w.org', 'http://s.w.org' )
				);
			}

			// Add custom DNS prefetch hints.
			if ( $this->is_sub_feature_enabled( 'dns_prefetch', true ) ) {
				$custom_hints = $this->get_setting( 'custom_hints', array() );
				if ( ! empty( $custom_hints ) ) {
					$urls = array_merge( $urls, (array) $custom_hints );
					$urls = array_unique( $urls );
				}
			}
		}

		// Handle preconnect.
		if ( 'preconnect' === $relation_type && $this->is_sub_feature_enabled( 'preconnect', true ) ) {
			// Add common CDN and service preconnects.
			$preconnects = array(
				'https://fonts.googleapis.com',
				'https://fonts.gstatic.com',
			);

			// Allow filtering.
			$preconnects = apply_filters( 'wpshadow_preconnect_urls', $preconnects );
			$urls = array_merge( $urls, $preconnects );
			$urls = array_unique( $urls );
		}

		do_action( 'wpshadow_resource_hints_filtered', $urls, $relation_type );

		return $urls;
	}

	/**
	 * Add preload headers.
	 */
	public function add_preload_headers(): void {
		// Auto-preload fonts if enabled.
		if ( $this->is_sub_feature_enabled( 'preload_fonts', false ) ) {
			$this->preload_theme_fonts();
		}

		// Auto-preload scripts if enabled.
		if ( $this->is_sub_feature_enabled( 'preload_scripts', false ) ) {
			$this->preload_critical_scripts();
		}

		// Custom preload resources.
		$preload_resources = $this->get_setting( 'preload_resources', array() );
		$preload_resources = apply_filters( 'wpshadow_preload_resources', $preload_resources );

		if ( ! is_array( $preload_resources ) || empty( $preload_resources ) ) {
			return;
		}

		foreach ( $preload_resources as $resource ) {
			if ( ! is_array( $resource ) || empty( $resource['url'] ) || empty( $resource['type'] ) ) {
				continue;
			}

			$url  = esc_url( $resource['url'] );
			$type = sanitize_key( $resource['type'] );
			$attrs = sprintf( 'rel="preload" href="%s" as="%s"', $url, $type );

			if ( 'font' === $type ) {
				$mime_type = $resource['mime_type'] ?? 'font/woff2';
				$attrs .= sprintf( ' type="%s" crossorigin', esc_attr( $mime_type ) );
			}

			if ( 'style' === $type && ! empty( $resource['media'] ) ) {
				$attrs .= sprintf( ' media="%s"', esc_attr( $resource['media'] ) );
			}

			echo '<link ' . $attrs . '>' . "\n";
			do_action( 'wpshadow_resource_preload_output', $resource );
		}
	}

	/**
	 * Preload theme fonts.
	 */
	private function preload_theme_fonts(): void {
		// This would scan theme CSS for font-face declarations.
		// Simplified implementation - allows filtering.
		$fonts = apply_filters( 'wpshadow_preload_fonts', array() );

		foreach ( $fonts as $font_url ) {
			if ( ! empty( $font_url ) ) {
				echo sprintf(
					'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
					esc_url( $font_url )
				);
				do_action( 'wpshadow_resource_font_preload', $font_url );
			}
		}
	}

	/**
	 * Preload critical scripts.
	 */
	private function preload_critical_scripts(): void {
		// Simplified implementation - allows filtering.
		$scripts = apply_filters( 'wpshadow_preload_scripts', array() );

		foreach ( $scripts as $script_url ) {
			if ( ! empty( $script_url ) ) {
				echo sprintf(
					'<link rel="preload" href="%s" as="script">' . "\n",
					esc_url( $script_url )
				);
				do_action( 'wpshadow_resource_script_preload', $script_url );
			}
		}
	}

	/**
	 * Handle WP-CLI command for resource hints.
	 *
	 * @param array $args       Positional arguments.
	 * @param array $assoc_args Associative arguments.
	 *
	 * @return void
	 */
	public function handle_cli_command( array $args, array $assoc_args ): void {
		$action = $args[0] ?? 'status';

		if ( 'status' !== $action ) {
			\WP_CLI::error( __( 'Unknown subcommand. Try: wp wpshadow resource-hints status', 'wpshadow' ) );
			return;
		}

		\WP_CLI::log( __( 'Resource Hints status:', 'wpshadow' ) );
		\WP_CLI::log( sprintf( '  %s: %s', __( 'Feature enabled', 'wpshadow' ), $this->is_enabled() ? 'yes' : 'no' ) );

		$subs = array(
			'dns_prefetch',
			'preconnect',
			'preload_fonts',
			'preload_scripts',
			'remove_s_w_org',
		);

		foreach ( $subs as $sub ) {
			$enabled = $this->is_sub_feature_enabled( $sub, false );
			\WP_CLI::log( sprintf( '  - %s: %s', $sub, $enabled ? 'on' : 'off' ) );
		}

		$custom_hints   = $this->get_setting( 'custom_hints', array() );
		$preload_custom = $this->get_setting( 'preload_resources', array() );

		\WP_CLI::log( sprintf( '  %s: %d', __( 'Custom hints', 'wpshadow' ), is_array( $custom_hints ) ? count( $custom_hints ) : 0 ) );
		\WP_CLI::log( sprintf( '  %s: %d', __( 'Custom preloads', 'wpshadow' ), is_array( $preload_custom ) ? count( $preload_custom ) : 0 ) );
		\WP_CLI::success( __( 'Resource hints inspected.', 'wpshadow' ) );
	}

	public function register_site_health_test( array $tests ): array {
		$tests['direct']['resource_hints'] = array(
			'label'  => __( 'Resource Hints', 'wpshadow' ),
			'test'   => array( $this, 'test_hints' ),
		);

		return $tests;
	}

	public function test_hints(): array {
		if ( ! $this->is_enabled() ) {
			return array(
				'label'       => __( 'Resource Hints', 'wpshadow' ),
				'status'      => 'recommended',
				'badge'       => array( 'label' => __( 'WPShadow', 'wpshadow' ) ),
				'description' => __( 'Enable resource hints for performance.', 'wpshadow' ),
				'actions'     => '',
				'test'        => 'resource_hints',
			);
		}

		$enabled_count = 0;
		$subs = array( 'dns_prefetch', 'preconnect', 'preload_fonts', 'preload_scripts', 'remove_s_w_org' );
		foreach ( $subs as $sub ) {
			if ( $this->is_sub_feature_enabled( $sub, false ) ) {
				$enabled_count++;
			}
		}

		$custom_hints = $this->get_setting( 'custom_hints', array() );
		$custom_count = is_array( $custom_hints ) ? count( $custom_hints ) : 0;

		return array(
			'label'       => __( 'Resource Hints', 'wpshadow' ),
			'status'      => 'good',
			'badge'       => array( 'label' => __( 'WPShadow', 'wpshadow' ) ),
			'description' => sprintf(
				__( '%d features enabled, %d custom hints.', 'wpshadow' ),
				$enabled_count,
				$custom_count
			),
			'actions'     => '',
			'test'        => 'resource_hints',
		);
	}
}
