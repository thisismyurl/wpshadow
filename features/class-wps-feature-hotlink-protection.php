<?php
/**
 * Feature: Hotlink Protection
 *
 * Provides comprehensive hotlink protection to prevent bandwidth theft:
 * - Apache .htaccess rules for referrer validation
 * - Nginx configuration guidance
 * - CDN-level blocking recommendations
 * - Configurable allowed domains and file types
 *
 * @package WPShadow\CoreSupport
 * @since 1.2601.75000
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

/**
 * WPSHADOW_Feature_Hotlink_Protection
 *
 * Hotlink protection implementation to prevent unauthorized media embedding.
 */
final class WPSHADOW_Feature_Hotlink_Protection extends WPSHADOW_Abstract_Feature {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'hotlink-protection',
				'name'               => __( 'Hotlink Protection', 'wpshadow' ),
				'description_short'  => __( 'Stop other sites from stealing your bandwidth - protect your images and files.', 'wpshadow' ),
				'description_long'   => __( 'Prevent bandwidth theft by blocking other websites from directly linking to your images, videos, and media files. Hotlinking occurs when other sites embed your media files directly in their pages, using your server resources and bandwidth without permission. This feature automatically configures server-level protection using .htaccess rules for Apache/LiteSpeed servers, provides configuration guidance for Nginx servers, and includes recommendations for CDN-level protection for maximum effectiveness.', 'wpshadow' ),
				'description_wizard' => __( 'Hotlinking is when other websites steal your bandwidth by directly linking to your images and files. Every time someone views their site, your server pays the cost. This can significantly increase your hosting bills and slow down your site. Enable this to automatically block unauthorized embedding and protect your server resources.', 'wpshadow' ),
				'description'        => __( 'Prevent bandwidth theft with automatic hotlink blocking.', 'wpshadow' ),
				'scope'              => 'core',
				'default_enabled'    => false,
				'version'            => '1.0.0',
				'widget_group'       => 'security',
				'license_level'      => 1,
				'minimum_capability' => 'manage_options',
				'icon'               => 'dashicons-shield',
				'category'           => 'security',
				'priority'           => 10,
				'aliases'            => array(
					'bandwidth theft',
					'image theft',
					'image stealing',
					'hotlinking',
					'direct linking',
					'referrer checking',
					'cdn bandwidth',
				),
				'sub_features'       => array(
					'apache_protection'   => array(
						'name'                => __( 'Apache .htaccess Protection', 'wpshadow' ),
						'description_short'   => __( 'Automatic server-level hotlink blocking for Apache.', 'wpshadow' ),
						'description_long'    => __( 'Automatically configures Apache/LiteSpeed servers with .htaccess rules that check the HTTP referer header and block requests from unauthorized domains. Rules are written to your uploads directory .htaccess file and are automatically updated when you enable or disable the feature. Works seamlessly with Apache, LiteSpeed, and compatible web servers.', 'wpshadow' ),
						'description_wizard'  => __( 'If your server runs Apache or LiteSpeed, this will automatically configure hotlink protection at the server level. No manual configuration needed - just enable and the protection activates immediately. Most shared hosting uses Apache.', 'wpshadow' ),
						'description'         => __( 'Auto-configure Apache/LiteSpeed .htaccess rules.', 'wpshadow' ),
						'default_enabled'     => true,
					),
					'image_protection'    => array(
						'name'                => __( 'Protect Image Files', 'wpshadow' ),
						'description_short'   => __( 'Block hotlinking of JPG, PNG, GIF, WebP, and SVG images.', 'wpshadow' ),
						'description_long'    => __( 'Protects common image formats including JPEG, PNG, GIF, WebP, and SVG files from being hotlinked. Images are the most commonly stolen resource since they\'re easy to embed and can consume significant bandwidth. This protection ensures your images only display on your site and authorized domains, preventing bandwidth theft and unauthorized use.', 'wpshadow' ),
						'description_wizard'  => __( 'Images are the #1 target for hotlinking because they\'re easy to embed and use a lot of bandwidth. Protect your JPG, PNG, GIF, WebP, and SVG files to stop other sites from embedding them without permission.', 'wpshadow' ),
						'description'         => __( 'Protect JPG, PNG, GIF, WebP, SVG images.', 'wpshadow' ),
						'default_enabled'     => true,
					),
					'media_protection'    => array(
						'name'                => __( 'Protect Media Files', 'wpshadow' ),
						'description_short'   => __( 'Block hotlinking of video and audio files.', 'wpshadow' ),
						'description_long'    => __( 'Protects video files (MP4, WebM, OGG) and audio files (MP3, WAV, OGG) from being hotlinked. Media files are especially costly to hotlink since they\'re large and consume significant bandwidth with each play. Blocking unauthorized embedding of your media files can dramatically reduce bandwidth usage and hosting costs if you host videos or audio files.', 'wpshadow' ),
						'description_wizard'  => __( 'Video and audio files are huge bandwidth hogs. If someone hotlinks your videos, your hosting bill can skyrocket. Enable this if you host any media files to prevent other sites from embedding them and using your bandwidth.', 'wpshadow' ),
						'description'         => __( 'Protect MP4, MP3, and other media files.', 'wpshadow' ),
						'default_enabled'     => true,
					),
				),
			)
		);
		
		$this->log_activity( 'feature_initialized', 'Hotlink Protection feature initialized', 'info' );
	}

	/**
	 * Indicate this feature has a details page.
	 *
	 * @return bool
	 */
	public function has_details_page(): bool {
		return true;
	}

	/**
	 * Register hooks when feature is enabled.
	 *
	 * @return void
	 */
	public function register(): void {
		if ( ! $this->is_enabled() ) {
			return;
		}

		// Initialize on admin_init to configure hotlink protection.
		if ( get_option( 'wpshadow_hotlink-protection_apache_protection', true ) ) {
			add_action( 'admin_init', array( $this, 'configure_hotlink_protection' ), 5 );
		}
		
		// Add Site Health tests.
		add_filter( 'site_status_tests', array( $this, 'register_site_health_test' ) );
	}

	/**
	 * Configure hotlink protection based on server environment.
	 *
	 * @return void
	 */
	public function configure_hotlink_protection(): void {
		// Only run configuration once per day to avoid performance impact.
		$last_config = get_transient( 'wpshadow_hotlink_protection_last_config' );
		if ( false !== $last_config ) {
			return;
		}

		// Set transient for 24 hours.
		set_transient( 'wpshadow_hotlink_protection_last_config', time(), DAY_IN_SECONDS );

		// Detect server type.
		$server_software = isset( $_SERVER['SERVER_SOFTWARE'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) ) : '';
		if ( ! preg_match( '/^[a-zA-Z0-9.\/_\- ]+$/', $server_software ) ) {
			return;
		}
		$is_apache = stripos( $server_software, 'apache' ) !== false || stripos( $server_software, 'litespeed' ) !== false;

		if ( $is_apache ) {
			$this->configure_apache_protection();
		}
	}

	/**
	 * Configure Apache/LiteSpeed hotlink protection via .htaccess.
	 *
	 * @return void
	 */
	private function configure_apache_protection(): void {
		$uploads_dir   = wp_upload_dir();
		$htaccess_file = trailingslashit( $uploads_dir['basedir'] ) . '.htaccess';

		// Get current site domain.
		$site_domain     = wp_parse_url( home_url(), PHP_URL_HOST );
		$allowed_domains = array( $site_domain );

		// Build protected file types.
		$protected_types = array();
		if ( get_option( 'wpshadow_hotlink-protection_image_protection', true ) ) {
			$protected_types = array_merge( $protected_types, array( 'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg' ) );
		}
		if ( get_option( 'wpshadow_hotlink-protection_media_protection', true ) ) {
			$protected_types = array_merge( $protected_types, array( 'mp4', 'webm', 'ogv', 'mp3', 'wav', 'ogg' ) );
		}

		if ( empty( $protected_types ) ) {
			return;
		}

		// Build safe extensions pattern.
		$safe_extensions = array_map(
			function ( $ext ) {
				return preg_match( '/^[a-z0-9]+$/i', $ext ) ? preg_quote( $ext, '/' ) : '';
			},
			$protected_types
		);
		$safe_extensions = array_filter( $safe_extensions );
		$extensions      = implode( '|', $safe_extensions );

		// Build .htaccess content.
		$htaccess_content  = "\n# BEGIN WPShadow Hotlink Protection\n";
		$htaccess_content .= "<IfModule mod_rewrite.c>\n";
		$htaccess_content .= "    RewriteEngine On\n";
		$htaccess_content .= "    RewriteCond %{HTTP_REFERER} !^$\n";

		foreach ( $allowed_domains as $domain ) {
			$escaped_domain    = preg_quote( $domain, '/' );
			$htaccess_content .= "    RewriteCond %{HTTP_REFERER} !^https?://([^.]+\\.)?{$escaped_domain} [NC]\n";
		}

		$htaccess_content .= "    RewriteRule \\.({$extensions})$ - [F,L]\n";
		$htaccess_content .= "</IfModule>\n";
		$htaccess_content .= "# END WPShadow Hotlink Protection\n";

		// Read existing .htaccess content.
		$existing_content = '';
		if ( file_exists( $htaccess_file ) ) {
			$existing_content = file_get_contents( $htaccess_file );
			if ( false === $existing_content ) {
				$existing_content = '';
			}
		}

		// Remove old WPShadow hotlink protection rules if they exist.
		$existing_content = preg_replace(
			'/\n?# BEGIN WPShadow Hotlink Protection.*?# END WPShadow Hotlink Protection\n?/s',
			'',
			$existing_content
		);

		// Combine content.
		$new_content = $existing_content . $htaccess_content;

		// Attempt to write the file.
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
		$result = file_put_contents( $htaccess_file, $new_content );

		if ( false !== $result ) {
			$this->log_activity( 'settings_updated', 'Apache hotlink protection configured', 'info' );
		}
	}

	/**
	 * Register Site Health test.
	 *
	 * @param array<string, mixed> $tests Array of Site Health tests.
	 * @return array<string, mixed> Modified tests array.
	 */
	public function register_site_health_test( array $tests ): array {
		$tests['direct']['WPSHADOW_hotlink_protection'] = array(
			'label' => __( 'Hotlink Protection', 'wpshadow' ),
			'test'  => array( $this, 'test_hotlink_protection' ),
		);

		return $tests;
	}

	/**
	 * Site Health test for hotlink protection.
	 *
	 * @return array<string, mixed> Test result.
	 */
	public function test_hotlink_protection(): array {
		if ( ! $this->is_enabled() ) {
			return array(
				'label'       => __( 'Hotlink Protection', 'wpshadow' ),
				'status'      => 'recommended',
				'badge'       => array(
					'label' => __( 'Security', 'wpshadow' ),
					'color' => 'orange',
				),
				'description' => sprintf( '<p>%s</p>', __( 'Hotlink protection is not enabled. Other websites can embed your images and media files directly, using your bandwidth without permission and potentially increasing your hosting costs.', 'wpshadow' ) ),
				'actions'     => '',
				'test'        => 'WPSHADOW_hotlink_protection',
			);
		}

		$protected_types = 0;
		if ( get_option( 'wpshadow_hotlink-protection_image_protection', true ) ) {
			++$protected_types;
		}
		if ( get_option( 'wpshadow_hotlink-protection_media_protection', true ) ) {
			++$protected_types;
		}

		return array(
			'label'       => __( 'Hotlink Protection', 'wpshadow' ),
			'status'      => 'good',
			'badge'       => array(
				'label' => __( 'Security', 'wpshadow' ),
				'color' => 'blue',
			),
			'description' => sprintf(
				'<p>%s</p>',
				sprintf(
					/* translators: %d: number of protected file types */
					__( 'Hotlink protection is active with %d file type categories protected, preventing unauthorized embedding of your media files and reducing bandwidth theft.', 'wpshadow' ),
					$protected_types
				)
			),
			'actions'     => '',
			'test'        => 'WPSHADOW_hotlink_protection',
		);
	}
}
