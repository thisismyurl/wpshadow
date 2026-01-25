<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;



class Diagnostic_Ai_Image_Alt_Text extends Diagnostic_Base {
	protected static $slug = 'ai-image-alt-text';

	protected static $title = 'Ai Image Alt Text';

	protected static $description = 'Automatically initialized lean diagnostic for Ai Image Alt Text. Optimized for minimal overhead while surfacing high-value signals.';

	protected static $family = 'general';

	protected static $family_label = 'General';

	/**
	 * Get diagnostic ID
	 */
	public static function get_id(): string {
		return 'ai-image-alt-text';
	}

	/**
	 * Get diagnostic name
	 */
	public static function get_name(): string {
		return __( 'Are images described for AI?', 'wpshadow' );
	}

	/**
	 * Get diagnostic description
	 */
	public static function get_description(): string {
		return __( 'Are images described for AI?. Part of AI & ML Readiness analysis.', 'wpshadow' );
	}

	/**
	 * Get diagnostic category
	 */
	public static function get_category(): string {
		return 'ai_readiness';
	}

	/**
	 * Run the diagnostic test
	 *
	 * @return array Finding data or empty if no issue
	 */
	public static function run(): array {
		// Implement: Are images described for AI? test
		// Smart implementation needed

		return array(); // Stub: full implementation pending
	}

	/**
	 * Get threat level for this finding (0-100)
	 */
	public static function get_threat_level(): int {
		// Threat level based on diagnostic category
		return 60;
	}

	/**
	 * Get KB article URL
	 */
	public static function get_kb_article(): string {
		return 'https://wpshadow.com/kb/ai-image-alt-text/';
	}

	/**
	 * Get training video URL
	 */
	public static function get_training_video(): string {
		return 'https://wpshadow.com/training/ai-image-alt-text/';
	}

	protected static function get_guardian_html(): string {
		if ( isset( $_POST['html'] ) && is_string( $_POST['html'] ) ) {
			return sanitize_text_field( wp_unslash( $_POST['html'] ) );
		}
		return '';
	}

	public static function check(): ?array {
		$html = self::get_guardian_html();
		if ( empty( $html ) ) {
			return null;
		}

		$issues = array();
		try {
			$dom = new \DOMDocument();
			@$dom->loadHTML( $html );
			$xpath = new \DOMXPath( $dom );

			$images = $xpath->query( '//img' );
			foreach ( $images as $img ) {
				if ( ! $img->hasAttribute( 'alt' ) || empty( trim( $img->getAttribute( 'alt' ) ) ) ) {
					$issues[] = 'Image with descriptive alt text missing';
					break;
				}
			}
		} catch ( \Exception $e ) {
			return null;
		}

		return empty( $issues ) ? null : array(
			'id'           => 'ai-image-alt-text',
			'title'        => 'Images lack AI-readable alt text',
			'description'  => 'Add descriptive alt text for AI content analysis',
			'severity'     => 'high',
			'category'     => 'ai_readiness',
			'threat_level' => 65,
			'details'      => $issues,
		);
	}

	public static function test_live_ai_image_alt_text(): array {
		$good = '<html><body><img src="test.jpg" alt="Descriptive alt text for AI"></body></html>';
		$bad  = '<html><body><img src="test.jpg"></body></html>';

		$_POST['html'] = $good;
		$r1            = self::check();
		$_POST['html'] = $bad;
		$r2            = self::check();

		return array(
			'passed'  => is_null( $r1 ) && is_array( $r2 ),
			'message' => 'AI image alt text check working',
		);
	}
}
