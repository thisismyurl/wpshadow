<?php
/**
 * Post-Fix Education System
 *
 * Provides educational context after treatments are applied.
 * Explains what was fixed, why it matters, and how to prevent future issues.
 *
 * @package    WPShadow
 * @subpackage Content
 * @since      1.2604.0100
 */

declare(strict_types=1);

namespace WPShadow\Content;

use WPShadow\Core\UTM_Link_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Post-Fix Education Class
 *
 * Provides learning opportunities after fixes are applied.
 *
 * @since 1.2604.0100
 */
class Post_Fix_Education {

	/**
	 * Initialize system
	 *
	 * @since  1.2604.0100
	 * @return void
	 */
	public static function init() {
		add_action( 'wpshadow_after_treatment_apply', array( __CLASS__, 'show_education' ), 10, 3 );
	}

	/**
	 * Show post-fix education
	 *
	 * @since  1.2604.0100
	 * @param  string $class      Treatment class name.
	 * @param  string $finding_id Finding ID.
	 * @param  array  $result     Treatment result.
	 * @return void
	 */
	public static function show_education( string $class, string $finding_id, array $result ) {
		if ( ! $result['success'] ) {
			return; // Only show for successful fixes
		}

		// Get education content
		$education = self::get_education_content( $finding_id );
		if ( ! $education ) {
			return;
		}

		// Store in session for display on next page load
		\WPShadow\Core\Cache_Manager::set(
			'education_' . get_current_user_id(),
			$education,
			'wpshadow_education',
			300 // 5 minutes
		);
	}

	/**
	 * Render education notice
	 *
	 * Call this after treatment success message
	 *
	 * @since  1.2604.0100
	 * @return void
	 */
	public static function render_education_notice() {
		$user_id = get_current_user_id();
	$education = \WPShadow\Core\Cache_Manager::get(
		'education_' . $user_id,
		'wpshadow_education'
	);
		if ( ! $education ) {
			return;
		}

		// Clear transient
		\WPShadow\Core\Cache_Manager::delete(
			'education_' . $user_id,
			'wpshadow_education'
		);

		?>
		<div class="wpshadow-education-notice">
			<div class="wpshadow-education-header">
				<span class="dashicons dashicons-welcome-learn-more"></span>
				<h3><?php esc_html_e( 'What We Just Fixed & Why It Matters', 'wpshadow' ); ?></h3>
			</div>

			<div class="wpshadow-education-content">
				<div class="wpshadow-education-section">
					<h4>
						<span class="dashicons dashicons-admin-tools"></span>
						<?php esc_html_e( 'What We Did', 'wpshadow' ); ?>
					</h4>
					<p><?php echo wp_kses_post( $education['what'] ); ?></p>
				</div>

				<div class="wpshadow-education-section">
					<h4>
						<span class="dashicons dashicons-lightbulb"></span>
						<?php esc_html_e( 'Why This Matters', 'wpshadow' ); ?>
					</h4>
					<p><?php echo wp_kses_post( $education['why'] ); ?></p>
				</div>

				<?php if ( ! empty( $education['prevent'] ) ) : ?>
					<div class="wpshadow-education-section">
						<h4>
							<span class="dashicons dashicons-shield"></span>
							<?php esc_html_e( 'Preventing Future Issues', 'wpshadow' ); ?>
						</h4>
						<ul>
							<?php foreach ( $education['prevent'] as $tip ) : ?>
								<li><?php echo esc_html( $tip ); ?></li>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php endif; ?>

				<?php if ( ! empty( $education['learn_more'] ) ) : ?>
					<div class="wpshadow-education-actions">
						<h4><?php esc_html_e( 'Want to Learn More?', 'wpshadow' ); ?></h4>
						<div class="wpshadow-education-links">
							<?php foreach ( $education['learn_more'] as $link ) : ?>
								<a href="<?php echo esc_url( $link['url'] ); ?>" 
								   target="_blank" 
								   class="button button-secondary">
									<span class="dashicons dashicons-<?php echo esc_attr( $link['icon'] ?? 'external' ); ?>"></span>
									<?php echo esc_html( $link['text'] ); ?>
								</a>
							<?php endforeach; ?>
						</div>
					</div>
				<?php endif; ?>
			</div>

			<div class="wpshadow-education-footer">
				<p>
					<em>
						<?php esc_html_e( 'Understanding these fixes helps you maintain a healthier, more secure WordPress site.', 'wpshadow' ); ?>
					</em>
				</p>
			</div>
		</div>

		<style>
		.wpshadow-education-notice {
			background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
			color: #fff;
			padding: 25px;
			border-radius: 8px;
			margin: 20px 0;
			box-shadow: 0 4px 12px rgba(0,0,0,0.15);
		}
		.wpshadow-education-header {
			display: flex;
			align-items: center;
			gap: 12px;
			margin-bottom: 20px;
			padding-bottom: 15px;
			border-bottom: 1px solid rgba(255,255,255,0.2);
		}
		.wpshadow-education-header .dashicons {
			font-size: 32px;
			width: 32px;
			height: 32px;
		}
		.wpshadow-education-header h3 {
			margin: 0;
			color: #fff;
			font-size: 20px;
			font-weight: 600;
		}
		.wpshadow-education-content {
			background: rgba(255,255,255,0.95);
			padding: 20px;
			border-radius: 6px;
			color: #1d2327;
		}
		.wpshadow-education-section {
			margin-bottom: 20px;
		}
		.wpshadow-education-section:last-child {
			margin-bottom: 0;
		}
		.wpshadow-education-section h4 {
			display: flex;
			align-items: center;
			gap: 8px;
			margin: 0 0 10px;
			color: #2271b1;
			font-size: 16px;
			font-weight: 600;
		}
		.wpshadow-education-section .dashicons {
			font-size: 20px;
			width: 20px;
			height: 20px;
		}
		.wpshadow-education-section p {
			margin: 0;
			line-height: 1.6;
			color: #50575e;
		}
		.wpshadow-education-section ul {
			margin: 0;
			padding-left: 28px;
		}
		.wpshadow-education-section li {
			margin-bottom: 8px;
			color: #50575e;
		}
		.wpshadow-education-actions {
			margin-top: 20px;
			padding-top: 20px;
			border-top: 1px solid #ddd;
		}
		.wpshadow-education-actions h4 {
			margin: 0 0 12px;
			color: #1d2327;
			font-size: 14px;
			font-weight: 600;
		}
		.wpshadow-education-links {
			display: flex;
			gap: 10px;
			flex-wrap: wrap;
		}
		.wpshadow-education-links .button {
			display: inline-flex;
			align-items: center;
			gap: 6px;
		}
		.wpshadow-education-links .dashicons {
			margin-top: 3px;
		}
		.wpshadow-education-footer {
			margin-top: 15px;
			padding-top: 15px;
			border-top: 1px solid rgba(255,255,255,0.2);
		}
		.wpshadow-education-footer p {
			margin: 0;
			font-size: 14px;
			color: rgba(255,255,255,0.9);
			text-align: center;
		}
		</style>
		<?php
	}

	/**
	 * Get education content for finding
	 *
	 * @since  1.2604.0100
	 * @param  string $finding_id Finding ID.
	 * @return array|null Education content or null if not available.
	 */
	private static function get_education_content( string $finding_id ): ?array {
		$content_map = array(
			'security-file-permissions' => array(
				'what'    => __( 'We adjusted file permissions on your WordPress installation to follow security best practices. Directories are now set to 755 and files to 644, which prevents unauthorized modifications while maintaining normal functionality.', 'wpshadow' ),
				'why'     => __( 'Incorrect file permissions can allow attackers to modify your core WordPress files, inject malicious code, or steal sensitive information. Proper permissions create a barrier that makes exploitation significantly harder.', 'wpshadow' ),
				'prevent' => array(
					__( 'Check file permissions after manual file uploads', 'wpshadow' ),
					__( 'Use SFTP (not FTP) to avoid permission changes', 'wpshadow' ),
					__( 'Don\'t set 777 permissions, even temporarily', 'wpshadow' ),
				),
				'learn_more' => array(
					array(
						'text' => __( '5-Min Video: File Permissions Explained', 'wpshadow' ),
						'url'  => UTM_Link_Manager::academy_link( 'file-permissions', 'post-fix' ),
						'icon' => 'video-alt3',
					),
					array(
						'text' => __( 'Read: Security Best Practices', 'wpshadow' ),
						'url'  => UTM_Link_Manager::kb_link( 'file-permissions', 'post-fix' ),
						'icon' => 'book-alt',
					),
				),
			),
			'performance-memory-limit' => array(
				'what'    => __( 'We increased your PHP memory limit to 256MB (or higher) by updating your wp-config.php file. This gives WordPress more room to operate, especially during resource-intensive operations like updates or image processing.', 'wpshadow' ),
				'why'     => __( 'Memory limit errors cause "white screen of death" problems and failed operations. A higher limit prevents crashes when multiple plugins are active or when processing large amounts of data.', 'wpshadow' ),
				'prevent' => array(
					__( 'Monitor memory usage via WPShadow dashboard', 'wpshadow' ),
					__( 'Audit plugins regularly for memory leaks', 'wpshadow' ),
					__( 'Consider upgrading hosting if limit is often reached', 'wpshadow' ),
				),
				'learn_more' => array(
					array(
						'text' => __( 'Understanding Memory Limits', 'wpshadow' ),
						'url'  => UTM_Link_Manager::kb_link( 'memory-limit', 'post-fix' ),
						'icon' => 'book-alt',
					),
				),
			),
			'seo-meta-description' => array(
				'what'    => __( 'We added meta descriptions to pages that were missing them. These descriptions appear in search results and help users understand what your page is about before clicking.', 'wpshadow' ),
				'why'     => __( 'Meta descriptions don\'t directly affect rankings, but they dramatically impact click-through rates. A compelling description can double or triple your search traffic from the same ranking position.', 'wpshadow' ),
				'prevent' => array(
					__( 'Write unique descriptions for each important page', 'wpshadow' ),
					__( 'Keep them between 150-160 characters', 'wpshadow' ),
					__( 'Include target keywords naturally', 'wpshadow' ),
				),
				'learn_more' => array(
					array(
						'text' => __( 'Writing Great Meta Descriptions', 'wpshadow' ),
						'url'  => UTM_Link_Manager::academy_link( 'meta-descriptions', 'post-fix' ),
						'icon' => 'video-alt3',
					),
					array(
						'text' => __( 'SEO Fundamentals Guide', 'wpshadow' ),
						'url'  => UTM_Link_Manager::kb_link( 'seo-guide', 'post-fix' ),
						'icon' => 'book-alt',
					),
				),
			),
		);

		/**
		 * Filter post-fix education content
		 *
		 * @since 1.2604.0100
		 *
		 * @param array|null $content    Education content.
		 * @param string     $finding_id Finding ID.
		 */
		return apply_filters( 'wpshadow_postfix_education', $content_map[ $finding_id ] ?? null, $finding_id );
	}
}

// Initialize
Post_Fix_Education::init();
