<?php
/**
 * Content Review Manager
 *
 * Manages the content review wizard for posts/pages/CPTs before publishing.
 * Coordinates diagnostics, treatments, and user preferences.
 *
 * @package    WPShadow
 * @subpackage Features/ContentReview
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Features\ContentReview;

use WPShadow\Core\AJAX_Handler_Base;
use WPShadow\Integration\Cloud\Cloud_Service_Connector;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Content Review Manager Class
 *
 * Orchestrates content quality checks, user preferences, and cloud integrations
 * for the pre-publish review wizard.
 *
 * @since 1.6093.1200
 */
class Content_Review_Manager {

	/**
	 * Singleton instance
	 *
	 * @var self
	 */
	private static $instance;

	/**
	 * Diagnostic families to check for content
	 *
	 * @var array
	 */
	private static $content_families = array(
		'content',
		'seo',
		'accessibility',
		'readability',
		'code-quality',
	);

	/**
	 * Get singleton instance
	 *
	 * @since 1.6093.1200
	 * @return self
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Initialize hooks
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function init() {
		add_action( 'add_meta_boxes', array( $this, 'add_review_metabox' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Add review metabox to post types
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function add_review_metabox() {
		$post_types = get_post_types( array( 'public' => true ) );
		foreach ( $post_types as $post_type ) {
			if ( post_type_supports( $post_type, 'editor' ) ) {
				add_meta_box(
					'wpshadow-content-review',
					__( 'WPShadow Content Review', 'wpshadow' ),
					array( $this, 'render_review_button' ),
					$post_type,
					'side',
					'high'
				);
			}
		}
	}

	/**
	 * Render review button in metabox
	 *
	 * @since 1.6093.1200
	 * @param  \WP_Post $post Current post object.
	 * @return void
	 */
	public function render_review_button( $post ) {
		wp_nonce_field( 'wpshadow_content_review_' . $post->ID, 'wpshadow_review_nonce' );
		?>
		<div class="wpshadow-review-metabox">
			<p><?php esc_html_e( 'Review your content before publishing', 'wpshadow' ); ?></p>
			<button
				type="button"
				class="button button-primary wpshadow-review-button"
				data-post-id="<?php echo absint( $post->ID ); ?>"
				data-nonce="<?php echo esc_attr( wp_create_nonce( 'wpshadow_content_review' ) ); ?>"
			>
				<?php esc_html_e( 'Review Content', 'wpshadow' ); ?>
			</button>
			<p class="wpshadow-review-hint">
				<small><?php esc_html_e( 'Checks SEO, accessibility, readability, and content quality before you publish.', 'wpshadow' ); ?></small>
			</p>
		</div>
		<?php
	}

	/**
	 * Enqueue assets on edit post screen
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public function enqueue_assets() {
		$screen = get_current_screen();
		if ( ! $screen || 'post' !== $screen->base ) {
			return;
		}

		wp_enqueue_script(
			'wpshadow-content-review',
			WPSHADOW_URL . 'assets/js/content-review-wizard.js',
			array( 'jquery', 'wp-util', 'underscore' ),
			WPSHADOW_VERSION,
			true
		);

		wp_enqueue_style(
			'wpshadow-content-review',
			WPSHADOW_URL . 'assets/css/content-review-wizard.css',
			array(),
			WPSHADOW_VERSION
		);

		\WPShadow\Core\Admin_Asset_Registry::localize_with_ajax_nonce(
			'wpshadow-content-review',
			'wpShadowReview',
			'wpshadow_content_review',
			array(
				'is_cloud_registered'  => Cloud_Service_Connector::is_registered(),
				'cloud_nonce'          => wp_create_nonce( 'wpshadow_cloud_improvement' ),
			),
			'nonce',
			'ajax_url'
		);
	}

	/**
	 * Get content diagnostics for a post
	 *
	 * @since 1.6093.1200
	 * @param  int $post_id Post ID to check.
	 * @return array {
	 *     Diagnostics data grouped by family.
	 *
	 *     @type array $content       Content quality diagnostics.
	 *     @type array $seo           SEO diagnostics.
	 *     @type array $accessibility Accessibility diagnostics.
	 *     @type array $readability   Readability diagnostics.
	 *     @type array $code_quality  Code quality diagnostics.
	 * }
	 */
	public static function get_content_diagnostics( int $post_id ) {
		$diagnostics = array();

		// Get all registered diagnostics.
		$all_diagnostics = apply_filters( 'wpshadow_registered_diagnostics', array() );

		foreach ( $all_diagnostics as $slug => $class ) {
			// Check if class exists and has the check method.
			if ( ! class_exists( $class ) || ! method_exists( $class, 'check' ) ) {
				continue;
			}

			// Get diagnostic info.
			$reflection = new \ReflectionClass( $class );
			if ( ! $reflection->hasProperty( 'family' ) ) {
				continue;
			}

			// Only get content-related diagnostics.
			$family = $reflection->getStaticPropertyValue( 'family' );
			if ( ! in_array( $family, self::$content_families, true ) ) {
				continue;
			}

			// Run the diagnostic.
			$finding = $class::execute();

			if ( is_array( $finding ) && ! empty( $finding ) ) {
				if ( ! isset( $diagnostics[ $family ] ) ) {
					$diagnostics[ $family ] = array();
				}

				$diagnostics[ $family ][] = array(
					'slug'     => $slug,
					'class'    => $class,
					'finding'  => $finding,
					'severity' => $finding['severity'] ?? 'medium',
				);
			}
		}

		return $diagnostics;
	}

	/**
	 * Get user preferences for content review
	 *
	 * @since 1.6093.1200
	 * @param  int $user_id User ID.
	 * @return array User preferences for tips and skipped diagnostics.
	 */
	public static function get_user_preferences( int $user_id = 0 ) {
		if ( 0 === $user_id ) {
			$user_id = get_current_user_id();
		}

		$preferences = get_user_meta( $user_id, 'wpshadow_review_preferences', true );

		if ( ! is_array( $preferences ) ) {
			$preferences = array(
				'hide_tips'          => array(),
				'skip_diagnostics'   => array(),
				'show_ai_tips'       => true,
				'show_kb_links'      => true,
			);
		}

		return $preferences;
	}

	/**
	 * Save user preferences
	 *
	 * @since 1.6093.1200
	 * @param  int   $user_id      User ID.
	 * @param  array $preferences  Preference data.
	 * @return bool True on success.
	 */
	public static function save_user_preferences( int $user_id, array $preferences ) {
		return (bool) update_user_meta(
			$user_id,
			'wpshadow_review_preferences',
			$preferences
		);
	}

	/**
	 * Check if diagnostic is skipped by user
	 *
	 * @since 1.6093.1200
	 * @param  string $diagnostic_slug Diagnostic slug.
	 * @param  int    $user_id         User ID.
	 * @return bool True if skipped.
	 */
	public static function is_diagnostic_skipped( string $diagnostic_slug, int $user_id = 0 ) {
		if ( 0 === $user_id ) {
			$user_id = get_current_user_id();
		}

		$preferences = self::get_user_preferences( $user_id );
		return in_array( $diagnostic_slug, $preferences['skip_diagnostics'] ?? array(), true );
	}

	/**
	 * Skip a diagnostic for user
	 *
	 * @since 1.6093.1200
	 * @param  string $diagnostic_slug Diagnostic slug.
	 * @param  int    $user_id         User ID.
	 * @return bool True on success.
	 */
	public static function skip_diagnostic( string $diagnostic_slug, int $user_id = 0 ) {
		if ( 0 === $user_id ) {
			$user_id = get_current_user_id();
		}

		$preferences = self::get_user_preferences( $user_id );

		if ( ! in_array( $diagnostic_slug, $preferences['skip_diagnostics'] ?? array(), true ) ) {
			$preferences['skip_diagnostics'][] = $diagnostic_slug;
		}

		return self::save_user_preferences( $user_id, $preferences );
	}

	/**
	 * Hide a tip for user
	 *
	 * @since 1.6093.1200
	 * @param  string $tip_id  Tip identifier.
	 * @param  int    $user_id User ID.
	 * @return bool True on success.
	 */
	public static function hide_tip( string $tip_id, int $user_id = 0 ) {
		if ( 0 === $user_id ) {
			$user_id = get_current_user_id();
		}

		$preferences = self::get_user_preferences( $user_id );

		if ( ! in_array( $tip_id, $preferences['hide_tips'] ?? array(), true ) ) {
			$preferences['hide_tips'][] = $tip_id;
		}

		return self::save_user_preferences( $user_id, $preferences );
	}

	/**
	 * Get KB articles related to diagnostics
	 *
	 * @since 1.6093.1200
	 * @param  array $diagnostic_slugs Array of diagnostic slugs.
	 * @return array KB articles keyed by diagnostic slug.
	 */
	public static function get_related_kb_articles( array $diagnostic_slugs ) {
		$articles = array();

		foreach ( $diagnostic_slugs as $slug ) {
			// Try to get KB articles from registry.
			$kb_articles = apply_filters(
				'wpshadow_kb_articles_for_diagnostic',
				array(),
				$slug
			);

			if ( ! empty( $kb_articles ) ) {
				$articles[ $slug ] = $kb_articles;
			}
		}

		return $articles;
	}

	/**
	 * Get training courses related to diagnostic families
	 *
	 * @since 1.6093.1200
	 * @param  array $families Array of family names.
	 * @return array Training courses keyed by family.
	 */
	public static function get_related_training( array $families ) {
		$training = array();

		foreach ( $families as $family ) {
			$courses = apply_filters(
				'wpshadow_training_courses_for_family',
				array(),
				$family
			);

			if ( ! empty( $courses ) ) {
				$training[ $family ] = $courses;
			}
		}

		return $training;
	}
}

// Initialize on plugins_loaded.
add_action(
	'plugins_loaded',
	function () {
		Content_Review_Manager::get_instance()->init();
	}
);
