<?php declare(strict_types=1);
/**
 * Feature: SEO Validator
 *
 * Validates sitemap.xml and robots.txt for correct formatting and accessibility.
 *
 * @package    WPShadow\CoreSupport
 * @subpackage Features
 * @since      1.2601.76000
 */

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * SEO Validator Class - Validates sitemap and robots.txt files.
 */
final class WPSHADOW_Feature_SEO_Validator extends WPSHADOW_Abstract_Feature {

	/**
	 * Expected sitemap namespace URI.
	 */
	private const SITEMAP_NAMESPACE = 'http://www.sitemaps.org/schemas/sitemap/0.9';

	/**
	 * Maximum number of sitemap URLs to validate.
	 */
	private const MAX_URLS_TO_VALIDATE = 5;

	/**
	 * Valid robots.txt directives.
	 */
	private const VALID_ROBOTS_DIRECTIVES = array(
		'User-agent',
		'Disallow',
		'Allow',
		'Sitemap',
		'Crawl-delay',
	);

	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'seo-validator',
				'name'               => __( 'Sitemap & Robots.txt Validator', 'wpshadow' ),
				'description'        => __( 'Make sure search engines can find and index your site properly.', 'wpshadow' ),
				'scope'              => 'core',
				'version'            => '1.0.0',
				'default_enabled'    => true,
				'category'           => 'tools',
				'icon'               => 'dashicons-search',
				'priority'           => 60,
				'widget_group'       => 'seo',
				'license_level'      => 1,
				'minimum_capability' => 'manage_options',
				'sub_features'       => array(
					'validate_robots'  => array(
						'name'            => __( 'Validate Robots.txt', 'wpshadow' ),
						'description'     => __( 'Check robots.txt accessibility and syntax.', 'wpshadow' ),
						'default_enabled' => true,
					),
					'validate_sitemap' => array(
						'name'            => __( 'Validate Sitemap.xml', 'wpshadow' ),
						'description'     => __( 'Check sitemap XML structure and URLs.', 'wpshadow' ),
						'default_enabled' => true,
					),
					'auto_fix_issues'  => array(
						'name'            => __( 'Auto-Fix Common Issues', 'wpshadow' ),
						'description'     => __( 'Automatically repair simple issues when detected.', 'wpshadow' ),
						'default_enabled' => false,
					),
					'email_alerts'     => array(
						'name'            => __( 'Send Email Alerts on Issues', 'wpshadow' ),
						'description'     => __( 'Notify admin when validation detects problems.', 'wpshadow' ),
						'default_enabled' => false,
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

		add_action( 'wp_ajax_wpshadow_validate_seo', array( $this, 'ajax_validate_seo' ) );
		add_filter( 'site_status_tests', array( $this, 'register_site_health_test' ) );

		$this->log_activity( 'feature_initialized', 'SEO Validator initialized', 'info' );
	}

	/**
	 * Validate sitemap.xml and robots.txt.
	 *
	 * @return array<string, mixed>
	 */
	private function validate_all(): array {
		return array(
			'sitemap' => $this->is_sub_feature_enabled( 'validate_sitemap', true ) ? $this->validate_sitemap() : array(),
			'robots'  => $this->is_sub_feature_enabled( 'validate_robots', true ) ? $this->validate_robots() : array(),
		);
	}

	/**
	 * Validate sitemap.xml.
	 *
	 * @return array<string, mixed>
	 */
	private function validate_sitemap(): array {
		$sitemap_url = home_url( '/sitemap.xml' );
		$result      = array(
			'status'          => 'error',
			'message'         => '',
			'issues'          => array(),
			'recommendations' => array(),
		);

		$response = wp_remote_get(
			$sitemap_url,
			array(
				'timeout'     => 10,
				'redirection' => 5,
				'user-agent'  => 'WPShadow-SEO-Validator/1.0',
			)
		);

		if ( is_wp_error( $response ) ) {
			$result['status']  = 'error';
			$result['message'] = __( 'Sitemap is not accessible.', 'wpshadow' );
			$result['issues']  = array( $response->get_error_message() );
			$result['recommendations'] = array(
				__( 'Ensure your sitemap plugin is active and configured.', 'wpshadow' ),
				__( 'Verify that your .htaccess or server configuration is not blocking the sitemap.', 'wpshadow' ),
			);
			return $result;
		}

		$status_code = wp_remote_retrieve_response_code( $response );
		$body        = wp_remote_retrieve_body( $response );

		if ( 200 !== $status_code ) {
			$result['status']  = 'error';
			$result['message'] = sprintf(
				/* translators: %d: HTTP status code */
				__( 'Sitemap returned HTTP status %d.', 'wpshadow' ),
				$status_code
			);
			$result['issues'][] = __( 'Expected HTTP 200 status code.', 'wpshadow' );
			return $result;
		}

		if ( empty( $body ) ) {
			$result['status']  = 'error';
			$result['message'] = __( 'Sitemap is empty.', 'wpshadow' );
			$result['recommendations'] = array(
				__( 'Regenerate your sitemap using your SEO plugin.', 'wpshadow' ),
			);
			return $result;
		}

		$previous_errors = libxml_use_internal_errors( true );

		if ( PHP_VERSION_ID < 80000 ) {
			libxml_disable_entity_loader( true );
		}

		$xml        = simplexml_load_string( $body, 'SimpleXMLElement', LIBXML_NONET | LIBXML_NOENT | LIBXML_NOCDATA );
		$xml_errors = libxml_get_errors();
		libxml_clear_errors();
		libxml_use_internal_errors( $previous_errors );

		if ( false === $xml || ! empty( $xml_errors ) ) {
			$result['status']  = 'error';
			$result['message'] = __( 'Sitemap XML is malformed.', 'wpshadow' );
			$result['issues']  = array();
			foreach ( $xml_errors as $error ) {
				$result['issues'][] = sprintf(
					/* translators: 1: line number, 2: error message */
					__( 'Line %1$d: %2$s', 'wpshadow' ),
					$error->line,
					trim( $error->message )
				);
			}
			return $result;
		}

		$issues = array();

		$has_urlset  = isset( $xml->url );
		$has_sitemap = isset( $xml->sitemap );

		if ( ! $has_urlset && ! $has_sitemap ) {
			$issues[] = __( 'No URL entries or sitemap references found.', 'wpshadow' );
		}

		$namespaces            = $xml->getNamespaces( true );
		$has_sitemap_namespace = false;

		if ( ! empty( $namespaces ) ) {
			foreach ( $namespaces as $prefix => $uri ) {
				if ( $uri === self::SITEMAP_NAMESPACE ) {
					$has_sitemap_namespace = true;
					break;
				}
			}
		}

		if ( ! $has_sitemap_namespace ) {
			$default_namespace = $xml->getNamespaces( false );
			if ( isset( $default_namespace[''] ) && $default_namespace[''] === self::SITEMAP_NAMESPACE ) {
				$has_sitemap_namespace = true;
			}
		}

		if ( ! $has_sitemap_namespace ) {
			$issues[] = sprintf(
				/* translators: %s: expected namespace URI */
				__( 'Missing required sitemap namespace (%s).', 'wpshadow' ),
				self::SITEMAP_NAMESPACE
			);
		}

		if ( $has_urlset ) {
			$url_issues = $this->validate_sitemap_urls( $xml );
			$issues     = array_merge( $issues, $url_issues );
		}

		if ( empty( $issues ) ) {
			$result['status']  = 'success';
			$result['message'] = __( 'Sitemap is valid and accessible.', 'wpshadow' );
		} else {
			$result['status']  = 'warning';
			$result['message'] = __( 'Sitemap has some issues.', 'wpshadow' );
			$result['issues']  = $issues;
		}

		return $result;
	}

	/**
	 * Validate sitemap URLs.
	 *
	 * @param \SimpleXMLElement $xml Sitemap XML object.
	 * @return array<int, string>
	 */
	private function validate_sitemap_urls( \SimpleXMLElement $xml ): array {
		$issues = array();
		$count  = 0;

		foreach ( $xml->url as $url ) {
			if ( $count >= self::MAX_URLS_TO_VALIDATE ) {
				break;
			}

			$loc = (string) $url->loc;
			if ( empty( $loc ) ) {
				$issues[] = __( 'Found URL entry without location (loc) element.', 'wpshadow' );
				continue;
			}

			if ( ! filter_var( $loc, FILTER_VALIDATE_URL ) ) {
				$issues[] = sprintf(
					/* translators: %s: invalid URL */
					__( 'Invalid URL format: %s', 'wpshadow' ),
					esc_html( $loc )
				);
			}

			$count++;
		}

		return $issues;
	}

	/**
	 * Validate robots.txt.
	 *
	 * @return array<string, mixed>
	 */
	private function validate_robots(): array {
		$robots_url = home_url( '/robots.txt' );
		$result     = array(
			'status'          => 'error',
			'message'         => '',
			'issues'          => array(),
			'recommendations' => array(),
		);

		$response = wp_remote_get(
			$robots_url,
			array(
				'timeout'     => 10,
				'redirection' => 5,
				'user-agent'  => 'WPShadow-SEO-Validator/1.0',
			)
		);

		if ( is_wp_error( $response ) ) {
			$result['status']  = 'error';
			$result['message'] = __( 'Robots.txt is not accessible.', 'wpshadow' );
			$result['issues']  = array( $response->get_error_message() );
			return $result;
		}

		$status_code = wp_remote_retrieve_response_code( $response );
		$body        = wp_remote_retrieve_body( $response );

		if ( 200 !== $status_code ) {
			$result['status']  = 'error';
			$result['message'] = sprintf(
				/* translators: %d: HTTP status code */
				__( 'Robots.txt returned HTTP status %d.', 'wpshadow' ),
				$status_code
			);
			return $result;
		}

		if ( empty( $body ) ) {
			$result['status']  = 'warning';
			$result['message'] = __( 'Robots.txt is empty.', 'wpshadow' );
			$result['recommendations'] = array(
				__( 'Consider adding directives to guide search engine crawlers.', 'wpshadow' ),
			);
			return $result;
		}

		$issues          = $this->validate_robots_content( $body );
		$recommendations = array();

		if ( ! preg_match( '/^Sitemap:/mi', $body ) ) {
			$recommendations[] = sprintf(
				/* translators: %s: sitemap URL */
				__( 'Add a Sitemap directive to your robots.txt: Sitemap: %s', 'wpshadow' ),
				home_url( '/sitemap.xml' )
			);
		}

		if ( preg_match( '/Disallow:\s*\/$/m', $body ) ) {
			$issues[] = __( 'Found "Disallow: /" which blocks all crawlers from your entire site.', 'wpshadow' );
		}

		$wp_content_dir = basename( WP_CONTENT_DIR );
		if ( preg_match( '/Disallow:.*' . preg_quote( $wp_content_dir, '/' ) . '\/uploads/i', $body ) ) {
			$recommendations[] = __( 'Your robots.txt blocks uploads directory, which may prevent image indexing.', 'wpshadow' );
		}

		if ( empty( $issues ) && empty( $recommendations ) ) {
			$result['status']  = 'success';
			$result['message'] = __( 'Robots.txt is valid and properly configured.', 'wpshadow' );
		} elseif ( empty( $issues ) ) {
			$result['status']          = 'success';
			$result['message']         = __( 'Robots.txt is valid with minor recommendations.', 'wpshadow' );
			$result['recommendations'] = $recommendations;
		} else {
			$result['status']          = 'warning';
			$result['message']         = __( 'Robots.txt has some issues.', 'wpshadow' );
			$result['issues']          = $issues;
			$result['recommendations'] = $recommendations;
		}

		return $result;
	}

	/**
	 * Validate robots.txt content.
	 *
	 * @param string $content Robots.txt content.
	 * @return array<int, string>
	 */
	private function validate_robots_content( string $content ): array {
		$issues = array();
		$lines  = explode( "\n", $content );

		foreach ( $lines as $line_num => $line ) {
			$line = trim( $line );

			if ( empty( $line ) || strpos( $line, '#' ) === 0 ) {
				continue;
			}

			$has_valid_directive = false;

			foreach ( self::VALID_ROBOTS_DIRECTIVES as $directive ) {
				if ( stripos( $line, $directive . ':' ) === 0 ) {
					$has_valid_directive = true;
					break;
				}
			}

			if ( ! $has_valid_directive && ! empty( $line ) ) {
				$issues[] = sprintf(
					/* translators: 1: line number, 2: line content */
					__( 'Line %1$d contains unrecognized directive: %2$s', 'wpshadow' ),
					$line_num + 1,
					esc_html( substr( $line, 0, 50 ) )
				);
			}
		}

		return $issues;
	}

	/**
	 * AJAX handler for validation.
	 */
	public function ajax_validate_seo(): void {
		check_ajax_referer( 'wpshadow_validate_seo' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'wpshadow' ) ) );
		}

		$results = $this->validate_all();
		$this->update_setting( 'last_check', time() );

		wp_send_json_success( array( 'results' => $results ) );
	}

	/**
	 * Register Site Health test.
	 *
	 * @param array<string, mixed> $tests Site Health tests.
	 * @return array<string, mixed>
	 */
	public function register_site_health_test( array $tests ): array {
		$tests['direct']['seo_validator'] = array(
			'label' => __( 'Sitemap & Robots.txt', 'wpshadow' ),
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
				'label'       => __( 'Sitemap & Robots.txt', 'wpshadow' ),
				'status'      => 'recommended',
				'badge'       => array(
					'label' => __( 'SEO', 'wpshadow' ),
					'color' => 'gray',
				),
				'description' => sprintf( '<p>%s</p>', __( 'SEO validation is disabled.', 'wpshadow' ) ),
				'test'        => 'seo_validator',
			);
		}

		$validation = $this->validate_all();

		$sitemap_ok = empty( $validation['sitemap']['issues'] ?? array() );
		$robots_ok  = empty( $validation['robots']['issues'] ?? array() );

		$status = ( $sitemap_ok && $robots_ok ) ? 'good' : 'recommended';

		$description = '';
		if ( ! $sitemap_ok ) {
			$description .= __( 'Sitemap has issues. ', 'wpshadow' );
		}
		if ( ! $robots_ok ) {
			$description .= __( 'Robots.txt has issues.', 'wpshadow' );
		}

		return array(
			'label'       => __( 'Sitemap & Robots.txt', 'wpshadow' ),
			'status'      => $status,
			'badge'       => array(
				'label' => __( 'SEO', 'wpshadow' ),
				'color' => 'blue',
			),
			'description' => sprintf( '<p>%s</p>', $description ?: __( 'Your sitemap and robots.txt are properly configured for search engines.', 'wpshadow' ) ),
			'test'        => 'seo_validator',
		);
	}
}
