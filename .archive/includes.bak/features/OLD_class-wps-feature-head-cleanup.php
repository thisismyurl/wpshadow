<?php

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class WPSHADOW_Feature_Head_Cleanup extends WPSHADOW_Abstract_Feature {

	public function __construct() {
		parent::__construct(
			array(
				'id'              => 'head-cleanup',
				'name'            => __( 'Remove Unnecessary Page Code', 'wpshadow' ),
				'description'     => __( 'Clean up your page headers - remove clutter that slows you down and reveals too much about your site.', 'wpshadow' ),
				'scope'           => 'core',
				'default_enabled' => true,
				'version'         => '1.0.0',
				'widget_group'    => 'performance',
				'aliases'         => array( 'remove emoji', 'wp head', 'version hiding', 'security hardening', 'xmlrpc disable', 'rsd link', 'generator tag', 'head optimization', 'remove feeds', 'rest api', 'oembed', 'shortlink' ),
				'sub_features'    => array(
					'remove_emoji'          => array(
						'name'               => __( 'Emoji Code Removal', 'wpshadow' ),
						'description_short'  => __( 'Remove emoji detection script to reduce page size', 'wpshadow' ),
						'description_long'   => __( 'Removes WordPress\'s automatic emoji detection script that loads on every page. While convenient for sites that use emojis, this script adds unnecessary bytes to pages that don\'t need it. Disabling it saves 2-5KB per page load and improves performance, especially on mobile connections. You can still use emojis by typing them directly.', 'wpshadow' ),
						'description_wizard' => __( 'Removes the emoji detection script that WordPress loads by default. Most sites don\'t need this, and disabling it improves page speed. Enable to make your site faster and lighter.', 'wpshadow' ),
						'default_enabled'    => true,
					),
					'remove_generator'      => array(
						'name'               => __( 'WordPress Version Hiding', 'wpshadow' ),
						'description_short'  => __( 'Hide WordPress version number from visitors', 'wpshadow' ),
						'description_long'   => __( 'Removes the WordPress version meta tag that advertises which version of WordPress powers your site. This meta tag is visible in page source code and HTTP headers, helping attackers identify vulnerable installations. By hiding it, you make your site less of a target for automated attacks. This doesn\'t prevent the version from being discovered through other means, but it eliminates the easy way.', 'wpshadow' ),
						'description_wizard' => __( 'WordPress version numbers help hackers find sites running old, vulnerable versions. Hide yours to reduce your attack surface. This is basic security hardening recommended by all WordPress security guides.', 'wpshadow' ),
						'default_enabled'    => true,
					),
					'remove_shortlink'      => array(
						'name'               => __( 'Remove Short Link Tag', 'wpshadow' ),
						'description_short'  => __( 'Remove WordPress short URL link tag', 'wpshadow' ),
						'description_long'   => __( 'Removes the WordPress short URL link tag from page heads. This tag provides WordPress\'s built-in short URL feature, but most sites use third-party URL shorteners like Bit.ly if they need short links. Removing this unused link tag reduces HTML bloat and has no impact on site functionality.', 'wpshadow' ),
						'description_wizard' => __( 'Most sites don\'t use WordPress\'s built-in short URL feature. This removes the unused link tag from your page code, making your pages slightly smaller and cleaner.', 'wpshadow' ),
						'default_enabled'    => true,
					),
					'remove_rsd'            => array(
						'name'               => __( 'Remove Really Simple Discovery Link', 'wpshadow' ),
						'description_short'  => __( 'Remove RSD link for old blogging tools', 'wpshadow' ),
						'description_long'   => __( 'Removes the Really Simple Discovery (RSD) link that enables old desktop blogging applications like Windows Live Writer to discover your blog\'s capabilities. These tools are deprecated and rarely used today, making this link obsolete for most sites. Removing it reduces HTML bloat with zero impact on functionality.', 'wpshadow' ),
						'description_wizard' => __( 'RSD is for old blogging tools that nobody uses anymore. Safe to remove unless you\'re using legacy desktop blogging software.', 'wpshadow' ),
						'default_enabled'    => true,
					),
					'remove_wlw'            => array(
						'name'               => __( 'Remove Windows Live Writer Link', 'wpshadow' ),
						'description_short'  => __( 'Remove Windows Live Writer manifest link', 'wpshadow' ),
						'description_long'   => __( 'Removes the Windows Live Writer manifest link that enables Windows Live Writer blogging tool integration. Windows Live Writer was discontinued by Microsoft over a decade ago. This link has no purpose for modern sites and removing it reduces unnecessary HTML markup.', 'wpshadow' ),
						'description_wizard' => __( 'Windows Live Writer was discontinued years ago. Safe to remove unless you\'re using an extremely old blogging setup.', 'wpshadow' ),
						'default_enabled'    => true,
					),
					'remove_rest_link'      => array(
						'name'               => __( 'Remove REST API Link', 'wpshadow' ),
						'description_short'  => __( 'Remove connection link for REST API', 'wpshadow' ),
						'description_long'   => __( 'Removes the WordPress REST API discovery link from page heads. The REST API is essential for WordPress mobile apps and block editor functionality. Disabling this may break official WordPress apps or cause issues with modern page builders. Only disable if you have a specific reason and don\'t use mobile apps.', 'wpshadow' ),
						'description_wizard' => __( 'Warning: Disabling this may break WordPress mobile apps and the block editor. Only disable if you don\'t use these features and understand the consequences.', 'wpshadow' ),
						'default_enabled'    => false,
					),
					'remove_oembed'         => array(
						'name'               => __( 'Remove Embed Discovery', 'wpshadow' ),
						'description_short'  => __( 'Remove embed discovery and wp-embed script', 'wpshadow' ),
						'description_long'   => __( 'Removes WordPress oEmbed discovery links and the wp-embed.js script that enables embedding WordPress posts on other sites. If your content is frequently embedded elsewhere, keep this enabled. If you don\'t use this feature, disabling saves 2-3KB per page. This also blocks other sites from embedding your posts, which some prefer for privacy reasons.', 'wpshadow' ),
						'description_wizard' => __( 'Disables the feature that lets other sites embed your posts. Saves bandwidth and improves privacy if you don\'t want your content embedded elsewhere.', 'wpshadow' ),
						'default_enabled'    => true,
					),
					'remove_feeds'          => array(
						'name'               => __( 'Remove Feed Links', 'wpshadow' ),
						'description_short'  => __( 'Remove RSS feed discovery links', 'wpshadow' ),
						'description_long'   => __( 'Removes WordPress RSS feed discovery links from page heads. Feed readers and aggregators use these links to find your blog feeds. If you actively promote your RSS feed or use feed-based distribution, keep this enabled. If you don\'t use RSS, disabling saves a tiny amount of HTML and reduces unnecessary link references.', 'wpshadow' ),
						'description_wizard' => __( 'Only disable if you don\'t care about RSS readers accessing your feed. Most sites benefit from keeping this enabled for feed subscribers.', 'wpshadow' ),
						'default_enabled'    => false,
					),
					'remove_comments_style' => array(
						'name'               => __( 'Remove Comments Styling', 'wpshadow' ),
						'description_short'  => __( 'Remove recent comments widget CSS', 'wpshadow' ),
						'description_long'   => __( 'Removes the CSS styling that WordPress loads for the Recent Comments widget. If you don\'t use the Recent Comments widget, this CSS is unused bloat. If you do use this widget and rely on WordPress styling, keep this disabled. Most modern themes style comments themselves anyway.', 'wpshadow' ),
						'description_wizard' => __( 'Only remove if you don\'t use the Recent Comments widget. Most themes handle comment styling themselves.', 'wpshadow' ),
						'default_enabled'    => true,
					),
					'disable_xmlrpc'        => array(
						'name'               => __( 'Disable XML-RPC', 'wpshadow' ),
						'description_short'  => __( 'Disable old remote access protocol', 'wpshadow' ),
						'description_long'   => __( 'Disables XML-RPC, an old protocol that allowed remote applications to interact with WordPress before the REST API existed. XML-RPC is a major attack vector for brute force login attempts and is rarely needed for modern WordPress setups. Disabling it significantly improves security. Most WordPress apps use REST API now, so disabling this has minimal impact.', 'wpshadow' ),
						'description_wizard' => __( 'XML-RPC is an old, security-vulnerable protocol that opens your site to brute force attacks. Modern WordPress apps use REST API instead. Disable this for better security with no downside for most sites.', 'wpshadow' ),
						'default_enabled'    => true,
					),
				),
			)
		);

		$this->register_default_settings(
			array(
				'remove_emoji'          => true,
				'remove_generator'      => true,
				'remove_shortlink'      => true,
				'remove_rsd'            => true,
				'remove_wlw'            => true,
				'remove_rest_link'      => false,
				'remove_oembed'         => true,
				'remove_feeds'          => false,
				'remove_comments_style' => true,
				'disable_xmlrpc'        => true,
			)
		);
	}

	public function register(): void {
		if ( ! $this->is_enabled() ) {
			return;
		}

		add_action( 'init', array( $this, 'apply_head_cleanup' ) );
		add_filter( 'site_status_tests', array( $this, 'register_site_health_tests' ) );

		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			\WP_CLI::add_command( 'wpshadow head-cleanup', array( $this, 'handle_cli_command' ) );
		}
	}

	public function apply_head_cleanup(): void {
		$removed = array();

		if ( $this->is_sub_feature_enabled( 'remove_emoji', true ) ) {
			remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
			remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
			remove_action( 'wp_print_styles', 'print_emoji_styles' );
			remove_action( 'admin_print_styles', 'print_emoji_styles' );
			$removed[] = 'emoji';
		}

		if ( $this->is_sub_feature_enabled( 'remove_generator', true ) ) {
			remove_action( 'wp_head', 'wp_generator' );
			add_filter( 'the_generator', '__return_false' );
			$removed[] = 'generator';
		}

		if ( $this->is_sub_feature_enabled( 'remove_shortlink', true ) ) {
			remove_action( 'wp_head', 'wp_shortlink_wp_head' );
			$removed[] = 'shortlink';
		}

		if ( $this->is_sub_feature_enabled( 'remove_rsd', true ) ) {
			remove_action( 'wp_head', 'rsd_link' );
			$removed[] = 'rsd';
		}

		if ( $this->is_sub_feature_enabled( 'remove_wlw', true ) ) {
			remove_action( 'wp_head', 'wlwmanifest_link' );
			$removed[] = 'wlw';
		}

		if ( $this->is_sub_feature_enabled( 'remove_rest_link', false ) ) {
			remove_action( 'wp_head', 'rest_output_link_wp_head', 10 );
			$removed[] = 'rest_link';
		}

		if ( $this->is_sub_feature_enabled( 'remove_oembed', true ) ) {
			remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
			remove_action( 'wp_head', 'wp_oembed_add_host_js' );
			$removed[] = 'oembed';
		}

		if ( $this->is_sub_feature_enabled( 'remove_feeds', false ) ) {
			remove_action( 'wp_head', 'feed_links_extra', 3 );
			remove_action( 'wp_head', 'feed_links', 2 );
			$removed[] = 'feeds';
		}

		if ( $this->is_sub_feature_enabled( 'remove_comments_style', true ) ) {
			global $wp_widget_factory;
			if ( isset( $wp_widget_factory->widgets['WP_Widget_Recent_Comments'] ) ) {
				remove_action( 'wp_head', array( $wp_widget_factory->widgets['WP_Widget_Recent_Comments'], 'recent_comments_style' ) );
			}
			$removed[] = 'comments_style';
		}

		if ( $this->is_sub_feature_enabled( 'disable_xmlrpc', true ) ) {
			add_filter( 'xmlrpc_enabled', '__return_false' );
			$removed[] = 'xmlrpc';
		}

		if ( ! empty( $removed ) ) {
			do_action( 'wpshadow_head_cleanup_applied', array_unique( $removed ) );
		}
	}

	public function register_site_health_tests( array $tests ): array {
		$tests['direct']['head_cleanup_emoji'] = array(
			'label' => __( 'Emoji Scripts', 'wpshadow' ),
			'test'  => array( $this, 'test_emoji_removal' ),
		);

		$tests['direct']['head_cleanup_xmlrpc'] = array(
			'label' => __( 'XML-RPC Security', 'wpshadow' ),
			'test'  => array( $this, 'test_xmlrpc_disabled' ),
		);

		$tests['direct']['head_cleanup_oembed'] = array(
			'label' => __( 'oEmbed Links', 'wpshadow' ),
			'test'  => array( $this, 'test_oembed_removal' ),
		);

		return $tests;
	}

	public function test_emoji_removal(): array {
		if ( ! $this->is_enabled() ) {
			return array(
				'label'       => __( 'Emoji Scripts', 'wpshadow' ),
				'status'      => 'recommended',
				'badge'       => array(
					'label' => __( 'Performance', 'wpshadow' ),
					'color' => 'blue',
				),
				'description' => __( 'Head cleanup is disabled.', 'wpshadow' ),
				'test'        => 'head_cleanup_emoji',
			);
		}

		$is_removed = $this->is_sub_feature_enabled( 'remove_emoji', true );

		if ( $is_removed ) {
			return array(
				'label'       => __( 'Emoji scripts are disabled', 'wpshadow' ),
				'status'      => 'good',
				'badge'       => array(
					'label' => __( 'Performance', 'wpshadow' ),
					'color' => 'blue',
				),
				'description' => __( 'WordPress emoji detection scripts and styles have been removed, improving page load performance.', 'wpshadow' ),
				'test'        => 'head_cleanup_emoji',
			);
		}

		return array(
			'label'       => __( 'Emoji scripts are still loading', 'wpshadow' ),
			'status'      => 'recommended',
			'badge'       => array(
				'label' => __( 'Performance', 'wpshadow' ),
				'color' => 'orange',
			),
			'description' => __( 'WordPress is loading emoji detection scripts on every page. Modern browsers support emojis natively, so these scripts are unnecessary.', 'wpshadow' ),
			'test'        => 'head_cleanup_emoji',
		);
	}

	public function test_xmlrpc_disabled(): array {
		if ( ! $this->is_enabled() ) {
			return array(
				'label'       => __( 'XML-RPC', 'wpshadow' ),
				'status'      => 'recommended',
				'badge'       => array(
					'label' => __( 'Security', 'wpshadow' ),
					'color' => 'blue',
				),
				'description' => __( 'Head cleanup is disabled.', 'wpshadow' ),
				'test'        => 'head_cleanup_xmlrpc',
			);
		}

		$is_disabled = $this->is_sub_feature_enabled( 'disable_xmlrpc', true );

		if ( $is_disabled ) {
			return array(
				'label'       => __( 'XML-RPC is disabled', 'wpshadow' ),
				'status'      => 'good',
				'badge'       => array(
					'label' => __( 'Security', 'wpshadow' ),
					'color' => 'blue',
				),
				'description' => __( 'XML-RPC has been disabled, closing a common attack vector for brute-force and DDoS attacks.', 'wpshadow' ),
				'test'        => 'head_cleanup_xmlrpc',
			);
		}

		return array(
			'label'       => __( 'XML-RPC is enabled', 'wpshadow' ),
			'status'      => 'recommended',
			'badge'       => array(
				'label' => __( 'Security', 'wpshadow' ),
				'color' => 'orange',
			),
			'description' => __( 'XML-RPC is frequently targeted by attackers for brute-force login attempts. Consider disabling it unless you need remote publishing.', 'wpshadow' ),
			'test'        => 'head_cleanup_xmlrpc',
		);
	}

	public function test_oembed_removal(): array {
		if ( ! $this->is_enabled() ) {
			return array(
				'label'       => __( 'oEmbed Links', 'wpshadow' ),
				'status'      => 'recommended',
				'badge'       => array(
					'label' => __( 'Performance', 'wpshadow' ),
					'color' => 'blue',
				),
				'description' => __( 'Head cleanup is disabled.', 'wpshadow' ),
				'test'        => 'head_cleanup_oembed',
			);
		}

		$is_removed = $this->is_sub_feature_enabled( 'remove_oembed', true );

		if ( $is_removed ) {
			return array(
				'label'       => __( 'oEmbed discovery links removed', 'wpshadow' ),
				'status'      => 'good',
				'badge'       => array(
					'label' => __( 'Performance', 'wpshadow' ),
					'color' => 'blue',
				),
				'description' => __( 'oEmbed discovery links and scripts have been removed, reducing page weight.', 'wpshadow' ),
				'test'        => 'head_cleanup_oembed',
			);
		}

		return array(
			'label'       => __( 'oEmbed discovery links present', 'wpshadow' ),
			'status'      => 'recommended',
			'badge'       => array(
				'label' => __( 'Performance', 'wpshadow' ),
				'color' => 'orange',
			),
			'description' => __( 'WordPress is adding oEmbed discovery links to your page head. These can be safely removed if external sites do not need to embed your content.', 'wpshadow' ),
			'test'        => 'head_cleanup_oembed',
		);
	}

	public function handle_cli_command( array $args, array $assoc_args ): void {
		$action = $args[0] ?? 'status';

		if ( 'status' !== $action ) {
			\WP_CLI::error( __( 'Unknown subcommand. Try: wp wpshadow head-cleanup status', 'wpshadow' ) );
			return;
		}

		\WP_CLI::log( __( 'Head Cleanup status:', 'wpshadow' ) );
		\WP_CLI::log( sprintf( '  %s: %s', __( 'Feature enabled', 'wpshadow' ), $this->is_enabled() ? 'yes' : 'no' ) );

		$subs = array(
			'remove_emoji',
			'remove_generator',
			'remove_shortlink',
			'remove_rsd',
			'remove_wlw',
			'remove_rest_link',
			'remove_oembed',
			'remove_feeds',
			'remove_comments_style',
			'disable_xmlrpc',
		);

		foreach ( $subs as $sub ) {
			$enabled = $this->is_sub_feature_enabled( $sub, false );
			\WP_CLI::log( sprintf( '  - %s: %s', $sub, $enabled ? 'on' : 'off' ) );
		}

		\WP_CLI::success( __( 'Head cleanup inspected.', 'wpshadow' ) );
	}
}
