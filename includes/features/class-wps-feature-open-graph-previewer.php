<?php
/**
 * Open Graph Previewer feature definition.
 *
 * Analyzes how website links appear when shared on social media platforms
 * like LinkedIn and X (Twitter). Checks for missing Open Graph tags.
 *
 * @package wpshadow_SUPPORT
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPSHADOW_Feature_Open_Graph_Previewer extends WPSHADOW_Abstract_Feature {
	
	/**
	 * Initialize feature.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'wpshadow_open_graph_previewer',
				'name'               => __( 'Social Media Open Graph Previewer', 'plugin-wpshadow' ),
				'description'        => __( 'Analyzes how your website links appear when shared on platforms like LinkedIn or X (formerly Twitter). Checks for missing Open Graph tags that control shared images, titles, and descriptions.', 'plugin-wpshadow' ),
				'scope'              => 'core',
				'version'            => '1.0.0',
				'default_enabled'    => true,
				'widget_group'       => 'seo',
				'widget_label'       => __( 'SEO & Social Media', 'plugin-wpshadow' ),
				'widget_description' => __( 'SEO and social media optimization features', 'plugin-wpshadow' ),
				'icon'               => 'dashicons-share',
				'category'           => 'seo',
				'priority'           => 15,
				'dashboard'          => 'overview',
				'widget_column'      => 'left',
				'widget_priority'    => 20,
			)
		);
	}

	/**
	 * Initialize the feature.
	 *
	 * @return void
	 */
	public static function init(): void {
		$instance = new self();
		if ( $instance->is_enabled() ) {
			add_action( 'admin_menu', array( $instance, 'add_admin_menu' ) );
			add_action( 'wp_ajax_wpshadow_analyze_open_graph', array( $instance, 'ajax_analyze_open_graph' ) );
		}
	}

	/**
	 * Add admin menu item.
	 *
	 * @return void
	 */
	public function add_admin_menu(): void {
		add_submenu_page(
			'wpshadow',
			__( 'Open Graph Previewer', 'plugin-wpshadow' ),
			__( 'Open Graph', 'plugin-wpshadow' ),
			'manage_options',
			'wpshadow-open-graph',
			array( $this, 'render_admin_page' )
		);
	}

	/**
	 * Render admin page.
	 *
	 * @return void
	 */
	public function render_admin_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'plugin-wpshadow' ) );
		}

		$url = isset( $_GET['url'] ) ? esc_url_raw( wp_unslash( $_GET['url'] ) ) : home_url();
		$analysis = $this->analyze_page( $url );

		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<p><?php esc_html_e( 'Analyze how your website pages appear when shared on social media platforms like LinkedIn and X (Twitter).', 'plugin-wpshadow' ); ?></p>

			<form method="get" action="" style="margin: 20px 0;">
				<input type="hidden" name="page" value="wpshadow-open-graph" />
				<label for="url-input" style="font-weight: 600;">
					<?php esc_html_e( 'Page URL to analyze:', 'plugin-wpshadow' ); ?>
				</label>
				<br/>
				<input type="url" id="url-input" name="url" value="<?php echo esc_attr( $url ); ?>" 
					   style="width: 100%; max-width: 600px; margin-top: 8px;" 
					   placeholder="<?php esc_attr_e( 'Enter URL to analyze', 'plugin-wpshadow' ); ?>" />
				<br/>
				<button type="submit" class="button button-primary" style="margin-top: 10px;">
					<?php esc_html_e( 'Analyze', 'plugin-wpshadow' ); ?>
				</button>
			</form>

			<?php if ( ! empty( $analysis ) ) : ?>
				<div class="wps-og-analysis" style="margin-top: 30px;">
					<?php $this->render_analysis_results( $analysis, $url ); ?>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Analyze Open Graph tags on a page.
	 *
	 * @param string $url URL to analyze.
	 * @return array Analysis results.
	 */
	private function analyze_page( string $url ): array {
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
			return array(
				'error' => $response->get_error_message(),
			);
		}

		$html = wp_remote_retrieve_body( $response );
		return $this->parse_open_graph_tags( $html, $url );
	}

	/**
	 * Parse Open Graph tags from HTML.
	 *
	 * @param string $html HTML content.
	 * @param string $url Source URL.
	 * @return array Parsed tags and analysis.
	 */
	private function parse_open_graph_tags( string $html, string $url ): array {
		$tags = array();
		$required_tags = array(
			'og:title'       => __( 'Title', 'plugin-wpshadow' ),
			'og:description' => __( 'Description', 'plugin-wpshadow' ),
			'og:image'       => __( 'Image', 'plugin-wpshadow' ),
			'og:url'         => __( 'URL', 'plugin-wpshadow' ),
			'og:type'        => __( 'Type', 'plugin-wpshadow' ),
		);

		$twitter_tags = array(
			'twitter:card'        => __( 'Card Type', 'plugin-wpshadow' ),
			'twitter:title'       => __( 'Title', 'plugin-wpshadow' ),
			'twitter:description' => __( 'Description', 'plugin-wpshadow' ),
			'twitter:image'       => __( 'Image', 'plugin-wpshadow' ),
		);

		// Extract Open Graph tags
		preg_match_all(
			'/<meta\s+property=["\']og:([^"\']+)["\']\s+content=["\']([^"\']+)["\']/i',
			$html,
			$og_matches,
			PREG_SET_ORDER
		);

		foreach ( $og_matches as $match ) {
			$property = 'og:' . $match[1];
			$tags[ $property ] = $match[2];
		}

		// Extract Twitter Card tags
		preg_match_all(
			'/<meta\s+name=["\']twitter:([^"\']+)["\']\s+content=["\']([^"\']+)["\']/i',
			$html,
			$twitter_matches,
			PREG_SET_ORDER
		);

		foreach ( $twitter_matches as $match ) {
			$property = 'twitter:' . $match[1];
			$tags[ $property ] = $match[2];
		}

		// Analyze missing tags
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
	 * Render analysis results.
	 *
	 * @param array  $analysis Analysis results.
	 * @param string $url Analyzed URL.
	 * @return void
	 */
	private function render_analysis_results( array $analysis, string $url ): void {
		if ( isset( $analysis['error'] ) ) {
			?>
			<div class="notice notice-error">
				<p>
					<strong><?php esc_html_e( 'Error:', 'plugin-wpshadow' ); ?></strong>
					<?php echo esc_html( $analysis['error'] ); ?>
				</p>
			</div>
			<?php
			return;
		}

		$tags = $analysis['tags'] ?? array();
		$missing = $analysis['missing'] ?? array();
		$missing_twitter = $analysis['missing_twitter'] ?? array();
		$required_tags = $analysis['required_tags'] ?? array();
		$twitter_tags = $analysis['twitter_tags'] ?? array();

		// Status overview
		$og_complete = empty( $missing );
		$twitter_complete = empty( $missing_twitter );
		?>

		<div class="wps-og-status" style="background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; padding: 20px; margin-bottom: 20px;">
			<h2 style="margin-top: 0;"><?php esc_html_e( 'Analysis Summary', 'plugin-wpshadow' ); ?></h2>
			<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
				<div>
					<h3 style="margin-top: 0;">
						<span class="dashicons dashicons-share" style="color: #3b5998;"></span>
						<?php esc_html_e( 'Open Graph (LinkedIn, Facebook)', 'plugin-wpshadow' ); ?>
					</h3>
					<?php if ( $og_complete ) : ?>
						<p style="color: #46b450; font-weight: 600;">
							<span class="dashicons dashicons-yes-alt"></span>
							<?php esc_html_e( 'All required tags found', 'plugin-wpshadow' ); ?>
						</p>
					<?php else : ?>
						<p style="color: #dc3232; font-weight: 600;">
							<span class="dashicons dashicons-warning"></span>
							<?php
							printf(
								/* translators: %d: number of missing tags */
								esc_html( _n( '%d required tag missing', '%d required tags missing', count( $missing ), 'plugin-wpshadow' ) ),
								count( $missing )
							);
							?>
						</p>
					<?php endif; ?>
				</div>
				<div>
					<h3 style="margin-top: 0;">
						<span class="dashicons dashicons-twitter" style="color: #1da1f2;"></span>
						<?php esc_html_e( 'Twitter Card', 'plugin-wpshadow' ); ?>
					</h3>
					<?php if ( $twitter_complete ) : ?>
						<p style="color: #46b450; font-weight: 600;">
							<span class="dashicons dashicons-yes-alt"></span>
							<?php esc_html_e( 'All required tags found', 'plugin-wpshadow' ); ?>
						</p>
					<?php else : ?>
						<p style="color: #dc3232; font-weight: 600;">
							<span class="dashicons dashicons-warning"></span>
							<?php
							printf(
								/* translators: %d: number of missing tags */
								esc_html( _n( '%d tag missing', '%d tags missing', count( $missing_twitter ), 'plugin-wpshadow' ) ),
								count( $missing_twitter )
							);
							?>
						</p>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<?php if ( ! empty( $missing ) ) : ?>
			<div class="notice notice-warning" style="margin-bottom: 20px;">
				<h3><?php esc_html_e( 'Missing Open Graph Tags', 'plugin-wpshadow' ); ?></h3>
				<ul>
					<?php foreach ( $missing as $tag ) : ?>
						<li><code><?php echo esc_html( $tag ); ?></code> - <?php echo esc_html( $required_tags[ $tag ] ); ?></li>
					<?php endforeach; ?>
				</ul>
				<p><em><?php esc_html_e( 'These tags are important for how your content appears on LinkedIn, Facebook, and other platforms.', 'plugin-wpshadow' ); ?></em></p>
			</div>
		<?php endif; ?>

		<?php if ( ! empty( $missing_twitter ) ) : ?>
			<div class="notice notice-warning" style="margin-bottom: 20px;">
				<h3><?php esc_html_e( 'Missing Twitter Card Tags', 'plugin-wpshadow' ); ?></h3>
				<ul>
					<?php foreach ( $missing_twitter as $tag ) : ?>
						<li><code><?php echo esc_html( $tag ); ?></code> - <?php echo esc_html( $twitter_tags[ $tag ] ); ?></li>
					<?php endforeach; ?>
				</ul>
				<p><em><?php esc_html_e( 'These tags control how your content appears on X (Twitter).', 'plugin-wpshadow' ); ?></em></p>
			</div>
		<?php endif; ?>

		<!-- Preview Section -->
		<div style="background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; padding: 20px; margin-bottom: 20px;">
			<h2 style="margin-top: 0;"><?php esc_html_e( 'Social Media Previews', 'plugin-wpshadow' ); ?></h2>
			
			<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
				<!-- LinkedIn/Facebook Preview -->
				<div>
					<h3><?php esc_html_e( 'LinkedIn / Facebook Preview', 'plugin-wpshadow' ); ?></h3>
					<div style="border: 1px solid #ddd; border-radius: 8px; overflow: hidden; background: #fff;">
						<?php if ( ! empty( $tags['og:image'] ) ) : ?>
							<div style="background-color: #f0f0f0; height: 200px; display: flex; align-items: center; justify-content: center; overflow: hidden;">
								<img src="<?php echo esc_url( $tags['og:image'] ); ?>" 
									 alt="<?php esc_attr_e( 'Preview image', 'plugin-wpshadow' ); ?>" 
									 style="max-width: 100%; max-height: 100%; object-fit: cover;" />
							</div>
						<?php else : ?>
							<div style="background-color: #f0f0f0; height: 200px; display: flex; align-items: center; justify-content: center; color: #999;">
								<span class="dashicons dashicons-format-image" style="font-size: 48px;"></span>
							</div>
						<?php endif; ?>
						<div style="padding: 12px;">
							<div style="color: #999; font-size: 12px; margin-bottom: 4px;">
								<?php echo esc_html( wp_parse_url( $url, PHP_URL_HOST ) ); ?>
							</div>
							<div style="font-weight: 600; font-size: 16px; margin-bottom: 4px; color: #1d2129;">
								<?php echo esc_html( $tags['og:title'] ?? __( 'No title', 'plugin-wpshadow' ) ); ?>
							</div>
							<div style="color: #606770; font-size: 14px;">
								<?php
								$description = $tags['og:description'] ?? __( 'No description', 'plugin-wpshadow' );
								echo esc_html( wp_trim_words( $description, 20 ) );
								?>
							</div>
						</div>
					</div>
				</div>

				<!-- Twitter Preview -->
				<div>
					<h3><?php esc_html_e( 'X (Twitter) Preview', 'plugin-wpshadow' ); ?></h3>
					<div style="border: 1px solid #e1e8ed; border-radius: 14px; overflow: hidden; background: #fff;">
						<?php if ( ! empty( $tags['twitter:image'] ) || ! empty( $tags['og:image'] ) ) : ?>
							<div style="background-color: #f0f0f0; height: 200px; display: flex; align-items: center; justify-content: center; overflow: hidden;">
								<img src="<?php echo esc_url( $tags['twitter:image'] ?? $tags['og:image'] ); ?>" 
									 alt="<?php esc_attr_e( 'Preview image', 'plugin-wpshadow' ); ?>" 
									 style="max-width: 100%; max-height: 100%; object-fit: cover;" />
							</div>
						<?php else : ?>
							<div style="background-color: #f0f0f0; height: 200px; display: flex; align-items: center; justify-content: center; color: #999;">
								<span class="dashicons dashicons-format-image" style="font-size: 48px;"></span>
							</div>
						<?php endif; ?>
						<div style="padding: 12px; border-top: 1px solid #e1e8ed;">
							<div style="font-size: 15px; font-weight: 600; margin-bottom: 2px; color: #0f1419;">
								<?php echo esc_html( $tags['twitter:title'] ?? $tags['og:title'] ?? __( 'No title', 'plugin-wpshadow' ) ); ?>
							</div>
							<div style="color: #536471; font-size: 15px; margin-bottom: 2px;">
								<?php
								$twitter_desc = $tags['twitter:description'] ?? $tags['og:description'] ?? __( 'No description', 'plugin-wpshadow' );
								echo esc_html( wp_trim_words( $twitter_desc, 20 ) );
								?>
							</div>
							<div style="color: #536471; font-size: 13px;">
								<span class="dashicons dashicons-admin-links" style="font-size: 13px; vertical-align: middle;"></span>
								<?php echo esc_html( wp_parse_url( $url, PHP_URL_HOST ) ); ?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Found Tags -->
		<div style="background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; padding: 20px;">
			<h2 style="margin-top: 0;"><?php esc_html_e( 'All Detected Tags', 'plugin-wpshadow' ); ?></h2>
			<?php if ( empty( $tags ) ) : ?>
				<p><?php esc_html_e( 'No Open Graph or Twitter Card tags found on this page.', 'plugin-wpshadow' ); ?></p>
			<?php else : ?>
				<table class="widefat striped" style="margin-top: 10px;">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Tag', 'plugin-wpshadow' ); ?></th>
							<th><?php esc_html_e( 'Content', 'plugin-wpshadow' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $tags as $tag => $content ) : ?>
							<tr>
								<td><code><?php echo esc_html( $tag ); ?></code></td>
								<td>
									<?php
									if ( strpos( $tag, 'image' ) !== false && filter_var( $content, FILTER_VALIDATE_URL ) ) {
										echo '<img src="' . esc_url( $content ) . '" alt="" style="max-width: 100px; max-height: 50px; margin-right: 10px; vertical-align: middle;" />';
									}
									echo esc_html( wp_trim_words( $content, 20 ) );
									?>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
		</div>

		<!-- Recommendations -->
		<?php if ( ! empty( $missing ) || ! empty( $missing_twitter ) ) : ?>
			<div style="background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; padding: 20px; margin-top: 20px;">
				<h2 style="margin-top: 0;"><?php esc_html_e( 'Recommendations', 'plugin-wpshadow' ); ?></h2>
				<p><?php esc_html_e( 'To improve how your content appears when shared on social media:', 'plugin-wpshadow' ); ?></p>
				<ul>
					<li><?php esc_html_e( 'Install an SEO plugin like Yoast SEO or Rank Math to automatically add Open Graph tags', 'plugin-wpshadow' ); ?></li>
					<li><?php esc_html_e( 'Ensure each page has a unique title and description', 'plugin-wpshadow' ); ?></li>
					<li><?php esc_html_e( 'Use high-quality images (recommended size: 1200x630px for best results)', 'plugin-wpshadow' ); ?></li>
					<li><?php esc_html_e( 'Test your pages using Facebook\'s Sharing Debugger and Twitter\'s Card Validator', 'plugin-wpshadow' ); ?></li>
				</ul>
			</div>
		<?php endif; ?>
		<?php
	}

	/**
	 * AJAX handler for analyzing Open Graph tags.
	 *
	 * @return void
	 */
	public function ajax_analyze_open_graph(): void {
		check_ajax_referer( 'wpshadow_open_graph', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'plugin-wpshadow' ) ) );
		}

		$url = isset( $_POST['url'] ) ? esc_url_raw( wp_unslash( $_POST['url'] ) ) : '';
		if ( empty( $url ) ) {
			wp_send_json_error( array( 'message' => __( 'URL is required', 'plugin-wpshadow' ) ) );
		}

		$analysis = $this->analyze_page( $url );
		wp_send_json_success( $analysis );
	}
}
