<?php
/**
 * Feature: WordPress Head Cleanup & Security Hardening
 *
 * Remove unnecessary meta tags, links, and scripts from <head> that expose
 * version info, add bloat, or provide no value for modern sites.
 *
 * @package WPShadow\CoreSupport
 * @since 1.2601.73001
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

/**
 * WPSHADOW_Feature_Head_Cleanup
 *
 * Comprehensive cleanup of WordPress head section.
 */
final class WPSHADOW_Feature_Head_Cleanup extends WPSHADOW_Abstract_Feature {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'head-cleanup',
				'name'               => __( 'Remove Unnecessary Page Code', 'wpshadow' ),
				'description'        => __( 'Clean up your page headers - remove clutter that slows you down and reveals too much about your site.', 'wpshadow' ),
				'scope'              => 'core',
				'default_enabled'    => true,
				'version'            => '1.0.0',
				'widget_group'       => 'performance',
				'license_level'      => 1,
				'minimum_capability' => 'manage_options',
				'icon'               => 'dashicons-editor-removeformatting',
				'category'           => 'performance',
				'priority'           => 20,
				'sub_features'       => array(
					'remove_emoji'          => __( 'Remove Emoji Scripts (Improves performance)', 'wpshadow' ),
					'remove_generator'      => __( 'Remove WP Generator Meta Tag (Security)', 'wpshadow' ),
					'remove_shortlink'      => __( 'Remove Shortlink Tag', 'wpshadow' ),
					'remove_rsd'            => __( 'Remove RSD Link (Really Simple Discovery)', 'wpshadow' ),
					'remove_wlw'            => __( 'Remove Windows Live Writer Manifest', 'wpshadow' ),
					'remove_rest_link'      => __( 'Remove REST API Link (May break REST clients)', 'wpshadow' ),
					'remove_oembed'         => __( 'Remove oEmbed Discovery Links', 'wpshadow' ),
					'remove_feeds'          => __( 'Remove Feed Links (May break RSS readers)', 'wpshadow' ),
					'remove_comments_style' => __( 'Remove Recent Comments Inline Styles', 'wpshadow' ),
					'disable_xmlrpc'        => __( 'Disable XML-RPC (Security)', 'wpshadow' ),
				),
			)
		);

		// Set default values for new installations
		$this->set_default_sub_features();
	}

	/**
	 * Register hooks for head cleanup parent feature.
	 *
	 * Only attaches Site Health tests; child features handle cleanup actions.
	 *
	 * @return void
	 */
	public function register(): void {
		if ( ! $this->is_enabled() ) {
			return;
		}

		add_filter( 'site_status_tests', array( $this, 'add_site_health_tests' ) );
	}

	/**
	 * Set default values for sub-features if not already set.
	 *
	 * @return void
	 */
	private function set_default_sub_features(): void {
		$defaults = array(
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
		);

		foreach ( $defaults as $key => $default_value ) {
			$option_name   = 'wpshadow_head-cleanup_' . $key;
			$current_value = get_option( $option_name, null );

			if ( null === $current_value ) {
				update_option( $option_name, $default_value, false );
			}
		}
	}

	/**
	 * Add Site Health tests for head cleanup features.
	 *
	 * @param array $tests Existing tests.
	 * @return array Modified tests.
	 */
	public function add_site_health_tests( array $tests ): array {
		$tests['direct']['wpshadow_head_cleanup_emoji'] = array(
			'label' => __( 'WPShadow: Emoji Scripts', 'wpshadow' ),
			'test'  => array( $this, 'test_emoji_removal' ),
		);

		$tests['direct']['wpshadow_head_cleanup_generator'] = array(
			'label' => __( 'WPShadow: XML-RPC Security', 'wpshadow' ),
			'test'  => array( $this, 'test_xmlrpc_disabled' ),
		);

		$tests['direct']['wpshadow_head_cleanup_oembed'] = array(
			'label' => __( 'WPShadow: oEmbed Links', 'wpshadow' ),
			'test'  => array( $this, 'test_oembed_removal' ),
		);

		return $tests;
	}

	/**
	 * Test if emoji scripts are removed.
	 *
	 * @return array Test results.
	 */
	public function test_emoji_removal(): array {
		$is_removed = get_option( 'wpshadow_head-cleanup_remove_emoji', true );

		if ( $is_removed ) {
			return array(
				'label'       => __( 'Emoji scripts are disabled', 'wpshadow' ),
				'status'      => 'good',
				'badge'       => array(
					'label' => __( 'Performance', 'wpshadow' ),
					'color' => 'blue',
				),
				'description' => sprintf(
					'<p>%s</p>',
					__( 'WordPress emoji detection scripts and styles have been removed from your pages, improving page load performance. Modern browsers support emojis natively without needing these scripts.', 'wpshadow' )
				),
				'actions'     => '',
				'test'        => 'wpshadow_head_cleanup_emoji',
			);
		}

		return array(
			'label'       => __( 'Emoji scripts are still loading', 'wpshadow' ),
			'status'      => 'recommended',
			'badge'       => array(
				'label' => __( 'Performance', 'wpshadow' ),
				'color' => 'orange',
			),
			'description' => sprintf(
				'<p>%s</p>',
				__( 'WordPress is loading emoji detection scripts on every page. These scripts are unnecessary for most sites as modern browsers support emojis natively. Removing them can improve page load performance.', 'wpshadow' )
			),
			'actions'     => sprintf(
				'<p><a href="%s">%s</a></p>',
				esc_url( $this->get_details_url() ),
				__( 'Enable emoji script removal', 'wpshadow' )
			),
			'test'        => 'wpshadow_head_cleanup_emoji',
		);
	}

	/**
	 * Test if generator tag is removed.
	 *
	 * @return array Test results.
	 */
	public function test_generator_removal(): array {
		$is_removed = get_option( 'wpshadow_head-cleanup_remove_generator', true );

		if ( $is_removed ) {
			return array(
				'label'       => __( 'WordPress version is hidden', 'wpshadow' ),
				'status'      => 'good',
				'badge'       => array(
					'label' => __( 'Security', 'wpshadow' ),
					'color' => 'blue',
				),
				'description' => sprintf(
					'<p>%s</p>',
					__( 'The WordPress generator meta tag has been removed from your pages, preventing attackers from easily identifying your WordPress version. This is a recommended security practice.', 'wpshadow' )
				),
				'actions'     => '',
				'test'        => 'wpshadow_head_cleanup_generator',
			);
		}

		return array(
			'label'       => __( 'WordPress version is publicly visible', 'wpshadow' ),
			'status'      => 'recommended',
			'badge'       => array(
				'label' => __( 'Security', 'wpshadow' ),
				'color' => 'orange',
			),
			'description' => sprintf(
				'<p>%s</p>',
				__( 'Your WordPress version is visible in the page source via the generator meta tag. This information can help attackers target known vulnerabilities in specific WordPress versions. Consider removing this tag for better security.', 'wpshadow' )
			),
			'actions'     => sprintf(
				'<p><a href="%s">%s</a></p>',
				esc_url( $this->get_details_url() ),
				__( 'Enable generator tag removal', 'wpshadow' )
			),
			'test'        => 'wpshadow_head_cleanup_generator',
		);
	}

	/**
	 * Test if XML-RPC is disabled.
	 *
	 * @return array Test results.
	 */
	public function test_xmlrpc_disabled(): array {
		$is_disabled = get_option( 'wpshadow_head-cleanup_disable_xmlrpc', true );

		if ( $is_disabled ) {
			return array(
				'label'       => __( 'XML-RPC is disabled', 'wpshadow' ),
				'status'      => 'good',
				'badge'       => array(
					'label' => __( 'Security', 'wpshadow' ),
					'color' => 'blue',
				),
				'description' => sprintf(
					'<p>%s</p>',
					__( 'XML-RPC has been disabled, closing a common attack vector. XML-RPC is rarely needed on modern WordPress sites and is frequently targeted by brute-force attacks and DDoS amplification attacks.', 'wpshadow' )
				),
				'actions'     => '',
				'test'        => 'wpshadow_head_cleanup_xmlrpc',
			);
		}

		return array(
			'label'       => __( 'XML-RPC is enabled', 'wpshadow' ),
			'status'      => 'recommended',
			'badge'       => array(
				'label' => __( 'Security', 'wpshadow' ),
				'color' => 'orange',
			),
			'description' => sprintf(
				'<p>%s</p>',
				__( 'XML-RPC is currently enabled on your site. This legacy feature is frequently targeted by attackers for brute-force login attempts and DDoS amplification attacks. Unless you specifically need XML-RPC for mobile apps or remote publishing, it is recommended to disable it.', 'wpshadow' )
			),
			'actions'     => sprintf(
				'<p><a href="%s">%s</a></p>',
				esc_url( $this->get_details_url() ),
				__( 'Enable XML-RPC blocking', 'wpshadow' )
			),
			'test'        => 'wpshadow_head_cleanup_xmlrpc',
		);
	}

	/**
	 * Test if oEmbed links are removed.
	 *
	 * @return array Test results.
	 */
	public function test_oembed_removal(): array {
		$is_removed = get_option( 'wpshadow_head-cleanup_remove_oembed', true );

		if ( $is_removed ) {
			return array(
				'label'       => __( 'oEmbed discovery links removed', 'wpshadow' ),
				'status'      => 'good',
				'badge'       => array(
					'label' => __( 'Performance', 'wpshadow' ),
					'color' => 'blue',
				),
				'description' => sprintf(
					'<p>%s</p>',
					__( 'oEmbed discovery links and scripts have been removed from your page head, reducing page weight and load time. These are only needed if you want external sites to automatically embed your content.', 'wpshadow' )
				),
				'actions'     => '',
				'test'        => 'wpshadow_head_cleanup_oembed',
			);
		}

		return array(
			'label'       => __( 'oEmbed discovery links present', 'wpshadow' ),
			'status'      => 'recommended',
			'badge'       => array(
				'label' => __( 'Performance', 'wpshadow' ),
				'color' => 'orange',
			),
			'description' => sprintf(
				'<p>%s</p>',
				__( 'WordPress is adding oEmbed discovery links and scripts to your page head. Unless you need external sites to automatically embed your content, these can be safely removed to improve page performance.', 'wpshadow' )
			),
			'actions'     => sprintf(
				'<p><a href="%s">%s</a></p>',
				esc_url( $this->get_details_url() ),
				__( 'Enable oEmbed link removal', 'wpshadow' )
			),
			'test'        => 'wpshadow_head_cleanup_oembed',
		);
	}
}
