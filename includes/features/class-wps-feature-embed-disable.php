<?php declare(strict_types=1);
/**
 * Feature: Embed Script Disabling & Optimization
 *
 * Disable WordPress embed functionality (wp-embed.js) for performance.
 *
 * @package WPShadow\CoreSupport
 * @since 1.2601.75001
 */

namespace WPShadow\CoreSupport;

final class WPSHADOW_Feature_Embed_Disable extends WPSHADOW_Abstract_Feature {

	public function __construct() {
		parent::__construct( array(
			'id'          => 'embed-disable',
			'name'        => __( 'Stop Extra Embed Code', 'wpshadow' ),
			'description' => __( 'Remove code that lets people embed your posts elsewhere. Makes your site load faster if you don\'t need this feature.', 'wpshadow' ),
			'aliases'     => array( 'oembed', 'wp-embed', 'embed disable', 'embed cleanup', 'oembed disable', 'remove embeds', 'embed optimization', 'embed script', 'embed performance', 'disable oembed', 'embed links', 'rest oembed' ),
			'sub_features' => array(
				'disable_embed_script' => __( 'Remove embed loading code', 'wpshadow' ),
				'remove_oembed_links'  => __( 'Hide embed discovery links', 'wpshadow' ),
				'disable_rest_oembed'  => __( 'Turn off embed connections', 'wpshadow' ),
				'remove_embed_rewrite' => __( 'Remove embed URL patterns', 'wpshadow' ),
			),
		) );

		$this->register_default_settings( array(
			'disable_embed_script' => true,
			'remove_oembed_links'  => true,
			'disable_rest_oembed'  => false,
			'remove_embed_rewrite' => true,
		) );
	}

	public function register(): void {
		if ( ! $this->is_enabled() ) {
			return;
		}

		add_action( 'init', array( $this, 'disable_embeds' ) );
		add_filter( 'site_status_tests', array( $this, 'register_site_health_test' ) );

		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			\WP_CLI::add_command( 'wpshadow embed-disable', array( $this, 'handle_cli_command' ) );
		}
	}

	/**
	 * Disable embed functionality.
	 */
	public function disable_embeds(): void {
		$removed = array();

		// Disable wp-embed.js on frontend
		if ( $this->is_sub_feature_enabled( 'disable_embed_script', true ) ) {
			if ( ! is_admin() ) {
				wp_deregister_script( 'wp-embed' );
				$removed[] = 'embed_script';
			}
		}

		// Remove oEmbed discovery links
		if ( $this->is_sub_feature_enabled( 'remove_oembed_links', true ) ) {
			remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
			remove_action( 'wp_head', 'wp_oembed_add_host_js' );
			$removed[] = 'oembed_links';
		}

		// Disable REST oEmbed endpoint
		if ( $this->is_sub_feature_enabled( 'disable_rest_oembed', false ) ) {
			add_filter(
				'rest_endpoints',
				static function ( $endpoints ) use ( &$removed ) {
					unset( $endpoints['/oembed/1.0/embed'] );
					$removed[] = 'rest_endpoint';
					return $endpoints;
				}
			);
		}

		// Remove embed rewrite rules
		if ( $this->is_sub_feature_enabled( 'remove_embed_rewrite', true ) ) {
			add_filter(
				'rewrite_rules_array',
				static function ( $rules ) use ( &$removed ) {
					foreach ( $rules as $rule => $rewrite ) {
						if ( is_string( $rewrite ) && strpos( $rewrite, 'embed=true' ) !== false ) {
							unset( $rules[ $rule ] );
							$removed[] = 'rewrite_rule';
						}
					}
					return $rules;
				}
			);
		}

		if ( ! empty( $removed ) ) {
			do_action( 'wpshadow_embed_disable_applied', array_unique( $removed ) );
		}
	}

	public function register_site_health_test( array $tests ): array {
		$tests['direct']['embed_disable'] = array(
			'label'  => __( 'Embed Optimization', 'wpshadow' ),
			'test'   => array( $this, 'test_embeds' ),
		);

		return $tests;
	}

	public function test_embeds(): array {
		if ( ! $this->is_enabled() ) {
			return array(
				'label'       => __( 'Embed Optimization', 'wpshadow' ),
				'status'      => 'recommended',
				'badge'       => array( 'label' => __( 'WPShadow', 'wpshadow' ) ),
				'description' => __( 'Enable embed disabling to improve performance.', 'wpshadow' ),
				'actions'     => '',
				'test'        => 'embed_disable',
			);
		}

		$enabled_count = 0;
		$subs = array( 'disable_embed_script', 'remove_oembed_links', 'disable_rest_oembed', 'remove_embed_rewrite' );
		foreach ( $subs as $sub ) {
			if ( $this->is_sub_feature_enabled( $sub, false ) ) {
				$enabled_count++;
			}
		}

		return array(
			'label'       => __( 'Embed Optimization', 'wpshadow' ),
			'status'      => 'good',
			'badge'       => array( 'label' => __( 'WPShadow', 'wpshadow' ) ),
			'description' => sprintf(
				__( '%d of 4 optimizations enabled.', 'wpshadow' ),
				$enabled_count
			),
			'actions'     => '',
			'test'        => 'embed_disable',
		);
	}

	/**
	 * Handle WP-CLI command for embed disable.
	 *
	 * @param array $args       Positional args.
	 * @param array $assoc_args Named args (unused).
	 *
	 * @return void
	 */
	public function handle_cli_command( array $args, array $assoc_args ): void {
		$action = $args[0] ?? 'status';

		if ( 'status' !== $action ) {
			\WP_CLI::error( __( 'Unknown subcommand. Try: wp wpshadow embed-disable status', 'wpshadow' ) );
			return;
		}

		\WP_CLI::log( __( 'Embed Disable status:', 'wpshadow' ) );
		\WP_CLI::log( sprintf( '  %s: %s', __( 'Feature enabled', 'wpshadow' ), $this->is_enabled() ? 'yes' : 'no' ) );

		$subs = array(
			'disable_embed_script',
			'remove_oembed_links',
			'disable_rest_oembed',
			'remove_embed_rewrite',
		);

		foreach ( $subs as $sub ) {
			$enabled = $this->is_sub_feature_enabled( $sub, false );
			\WP_CLI::log( sprintf( '  - %s: %s', $sub, $enabled ? 'on' : 'off' ) );
		}

		\WP_CLI::success( __( 'Embed disable inspected.', 'wpshadow' ) );
	}
}
