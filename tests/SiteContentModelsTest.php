<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use WPShadow\Content\Post_Types\Site_Content_Models;

require_once dirname( __DIR__ ) . '/includes/content/post-types/class-site-content-models.php';

final class SiteContentModelsTest extends TestCase {

	public static function setUpBeforeClass(): void {
		update_option(
			'wpshadow_post_type_activation_settings',
			array(
				'case_study'       => 1,
				'portfolio_item'   => 1,
				'testimonial'      => 1,
				'service'          => 1,
				'training_program' => 1,
				'training_event'   => 1,
				'download'         => 1,
				'tool'             => 1,
				'faq'              => 1,
			),
			false
		);

		Site_Content_Models::register_post_types();
		Site_Content_Models::register_taxonomies();
	}

	/**
	 * @var array<int, string>
	 */
	private array $post_types = array(
		'case_study',
		'portfolio_item',
		'testimonial',
		'service',
		'training_program',
		'training_event',
		'download',
		'tool',
		'faq',
	);

	/**
	 * @var array<int, string>
	 */
	private array $taxonomies = array(
		'case_study_industry',
		'case_study_service',
		'portfolio_type',
		'portfolio_technology',
		'testimonial_service',
		'location',
		'faq_topic',
	);

	public function test_migrated_post_types_are_registered(): void {
		foreach ( $this->post_types as $post_type ) {
			$this->assertTrue(
				post_type_exists( $post_type ),
				sprintf( 'Expected post type %s to be registered.', $post_type )
			);
		}
	}

	public function test_migrated_taxonomies_are_registered(): void {
		foreach ( $this->taxonomies as $taxonomy ) {
			$this->assertTrue(
				taxonomy_exists( $taxonomy ),
				sprintf( 'Expected taxonomy %s to be registered.', $taxonomy )
			);
		}
	}

	public function test_training_event_menu_and_archive_settings(): void {
		$object = get_post_type_object( 'training_event' );
		$this->assertNotNull( $object );
		$this->assertSame( 'edit.php?post_type=training_program', $object->show_in_menu );
		$this->assertSame( 'training/events', $object->has_archive );
	}

	public function test_location_taxonomy_is_attached_to_expected_post_types(): void {
		$location_taxonomy = get_taxonomy( 'location' );
		$this->assertNotFalse( $location_taxonomy );
		$this->assertContains( 'case_study', $location_taxonomy->object_type );
		$this->assertContains( 'tool', $location_taxonomy->object_type );
		$this->assertContains( 'download', $location_taxonomy->object_type );
	}

	public function test_definition_accessors_expose_post_type_and_taxonomy_maps(): void {
		$post_type_definitions = Site_Content_Models::get_post_type_definitions();
		$taxonomy_definitions  = Site_Content_Models::get_taxonomy_definitions();

		$this->assertArrayHasKey( 'training_program', $post_type_definitions );
		$this->assertArrayHasKey( 'location', $taxonomy_definitions );
		$this->assertSame( 'training', $post_type_definitions['training_program']['rewrite_slug'] );
	}

	public function test_taxonomy_lookup_is_scoped_to_post_type(): void {
		$training_program_taxonomies = Site_Content_Models::get_taxonomies_for_post_type( 'training_program' );
		$faq_taxonomies              = Site_Content_Models::get_taxonomies_for_post_type( 'faq' );

		$this->assertContains( 'location', $training_program_taxonomies );
		$this->assertContains( 'faq_topic', $training_program_taxonomies );
		$this->assertContains( 'faq_topic', $faq_taxonomies );
		$this->assertNotContains( 'location', $faq_taxonomies );
	}
}
