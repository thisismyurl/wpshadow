<?php
declare(strict_types=1);

namespace WPShadow\Onboarding;

/**
 * Platform Translator
 * 
 * Translates WordPress terminology to familiar terms from other platforms.
 * 
 * Philosophy: #8 Inspire Confidence - Use familiar language
 * Philosophy: #5 Drive to KB - Link to learning resources
 * 
 * @since 1.2601.2201
 * @package WPShadow
 */
class Platform_Translator {
	
	/**
	 * Cache for loaded platform data
	 * 
	 * @var array
	 */
	private static $platform_cache = [];
	
	/**
	 * Get available platforms
	 * 
	 * @return array Platform data
	 */
	public static function get_platforms(): array {
		return [
			'wordpress'    => [
				'id'          => 'wordpress',
				'label'       => __( 'WordPress (I have experience)', 'wpshadow' ),
				'description' => __( 'You\'re already familiar with WordPress', 'wpshadow' ),
				'icon'        => 'wordpress-alt',
			],
			'word'         => [
				'id'          => 'word',
				'label'       => __( 'Microsoft Word', 'wpshadow' ),
				'description' => __( 'You\'re comfortable with Word documents', 'wpshadow' ),
				'icon'        => 'media-document',
			],
			'google-docs'  => [
				'id'          => 'google-docs',
				'label'       => __( 'Google Docs', 'wpshadow' ),
				'description' => __( 'You\'re familiar with Google Docs', 'wpshadow' ),
				'icon'        => 'media-text',
			],
			'wix'          => [
				'id'          => 'wix',
				'label'       => __( 'Wix', 'wpshadow' ),
				'description' => __( 'You\'ve used Wix website builder', 'wpshadow' ),
				'icon'        => 'admin-site-alt3',
			],
			'squarespace'  => [
				'id'          => 'squarespace',
				'label'       => __( 'Squarespace', 'wpshadow' ),
				'description' => __( 'You\'ve built sites with Squarespace', 'wpshadow' ),
				'icon'        => 'layout',
			],
			'moodle'       => [
				'id'          => 'moodle',
				'label'       => __( 'Moodle', 'wpshadow' ),
				'description' => __( 'You\'ve used Moodle for courses', 'wpshadow' ),
				'icon'        => 'welcome-learn-more',
			],
			'notion'       => [
				'id'          => 'notion',
				'label'       => __( 'Notion', 'wpshadow' ),
				'description' => __( 'You use Notion for documentation', 'wpshadow' ),
				'icon'        => 'editor-table',
			],
			'none'         => [
				'id'          => 'none',
				'label'       => __( 'I\'m new to all of this', 'wpshadow' ),
				'description' => __( 'No worries! We\'ll guide you every step', 'wpshadow' ),
				'icon'        => 'heart',
			],
		];
	}
	
	/**
	 * Load platform terminology data
	 * 
	 * @param string $platform_id Platform ID
	 * @return array|null Platform data or null if not found
	 */
	public static function load_platform( string $platform_id ): ?array {
		// Check cache first
		if ( isset( self::$platform_cache[ $platform_id ] ) ) {
			return self::$platform_cache[ $platform_id ];
		}
		
		$file_path = WPSHADOW_PATH . "includes/onboarding/data/terminology-{$platform_id}.json";
		
		if ( ! file_exists( $file_path ) ) {
			return null;
		}
		
		$data = json_decode( file_get_contents( $file_path ), true );
		
		if ( ! is_array( $data ) ) {
			return null;
		}
		
		// Cache it
		self::$platform_cache[ $platform_id ] = $data;
		
		return $data;
	}
	
	/**
	 * Get translated term for user's platform
	 * 
	 * @param string $wp_term WordPress term (e.g., 'post', 'plugin')
	 * @param int    $user_id User ID (default: current user)
	 * @return string|null Translated term or null if no translation
	 */
	public static function get_term( string $wp_term, int $user_id = 0 ): ?string {
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}
		
		$platform = Onboarding_Manager::get_user_platform( $user_id );
		
		if ( empty( $platform ) || 'wordpress' === $platform ) {
			return null; // No translation needed
		}
		
		$platform_data = self::load_platform( $platform );
		
		if ( ! $platform_data || ! isset( $platform_data['terms'][ $wp_term ] ) ) {
			return null;
		}
		
		return $platform_data['terms'][ $wp_term ];
	}
	
	/**
	 * Get all terms for user's platform
	 * 
	 * @param int $user_id User ID (default: current user)
	 * @return array Terms array
	 */
	public static function get_all_terms( int $user_id = 0 ): array {
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}
		
		$platform = Onboarding_Manager::get_user_platform( $user_id );
		
		if ( empty( $platform ) || 'wordpress' === $platform ) {
			return [];
		}
		
		$platform_data = self::load_platform( $platform );
		
		return $platform_data['terms'] ?? [];
	}
	
	/**
	 * Check if term is dismissed by user
	 * 
	 * @param string $term WordPress term
	 * @param int    $user_id User ID (default: current user)
	 * @return bool True if dismissed
	 */
	public static function is_term_dismissed( string $term, int $user_id = 0 ): bool {
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}
		
		$dismissed = get_user_meta( $user_id, Onboarding_Manager::META_DISMISSED_TERMS, true ) ?: [];
		
		return in_array( $term, $dismissed, true );
	}
	
	/**
	 * Get tooltip HTML for a term
	 * 
	 * @param string $wp_term WordPress term
	 * @param int    $user_id User ID (default: current user)
	 * @return string HTML or empty string
	 */
	public static function get_term_tooltip( string $wp_term, int $user_id = 0 ): string {
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}
		
		// Check if user needs terminology help
		if ( ! Onboarding_Manager::is_ui_simplified( $user_id ) ) {
			return '';
		}
		
		// Check if already dismissed
		if ( self::is_term_dismissed( $wp_term, $user_id ) ) {
			return '';
		}
		
		$translated = self::get_term( $wp_term, $user_id );
		
		if ( ! $translated ) {
			return '';
		}
		
		$platform = Onboarding_Manager::get_user_platform( $user_id );
		$platform_data = self::get_platforms()[ $platform ] ?? null;
		
		if ( ! $platform_data ) {
			return '';
		}
		
		return sprintf(
			'<span class="wpshadow-term-tooltip" data-term="%s" title="%s">
				<span class="dashicons dashicons-info"></span>
				<span class="tooltip-text">%s</span>
			</span>',
			esc_attr( $wp_term ),
			esc_attr( sprintf(
				/* translators: 1: Platform name, 2: Translated term */
				__( 'In %1$s, this is called "%2$s"', 'wpshadow' ),
				$platform_data['label'],
				$translated
			) ),
			esc_html( sprintf(
				/* translators: 1: Platform name, 2: Translated term */
				__( 'Called "%2$s" in %1$s', 'wpshadow' ),
				$platform_data['label'],
				$translated
			) )
		);
	}
}
