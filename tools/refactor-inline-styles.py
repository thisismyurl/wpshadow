#!/usr/bin/env python3
"""
Advanced inline style refactoring script.
Extracts inline styles, creates CSS files, and updates PHP files with class-based styling.
"""

import os
import re
import json
from pathlib import Path
from collections import defaultdict

class AdvancedStyleRefactor:
    def __init__(self, plugin_dir):
        self.plugin_dir = Path(plugin_dir)
        self.css_dir = self.plugin_dir / 'assets' / 'css'
        self.css_dir.mkdir(parents=True, exist_ok=True)
        self.styles = defaultdict(lambda: defaultdict(int))
        self.replacements = {}  # Map of old_inline_style -> class_name
        self.css_rules = defaultdict(set)  # category -> set of css rules
        self.php_files = []
        
    def normalize_style(self, style_string):
        """Normalize style strings for better matching."""
        # Remove extra spaces, normalize colons and semicolons
        normalized = re.sub(r'\s+', ' ', style_string.strip())
        normalized = re.sub(r';\s*$', '', normalized)  # Remove trailing semicolon
        return normalized
    
    def categorize_style(self, style_string):
        """Categorize a style for CSS organization."""
        if any(prop in style_string for prop in ['display:', 'flex', 'grid', 'align', 'justify']):
            return 'layouts'
        elif any(prop in style_string for prop in ['color:', 'background:', 'border:', 'shadow']):
            return 'colors'
        elif any(prop in style_string for prop in ['margin:', 'padding:']):
            return 'spacing'
        elif any(prop in style_string for prop in ['font', 'text-align']):
            return 'typography'
        else:
            return 'utilities'
    
    def style_to_classname(self, style_string):
        """Convert style string to a meaningful CSS class name."""
        props = {}
        for prop in style_string.split(';'):
            if ':' in prop:
                key, val = prop.split(':', 1)
                key = key.strip()
                val = val.strip()
                if key and val:
                    props[key.lower()] = val
        
        # Create meaningful class names based on properties
        parts = []
        
        if 'display' in props:
            parts.append(props['display'].replace(' ', '-'))
        
        if 'flex' in props or 'display: flex' in style_string.lower():
            if 'gap' in props:
                parts.append(f"gap-{props['gap'].replace('px', '')}")
            if 'align-items' in props:
                parts.append('items-' + props['align-items'].replace(' ', '-').lower())
            if 'justify-content' in props:
                parts.append('justify-' + props['justify-content'].replace(' ', '-').lower())
        
        if 'margin' in props:
            margin_val = re.sub(r'[^0-9]', '', props['margin'].split()[0])
            if margin_val:
                parts.append(f"m-{margin_val}")
        
        if 'padding' in props:
            padding_val = re.sub(r'[^0-9]', '', props['padding'].split()[0])
            if padding_val:
                parts.append(f"p-{padding_val}")
        
        if 'border-radius' in props:
            radius = props['border-radius'].replace('px', '')
            parts.append(f"rounded-{radius}")
        
        if parts:
            class_name = 'wps-' + '-'.join(parts)[:40]  # Limit length
            return class_name
        
        return None
    
    def extract_from_file(self, file_path):
        """Extract inline styles from a single file."""
        try:
            with open(file_path, 'r', encoding='utf-8', errors='ignore') as f:
                content = f.read()
        except Exception as e:
            print(f"  ⚠ Error reading {file_path}: {e}")
            return content, {}
        
        # Find all inline styles
        style_pattern = r'style\s*=\s*["\']([^"\']*)["\']'
        style_count = 0
        replacements_for_file = {}
        
        def replace_style(match):
            nonlocal style_count
            style_content = match.group(1).strip()
            
            # Skip empty styles or those with PHP variables in critical positions
            if not style_content or style_content.count('<?php') > style_content.count('?>'):
                return match.group(0)
            
            # Normalize
            normalized = self.normalize_style(style_content)
            
            # Check if we've already processed this exact style
            if normalized in self.replacements:
                class_name = self.replacements[normalized]
                style_count += 1
                return f'class="{class_name}"'
            
            # Try to generate a class name
            class_name = self.style_to_classname(style_content)
            if class_name:
                category = self.categorize_style(style_content)
                self.css_rules[category].add(f".{class_name} {{ {style_content} }}")
                self.replacements[normalized] = class_name
                replacements_for_file[normalized] = class_name
                style_count += 1
                return f'class="{class_name}"'
            
            # If we can't auto-generate, keep the inline style for now
            return match.group(0)
        
        # Replace all inline styles
        new_content = re.sub(style_pattern, replace_style, content)
        return new_content, replacements_for_file
    
    def process_all_files(self):
        """Process all PHP files and extract/replace styles."""
        print("📝 Processing files for style extraction...\n")
        
        files_processed = 0
        total_styles_replaced = 0
        
        # Find all PHP files
        for root, dirs, files in os.walk(self.plugin_dir):
            dirs[:] = [d for d in dirs if d not in ['vendor', 'node_modules', 'tmp', '.git', '.github']]
            
            for file in files:
                if not file.endswith('.php'):
                    continue
                
                file_path = Path(root) / file
                rel_path = file_path.relative_to(self.plugin_dir)
                
                # Skip certain files
                if any(x in str(rel_path) for x in ['wp-content/plugins', 'node_modules']):
                    continue
                
                new_content, replacements = self.extract_from_file(file_path)
                
                if replacements:
                    try:
                        with open(file_path, 'w', encoding='utf-8') as f:
                            f.write(new_content)
                        files_processed += 1
                        total_styles_replaced += len(replacements)
                        print(f"  ✓ {rel_path}: {len(replacements)} styles replaced")
                    except Exception as e:
                        print(f"  ✗ Error writing {rel_path}: {e}")
        
        print(f"\n✅ Processed {files_processed} files, replaced {total_styles_replaced} inline styles")
        return files_processed, total_styles_replaced
    
    def generate_css_files(self):
        """Generate CSS files from collected rules."""
        print("\n🎨 Generating CSS files...\n")
        
        css_files_created = {}
        
        for category, rules in self.css_rules.items():
            css_path = self.css_dir / f'wps-inline-{category}.css'
            css_content = f"""/**
 * WPShadow Auto-Generated CSS - {category.title()}
 * Auto-extracted from inline styles
 * Generated: {Path(__file__).name}
 * Do not edit manually - run extract-inline-styles.py to regenerate
 */

"""
            for rule in sorted(rules):
                css_content += rule + "\n\n"
            
            with open(css_path, 'w') as f:
                f.write(css_content)
            
            css_files_created[category] = str(css_path)
            print(f"  ✓ Created {css_path.name} with {len(rules)} CSS rules")
        
        return css_files_created
    
    def generate_enqueue_code(self):
        """Generate PHP code to enqueue the new CSS files."""
        css_files = list(self.css_dir.glob('wps-inline-*.css'))
        
        php_code = """/**
 * Auto-generated enqueue code for extracted CSS
 * Add this to your asset loading function
 */

// Enqueue extracted inline styles
"""
        
        for css_file in sorted(css_files):
            handle = 'wpshadow-' + css_file.stem
            rel_path = css_file.relative_to(self.plugin_dir)
            php_code += f"""
wp_enqueue_style(
    '{handle}',
    WPSHADOW_URL . '{rel_path}',
    array('wpshadow-main'),
    WPSHADOW_VERSION
);
"""
        
        return php_code
    
    def create_summary_report(self):
        """Create a summary report of changes."""
        report = {
            'total_css_rules_generated': sum(len(rules) for rules in self.css_rules.values()),
            'css_rules_by_category': {cat: len(rules) for cat, rules in self.css_rules.items()},
            'style_replacements': len(self.replacements),
            'css_files_created': list(self.css_dir.glob('wps-inline-*.css'))
        }
        
        report_path = self.plugin_dir / 'INLINE_STYLES_REFACTOR_REPORT.json'
        with open(report_path, 'w') as f:
            json.dump(report, f, indent=2, default=str)
        
        return report

def main():
    plugin_dir = '/workspaces/wpshadow'
    refactor = AdvancedStyleRefactor(plugin_dir)
    
    print("=" * 60)
    print("WPShadow Inline Style Refactoring Tool")
    print("=" * 60 + "\n")
    
    files_processed, styles_replaced = refactor.process_all_files()
    css_files = refactor.generate_css_files()
    report = refactor.create_summary_report()
    
    print("\n" + "=" * 60)
    print("REFACTORING SUMMARY")
    print("=" * 60)
    print(f"\n📊 Files processed: {files_processed}")
    print(f"🔄 Inline styles replaced: {styles_replaced}")
    print(f"📄 CSS files created: {len(css_files)}")
    print(f"🎯 Total CSS rules: {report['total_css_rules_generated']}")
    
    print("\n📝 CSS Rules by Category:")
    for cat, count in report['css_rules_by_category'].items():
        if count > 0:
            print(f"   {cat}: {count} rules")
    
    print("\n✅ Refactoring complete!")
    print(f"\n📍 Generated CSS files in: {refactor.css_dir}")
    print(f"📌 Report saved to: {plugin_dir}/INLINE_STYLES_REFACTOR_REPORT.json")

if __name__ == '__main__':
    main()
