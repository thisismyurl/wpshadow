<?php declare(strict_types=1);
/**
 * Feature: Broken Link Checker
 *
 * Scans for broken links in posts, pages, and CSS.
 *
 * @package WPShadow\CoreSupport
 * @since 1.2601.75001
 */

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


final class WPSHADOW_Feature_Broken_Link_Checker extends WPSHADOW_Abstract_Feature {

	public function __construct() {
		parent::__construct( array(
			'id'          => 'broken-link-checker',
			'name'        => __( 'Find Broken Links', 'wpshadow' ),
			'description' => __( 'Find links that don\'t work anymore so visitors don\'t get error pages. Helps your site rank better in Google.', 'wpshadow' ),
			'aliases'     => array( 'broken links', 'link checker', 'dead links', '404 errors', 'link validation', 'link monitoring', 'seo links', 'link health', 'url checker', 'link audit', 'external links', 'internal links' ),
			'sub_features' => array(
				'check_internal'    => array(
					'name'               => __( 'Check Internal Links', 'wpshadow' ),
					'description_short'  => __( 'Test links to your own pages', 'wpshadow' ),
					'description_long'   => __( 'Scans all links pointing to pages and posts on your website to make sure they still work. Catches broken internal links that could hurt visitors\' experience and your Google ranking.', 'wpshadow' ),
					'description_wizard' => __( 'Check links to your own pages to make sure they work.', 'wpshadow' ),
					'default_enabled'    => true,
				),
				'check_external'    => array(
					'name'               => __( 'Check External Links', 'wpshadow' ),
					'description_short'  => __( 'Test links to other websites', 'wpshadow' ),
					'description_long'   => __( 'Checks links pointing to other websites and services to see if they still available. Finds when other sites go down or move so you can update or remove broken references. Helps prevent sending visitors to dead links.', 'wpshadow' ),
					'description_wizard' => __( 'Check links to other websites to make sure they still work.', 'wpshadow' ),
					'default_enabled'    => true,
				),
				'check_css'         => array(
					'name'               => __( 'Check CSS Links', 'wpshadow' ),
					'description_short'  => __( 'Test links in your styling files', 'wpshadow' ),
					'description_long'   => __( 'Scans URLs referenced in your CSS stylesheets (fonts, images, etc.) to ensure they still working. Broken CSS links can cause visual issues like missing fonts or images on the page.', 'wpshadow' ),
					'description_wizard' => __( 'Check links in your CSS files.', 'wpshadow' ),
					'default_enabled'    => false,
				),
				'log_broken_links'  => array(
					'name'               => __( 'Record Broken Links', 'wpshadow' ),
					'description_short'  => __( 'Keep a log of broken links found', 'wpshadow' ),
					'description_long'   => __( 'Saves records of all broken links discovered with details about what went wrong, when they were found, and where they were found. Creates a history you can use to track which links need attention and fix them over time.', 'wpshadow' ),
					'description_wizard' => __( 'Keep a record of broken links for your review.', 'wpshadow' ),
					'default_enabled'    => true,
				),
			),
		) );

		$this->register_default_settings( array(
			'check_internal'    => true,
			'check_external'    => true,
			'check_css'         => false,
			'log_broken_links'  => true,
		) );
	}

	public function register(): void {
		if ( ! $this->is_enabled() ) {
			return;
		}

		// Schedule periodic link checks
		add_action( 'wp_scheduled_delete', array( $this, 'run_link_scan' ) );
		add_filter( 'site_status_tests', array( $this, 'register_site_health_test' ) );
	}

	/**
	 * Run link scan.
	 */
	public function run_link_scan(): void {
		$broken_links = $this->scan_links();
		set_transient( 'wpshadow_broken_links', $broken_links, DAY_IN_SECONDS );

		// Log if configured
		if ( $this->is_sub_feature_enabled( 'log_broken_links', true ) ) {
			foreach ( $broken_links as $link ) {
				$this->log_activity(
					'Broken Link',
					sprintf( 'Found broken link: %s (code: %s)', $link['url'], $link['code'] ),
					'warning'
				);
			}
		}
	}

	/**
	 * Scan for broken links.
	 */
	private function scan_links(): array {
		$broken = array();
		$checked_urls = array();

		// Get all posts and pages
		$posts = get_posts( array( 'post_type' => array( 'post', 'page' ), 'numberposts' => -1 ) );

		foreach ( $posts as $post ) {
			if ( ! $post->post_content ) {
				continue;
			}

			// Extract URLs
			preg_match_all( '/href=["\']([^"\']+)["\']/', $post->post_content, $matches );

			if ( empty( $matches[1] ) ) {
				continue;
			}

			foreach ( $matches[1] as $url ) {
				// Skip duplicates
				if ( in_array( $url, $checked_urls, true ) ) {
					continue;
				}

				$checked_urls[] = $url;

				// Filter by settings
				$is_external = ! $this->is_internal_url( $url );
				if ( ! $this->is_sub_feature_enabled( 'check_external', true ) && $is_external ) {
					continue;
				}
				if ( ! $this->is_sub_feature_enabled( 'check_internal', true ) && ! $is_external ) {
					continue;
				}

				// Skip anchors and special URLs
				if ( $url === '#' || str_starts_with( $url, 'javascript:' ) || str_starts_with( $url, 'mailto:' ) ) {
					continue;
				}

				$status = $this->check_link( $url );
				if ( ! $status['is_ok'] ) {
					$broken[] = array(
						'url'     => $url,
						'code'    => $status['code'],
						'message' => $status['message'],
						'found_in' => $post->ID,
					);
				}
			}
		}

		return $broken;
	}

	/**
	 * Check if URL is internal.
	 */
	private function is_internal_url( string $url ): bool {
		$home = home_url();
		$site = site_url();

		return str_starts_with( $url, $home ) || 
		       str_starts_with( $url, $site ) || 
		       str_starts_with( $url, '/' );
	}

	/**
	 * Check if a link is working.
	 */
	private function check_link( string $url ): array {
		// For internal links, do a HEAD request
		$response = wp_remote_head( $url, array( 'timeout' => 5, 'blocking' => true ) );

		if ( is_wp_error( $response ) ) {
			return array(
				'is_ok'   => false,
				'code'    => 0,
				'message' => __( 'Could not reach link', 'wpshadow' ),
			);
		}

		$code = wp_remote_retrieve_response_code( $response );

		// 200-399 are OK, 400+ are errors
		$is_ok = $code >= 200 && $code < 400;

		return array(
			'is_ok'   => $is_ok,
			'code'    => $code,
			'message' => $this->get_http_status_message( $code ),
		);
	}

	/**
	 * Get HTTP status message.
	 */
	private function get_http_status_message( int $code ): string {
		$messages = array(
			404 => __( 'Not Found', 'wpshadow' ),
			403 => __( 'Forbidden', 'wpshadow' ),
			500 => __( 'Server Error', 'wpshadow' ),
			503 => __( 'Service Unavailable', 'wpshadow' ),
			0   => __( 'Timeout', 'wpshadow' ),
		);

		return $messages[ $code ] ?? sprintf( __( 'HTTP %d', 'wpshadow' ), $code );
	}

	public function register_site_health_test( array $tests ): array {
		$tests['direct']['broken_links'] = array(
			'label'  => __( 'Broken Link Checker', 'wpshadow' ),
			'test'   => array( $this, 'test_links' ),
		);

		return $tests;
	}

	public function test_links(): array {
		if ( ! $this->is_enabled() ) {
			return array(
				'label'       => __( 'Broken Link Checker', 'wpshadow' ),
				'status'      => 'recommended',
				'badge'       => array( 'label' => __( 'WPShadow', 'wpshadow' ) ),
				'description' => __( 'Enable broken link checking to maintain SEO.', 'wpshadow' ),
				'actions'     => '',
				'test'        => 'broken_links',
			);
		}

		$broken_links = get_transient( 'wpshadow_broken_links' );
		if ( false === $broken_links ) {
			$broken_links = $this->scan_links();
		}

		$status = empty( $broken_links ) ? 'good' : 'recommended';

		return array(
			'label'       => __( 'Broken Link Checker', 'wpshadow' ),
			'status'      => $status,
			'badge'       => array( 'label' => __( 'WPShadow', 'wpshadow' ) ),
			'description' => sprintf(
				__( '%d broken link(s) found.', 'wpshadow' ),
				count( $broken_links )
			),
			'actions'     => '',
			'test'        => 'broken_links',
		);
	}
}
