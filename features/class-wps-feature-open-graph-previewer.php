<?php declare(strict_types=1);
/**
 * Feature: Social Media Open Graph Previewer
 *
 * Analyzes how website links appear when shared on social media platforms
 * like LinkedIn and X (Twitter). Checks for missing Open Graph tags.
 *
 * @package    WPShadow\CoreSupport
 * @subpackage Features
 * @since      1.2601.75000
 */

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Social Media Open Graph Previewer feature.
 */
final class WPSHADOW_Feature_Open_Graph_Previewer extends WPSHADOW_Abstract_Feature {

	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'open-graph-previewer',
				'name'               => __( 'Social Media Open Graph Previewer', 'wpshadow' ),
				'description'        => __( 'See exactly how your links look when shared on social media - fix missing images, titles, and descriptions.', 'wpshadow' ),
				'scope'              => 'core',
				'version'            => '1.0.0',
				'default_enabled'    => true,
				'widget_group'       => 'seo',
				'license_level'      => 1,
				'minimum_capability' => 'manage_options',
				'icon'               => 'dashicons-share',
				'category'           => 'seo',
				'priority'           => 15,
				'sub_features'       => array(
					'check_facebook'  => array(
						'name'            => __( 'Check Facebook OG Tags', 'wpshadow' ),
						'description'     => __( 'Validate Open Graph tags for Facebook and LinkedIn.', 'wpshadow' ),
						'default_enabled' => true,
					),
					'check_twitter'   => array(
						'name'            => __( 'Check Twitter Card Tags', 'wpshadow' ),
						'description'     => __( 'Validate Twitter Card meta tags.', 'wpshadow' ),
						'default_enabled' => true,
					),
					'check_linkedin'  => array(
						'name'            => __( 'Check LinkedIn Tags', 'wpshadow' ),
						'description'     => __( 'LinkedIn-specific meta tag validation.', 'wpshadow' ),
						'default_enabled' => true,
					),
					'check_pinterest' => array(
						'name'            => __( 'Check Pinterest Tags', 'wpshadow' ),
						'description'     => __( 'Pinterest-specific meta tag validation.', 'wpshadow' ),
						'default_enabled' => true,
					),
				),
			)
		);
	}

	public function has_details_page(): bool {
		return true;
	}

	public function register(): void {
		if ( ! $this->is_enabled() ) {
			return;
		}

		add_filter( 'site_status_tests', array( $this, 'register_site_health_test' ) );

		$this->log_activity( 'feature_initialized', 'Open Graph Previewer initialized', 'info' );
	}

	/**
	 * Analyze Open Graph tags on a page.
	 *
	 * @param string $url URL to analyze.
	 * @return array<string, mixed>
	 */
	public function analyze_page( string $url ): array {
		if ( empty( $url ) ) {
			return array();
		}

		$response = wp_remote_get(
			$url,
			array(
				'timeout'    => 10,
				'user-agent' => 'WPShadow Open Graph Analyzer/1.0',
			)
		);

		if ( is_wp_error( $response ) ) {
			return array( 'error' => $response->get_error_message() );
		}

		$html = wp_remote_retrieve_body( $response );
		return $this->parse_open_graph_tags( $html, $url );
	}

	/**
	 * Parse Open Graph tags from HTML.
	 *
	 * @param string $html HTML content.
	 * @param string $url Source URL.
	 * @return array<string, mixed>
	 */
	private function parse_open_graph_tags( string $html, string $url ): array {
		$tags = array();

		$required_tags = array(
			'og:title'       => __( 'Title', 'wpshadow' ),
			'og:description' => __( 'Description', 'wpshadow' ),
			'og:image'       => __( 'Image', 'wpshadow' ),
			'og:url'         => __( 'URL', 'wpshadow' ),
			'og:type'        => __( 'Type', 'wpshadow' ),
		);

		$twitter_tags = array(
			'twitter:card'        => __( 'Card Type', 'wpshadow' ),
			'twitter:title'       => __( 'Title', 'wpshadow' ),
			'twitter:description' => __( 'Description', 'wpshadow' ),
			'twitter:image'       => __( 'Image', 'wpshadow' ),
		);

		preg_match_all(
			'/<meta\s+[^>]*(?:property=["\']og:([^"\']+)["\'][^>]*content=["\']([^"\']+)["\']|content=["\']([^"\']+)["\'][^>]*property=["\']og:([^"\']+)["\'])[^>]*>/i',
			$html,
			$og_matches,
			PREG_SET_ORDER
		);

		foreach ( $og_matches as $match ) {
			if ( ! empty( $match[1] ) && ! empty( $match[2] ) ) {
				$property = 'og:' . $match[1];
				$tags[ $property ] = $match[2];
			} elseif ( ! empty( $match[3] ) && ! empty( $match[4] ) ) {
				$property = 'og:' . $match[4];
				$tags[ $property ] = $match[3];
			}
		}

		preg_match_all(
			'/<meta\s+[^>]*(?:name=["\']twitter:([^"\']+)["\'][^>]*content=["\']([^"\']+)["\']|content=["\']([^"\']+)["\'][^>]*name=["\']twitter:([^"\']+)["\'])[^>]*>/i',
			$html,
			$twitter_matches,
			PREG_SET_ORDER
		);

		foreach ( $twitter_matches as $match ) {
			if ( ! empty( $match[1] ) && ! empty( $match[2] ) ) {
				$property = 'twitter:' . $match[1];
				$tags[ $property ] = $match[2];
			} elseif ( ! empty( $match[3] ) && ! empty( $match[4] ) ) {
				$property = 'twitter:' . $match[4];
				$tags[ $property ] = $match[3];
			}
		}

		$missing = array();
		foreach ( $required_tags as $tag => $label ) {
			if ( ! isset( $tags[ $tag ] ) || empty( $tags[ $tag ] ) ) {
				$missing[] = $tag;
			}
		}

		$missing_twitter = array();
		foreach ( $twitter_tags as $tag => $label ) {
			if ( ! isset( $tags[ $tag ] ) || empty( $tags[ $tag ] ) ) {
				$missing_twitter[] = $tag;
			}
		}

		return array(
			'tags'            => $tags,
			'missing'         => $missing,
			'missing_twitter' => $missing_twitter,
			'required_tags'   => $required_tags,
			'twitter_tags'    => $twitter_tags,
			'url'             => $url,
		);
	}

	/**
	 * Validate and sanitize image URL.
	 *
	 * @param string $url Image URL.
	 * @return string|null
	 */
	private function validate_image_url( string $url ): ?string {
		if ( empty( $url ) ) {
			return null;
		}

		if ( ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
			return null;
		}

		$path = wp_parse_url( $url, PHP_URL_PATH );
		if ( $path ) {
			$ext = strtolower( pathinfo( $path, PATHINFO_EXTENSION ) );
			$valid_extensions = array( 'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg' );
			if ( ! empty( $ext ) && ! in_array( $ext, $valid_extensions, true ) ) {
				return null;
			}
		}

		return esc_url( $url );
	}

	/**
	 * Register Site Health test.
	 *
	 * @param array<string, mixed> $tests Site Health tests.
	 * @return array<string, mixed>
	 */
	public function register_site_health_test( array $tests ): array {
		$tests['direct']['open_graph_preview'] = array(
			'label' => __( 'Social Media Meta Tags', 'wpshadow' ),
			'test'  => array( $this, 'site_health_test_callback' ),
		);

		return $tests;
	}

	/**
	 * Site Health test callback.
	 *
	 * @return array<string, mixed>
	 */
	public function site_health_test_callback(): array {
		if ( ! $this->is_enabled() ) {
			return array(
				'label'       => __( 'Social Media Meta Tags', 'wpshadow' ),
				'status'      => 'recommended',
				'badge'       => array(
					'label' => __( 'SEO', 'wpshadow' ),
					'color' => 'gray',
				),
				'description' => sprintf( '<p>%s</p>', __( 'Social media meta tag checking is disabled.', 'wpshadow' ) ),
				'test'        => 'open_graph_preview',
			);
		}

		$homepage_analysis = $this->analyze_page( home_url() );

		if ( isset( $homepage_analysis['error'] ) ) {
			return array(
				'label'       => __( 'Social Media Meta Tags', 'wpshadow' ),
				'status'      => 'recommended',
				'badge'       => array(
					'label' => __( 'SEO', 'wpshadow' ),
					'color' => 'orange',
				),
				'description' => sprintf( '<p>%s</p>', __( 'Could not analyze homepage for meta tags.', 'wpshadow' ) ),
				'test'        => 'open_graph_preview',
			);
		}

		$og_complete = empty( $homepage_analysis['missing'] ?? array() );
		$twitter_complete = empty( $homepage_analysis['missing_twitter'] ?? array() );

		$status = ( $og_complete && $twitter_complete ) ? 'good' : 'recommended';

		$description = $og_complete
			? __( 'Open Graph tags are properly configured for social media sharing.', 'wpshadow' )
			: __( 'Some required Open Graph tags are missing. Your content may not appear correctly when shared on social media.', 'wpshadow' );

		return array(
			'label'       => __( 'Social Media Meta Tags', 'wpshadow' ),
			'status'      => $status,
			'badge'       => array(
				'label' => __( 'SEO', 'wpshadow' ),
				'color' => 'blue',
			),
			'description' => sprintf( '<p>%s</p>', $description ),
			'test'        => 'open_graph_preview',
		);
	}
}
