<?php
/**
 * KB Article Shortcodes Handler
 *
 * Handles visual content shortcodes for KB articles:
 * - [wpshadow_image] - Images, diagrams, screenshots
 * - [wpshadow_video] - Embedded videos
 * - [wpshadow_screenshot] - Annotated UI screenshots
 * - [wpshadow_cta] - Call-to-action blocks
 *
 * @package WPShadow
 * @subpackage ProModules\KB
 */

declare(strict_types=1);

namespace WPShadow_Pro\Modules\KB;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * KB Shortcodes class.
 */
class KB_Shortcodes {
	/**
	 * Register all shortcodes.
	 */
	public static function register(): void {
		add_shortcode( 'wpshadow_image', [ __CLASS__, 'render_image' ] );
		add_shortcode( 'wpshadow_video', [ __CLASS__, 'render_video' ] );
		add_shortcode( 'wpshadow_screenshot', [ __CLASS__, 'render_screenshot' ] );
		add_shortcode( 'wpshadow_cta', [ __CLASS__, 'render_cta' ] );
	}

	/**
	 * Render image shortcode.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public static function render_image( $atts ): string {
		$atts = shortcode_atts(
			[
				'id'      => '',
				'alt'     => '',
				'caption' => '',
				'size'    => 'large',
			],
			$atts,
			'wpshadow_image'
		);

		if ( empty( $atts['id'] ) || empty( $atts['alt'] ) ) {
			return '<!-- wpshadow_image: Missing required attributes (id, alt) -->';
		}

		// TODO: Replace with API-generated image lookup
		// For now, return placeholder
		$width  = 800;
		$height = 450;
		$bg_color = dechex( rand( 0xE0E0E0, 0xF5F5F5 ) );
		
		$html = '<figure class="wpshadow-kb-image wpshadow-kb-image-' . esc_attr( $atts['size'] ) . '">';
		$html .= '<img src="https://via.placeholder.com/' . $width . 'x' . $height . '/' . $bg_color . '/666666?text=' . urlencode( $atts['id'] ) . '" ';
		$html .= 'alt="' . esc_attr( $atts['alt'] ) . '" ';
		$html .= 'class="wpshadow-kb-image-placeholder" ';
		$html .= 'data-image-id="' . esc_attr( $atts['id'] ) . '" ';
		$html .= 'loading="lazy" />';
		
		if ( ! empty( $atts['caption'] ) ) {
			$html .= '<figcaption class="wpshadow-kb-caption">' . esc_html( $atts['caption'] ) . '</figcaption>';
		}
		
		$html .= '</figure>';

		return $html;
	}

	/**
	 * Render video shortcode.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public static function render_video( $atts ): string {
		$atts = shortcode_atts(
			[
				'id'       => '',
				'caption'  => '',
				'duration' => '',
				'autoplay' => 'false',
			],
			$atts,
			'wpshadow_video'
		);

		if ( empty( $atts['id'] ) ) {
			return '<!-- wpshadow_video: Missing required attribute (id) -->';
		}

		// TODO: Replace with actual video embed (YouTube, Vimeo, or hosted)
		// For now, return placeholder
		$html = '<div class="wpshadow-kb-video">';
		$html .= '<div class="wpshadow-kb-video-placeholder" data-video-id="' . esc_attr( $atts['id'] ) . '">';
		$html .= '<div class="video-placeholder-content">';
		$html .= '<svg width="80" height="80" viewBox="0 0 80 80" fill="none"><circle cx="40" cy="40" r="40" fill="#4CAF50"/><path d="M32 25L58 40L32 55V25Z" fill="white"/></svg>';
		$html .= '<p class="video-placeholder-text"><strong>Video:</strong> ' . esc_html( $atts['id'] ) . '</p>';
		
		if ( ! empty( $atts['duration'] ) ) {
			$html .= '<p class="video-duration">' . esc_html( $atts['duration'] ) . '</p>';
		}
		
		$html .= '</div>';
		$html .= '</div>';
		
		if ( ! empty( $atts['caption'] ) ) {
			$html .= '<p class="wpshadow-kb-caption">' . esc_html( $atts['caption'] ) . '</p>';
		}
		
		$html .= '</div>';

		return $html;
	}

	/**
	 * Render screenshot shortcode.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public static function render_screenshot( $atts ): string {
		$atts = shortcode_atts(
			[
				'id'        => '',
				'alt'       => '',
				'highlight' => '',
				'annotate'  => '',
			],
			$atts,
			'wpshadow_screenshot'
		);

		if ( empty( $atts['id'] ) || empty( $atts['alt'] ) ) {
			return '<!-- wpshadow_screenshot: Missing required attributes (id, alt) -->';
		}

		// TODO: Replace with API-captured screenshots with annotations
		// For now, return placeholder similar to image
		$width  = 1200;
		$height = 675;
		
		$html = '<figure class="wpshadow-kb-screenshot">';
		$html .= '<img src="https://via.placeholder.com/' . $width . 'x' . $height . '/EFEFEF/555555?text=' . urlencode( 'Screenshot: ' . $atts['id'] ) . '" ';
		$html .= 'alt="' . esc_attr( $atts['alt'] ) . '" ';
		$html .= 'class="wpshadow-kb-screenshot-placeholder" ';
		$html .= 'data-screenshot-id="' . esc_attr( $atts['id'] ) . '" ';
		
		if ( ! empty( $atts['highlight'] ) ) {
			$html .= 'data-highlight="' . esc_attr( $atts['highlight'] ) . '" ';
		}
		
		if ( ! empty( $atts['annotate'] ) ) {
			$html .= 'data-annotate="' . esc_attr( $atts['annotate'] ) . '" ';
		}
		
		$html .= 'loading="lazy" />';
		$html .= '</figure>';

		return $html;
	}

	/**
	 * Render CTA shortcode.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public static function render_cta( $atts ): string {
		$atts = shortcode_atts(
			[
				'id'      => 'pro-services',
				'variant' => 'standard',
			],
			$atts,
			'wpshadow_cta'
		);

		$templates = [
			'pro-services' => [
				'title'   => 'Get Expert Help',
				'content' => 'Let WPShadow Pro Services handle WordPress management completely. 24/7 monitoring, automated backups, security hardening, and emergency support.',
				'button'  => 'Learn About Pro Services',
				'url'     => 'https://wpshadow.com/pro-services',
			],
			'academy'      => [
				'title'   => 'FREE Training Available',
				'content' => 'Join WPShadow Academy for free WordPress management training. Learn best practices, avoid common pitfalls, and become the WordPress expert your team relies on.',
				'button'  => 'Enroll Free',
				'url'     => 'https://wpshadow.com/academy',
			],
			'vault'        => [
				'title'   => 'Need Reliable Backups?',
				'content' => 'WPShadow Vault provides one-click backups and instant restore. Never lose data again with automated, secure backup storage.',
				'button'  => 'Try Vault Free',
				'url'     => 'https://wpshadow.com/vault',
			],
			'support'      => [
				'title'   => 'Stuck? We Can Help',
				'content' => 'Our WordPress experts are standing by to help troubleshoot, optimize, and secure your site. Get personalized support when you need it.',
				'button'  => 'Contact Support',
				'url'     => 'https://wpshadow.com/support',
			],
		];

		$cta = $templates[ $atts['id'] ] ?? $templates['pro-services'];

		$variant_class = 'wpshadow-kb-cta-' . esc_attr( $atts['variant'] );

		$html = '<div class="wpshadow-kb-cta ' . $variant_class . '">';
		$html .= '<h3 class="cta-title">' . esc_html( $cta['title'] ) . '</h3>';
		$html .= '<p class="cta-content">' . esc_html( $cta['content'] ) . '</p>';
		$html .= '<a href="' . esc_url( $cta['url'] ) . '" class="cta-button" target="_blank" rel="noopener">';
		$html .= esc_html( $cta['button'] ) . ' →';
		$html .= '</a>';
		$html .= '</div>';

		return $html;
	}
}
