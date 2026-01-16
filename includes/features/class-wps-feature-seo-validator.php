<?php
/**
 * SEO Validator Feature
 *
 * Validates sitemap.xml and robots.txt for correct formatting and accessibility.
 *
 * @package WPShadow\CoreSupport
 * @since 1.2601.76000
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPSHADOW_Feature_SEO_Validator Class
 *
 * Validates sitemap and robots.txt files for search engine compatibility.
 */
class WPSHADOW_Feature_SEO_Validator extends WPSHADOW_Abstract_Feature {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'seo-validator',
				'name'               => __( 'Sitemap & Robots.txt Validator', 'plugin-wpshadow' ),
				'description'        => __( 'Confirms that your sitemap.xml and robots.txt files are correctly formatted and accessible to search engine crawlers.', 'plugin-wpshadow' ),
				'scope'              => 'core',
				'version'            => '1.0.0',
				'default_enabled'    => true,
				'category'           => 'tools',
				'icon'               => 'dashicons-search',
				'priority'           => 60,
				'widget_group'       => 'seo',
				'widget_label'       => __( 'SEO Tools', 'plugin-wpshadow' ),
				'widget_description' => __( 'Search engine optimization tools and validators', 'plugin-wpshadow' ),
				'license_level'      => 1,
				'minimum_capability' => 'manage_options',
				'dashboard'          => 'overview',
				'widget_column'      => 'left',
				'widget_priority'    => 60,
			)
		);

		// Register hooks when feature is enabled.
		if ( self::is_enabled() ) {
			$this->register_hooks();
		}
	}

	/**
	 * Register WordPress hooks.
	 *
	 * @return void
	 */
	private function register_hooks(): void {
		// Register dashboard widget.
		add_action( 'wpshadow_register_widgets', array( $this, 'register_widget' ) );
		
		// Register AJAX handlers.
		add_action( 'wp_ajax_wpshadow_validate_seo', array( $this, 'ajax_validate_seo' ) );
	}

	/**
	 * Register dashboard widget.
	 *
	 * @return void
	 */
	public function register_widget(): void {
		add_meta_box(
			'wpshadow_seo_validator',
			__( 'SEO Validator', 'plugin-wpshadow' ),
			array( $this, 'render_widget' ),
			'wpshadow-dashboard',
			'normal',
			'default'
		);
	}

	/**
	 * Render widget content.
	 *
	 * @return void
	 */
	public function render_widget(): void {
		// Get cached results if available.
		$cached_results = $this->get_cache( 'validation_results' );
		$last_check     = $this->get_setting( 'last_check', 0 );

		?>
		<div class="wpshadow-seo-validator-widget">
			<div id="wpshadow-seo-results">
				<?php
				if ( $cached_results && is_array( $cached_results ) ) {
					$this->render_validation_results( $cached_results );
				} else {
					?>
					<p><?php esc_html_e( 'Click "Validate Now" to check your sitemap and robots.txt files.', 'plugin-wpshadow' ); ?></p>
					<?php
				}
				?>
			</div>
			
			<p class="wpshadow-validation-actions">
				<button type="button" id="wpshadow-validate-seo-btn" class="button button-primary">
					<?php esc_html_e( 'Validate Now', 'plugin-wpshadow' ); ?>
				</button>
				<?php if ( $last_check > 0 ) : ?>
					<span class="description">
						<?php
						printf(
							/* translators: %s: human-readable time difference */
							esc_html__( 'Last checked: %s', 'plugin-wpshadow' ),
							esc_html( human_time_diff( $last_check ) . ' ' . __( 'ago', 'plugin-wpshadow' ) )
						);
						?>
					</span>
				<?php endif; ?>
			</p>

			<style>
				.wpshadow-validation-result {
					margin: 15px 0;
					padding: 12px;
					border-left: 4px solid #ddd;
					background: #f8f8f8;
				}
				.wpshadow-validation-result.status-success {
					border-left-color: #46b450;
					background: #ecf7ed;
				}
				.wpshadow-validation-result.status-warning {
					border-left-color: #ffb900;
					background: #fff8e5;
				}
				.wpshadow-validation-result.status-error {
					border-left-color: #dc3232;
					background: #fef7f7;
				}
				.wpshadow-validation-result h4 {
					margin: 0 0 10px 0;
					font-size: 14px;
				}
				.wpshadow-validation-result ul {
					margin: 5px 0 0 20px;
				}
				.wpshadow-validation-result .dashicons {
					margin-right: 5px;
				}
				.wpshadow-validation-actions {
					margin-top: 15px;
					padding-top: 15px;
					border-top: 1px solid #ddd;
				}
				#wpshadow-validate-seo-btn.loading {
					opacity: 0.6;
					pointer-events: none;
				}
			</style>

			<script>
			jQuery(document).ready(function($) {
				$('#wpshadow-validate-seo-btn').on('click', function() {
					var $btn = $(this);
					var originalText = $btn.text();
					
					$btn.addClass('loading').text('<?php echo esc_js( __( 'Validating...', 'plugin-wpshadow' ) ); ?>');
					
					$.ajax({
						url: ajaxurl,
						type: 'POST',
						data: {
							action: 'wpshadow_validate_seo',
							nonce: '<?php echo esc_js( wp_create_nonce( 'wpshadow_validate_seo' ) ); ?>'
						},
						success: function(response) {
							if (response.success && response.data.html) {
								$('#wpshadow-seo-results').html(response.data.html);
							} else {
								alert('<?php echo esc_js( __( 'Validation failed. Please try again.', 'plugin-wpshadow' ) ); ?>');
							}
						},
						error: function() {
							alert('<?php echo esc_js( __( 'An error occurred. Please try again.', 'plugin-wpshadow' ) ); ?>');
						},
						complete: function() {
							$btn.removeClass('loading').text(originalText);
						}
					});
				});
			});
			</script>
		</div>
		<?php
	}

	/**
	 * Render validation results.
	 *
	 * @param array $results Validation results.
	 * @return void
	 */
	private function render_validation_results( array $results ): void {
		if ( isset( $results['sitemap'] ) ) {
			$this->render_sitemap_results( $results['sitemap'] );
		}

		if ( isset( $results['robots'] ) ) {
			$this->render_robots_results( $results['robots'] );
		}
	}

	/**
	 * Render sitemap validation results.
	 *
	 * @param array $result Sitemap validation result.
	 * @return void
	 */
	private function render_sitemap_results( array $result ): void {
		$status_class = 'status-' . ( $result['status'] ?? 'error' );
		$icon         = $this->get_status_icon( $result['status'] ?? 'error' );

		?>
		<div class="wpshadow-validation-result <?php echo esc_attr( $status_class ); ?>">
			<h4>
				<span class="dashicons <?php echo esc_attr( $icon ); ?>"></span>
				<?php esc_html_e( 'Sitemap (sitemap.xml)', 'plugin-wpshadow' ); ?>
			</h4>
			<?php if ( ! empty( $result['message'] ) ) : ?>
				<p><?php echo esc_html( $result['message'] ); ?></p>
			<?php endif; ?>
			<?php if ( ! empty( $result['issues'] ) && is_array( $result['issues'] ) ) : ?>
				<ul>
					<?php foreach ( $result['issues'] as $issue ) : ?>
						<li><?php echo esc_html( $issue ); ?></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
			<?php if ( ! empty( $result['recommendations'] ) && is_array( $result['recommendations'] ) ) : ?>
				<p><strong><?php esc_html_e( 'Recommendations:', 'plugin-wpshadow' ); ?></strong></p>
				<ul>
					<?php foreach ( $result['recommendations'] as $recommendation ) : ?>
						<li><?php echo wp_kses_post( $recommendation ); ?></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Render robots.txt validation results.
	 *
	 * @param array $result Robots.txt validation result.
	 * @return void
	 */
	private function render_robots_results( array $result ): void {
		$status_class = 'status-' . ( $result['status'] ?? 'error' );
		$icon         = $this->get_status_icon( $result['status'] ?? 'error' );

		?>
		<div class="wpshadow-validation-result <?php echo esc_attr( $status_class ); ?>">
			<h4>
				<span class="dashicons <?php echo esc_attr( $icon ); ?>"></span>
				<?php esc_html_e( 'Robots.txt', 'plugin-wpshadow' ); ?>
			</h4>
			<?php if ( ! empty( $result['message'] ) ) : ?>
				<p><?php echo esc_html( $result['message'] ); ?></p>
			<?php endif; ?>
			<?php if ( ! empty( $result['issues'] ) && is_array( $result['issues'] ) ) : ?>
				<ul>
					<?php foreach ( $result['issues'] as $issue ) : ?>
						<li><?php echo esc_html( $issue ); ?></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
			<?php if ( ! empty( $result['recommendations'] ) && is_array( $result['recommendations'] ) ) : ?>
				<p><strong><?php esc_html_e( 'Recommendations:', 'plugin-wpshadow' ); ?></strong></p>
				<ul>
					<?php foreach ( $result['recommendations'] as $recommendation ) : ?>
						<li><?php echo wp_kses_post( $recommendation ); ?></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Get status icon based on status.
	 *
	 * @param string $status Status (success, warning, error).
	 * @return string Dashicon class.
	 */
	private function get_status_icon( string $status ): string {
		switch ( $status ) {
			case 'success':
				return 'dashicons-yes-alt';
			case 'warning':
				return 'dashicons-warning';
			case 'error':
			default:
				return 'dashicons-dismiss';
		}
	}

	/**
	 * AJAX handler for validation.
	 *
	 * @return void
	 */
	public function ajax_validate_seo(): void {
		check_ajax_referer( 'wpshadow_validate_seo', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'plugin-wpshadow' ) ) );
		}

		// Run validation.
		$results = $this->validate_all();

		// Cache results.
		$this->set_cache( 'validation_results', $results, HOUR_IN_SECONDS );
		$this->update_setting( 'last_check', time() );

		// Render HTML.
		ob_start();
		$this->render_validation_results( $results );
		$html = ob_get_clean();

		wp_send_json_success(
			array(
				'html'    => $html,
				'results' => $results,
			)
		);
	}

	/**
	 * Validate sitemap and robots.txt.
	 *
	 * @return array Validation results.
	 */
	private function validate_all(): array {
		return array(
			'sitemap' => $this->validate_sitemap(),
			'robots'  => $this->validate_robots(),
		);
	}

	/**
	 * Validate sitemap.xml.
	 *
	 * @return array Validation result.
	 */
	private function validate_sitemap(): array {
		$sitemap_url = home_url( '/sitemap.xml' );
		$result      = array(
			'status'          => 'error',
			'message'         => '',
			'issues'          => array(),
			'recommendations' => array(),
		);

		// Check if sitemap is accessible.
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
			$result['message'] = __( 'Sitemap is not accessible.', 'plugin-wpshadow' );
			$result['issues']  = array( $response->get_error_message() );
			$result['recommendations'] = array(
				sprintf(
					/* translators: %s: sitemap URL */
					__( 'Ensure your sitemap is accessible at <a href="%s" target="_blank">%s</a>', 'plugin-wpshadow' ),
					esc_url( $sitemap_url ),
					esc_html( $sitemap_url )
				),
				__( 'Check if a plugin like Yoast SEO, Rank Math, or All in One SEO is installed and configured.', 'plugin-wpshadow' ),
				__( 'Verify that your .htaccess or server configuration is not blocking the sitemap.', 'plugin-wpshadow' ),
			);
			return $result;
		}

		$status_code = wp_remote_retrieve_response_code( $response );
		$body        = wp_remote_retrieve_body( $response );

		// Check HTTP status.
		if ( 200 !== $status_code ) {
			$result['status']  = 'error';
			$result['message'] = sprintf(
				/* translators: %d: HTTP status code */
				__( 'Sitemap returned HTTP status %d.', 'plugin-wpshadow' ),
				$status_code
			);
			$result['issues'][] = __( 'Expected HTTP 200 status code.', 'plugin-wpshadow' );
			$result['recommendations'] = array(
				__( 'Check your sitemap plugin settings.', 'plugin-wpshadow' ),
				__( 'Verify that permalinks are configured correctly.', 'plugin-wpshadow' ),
			);
			return $result;
		}

		// Check if content is XML.
		$content_type = wp_remote_retrieve_header( $response, 'content-type' );
		if ( empty( $body ) ) {
			$result['status']  = 'error';
			$result['message'] = __( 'Sitemap is empty.', 'plugin-wpshadow' );
			$result['recommendations'] = array(
				__( 'Regenerate your sitemap using your SEO plugin.', 'plugin-wpshadow' ),
			);
			return $result;
		}

		// Validate XML structure.
		$previous_errors = libxml_use_internal_errors( true );
		$xml             = simplexml_load_string( $body );
		$xml_errors      = libxml_get_errors();
		libxml_clear_errors();
		libxml_use_internal_errors( $previous_errors );

		if ( false === $xml || ! empty( $xml_errors ) ) {
			$result['status']  = 'error';
			$result['message'] = __( 'Sitemap XML is malformed.', 'plugin-wpshadow' );
			$result['issues']  = array();
			foreach ( $xml_errors as $error ) {
				$result['issues'][] = sprintf(
					/* translators: 1: line number, 2: error message */
					__( 'Line %1$d: %2$s', 'plugin-wpshadow' ),
					$error->line,
					trim( $error->message )
				);
			}
			$result['recommendations'] = array(
				__( 'Fix XML syntax errors in your sitemap.', 'plugin-wpshadow' ),
				__( 'Regenerate the sitemap using your SEO plugin.', 'plugin-wpshadow' ),
			);
			return $result;
		}

		// Check for common sitemap elements.
		$issues          = array();
		$recommendations = array();

		// Check if it's a sitemap index or regular sitemap.
		$has_urlset  = isset( $xml->url );
		$has_sitemap = isset( $xml->sitemap );

		if ( ! $has_urlset && ! $has_sitemap ) {
			$issues[] = __( 'No URL entries or sitemap references found.', 'plugin-wpshadow' );
			$recommendations[] = __( 'Ensure your sitemap contains URL entries.', 'plugin-wpshadow' );
		}

		// Check for required namespaces.
		$namespaces = $xml->getNamespaces( true );
		if ( empty( $namespaces ) || ! isset( $namespaces[''] ) ) {
			$issues[] = __( 'Missing required XML namespace declaration.', 'plugin-wpshadow' );
			$recommendations[] = __( 'Sitemap should declare the namespace xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"', 'plugin-wpshadow' );
		}

		// Validate URLs if present.
		if ( $has_urlset ) {
			$url_issues = $this->validate_sitemap_urls( $xml );
			$issues     = array_merge( $issues, $url_issues );
		}

		// Set final status.
		if ( empty( $issues ) ) {
			$result['status']  = 'success';
			$result['message'] = __( 'Sitemap is valid and accessible.', 'plugin-wpshadow' );
		} else {
			$result['status']          = 'warning';
			$result['message']         = __( 'Sitemap has some issues.', 'plugin-wpshadow' );
			$result['issues']          = $issues;
			$result['recommendations'] = $recommendations;
		}

		return $result;
	}

	/**
	 * Validate sitemap URLs.
	 *
	 * @param \SimpleXMLElement $xml Sitemap XML object.
	 * @return array Issues found.
	 */
	private function validate_sitemap_urls( \SimpleXMLElement $xml ): array {
		$issues = array();

		// Check first few URLs for validity.
		$count       = 0;
		$max_to_check = 5; // Only check first 5 URLs to avoid performance issues.

		foreach ( $xml->url as $url ) {
			if ( $count >= $max_to_check ) {
				break;
			}

			$loc = (string) $url->loc;
			if ( empty( $loc ) ) {
				$issues[] = __( 'Found URL entry without location (loc) element.', 'plugin-wpshadow' );
				continue;
			}

			// Validate URL format.
			if ( ! filter_var( $loc, FILTER_VALIDATE_URL ) ) {
				$issues[] = sprintf(
					/* translators: %s: invalid URL */
					__( 'Invalid URL format: %s', 'plugin-wpshadow' ),
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
	 * @return array Validation result.
	 */
	private function validate_robots(): array {
		$robots_url = home_url( '/robots.txt' );
		$result     = array(
			'status'          => 'error',
			'message'         => '',
			'issues'          => array(),
			'recommendations' => array(),
		);

		// Check if robots.txt is accessible.
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
			$result['message'] = __( 'Robots.txt is not accessible.', 'plugin-wpshadow' );
			$result['issues']  = array( $response->get_error_message() );
			$result['recommendations'] = array(
				sprintf(
					/* translators: %s: robots.txt URL */
					__( 'Ensure robots.txt is accessible at <a href="%s" target="_blank">%s</a>', 'plugin-wpshadow' ),
					esc_url( $robots_url ),
					esc_html( $robots_url )
				),
				__( 'WordPress generates a virtual robots.txt by default. Check if a physical file exists that may be interfering.', 'plugin-wpshadow' ),
			);
			return $result;
		}

		$status_code = wp_remote_retrieve_response_code( $response );
		$body        = wp_remote_retrieve_body( $response );

		// Check HTTP status.
		if ( 200 !== $status_code ) {
			$result['status']  = 'error';
			$result['message'] = sprintf(
				/* translators: %d: HTTP status code */
				__( 'Robots.txt returned HTTP status %d.', 'plugin-wpshadow' ),
				$status_code
			);
			$result['recommendations'] = array(
				__( 'Check your server configuration and permalink settings.', 'plugin-wpshadow' ),
			);
			return $result;
		}

		if ( empty( $body ) ) {
			$result['status']  = 'warning';
			$result['message'] = __( 'Robots.txt is empty.', 'plugin-wpshadow' );
			$result['recommendations'] = array(
				__( 'Consider adding directives to guide search engine crawlers.', 'plugin-wpshadow' ),
				__( 'You can customize robots.txt using your SEO plugin or a custom file in the root directory.', 'plugin-wpshadow' ),
			);
			return $result;
		}

		// Validate robots.txt content.
		$issues          = $this->validate_robots_content( $body );
		$recommendations = array();

		// Check for sitemap reference.
		if ( ! preg_match( '/^Sitemap:/mi', $body ) ) {
			$recommendations[] = sprintf(
				/* translators: %s: sitemap URL */
				__( 'Add a Sitemap directive to your robots.txt: <code>Sitemap: %s</code>', 'plugin-wpshadow' ),
				esc_html( home_url( '/sitemap.xml' ) )
			);
		}

		// Check for common blocking patterns that may be problematic.
		if ( preg_match( '/Disallow:\s*\/$/m', $body ) ) {
			$issues[] = __( 'Found "Disallow: /" which blocks all crawlers from your entire site.', 'plugin-wpshadow' );
			$recommendations[] = __( 'Remove or modify the "Disallow: /" directive if you want search engines to index your site.', 'plugin-wpshadow' );
		}

		// Check if wp-content or wp-includes are blocked (which is generally good).
		$blocks_uploads = preg_match( '/Disallow:.*wp-content\/uploads/i', $body );
		if ( $blocks_uploads ) {
			$recommendations[] = __( 'Your robots.txt blocks wp-content/uploads. This may prevent indexing of media files. Remove this if you want images indexed.', 'plugin-wpshadow' );
		}

		// Set final status.
		if ( empty( $issues ) && empty( $recommendations ) ) {
			$result['status']  = 'success';
			$result['message'] = __( 'Robots.txt is valid and properly configured.', 'plugin-wpshadow' );
		} elseif ( empty( $issues ) ) {
			$result['status']          = 'success';
			$result['message']         = __( 'Robots.txt is valid with minor recommendations.', 'plugin-wpshadow' );
			$result['recommendations'] = $recommendations;
		} else {
			$result['status']          = 'warning';
			$result['message']         = __( 'Robots.txt has some issues.', 'plugin-wpshadow' );
			$result['issues']          = $issues;
			$result['recommendations'] = $recommendations;
		}

		return $result;
	}

	/**
	 * Validate robots.txt content.
	 *
	 * @param string $content Robots.txt content.
	 * @return array Issues found.
	 */
	private function validate_robots_content( string $content ): array {
		$issues = array();
		$lines  = explode( "\n", $content );

		foreach ( $lines as $line_num => $line ) {
			$line = trim( $line );

			// Skip empty lines and comments.
			if ( empty( $line ) || strpos( $line, '#' ) === 0 ) {
				continue;
			}

			// Check for valid directives.
			$valid_directives = array( 'User-agent', 'Disallow', 'Allow', 'Sitemap', 'Crawl-delay' );
			$has_valid_directive = false;

			foreach ( $valid_directives as $directive ) {
				if ( stripos( $line, $directive . ':' ) === 0 ) {
					$has_valid_directive = true;
					break;
				}
			}

			if ( ! $has_valid_directive && ! empty( $line ) ) {
				$issues[] = sprintf(
					/* translators: 1: line number, 2: line content */
					__( 'Line %1$d contains unrecognized directive: %2$s', 'plugin-wpshadow' ),
					$line_num + 1,
					esc_html( substr( $line, 0, 50 ) )
				);
			}
		}

		return $issues;
	}

	/**
	 * Initialize the feature.
	 *
	 * @return void
	 */
	public static function init(): void {
		// This method is called when feature is first loaded.
		// Nothing needed here as hooks are registered in constructor.
	}
}
