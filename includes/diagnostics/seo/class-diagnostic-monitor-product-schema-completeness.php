<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_Product extends Diagnostic_Base {
  public static function check(): ?array {
    return ['id' => 'monitor-product_schema_completeness', 'title' => __('Product Schema Completeness', 'wpshadow'), 'description' => __('E-commerce: verifies price, availability, stock schema. Incomplete = lower rankings.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/', 'training_link' => 'https://wpshadow.com/training/', 'auto_fixable' => false, 'threat_level' => 6];
  }
}
