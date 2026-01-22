<?php declare(strict_types=1);
namespace WPShadow\Diagnostics;
class Diagnostic_Monitor_Image-alt-text-coverage {
  public static function check() {
    return ['id' => 'monitor-image_alt_text_coverage', 'title' => __('Image Alt Text Coverage', 'wpshadow'), 'description' => __('Tracks % of images with alt text. Low coverage = accessibility issue, SEO miss.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/', 'training_link' => 'https://wpshadow.com/training/', 'auto_fixable' => false, 'threat_level' => 6];
  }
}
