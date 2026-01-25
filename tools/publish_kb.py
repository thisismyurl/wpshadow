#!/usr/bin/env python3
"""
Publish a KB markdown file to WordPress via REST API.
- Parses YAML frontmatter
- Converts Markdown to HTML
- Ensures taxonomies exist (kb_category, kb_difficulty, post_tag)
- Creates or updates the KB post by slug

Requirements:
  pip install pyyaml markdown requests (markdown optional if already installed)

Env vars (.env or environment):
  WP_SITE_URL=https://wpshadow.com
  WP_USERNAME=thisismyurl
  WP_APP_PASSWORD=xxxx xxxx xxxx xxxx

Usage:
  python3 tools/publish_kb.py kb-articles/performance/missing-database-indexes.md
"""

import base64
import json
import os
import re
import sys
import urllib.error
import urllib.parse
import urllib.request
from pathlib import Path

try:
    import yaml  # type: ignore
except ImportError:  # pragma: no cover
    print("❌ Missing dependency: pyyaml. Install with: pip install pyyaml")
    sys.exit(1)

try:
    import markdown  # type: ignore
except ImportError:  # pragma: no cover
    markdown = None


def load_env():
    env = {}
    if os.path.exists('.env'):
        with open('.env') as f:
            for line in f:
                line = line.strip()
                if line and not line.startswith('#') and '=' in line:
                    k, v = line.split('=', 1)
                    env[k] = v
    # Only override with real environment variables if they are set
    for key in ['WP_SITE_URL', 'WP_USERNAME', 'WP_APP_PASSWORD']:
        env_val = os.environ.get(key)
        if env_val:
            env[key] = env_val
    missing = [k for k, v in env.items() if k.startswith('WP_') and not v]
    if missing:
        print(f"❌ Missing env vars: {', '.join(missing)}")
        sys.exit(1)
    return env


def basic_auth_header(username: str, app_password: str) -> str:
    token = base64.b64encode(f"{username}:{app_password}".encode()).decode()
    return f"Basic {token}"


def http_request(url: str, method: str, headers: dict, data: dict | None = None):
    body = None
    if data is not None:
        body = json.dumps(data).encode()
        headers['Content-Type'] = 'application/json'
    req = urllib.request.Request(url, data=body, method=method)
    for k, v in headers.items():
        req.add_header(k, v)
    try:
        with urllib.request.urlopen(req, timeout=10) as resp:
            return resp.getcode(), json.loads(resp.read().decode())
    except urllib.error.HTTPError as e:  # pragma: no cover
        try:
            detail = e.read().decode()
        except Exception:
            detail = ''
        print(f"❌ HTTP {e.code} {e.reason}: {detail}")
        sys.exit(1)
    except urllib.error.URLError as e:  # pragma: no cover
        print(f"❌ Connection error: {e.reason}")
        sys.exit(1)


def parse_markdown(path: Path):
    text = path.read_text(encoding='utf-8')
    if not text.startswith('---'):
        print('❌ Missing frontmatter (---)')
        sys.exit(1)
    parts = text.split('---', 2)
    if len(parts) < 3:
        print('❌ Invalid frontmatter format')
        sys.exit(1)
    frontmatter_raw = parts[1]
    body = parts[2]
    meta = yaml.safe_load(frontmatter_raw) or {}
    return meta, body.strip()


def validate_article_content(meta: dict, body: str, path: Path) -> list:
    """
    Validate that article has real content, not placeholder instructions.
    Returns list of validation errors (empty list if valid).
    """
    errors = []
    
    # Check for placeholder patterns in frontmatter
    description = meta.get('description', '')
    if '[Brief description' in description or not description or len(description) < 20:
        errors.append('Description is placeholder or too short')
    
    related = meta.get('related_articles', [])
    if any('[related-article' in str(r) for r in related):
        errors.append('Related articles contain placeholders like [related-article-1]')
    
    # Check for placeholder patterns in body
    placeholder_patterns = [
        '[Clear, concise overview',
        '[Brief description',
        '[Real-world impact',
        '[describe benefit]',
        '[Result/confirmation text]',
        '[Steps for using WPShadow',
        '[Technical explanation',
        '[Benchmarking, optimization',
        '[Links to related KB',
        '[Common question about',
        '[Direct, helpful answer]',
        '[WPShadow Feature',
        '[How this article embodies',
        'Draft - Needs Content',
    ]
    
    for pattern in placeholder_patterns:
        if pattern in body:
            errors.append(f'Body contains placeholder: "{pattern}"')
            break  # Only report first placeholder found
    
    # Check minimum content length
    if len(body) < 500:
        errors.append(f'Article body too short ({len(body)} chars, minimum 500)')
    
    return errors


def principle_to_anchor(principle_id: str) -> str:
    """
    Convert principle ID like '#07-ridiculously-good' to anchor like 'ridiculously-good'.
    """
    # Remove # prefix and leading numbers/dashes
    anchor = principle_id.lstrip('#').lstrip('0123456789-')
    return anchor


def markdown_to_html(md: str, meta: dict = None) -> str:
    if markdown is None:
        print('❌ Missing dependency: markdown. Install with: pip install markdown')
        sys.exit(1)
    
    meta = meta or {}
    
    # Remove sections we don't want published
    lines = md.split('\n')
    filtered_lines = []
    skip_until_next_section = False
    in_quality_checklist = False
    in_read_on_wpshadow = False
    in_article_metadata = False
    in_core_principles = False
    in_related_features = False
    
    for i, line in enumerate(lines):
        # Skip "Read on WPShadow" section and block link
        if '> **Read on WPShadow:**' in line:
            in_read_on_wpshadow = True
            continue
        if in_read_on_wpshadow and line.startswith('---'):
            in_read_on_wpshadow = False
            skip_until_next_section = False
            continue
        if in_read_on_wpshadow:
            continue
        
        # Skip Quality Checklist section
        if '## ✓ Quality Checklist' in line:
            in_quality_checklist = True
            continue
        if in_quality_checklist and line.startswith('---'):
            in_quality_checklist = False
            continue
        if in_quality_checklist:
            continue
        
        # Skip Article Metadata section
        if '## Article Metadata' in line or '## 📋 Article Metadata' in line:
            in_article_metadata = True
            continue
        if in_article_metadata and line.startswith('---'):
            in_article_metadata = False
            continue
        if in_article_metadata:
            continue
        
        # Skip Core Principles section (will use frontmatter instead)
        if '## Core Principles' in line:
            in_core_principles = True
            continue
        if in_core_principles and line.startswith('---'):
            in_core_principles = False
            continue
        if in_core_principles:
            continue
        
        # Skip Related Features section (will handle separately if needed)
        if '## Related Features' in line or '## Related WPShadow Features' in line:
            in_related_features = True
            continue
        if in_related_features and line.startswith('---'):
            in_related_features = False
            continue
        if in_related_features:
            continue
        
        # Skip the first H1 title
        if i == 0 and line.startswith('# '):
            continue
        
        filtered_lines.append(line)
    
    md_filtered = '\n'.join(filtered_lines).strip()
    
    # Extract summary from TLDR section and opening paragraph from "What This Means"
    summary_text = ''
    summary_lines = []
    opening_paragraph = ''
    opening_lines = []
    in_summary = False
    in_opening = False
    summary_ended = False
    
    for line in md_filtered.split('\n'):
        if '## 📝 Summary (TLDR)' in line or '## Summary (TLDR)' in line:
            in_summary = True
            continue
        if in_summary and line.startswith('## '):
            summary_ended = True
            in_summary = False
        if in_summary and line.strip():
            summary_lines.append(line)
        
        # Capture opening paragraph (What This Means section)
        if '## What This Means' in line:
            in_opening = True
            continue
        if in_opening and line.startswith('## '):
            in_opening = False
        # Include blank lines to preserve paragraph breaks in markdown
        if in_opening and not line.startswith('---'):
            opening_lines.append(line)
    
    if summary_lines:
        summary_text = '\n'.join(summary_lines).strip()
    
    if opening_lines:
        opening_paragraph = '\n'.join(opening_lines).strip()
    
    # Remove TLDR section and What This Means section from body
    lines_out = []
    skip_summary = False
    skip_opening = False
    for line in md_filtered.split('\n'):
        if '## 📝 Summary (TLDR)' in line or '## Summary (TLDR)' in line:
            skip_summary = True
            continue
        if skip_summary and line.startswith('##'):
            skip_summary = False
        
        if '## What This Means' in line:
            skip_opening = True
            continue
        if skip_opening and line.startswith('##'):
            skip_opening = False
        
        if not skip_summary and not skip_opening:
            lines_out.append(line)
    md_filtered = '\n'.join(lines_out)
    
    # Replace tier headings with friendlier names
    md_filtered = md_filtered.replace('## Tier 1: Beginner Summary', '## Getting Started')
    md_filtered = md_filtered.replace('## Tier 2: Intermediate', '## How to Do It')
    md_filtered = md_filtered.replace('## Tier 3: Advanced', '## Technical Details')
    md_filtered = md_filtered.replace('## Tier 4: Developer', '## For Developers')
    
    # Remove the principles markdown section - will be added inline in HTML instead
    # (was adding as section at end, now we'll add as inline badges)
    
    # Add Related Features links
    # Note: Looking for 'related_features' in meta (not in current template, but supporting it)
    related_features = meta.get('related_features', [])
    if related_features:
        features_html = '\n\n## Related WPShadow Features\n\n'
        for feature_slug in related_features:
            # Convert slug to readable name
            feature_name = feature_slug.replace('-', ' ').title()
            features_html += f'- [{feature_name}](/features/{feature_slug})\n'
        md_filtered += features_html
    
    # Replace backup warnings with WPShadow backup reminder
    backup_reminder = '<div class="wp-block-notice notice-info"><strong>💾 Backup reminder:</strong> WPShadow includes an offsite backup tool with free registration. Make sure you\'re backed up before making database changes.</div>'
    md_filtered = md_filtered.replace('### ⚠️ Before You Start', f'### Before You Start\n\n{backup_reminder}')
    md_filtered = md_filtered.replace('**Create a backup.**', '**Create a backup.** WPShadow offers free offsite backups via our backup tool.')
    
    # Wrap code in <code> tags for inline code
    # (markdown will handle code blocks)
    
    # Convert markdown to HTML
    html_body = markdown.markdown(md_filtered, extensions=['extra', 'tables', 'fenced_code', 'toc'])
    
    # Extract headings for TOC
    headings = []
    for line in md_filtered.split('\n'):
        if line.startswith('##'):
            level = len(line) - len(line.lstrip('#'))
            text = line.lstrip('# ').strip()
            # Convert heading text to ID
            heading_id = text.lower().replace(' ', '-').replace(':', '').replace('(', '').replace(')', '')
            headings.append((level, text, heading_id))
    
    # Build TOC HTML with h2 (proper block format)
    toc_html = ''
    if headings:
        toc_html = '<!-- wp:heading -->\n<h2 class="wp-block-heading">Contents</h2>\n<!-- /wp:heading -->\n'
        toc_html += '<!-- wp:list {"className":"toc-list"} -->\n<ul class="toc-list">\n'
        for level, text, heading_id in headings:
            indent = '  ' * (level - 2)
            toc_html += f'{indent}<li><a href="#{heading_id}">{text}</a></li>\n'
        toc_html += '</ul><!-- /wp:list -->\n\n'
    
    # Build final HTML with WordPress Blocks
    final_html = ''
    
    # Summary block (proper block format with newlines)
    if summary_text:
        summary_html = markdown.markdown(summary_text, extensions=['extra'])
        final_html += '<!-- wp:group {"className":"summary-section"} -->\n'
        final_html += '<div class="wp-block-group summary-section">\n'
        final_html += summary_html
        final_html += '</div>\n'
        final_html += '<!-- /wp:group -->\n\n'
    
    # Opening paragraph block with special class (proper formatting)
    if opening_paragraph:
        opening_html = markdown.markdown(opening_paragraph, extensions=['extra'])
        # Add opening class to all paragraphs in the opening section
        opening_html = opening_html.replace('<p>', '<p class="opening">')
        # Wrap each paragraph in blocks
        opening_blocks = ''
        for p_tag in opening_html.split('</p>'):
            if p_tag.strip():
                opening_blocks += f'<!-- wp:paragraph {{"className":"opening"}} -->\n{p_tag}</p>\n<!-- /wp:paragraph -->\n'
        final_html += opening_blocks + '\n'
    
    # TOC
    final_html += toc_html
    
    # Main content wrapped in blocks
    # Split content by headings and paragraphs for proper block structure
    content_blocks = convert_html_to_blocks(html_body)
    final_html += content_blocks
    
    # Add Core Principles as inline badges at the end
    principles = meta.get('principles', [])
    if principles:
        final_html += '<!-- wp:paragraph {"className":"principles-badges"} -->\n'
        final_html += '<p class="principles-badges">'
        for i, principle in enumerate(principles):
            anchor = principle_to_anchor(principle)
            name = principle.lstrip('#').lstrip('0123456789-').replace('-', ' ').title()
            if i > 0:
                final_html += ' '
            final_html += f'<a href="/principles/#{anchor}" class="principle-badge">{name}</a>'
        final_html += '</p>\n'
        final_html += '<!-- /wp:paragraph -->\n'
    
    return final_html


def validate_gutenberg_blocks(content: str) -> list[str]:
    """
    Validate Gutenberg block structure in content.
    Returns list of validation errors (empty list = valid).
    """
    issues = []
    
    # Find all blocks with their boundaries
    blocks = []
    pos = 0
    while True:
        match = re.search(r'<!-- wp:(\w+)(?:\s+({[^}]*}))?(?:\s*\/)?-->', content[pos:])
        if not match:
            break
        
        block_name = match.group(1)
        block_start = pos + match.start()
        block_content_start = pos + match.end()
        close_pattern = f'<!-- /wp:{block_name} -->'
        close_match = content.find(close_pattern, block_content_start)
        
        if close_match != -1:
            blocks.append((block_name, block_start, close_match + len(close_pattern)))
            pos = close_match + len(close_pattern)
        else:
            pos = pos + match.end()
    
    # Check for stray content (text outside all block boundaries)
    covered = set()
    for name, start, end in blocks:
        for i in range(start, end):
            covered.add(i)
    
    stray_segments = []
    in_stray = False
    stray_start = 0
    
    for i, char in enumerate(content):
        if i not in covered:
            if not in_stray:
                stray_start = i
                in_stray = True
        else:
            if in_stray:
                text = content[stray_start:i].strip()
                if len(text) > 5 and not text.startswith('<!--'):
                    stray_segments.append(text)
                in_stray = False
    
    if in_stray:
        text = content[stray_start:].strip()
        if len(text) > 5 and not text.startswith('<!--'):
            stray_segments.append(text)
    
    if stray_segments:
        for segment in stray_segments:
            preview = segment.replace('\n', ' ')[:60]
            issues.append(f"Stray content outside block boundaries: {preview}...")
    
    return issues


def _add_class_to_tag(line: str, tag: str, class_name: str) -> str:
    """Ensure a tag line includes the given class while preserving other attributes."""
    if not line.strip().startswith(f'<{tag}'):
        return line
    if 'class="' in line:
        prefix, rest = line.split('class="', 1)
        classes, suffix = rest.split('"', 1)
        class_list = classes.split()
        if class_name not in class_list:
            class_list.append(class_name)
        return f"{prefix}class=\"{' '.join(class_list)}\"{suffix}"
    return line.replace(f'<{tag}', f'<{tag} class="{class_name}"', 1)


def _extract_code_content(code_block_html: str) -> str:
    """Extract inner code content from a <code>...</code> block."""
    if '<code' not in code_block_html:
        return code_block_html
    after_code = code_block_html.split('<code', 1)[1]
    parts = after_code.split('>', 1)
    if len(parts) < 2:
        return code_block_html
    inner = parts[1]
    inner = inner.rsplit('</code', 1)[0]
    return inner.strip('\n')


def convert_html_to_blocks(html: str) -> str:
    """
    Convert HTML content to WordPress Gutenberg blocks.
    Wraps headings, paragraphs, lists, code blocks in proper block comments.
    """
    lines = html.split('\n')
    output = []
    in_list = False
    in_code = False
    in_table = False
    code_language = ''
    current_list_ordered = False
    current_list_tag = ''
    code_buffer = []
    list_buffer = []
    table_buffer = []
    text_list_mode = False
    
    i = 0
    def finalize_code():
        nonlocal in_code, code_buffer, code_language
        if not in_code or not code_buffer:
            return
        code_content = _extract_code_content('\n'.join(code_buffer))
        if code_language:
            output.append(f'<!-- wp:code {{"language":"{code_language}"}} -->\n')
        else:
            output.append('<!-- wp:code -->\n')
        output.append(f'<pre class="wp-block-code"><code{f" class=\"language-{code_language}\"" if code_language else ""}>{code_content}</code></pre>\n')
        output.append('<!-- /wp:code -->\n')
        in_code = False
        code_buffer = []
        code_language = ''

    def finalize_list():
        nonlocal in_list, list_buffer, current_list_ordered, current_list_tag, text_list_mode
        if not in_list or not list_buffer:
            return
        if current_list_tag:
            list_buffer[0] = _add_class_to_tag(list_buffer[0], current_list_tag, 'wp-block-list')
        if current_list_ordered:
            output.append('<!-- wp:list {"ordered":true} -->\n')
        else:
            output.append('<!-- wp:list -->\n')
        output.append('\n'.join(list_buffer))
        output.append('\n<!-- /wp:list -->\n')
        in_list = False
        list_buffer = []
        current_list_ordered = False
        current_list_tag = ''
        text_list_mode = False

    while i < len(lines):
        line = lines[i]
        stripped = line.strip()
        
        # Handle headings
        if stripped.startswith('<h2'):
            finalize_list()
            finalize_code()
            output.append('<!-- wp:heading -->\n')
            output.append(_add_class_to_tag(line, 'h2', 'wp-block-heading'))
            output.append('\n<!-- /wp:heading -->\n')
        
        elif stripped.startswith('<h3'):
            finalize_list()
            output.append('<!-- wp:heading {"level":3} -->\n')
            output.append(_add_class_to_tag(line, 'h3', 'wp-block-heading'))
            output.append('\n<!-- /wp:heading -->\n')
        
        elif stripped.startswith('<h4'):
            finalize_list()
            output.append('<!-- wp:heading {"level":4} -->\n')
            output.append(_add_class_to_tag(line, 'h4', 'wp-block-heading'))
            output.append('\n<!-- /wp:heading -->\n')
        
        # Handle code blocks - capture entire block until closing </pre>
        elif (stripped.startswith('<pre><code') or stripped.startswith('<pre class')) and not in_code:
            in_code = True
            code_buffer = [line]
            lang_match = 'language-'
            code_language = ''
            if lang_match in line:
                try:
                    lang_start = line.index(lang_match) + len(lang_match)
                    lang_end = line.index('"', lang_start)
                    code_language = line[lang_start:lang_end]
                except Exception:
                    code_language = ''
        
        elif in_code:
            code_buffer.append(line)
            if stripped.startswith('</code></pre>') or '</pre>' in stripped:
                finalize_code()
        
        # Handle lists - collect entire list
        elif (stripped.startswith('<ul') or stripped.startswith('<ol')) and not in_list:
            in_list = True
            list_buffer = [line]
            current_list_ordered = stripped.startswith('<ol')
            current_list_tag = 'ol' if current_list_ordered else 'ul'
            text_list_mode = False
        
        elif in_list and not text_list_mode:
            list_buffer.append(line)
            if (stripped.startswith('</ul>') or stripped.startswith('</ol>')):
                finalize_list()
        
        # Handle plain-text bullet/numbered lists that weren't converted to HTML lists
        elif not in_code and (stripped.startswith('- ') or stripped.startswith('* ') or stripped.startswith('&#8211; ') or re.match(r'^\d+\.', stripped)):
            ordered_match = re.match(r'^(\d+)\.', stripped)
            is_ordered = bool(ordered_match)
            bullet_text = stripped
            if ordered_match:
                bullet_text = stripped[len(ordered_match.group(0)):].strip()
            elif stripped.startswith('&#8211; '):
                bullet_text = stripped[len('&#8211; '):].strip()
            else:
                bullet_text = stripped[2:].strip()
            closing_paragraph = False
            if bullet_text.endswith('</p>'):
                bullet_text = bullet_text[:-4].strip()
                closing_paragraph = True
            if bullet_text.startswith('<p>'):
                bullet_text = bullet_text[3:].strip()
            if not in_list:
                in_list = True
                current_list_ordered = is_ordered
                current_list_tag = 'ol' if is_ordered else 'ul'
                list_buffer = [f'<{current_list_tag}>']
                text_list_mode = True
            elif text_list_mode:
                text_list_mode = True
            list_buffer.append(f'<li>{bullet_text}</li>')
            # If next line is not a bullet, close the list immediately
            next_stripped = lines[i + 1].strip() if i + 1 < len(lines) else ''
            if closing_paragraph or not (next_stripped.startswith('- ') or next_stripped.startswith('* ') or next_stripped.startswith('&#8211; ') or re.match(r'^\d+\.', next_stripped)):
                list_buffer.append(f'</{current_list_tag}>')
                finalize_list()

        elif in_list and text_list_mode:
            # Non-bullet line after a text-derived list; close list and reprocess this line
            finalize_list()
            i -= 1
        
        # Handle paragraphs (not in code, not in lists)
        elif stripped.startswith('<p') and not in_code and not in_list:
            output.append('<!-- wp:paragraph -->\n')
            output.append(line)
            output.append('\n<!-- /wp:paragraph -->\n')
        
        # Handle tables
        elif stripped.startswith('<table') and not in_table:
            in_table = True
            table_buffer = []
            output.append('<!-- wp:table -->\n')
            output.append('<figure class="wp-block-table">\n')
            table_buffer.append(line)
        
        elif in_table:
            table_buffer.append(line)
            if stripped.startswith('</table>'):
                in_table = False
                output.append('\n'.join(table_buffer))
                output.append('\n</figure>\n')
                output.append('<!-- /wp:table -->\n')
                table_buffer = []
        
        # Handle divs (notices, etc)
        elif stripped.startswith('<div class="wp-block-notice'):
            output.append('<!-- wp:group {"className":"notice-info"} -->\n')
            output.append(line)
            # Find matching closing div
            i += 1
            while i < len(lines) and not lines[i].strip().endswith('</div>'):
                output.append(lines[i])
                i += 1
            if i < len(lines):
                output.append(lines[i])
            output.append('\n<!-- /wp:group -->\n')
            i -= 1  # Adjust since loop will increment
        
        else:
            # Only add non-empty lines that aren't already handled
            if stripped and not in_code and not in_list:
                output.append(line)
        
        i += 1
    
    # Close any remaining open blocks
    if in_list and list_buffer:
        finalize_list()
    if in_code and code_buffer:
        finalize_code()
    
    result = '\n'.join(output)
    # Clean up excessive newlines
    while '\n\n\n' in result:
        result = result.replace('\n\n\n', '\n\n')
    
    return result


def ensure_term(site: str, auth_header: str, taxonomy: str, name: str, slug: str | None = None) -> int:
    slug = slug or name
    base = f"{site}/wp-json/wp/v2/{taxonomy}"
    query = f"{base}?slug={urllib.parse.quote(slug)}"
    status, existing = http_request(query, 'GET', {'Authorization': auth_header})
    if isinstance(existing, list) and existing:
        return existing[0]['id']
    payload = {'name': name, 'slug': slug}
    status, created = http_request(base, 'POST', {'Authorization': auth_header}, payload)
    return created['id']


def find_post(site: str, auth_header: str, slug: str):
    url = f"{site}/wp-json/wp/v2/kb?slug={urllib.parse.quote(slug)}"
    status, data = http_request(url, 'GET', {'Authorization': auth_header})
    if isinstance(data, list) and data:
        return data[0]
    return None


def publish(article_path: Path):
    env = load_env()
    site = env['WP_SITE_URL'].rstrip('/')
    auth_header = basic_auth_header(env['WP_USERNAME'], env['WP_APP_PASSWORD'])

    meta, body_md = parse_markdown(article_path)
    
    # Validate article content before publishing
    validation_errors = validate_article_content(meta, body_md, article_path)
    if validation_errors:
        print(f'❌ Article validation failed: {article_path.name}')
        for error in validation_errors:
            print(f'   • {error}')
        print('\n💡 This article appears to be a stub with placeholder content.')
        print('   Please write real content before publishing.')
        sys.exit(1)
    
    slug = article_path.stem

    title = meta.get('title') or slug.replace('-', ' ').title()
    description = meta.get('description') or ''
    category = meta.get('category') or 'general'
    difficulty = meta.get('difficulty') or 'intermediate'
    tags = meta.get('tags') or []
    status = meta.get('status') or 'draft'

    read_time = meta.get('read_time')
    principles = meta.get('principles') or []
    related = meta.get('related_articles') or []
    course_link = meta.get('course_link') or ''
    course_name = meta.get('course_name') or ''
    kb_last_updated = meta.get('last_updated') or ''

    html = markdown_to_html(body_md, meta)

    # Validate Gutenberg blocks before publishing
    block_errors = validate_gutenberg_blocks(html)
    if block_errors:
        print(f'❌ Gutenberg block validation failed: {article_path.name}')
        print('\n📋 Issues found:')
        for error in block_errors:
            print(f'   • {error}')
        print('\n⚠️  Content has structure issues that will trigger editor warnings.')
        print('   Please review and fix before publishing.')
        sys.exit(1)
    
    print(f'✅ Gutenberg blocks validated successfully\n')

    # Ensure taxonomies exist
    cat_id = ensure_term(site, auth_header, 'kb_category', category, slug=category)
    diff_id = ensure_term(site, auth_header, 'kb_difficulty', difficulty, slug=difficulty)
    tag_ids = []
    for tag in tags:
        tag_id = ensure_term(site, auth_header, 'tags', tag, slug=tag)
        tag_ids.append(tag_id)

    existing = find_post(site, auth_header, slug)
    meta_fields = {
        'read_time': int(read_time) if read_time else None,
        'principles': principles,
        'related_articles': related,
        'course_link': course_link,
        'course_name': course_name,
        'article_status': status,
        'kb_last_updated': kb_last_updated,
    }
    # Strip None values so the API only receives populated meta
    meta_fields = {k: v for k, v in meta_fields.items() if v not in [None, '', []]}

    payload = {
        'title': title,
        'content': html,
        'slug': slug,
        'status': status,
        'author': 2,  # wpshadowteam
        'excerpt': description,
        'kb_category': [cat_id],
        'kb_difficulty': [diff_id],
        'tags': tag_ids,
        'meta': meta_fields,
    }

    if existing:
        post_id = existing['id']
        url = f"{site}/wp-json/wp/v2/kb/{post_id}"
        status_code, resp = http_request(url, 'PUT', {'Authorization': auth_header}, payload)
        action = 'updated'
    else:
        url = f"{site}/wp-json/wp/v2/kb"
        status_code, resp = http_request(url, 'POST', {'Authorization': auth_header}, payload)
        action = 'created'

    link = resp.get('link') or resp.get('guid', {}).get('rendered')
    print(f"✅ KB article {action}: {title}")
    if link:
        print(f"   URL: {link}")


def main():
    if len(sys.argv) != 2:
        print("Usage: python3 tools/publish_kb.py kb-articles/path/article.md")
        sys.exit(1)
    path = Path(sys.argv[1])
    if not path.exists():
        print(f"❌ File not found: {path}")
        sys.exit(1)
    publish(path)


if __name__ == '__main__':
    main()
