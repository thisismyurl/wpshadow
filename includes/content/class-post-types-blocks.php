<?php
/**
 * Custom Post Types Blocks Manager
 *
 * Registers Gutenberg blocks for all WPShadow custom post types.
 *
 * @package    WPShadow
 * @subpackage Content
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Content;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Post Types Blocks Manager Class
 *
 * Handles registration and rendering of Gutenberg blocks for CPTs.
 *
 * @since 1.6093.1200
 */
class Post_Types_Blocks {

	/**
	 * Initialize the blocks manager.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_blocks' ) );
		add_action( 'enqueue_block_editor_assets', array( __CLASS__, 'enqueue_editor_assets' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_frontend_assets' ) );
	}

	/**
	 * Register all custom post type blocks.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function register_blocks() {
		$active_post_types = get_option( 'wpshadow_active_post_types', array() );

		foreach ( $active_post_types as $post_type ) {
			self::register_block_for_post_type( $post_type );
		}
	}

	/**
	 * Register block for a specific post type.
	 *
	 * @since 1.6093.1200
	 * @param  string $post_type Post type key.
	 * @return void
	 */
	private static function register_block_for_post_type( $post_type ) {
		$block_configs = self::get_block_configurations();

		if ( ! isset( $block_configs[ $post_type ] ) ) {
			return;
		}

		$config = $block_configs[ $post_type ];

		register_block_type(
			$config['name'],
			array(
				'attributes'      => $config['attributes'],
				'render_callback' => array( __CLASS__, $config['render_callback'] ),
			)
		);
	}

	/**
	 * Get block configurations for all post types.
	 *
	 * @since 1.6093.1200
	 * @return array Block configurations.
	 */
	private static function get_block_configurations() {
		return array(
			'wps_testimonial'   => array(
				'name'            => 'wpshadow/testimonials',
				'attributes'      => array(
					'count'       => array( 'type' => 'number', 'default' => 3 ),
					'category'    => array( 'type' => 'string', 'default' => '' ),
					'rating'      => array( 'type' => 'string', 'default' => '' ),
					'layout'      => array( 'type' => 'string', 'default' => 'grid' ),
					'showExcerpt' => array( 'type' => 'boolean', 'default' => true ),
					'showRating'  => array( 'type' => 'boolean', 'default' => true ),
				),
				'render_callback' => 'render_testimonials_block',
			),
			'wps_team_member'   => array(
				'name'            => 'wpshadow/team-members',
				'attributes'      => array(
					'count'       => array( 'type' => 'number', 'default' => 6 ),
					'department'  => array( 'type' => 'string', 'default' => '' ),
					'role'        => array( 'type' => 'string', 'default' => '' ),
					'layout'      => array( 'type' => 'string', 'default' => 'grid' ),
					'columns'     => array( 'type' => 'number', 'default' => 3 ),
					'showExcerpt' => array( 'type' => 'boolean', 'default' => true ),
				),
				'render_callback' => 'render_team_members_block',
			),
			'wps_portfolio'     => array(
				'name'            => 'wpshadow/portfolio',
				'attributes'      => array(
					'count'       => array( 'type' => 'number', 'default' => 6 ),
					'category'    => array( 'type' => 'string', 'default' => '' ),
					'skill'       => array( 'type' => 'string', 'default' => '' ),
					'layout'      => array( 'type' => 'string', 'default' => 'grid' ),
					'columns'     => array( 'type' => 'number', 'default' => 3 ),
					'showExcerpt' => array( 'type' => 'boolean', 'default' => false ),
				),
				'render_callback' => 'render_portfolio_block',
			),
			'wps_event'         => array(
				'name'            => 'wpshadow/events',
				'attributes'      => array(
					'count'       => array( 'type' => 'number', 'default' => 5 ),
					'category'    => array( 'type' => 'string', 'default' => '' ),
					'eventType'   => array( 'type' => 'string', 'default' => '' ),
					'layout'      => array( 'type' => 'string', 'default' => 'list' ),
					'showExcerpt' => array( 'type' => 'boolean', 'default' => true ),
					'upcoming'    => array( 'type' => 'boolean', 'default' => true ),
				),
				'render_callback' => 'render_events_block',
			),
			'wps_resource'      => array(
				'name'            => 'wpshadow/resources',
				'attributes'      => array(
					'count'       => array( 'type' => 'number', 'default' => 6 ),
					'type'        => array( 'type' => 'string', 'default' => '' ),
					'category'    => array( 'type' => 'string', 'default' => '' ),
					'layout'      => array( 'type' => 'string', 'default' => 'list' ),
					'showExcerpt' => array( 'type' => 'boolean', 'default' => true ),
				),
				'render_callback' => 'render_resources_block',
			),
			'wps_case_study'    => array(
				'name'            => 'wpshadow/case-studies',
				'attributes'      => array(
					'count'       => array( 'type' => 'number', 'default' => 3 ),
					'industry'    => array( 'type' => 'string', 'default' => '' ),
					'solution'    => array( 'type' => 'string', 'default' => '' ),
					'layout'      => array( 'type' => 'string', 'default' => 'grid' ),
					'columns'     => array( 'type' => 'number', 'default' => 2 ),
					'showExcerpt' => array( 'type' => 'boolean', 'default' => true ),
				),
				'render_callback' => 'render_case_studies_block',
			),
			'wps_service'       => array(
				'name'            => 'wpshadow/services',
				'attributes'      => array(
					'count'       => array( 'type' => 'number', 'default' => 6 ),
					'category'    => array( 'type' => 'string', 'default' => '' ),
					'layout'      => array( 'type' => 'string', 'default' => 'grid' ),
					'columns'     => array( 'type' => 'number', 'default' => 3 ),
					'showExcerpt' => array( 'type' => 'boolean', 'default' => true ),
				),
				'render_callback' => 'render_services_block',
			),
			'wps_location'      => array(
				'name'            => 'wpshadow/locations',
				'attributes'      => array(
					'count'       => array( 'type' => 'number', 'default' => -1 ),
					'locationType' => array( 'type' => 'string', 'default' => '' ),
					'layout'      => array( 'type' => 'string', 'default' => 'list' ),
				),
				'render_callback' => 'render_locations_block',
			),
			'wps_documentation' => array(
				'name'            => 'wpshadow/documentation',
				'attributes'      => array(
					'count'       => array( 'type' => 'number', 'default' => 10 ),
					'category'    => array( 'type' => 'string', 'default' => '' ),
					'version'     => array( 'type' => 'string', 'default' => '' ),
					'layout'      => array( 'type' => 'string', 'default' => 'list' ),
					'hierarchical' => array( 'type' => 'boolean', 'default' => false ),
				),
				'render_callback' => 'render_documentation_block',
			),
		);
	}

	/**
	 * Enqueue block editor assets.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function enqueue_editor_assets() {
		wp_enqueue_script(
			'wpshadow-cpt-blocks',
			WPSHADOW_URL . 'assets/js/cpt-blocks.js',
			array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-i18n', 'wp-editor', 'wp-data', 'wp-server-side-render' ),
			WPSHADOW_VERSION,
			true
		);

		// Enqueue block styles registration
		wp_enqueue_script(
			'wpshadow-cpt-block-styles',
			WPSHADOW_URL . 'assets/js/cpt-block-styles.js',
			array( 'wp-blocks', 'wp-i18n' ),
			WPSHADOW_VERSION,
			true
		);

		wp_localize_script(
			'wpshadow-cpt-blocks',
			'wpshadowCPTBlocks',
			array(
				'activePostTypes' => get_option( 'wpshadow_active_post_types', array() ),
				'taxonomies'      => self::get_taxonomy_data(),
			)
		);

		wp_enqueue_style(
			'wpshadow-cpt-blocks-editor',
			WPSHADOW_URL . 'assets/css/cpt-blocks-editor.css',
			array(),
			WPSHADOW_VERSION
		);
	}

	/**
	 * Enqueue frontend assets for blocks.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function enqueue_frontend_assets() {
		if ( ! has_block( 'wpshadow/' ) ) {
			return; // Only load if WPShadow blocks are present
		}

		wp_enqueue_style(
			'wpshadow-cpt-blocks',
			WPSHADOW_URL . 'assets/css/cpt-blocks.css',
			array(),
			WPSHADOW_VERSION
		);
	}

	/**
	 * Get taxonomy data for localization.
	 *
	 * @since 1.6093.1200
	 * @return array Taxonomy data.
	 */
	private static function get_taxonomy_data() {
		$taxonomies = Post_Types_Manager::get_available_taxonomies();
		$data       = array();

		foreach ( $taxonomies as $tax_key => $tax_config ) {
			if ( ! taxonomy_exists( $tax_key ) ) {
				continue;
			}

			$terms = get_terms(
				array(
					'taxonomy'   => $tax_key,
					'hide_empty' => false,
				)
			);

			$data[ $tax_key ] = array(
				'label' => $tax_config['plural'],
				'terms' => array(),
			);

			if ( ! is_wp_error( $terms ) ) {
				foreach ( $terms as $term ) {
					$data[ $tax_key ]['terms'][] = array(
						'value' => $term->slug,
						'label' => $term->name,
					);
				}
			}
		}

		return $data;
	}

	// =================================================================
	// RENDER CALLBACKS
	// =================================================================

	/**
	 * Render testimonials block.
	 *
	 * @since 1.6093.1200
	 * @param  array $atts Block attributes.
	 * @return string Block HTML.
	 */
	public static function render_testimonials_block( $atts ) {
		$atts = wp_parse_args(
			$atts,
			array(
				'count'       => 3,
				'category'    => '',
				'rating'      => '',
				'layout'      => 'grid',
				'showExcerpt' => true,
				'showRating'  => true,
			)
		);

		$args = array(
			'post_type'      => 'wps_testimonial',
			'posts_per_page' => (int) $atts['count'],
			'post_status'    => 'publish',
		);

		$tax_query = array();

		if ( ! empty( $atts['category'] ) ) {
			$tax_query[] = array(
				'taxonomy' => 'wps_testimonial_category',
				'field'    => 'slug',
				'terms'    => sanitize_text_field( $atts['category'] ),
			);
		}

		if ( ! empty( $atts['rating'] ) ) {
			$tax_query[] = array(
				'taxonomy' => 'wps_rating',
				'field'    => 'slug',
				'terms'    => sanitize_text_field( $atts['rating'] ),
			);
		}

		if ( ! empty( $tax_query ) ) {
			$args['tax_query'] = $tax_query;
		}

		$query = new \WP_Query( $args );

		if ( ! $query->have_posts() ) {
			return '<p>' . esc_html__( 'No testimonials found.', 'wpshadow' ) . '</p>';
		}

		$layout_class = 'wpshadow-testimonials-' . sanitize_html_class( $atts['layout'] );

		ob_start();
		?>
		<div class="wpshadow-testimonials-block <?php echo esc_attr( $layout_class ); ?>">
			<?php
			while ( $query->have_posts() ) {
				$query->the_post();
				?>
				<div class="wpshadow-testimonial-item">
					<?php if ( $atts['showRating'] ) : ?>
						<div class="testimonial-rating">
							<?php echo esc_html( self::get_rating_display( get_the_ID() ) ); ?>
						</div>
					<?php endif; ?>
					
					<div class="testimonial-content">
						<?php if ( $atts['showExcerpt'] && has_excerpt() ) : ?>
							<?php the_excerpt(); ?>
						<?php else : ?>
							<?php the_content(); ?>
						<?php endif; ?>
					</div>
					
					<div class="testimonial-author">
						<h4><?php the_title(); ?></h4>
						<?php if ( has_post_thumbnail() ) : ?>
							<div class="testimonial-avatar">
								<?php the_post_thumbnail( 'thumbnail' ); ?>
							</div>
						<?php endif; ?>
					</div>
				</div>
				<?php
			}
			?>
		</div>
		<?php
		wp_reset_postdata();
		return ob_get_clean();
	}

	/**
	 * Render team members block.
	 *
	 * @since 1.6093.1200
	 * @param  array $atts Block attributes.
	 * @return string Block HTML.
	 */
	public static function render_team_members_block( $atts ) {
		$atts = wp_parse_args(
			$atts,
			array(
				'count'       => 6,
				'department'  => '',
				'role'        => '',
				'layout'      => 'grid',
				'columns'     => 3,
				'showExcerpt' => true,
			)
		);

		$args = array(
			'post_type'      => 'wps_team_member',
			'posts_per_page' => (int) $atts['count'],
			'post_status'    => 'publish',
		);

		$tax_query = array();

		if ( ! empty( $atts['department'] ) ) {
			$tax_query[] = array(
				'taxonomy' => 'wps_department',
				'field'    => 'slug',
				'terms'    => sanitize_text_field( $atts['department'] ),
			);
		}

		if ( ! empty( $atts['role'] ) ) {
			$tax_query[] = array(
				'taxonomy' => 'wps_team_role',
				'field'    => 'slug',
				'terms'    => sanitize_text_field( $atts['role'] ),
			);
		}

		if ( ! empty( $tax_query ) ) {
			$args['tax_query'] = $tax_query;
		}

		$query = new \WP_Query( $args );

		if ( ! $query->have_posts() ) {
			return '<p>' . esc_html__( 'No team members found.', 'wpshadow' ) . '</p>';
		}

		$layout_class = 'wpshadow-team-' . sanitize_html_class( $atts['layout'] );
		$columns      = absint( $atts['columns'] );

		ob_start();
		?>
		<div class="wpshadow-team-block <?php echo esc_attr( $layout_class ); ?>" data-columns="<?php echo esc_attr( $columns ); ?>">
			<?php
			while ( $query->have_posts() ) {
				$query->the_post();
				?>
				<div class="wpshadow-team-member">
					<?php if ( has_post_thumbnail() ) : ?>
						<div class="team-member-photo">
							<?php the_post_thumbnail( 'medium' ); ?>
						</div>
					<?php endif; ?>
					
					<div class="team-member-info">
						<h3><?php the_title(); ?></h3>
						
						<?php
						$roles = get_the_terms( get_the_ID(), 'wps_team_role' );
						if ( $roles && ! is_wp_error( $roles ) ) :
							?>
							<p class="team-member-role"><?php echo esc_html( $roles[0]->name ); ?></p>
						<?php endif; ?>
						
						<?php if ( $atts['showExcerpt'] && has_excerpt() ) : ?>
							<div class="team-member-bio">
								<?php the_excerpt(); ?>
							</div>
						<?php endif; ?>
					</div>
				</div>
				<?php
			}
			?>
		</div>
		<?php
		wp_reset_postdata();
		return ob_get_clean();
	}

	/**
	 * Render portfolio block.
	 *
	 * @since 1.6093.1200
	 * @param  array $atts Block attributes.
	 * @return string Block HTML.
	 */
	public static function render_portfolio_block( $atts ) {
		$atts = wp_parse_args(
			$atts,
			array(
				'count'       => 6,
				'category'    => '',
				'skill'       => '',
				'layout'      => 'grid',
				'columns'     => 3,
				'showExcerpt' => false,
			)
		);

		$args = array(
			'post_type'      => 'wps_portfolio',
			'posts_per_page' => (int) $atts['count'],
			'post_status'    => 'publish',
		);

		$tax_query = array();

		if ( ! empty( $atts['category'] ) ) {
			$tax_query[] = array(
				'taxonomy' => 'wps_portfolio_category',
				'field'    => 'slug',
				'terms'    => sanitize_text_field( $atts['category'] ),
			);
		}

		if ( ! empty( $atts['skill'] ) ) {
			$tax_query[] = array(
				'taxonomy' => 'wps_skill',
				'field'    => 'slug',
				'terms'    => sanitize_text_field( $atts['skill'] ),
			);
		}

		if ( ! empty( $tax_query ) ) {
			$args['tax_query'] = $tax_query;
		}

		$query = new \WP_Query( $args );

		if ( ! $query->have_posts() ) {
			return '<p>' . esc_html__( 'No portfolio items found.', 'wpshadow' ) . '</p>';
		}

		$layout_class = 'wpshadow-portfolio-' . sanitize_html_class( $atts['layout'] );
		$columns      = absint( $atts['columns'] );

		ob_start();
		?>
		<div class="wpshadow-portfolio-block <?php echo esc_attr( $layout_class ); ?>" data-columns="<?php echo esc_attr( $columns ); ?>">
			<?php
			while ( $query->have_posts() ) {
				$query->the_post();
				?>
				<div class="wpshadow-portfolio-item">
					<?php if ( has_post_thumbnail() ) : ?>
						<div class="portfolio-image">
							<a href="<?php the_permalink(); ?>">
								<?php the_post_thumbnail( 'large' ); ?>
							</a>
						</div>
					<?php endif; ?>
					
					<div class="portfolio-details">
						<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
						
						<?php
						$skills = get_the_terms( get_the_ID(), 'wps_skill' );
						if ( $skills && ! is_wp_error( $skills ) ) :
							?>
							<div class="portfolio-skills">
								<?php
								foreach ( $skills as $skill ) {
									echo '<span class="skill-tag">' . esc_html( $skill->name ) . '</span>';
								}
								?>
							</div>
						<?php endif; ?>
						
						<?php if ( $atts['showExcerpt'] && has_excerpt() ) : ?>
							<div class="portfolio-excerpt">
								<?php the_excerpt(); ?>
							</div>
						<?php endif; ?>
					</div>
				</div>
				<?php
			}
			?>
		</div>
		<?php
		wp_reset_postdata();
		return ob_get_clean();
	}

	/**
	 * Render events block.
	 *
	 * @since 1.6093.1200
	 * @param  array $atts Block attributes.
	 * @return string Block HTML.
	 */
	public static function render_events_block( $atts ) {
		$atts = wp_parse_args(
			$atts,
			array(
				'count'       => 5,
				'category'    => '',
				'eventType'   => '',
				'layout'      => 'list',
				'showExcerpt' => true,
				'upcoming'    => true,
			)
		);

		$args = array(
			'post_type'      => 'wps_event',
			'posts_per_page' => (int) $atts['count'],
			'post_status'    => 'publish',
			'orderby'        => 'date',
			'order'          => 'ASC',
		);

		$tax_query = array();

		if ( ! empty( $atts['category'] ) ) {
			$tax_query[] = array(
				'taxonomy' => 'wps_event_category',
				'field'    => 'slug',
				'terms'    => sanitize_text_field( $atts['category'] ),
			);
		}

		if ( ! empty( $atts['eventType'] ) ) {
			$tax_query[] = array(
				'taxonomy' => 'wps_event_type',
				'field'    => 'slug',
				'terms'    => sanitize_text_field( $atts['eventType'] ),
			);
		}

		if ( ! empty( $tax_query ) ) {
			$args['tax_query'] = $tax_query;
		}

		$query = new \WP_Query( $args );

		if ( ! $query->have_posts() ) {
			return '<p>' . esc_html__( 'No events found.', 'wpshadow' ) . '</p>';
		}

		$layout_class = 'wpshadow-events-' . sanitize_html_class( $atts['layout'] );

		ob_start();
		?>
		<div class="wpshadow-events-block <?php echo esc_attr( $layout_class ); ?>">
			<?php
			while ( $query->have_posts() ) {
				$query->the_post();
				?>
				<div class="wpshadow-event-item">
					<div class="event-date">
						<span class="event-day"><?php echo esc_html( get_the_date( 'd' ) ); ?></span>
						<span class="event-month"><?php echo esc_html( get_the_date( 'M' ) ); ?></span>
					</div>
					
					<div class="event-content">
						<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
						
						<?php if ( $atts['showExcerpt'] && has_excerpt() ) : ?>
							<div class="event-excerpt">
								<?php the_excerpt(); ?>
							</div>
						<?php endif; ?>
						
						<div class="event-meta">
							<?php
							$types = get_the_terms( get_the_ID(), 'wps_event_type' );
							if ( $types && ! is_wp_error( $types ) ) :
								?>
								<span class="event-type"><?php echo esc_html( $types[0]->name ); ?></span>
							<?php endif; ?>
						</div>
					</div>
				</div>
				<?php
			}
			?>
		</div>
		<?php
		wp_reset_postdata();
		return ob_get_clean();
	}

	/**
	 * Render resources block.
	 *
	 * @since 1.6093.1200
	 * @param  array $atts Block attributes.
	 * @return string Block HTML.
	 */
	public static function render_resources_block( $atts ) {
		$atts = wp_parse_args(
			$atts,
			array(
				'count'       => 6,
				'type'        => '',
				'category'    => '',
				'layout'      => 'list',
				'showExcerpt' => true,
			)
		);

		$args = array(
			'post_type'      => 'wps_resource',
			'posts_per_page' => (int) $atts['count'],
			'post_status'    => 'publish',
		);

		$tax_query = array();

		if ( ! empty( $atts['type'] ) ) {
			$tax_query[] = array(
				'taxonomy' => 'wps_resource_type',
				'field'    => 'slug',
				'terms'    => sanitize_text_field( $atts['type'] ),
			);
		}

		if ( ! empty( $atts['category'] ) ) {
			$tax_query[] = array(
				'taxonomy' => 'wps_resource_category',
				'field'    => 'slug',
				'terms'    => sanitize_text_field( $atts['category'] ),
			);
		}

		if ( ! empty( $tax_query ) ) {
			$args['tax_query'] = $tax_query;
		}

		$query = new \WP_Query( $args );

		if ( ! $query->have_posts() ) {
			return '<p>' . esc_html__( 'No resources found.', 'wpshadow' ) . '</p>';
		}

		$layout_class = 'wpshadow-resources-' . sanitize_html_class( $atts['layout'] );

		ob_start();
		?>
		<div class="wpshadow-resources-block <?php echo esc_attr( $layout_class ); ?>">
			<?php
			while ( $query->have_posts() ) {
				$query->the_post();
				?>
				<div class="wpshadow-resource-item">
					<div class="resource-icon">
						<span class="dashicons dashicons-download"></span>
					</div>
					
					<div class="resource-content">
						<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
						
						<?php if ( $atts['showExcerpt'] && has_excerpt() ) : ?>
							<div class="resource-excerpt">
								<?php the_excerpt(); ?>
							</div>
						<?php endif; ?>
						
						<div class="resource-meta">
							<?php
							$types = get_the_terms( get_the_ID(), 'wps_resource_type' );
							if ( $types && ! is_wp_error( $types ) ) :
								?>
								<span class="resource-type"><?php echo esc_html( $types[0]->name ); ?></span>
							<?php endif; ?>
						</div>
					</div>
				</div>
				<?php
			}
			?>
		</div>
		<?php
		wp_reset_postdata();
		return ob_get_clean();
	}

	/**
	 * Render case studies block.
	 *
	 * @since 1.6093.1200
	 * @param  array $atts Block attributes.
	 * @return string Block HTML.
	 */
	public static function render_case_studies_block( $atts ) {
		$atts = wp_parse_args(
			$atts,
			array(
				'count'       => 3,
				'industry'    => '',
				'solution'    => '',
				'layout'      => 'grid',
				'columns'     => 2,
				'showExcerpt' => true,
			)
		);

		$args = array(
			'post_type'      => 'wps_case_study',
			'posts_per_page' => (int) $atts['count'],
			'post_status'    => 'publish',
		);

		$tax_query = array();

		if ( ! empty( $atts['industry'] ) ) {
			$tax_query[] = array(
				'taxonomy' => 'wps_industry',
				'field'    => 'slug',
				'terms'    => sanitize_text_field( $atts['industry'] ),
			);
		}

		if ( ! empty( $atts['solution'] ) ) {
			$tax_query[] = array(
				'taxonomy' => 'wps_solution',
				'field'    => 'slug',
				'terms'    => sanitize_text_field( $atts['solution'] ),
			);
		}

		if ( ! empty( $tax_query ) ) {
			$args['tax_query'] = $tax_query;
		}

		$query = new \WP_Query( $args );

		if ( ! $query->have_posts() ) {
			return '<p>' . esc_html__( 'No case studies found.', 'wpshadow' ) . '</p>';
		}

		$layout_class = 'wpshadow-case-studies-' . sanitize_html_class( $atts['layout'] );
		$columns      = absint( $atts['columns'] );

		ob_start();
		?>
		<div class="wpshadow-case-studies-block <?php echo esc_attr( $layout_class ); ?>" data-columns="<?php echo esc_attr( $columns ); ?>">
			<?php
			while ( $query->have_posts() ) {
				$query->the_post();
				?>
				<div class="wpshadow-case-study-item">
					<?php if ( has_post_thumbnail() ) : ?>
						<div class="case-study-image">
							<a href="<?php the_permalink(); ?>">
								<?php the_post_thumbnail( 'large' ); ?>
							</a>
						</div>
					<?php endif; ?>
					
					<div class="case-study-content">
						<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
						
						<?php if ( $atts['showExcerpt'] && has_excerpt() ) : ?>
							<div class="case-study-excerpt">
								<?php the_excerpt(); ?>
							</div>
						<?php endif; ?>
						
						<div class="case-study-meta">
							<?php
							$industries = get_the_terms( get_the_ID(), 'wps_industry' );
							if ( $industries && ! is_wp_error( $industries ) ) :
								?>
								<span class="case-study-industry"><?php echo esc_html( $industries[0]->name ); ?></span>
							<?php endif; ?>
						</div>
						
						<a href="<?php the_permalink(); ?>" class="case-study-link">
							<?php esc_html_e( 'Read Case Study', 'wpshadow' ); ?> →
						</a>
					</div>
				</div>
				<?php
			}
			?>
		</div>
		<?php
		wp_reset_postdata();
		return ob_get_clean();
	}

	/**
	 * Render services block.
	 *
	 * @since 1.6093.1200
	 * @param  array $atts Block attributes.
	 * @return string Block HTML.
	 */
	public static function render_services_block( $atts ) {
		$atts = wp_parse_args(
			$atts,
			array(
				'count'       => 6,
				'category'    => '',
				'layout'      => 'grid',
				'columns'     => 3,
				'showExcerpt' => true,
			)
		);

		$args = array(
			'post_type'      => 'wps_service',
			'posts_per_page' => (int) $atts['count'],
			'post_status'    => 'publish',
			'orderby'        => 'menu_order',
			'order'          => 'ASC',
		);

		if ( ! empty( $atts['category'] ) ) {
			$args['tax_query'] = array(
				array(
					'taxonomy' => 'wps_service_category',
					'field'    => 'slug',
					'terms'    => sanitize_text_field( $atts['category'] ),
				),
			);
		}

		$query = new \WP_Query( $args );

		if ( ! $query->have_posts() ) {
			return '<p>' . esc_html__( 'No services found.', 'wpshadow' ) . '</p>';
		}

		$layout_class = 'wpshadow-services-' . sanitize_html_class( $atts['layout'] );
		$columns      = absint( $atts['columns'] );

		ob_start();
		?>
		<div class="wpshadow-services-block <?php echo esc_attr( $layout_class ); ?>" data-columns="<?php echo esc_attr( $columns ); ?>">
			<?php
			while ( $query->have_posts() ) {
				$query->the_post();
				?>
				<div class="wpshadow-service-item">
					<?php if ( has_post_thumbnail() ) : ?>
						<div class="service-icon">
							<?php the_post_thumbnail( 'thumbnail' ); ?>
						</div>
					<?php endif; ?>
					
					<div class="service-content">
						<h3><?php the_title(); ?></h3>
						
						<?php if ( $atts['showExcerpt'] && has_excerpt() ) : ?>
							<div class="service-excerpt">
								<?php the_excerpt(); ?>
							</div>
						<?php endif; ?>
						
						<a href="<?php the_permalink(); ?>" class="service-link">
							<?php esc_html_e( 'Learn More', 'wpshadow' ); ?> →
						</a>
					</div>
				</div>
				<?php
			}
			?>
		</div>
		<?php
		wp_reset_postdata();
		return ob_get_clean();
	}

	/**
	 * Render locations block.
	 *
	 * @since 1.6093.1200
	 * @param  array $atts Block attributes.
	 * @return string Block HTML.
	 */
	public static function render_locations_block( $atts ) {
		$atts = wp_parse_args(
			$atts,
			array(
				'count'        => -1,
				'locationType' => '',
				'layout'       => 'list',
			)
		);

		$args = array(
			'post_type'      => 'wps_location',
			'posts_per_page' => (int) $atts['count'],
			'post_status'    => 'publish',
		);

		if ( ! empty( $atts['locationType'] ) ) {
			$args['tax_query'] = array(
				array(
					'taxonomy' => 'wps_location_type',
					'field'    => 'slug',
					'terms'    => sanitize_text_field( $atts['locationType'] ),
				),
			);
		}

		$query = new \WP_Query( $args );

		if ( ! $query->have_posts() ) {
			return '<p>' . esc_html__( 'No locations found.', 'wpshadow' ) . '</p>';
		}

		$layout_class = 'wpshadow-locations-' . sanitize_html_class( $atts['layout'] );

		ob_start();
		?>
		<div class="wpshadow-locations-block <?php echo esc_attr( $layout_class ); ?>">
			<?php
			while ( $query->have_posts() ) {
				$query->the_post();
				?>
				<div class="wpshadow-location-item">
					<h3><?php the_title(); ?></h3>
					
					<div class="location-content">
						<?php the_content(); ?>
					</div>
					
					<a href="<?php the_permalink(); ?>" class="location-link">
						<?php esc_html_e( 'View Details', 'wpshadow' ); ?> →
					</a>
				</div>
				<?php
			}
			?>
		</div>
		<?php
		wp_reset_postdata();
		return ob_get_clean();
	}

	/**
	 * Render documentation block.
	 *
	 * @since 1.6093.1200
	 * @param  array $atts Block attributes.
	 * @return string Block HTML.
	 */
	public static function render_documentation_block( $atts ) {
		$atts = wp_parse_args(
			$atts,
			array(
				'count'        => 10,
				'category'     => '',
				'version'      => '',
				'layout'       => 'list',
				'hierarchical' => false,
			)
		);

		$args = array(
			'post_type'      => 'wps_documentation',
			'posts_per_page' => (int) $atts['count'],
			'post_status'    => 'publish',
			'orderby'        => 'menu_order',
			'order'          => 'ASC',
		);

		$tax_query = array();

		if ( ! empty( $atts['category'] ) ) {
			$tax_query[] = array(
				'taxonomy' => 'wps_doc_category',
				'field'    => 'slug',
				'terms'    => sanitize_text_field( $atts['category'] ),
			);
		}

		if ( ! empty( $atts['version'] ) ) {
			$tax_query[] = array(
				'taxonomy' => 'wps_doc_version',
				'field'    => 'slug',
				'terms'    => sanitize_text_field( $atts['version'] ),
			);
		}

		if ( ! empty( $tax_query ) ) {
			$args['tax_query'] = $tax_query;
		}

		$query = new \WP_Query( $args );

		if ( ! $query->have_posts() ) {
			return '<p>' . esc_html__( 'No documentation found.', 'wpshadow' ) . '</p>';
		}

		$layout_class = 'wpshadow-documentation-' . sanitize_html_class( $atts['layout'] );

		ob_start();
		?>
		<div class="wpshadow-documentation-block <?php echo esc_attr( $layout_class ); ?>">
			<?php
			while ( $query->have_posts() ) {
				$query->the_post();
				?>
				<div class="wpshadow-doc-item">
					<div class="doc-icon">
						<span class="dashicons dashicons-book"></span>
					</div>
					
					<div class="doc-content">
						<h4><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
						
						<?php if ( has_excerpt() ) : ?>
							<p><?php the_excerpt(); ?></p>
						<?php endif; ?>
						
						<div class="doc-meta">
							<?php
							$version = get_the_terms( get_the_ID(), 'wps_doc_version' );
							if ( $version && ! is_wp_error( $version ) ) :
								?>
								<span class="doc-version">v<?php echo esc_html( $version[0]->name ); ?></span>
							<?php endif; ?>
						</div>
					</div>
				</div>
				<?php
			}
			?>
		</div>
		<?php
		wp_reset_postdata();
		return ob_get_clean();
	}

	/**
	 * Get rating display for a testimonial.
	 *
	 * @since 1.6093.1200
	 * @param  int $post_id Post ID.
	 * @return string Rating display.
	 */
	private static function get_rating_display( $post_id ) {
		$ratings = get_the_terms( $post_id, 'wps_rating' );
		if ( ! $ratings || is_wp_error( $ratings ) ) {
			return '';
		}

		$rating_name = $ratings[0]->name;
		$rating_num  = (int) filter_var( $rating_name, FILTER_SANITIZE_NUMBER_INT );

		if ( $rating_num > 0 ) {
			return str_repeat( '★', $rating_num ) . str_repeat( '☆', 5 - $rating_num );
		}

		return $rating_name;
	}
}
