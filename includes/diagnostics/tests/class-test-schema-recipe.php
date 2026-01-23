<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_Schema_Recipe extends Diagnostic_Base
{

	protected static $slug = 'test-schema-recipe';
	protected static $title = 'Recipe Schema Test';
	protected static $description = 'Tests for Recipe structured data';

	public static function check(?string $url = null, ?string $html = null): ?array
	{
		if ($html !== null) {
			return self::analyze_html($html, $url ?? 'provided-html');
		}

		$html = self::fetch_html($url ?? home_url('/'));
		if ($html === false) {
			return null;
		}

		return self::analyze_html($html, $url ?? home_url('/'));
	}

	protected static function analyze_html(string $html, string $checked_url): ?array
	{
		// Check for recipe indicators
		$has_recipe_keywords = preg_match('/\b(recipe|ingredient|instructions|cook time|prep time|servings)\b/i', $html);
		$has_list_structure = preg_match('/<ol[^>]*>|<ul[^>]*>/i', $html);

		// Count potential ingredients (li elements in context)
		preg_match_all('/<li[^>]*>.*?(cup|tbsp|tsp|oz|lb|gram|ml|teaspoon|tablespoon).*?<\/li>/is', $html, $ingredient_matches);
		$potential_ingredients = count($ingredient_matches[0]);

		// Check for Recipe schema
		$has_recipe_schema = preg_match('/"@type"\s*:\s*"Recipe"/i', $html);

		// If looks like recipe but no schema
		if ($has_recipe_keywords && $has_list_structure && $potential_ingredients >= 3 && !$has_recipe_schema) {
			return [
				'id' => 'schema-recipe-missing',
				'title' => 'Recipe Schema Missing',
				'description' => sprintf(
					'Recipe content detected (%d potential ingredients) but no Recipe structured data found. Recipe schema enables rich results with images, ratings, and cook time.',
					$potential_ingredients
				),
				'color' => '#2196f3',
				'bg_color' => '#e3f2fd',
				'kb_link' => 'https://wpshadow.com/kb/recipe-schema/',
				'training_link' => 'https://wpshadow.com/training/recipe-seo/',
				'auto_fixable' => false,
				'threat_level' => 40,
				'module' => 'SEO',
				'priority' => 2,
				'meta' => [
					'has_recipe_keywords' => $has_recipe_keywords,
					'potential_ingredients' => $potential_ingredients,
					'has_schema' => $has_recipe_schema,
					'checked_url' => $checked_url,
				],
			];
		}

		return null;
	}

	protected static function fetch_html(string $url)
	{
		$response = wp_remote_get($url, ['timeout' => 10, 'sslverify' => false]);
		return is_wp_error($response) ? false : wp_remote_retrieve_body($response);
	}

	public static function get_name(): string
	{
		return __('Recipe Schema', 'wpshadow');
	}

	public static function get_description(): string
	{
		return __('Checks for Recipe structured data (food blogs).', 'wpshadow');
	}
}
