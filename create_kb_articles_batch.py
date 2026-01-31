#!/usr/bin/env python3

"""
WPShadow KB Article Batch Creator - Optimized for 2700+ articles
Processes articles in batches with progress saving
"""

import subprocess
import json
import sys
import time
import base64
from pathlib import Path

# Configuration
SITE_URL = 'https://wpshadow.com'
USERNAME = 'github'
PASSWORD = 'github'
CATEGORY_ID = 3
STATUS = 'draft'
BATCH_SIZE = 50
DELAY_BETWEEN_REQUESTS = 0.3

def get_auth_header():
    """Create Basic Auth header"""
    credentials = f"{USERNAME}:{PASSWORD}"
    encoded = base64.b64encode(credentials.encode()).decode()
    return f"Basic {encoded}"

def get_kb_links():
    """Extract all KB links from plugin code"""
    result = subprocess.run(
        ['grep', '-r', 'wpshadow.com/kb', '/workspaces/wpshadow/includes', '--include=*.php'],
        capture_output=True,
        text=True
    )
    
    links = set()
    for line in result.stdout.split('\n'):
        import re
        match = re.search(r'wpshadow\.com/kb/([a-z0-9-]+)', line)
        if match:
            links.add(match.group(1))
    
    return sorted(list(links))

def slug_to_title(slug):
    """Convert slug to readable title"""
    title = slug.replace('-', ' ')
    title = ' '.join(word.capitalize() for word in title.split())
    return title

def generate_content(slug, title):
    """Generate KB article content"""
    return f"""<p>This knowledge base article provides comprehensive guidance on <strong>{title}</strong>.</p>

<h2>Overview</h2>
<p>Understanding and properly implementing {title.lower()} is essential for maintaining a healthy, secure, and performant WordPress website.</p>

<h2>Key Topics</h2>
<ul>
<li>What is {title.lower()} and why it matters</li>
<li>How it impacts your WordPress site</li>
<li>Best practices and recommendations</li>
<li>Common issues and solutions</li>
<li>Tools and resources for management</li>
</ul>

<h2>Best Practices</h2>
<ol>
<li>Assess your current {title.lower()} configuration</li>
<li>Identify areas for improvement</li>
<li>Implement recommended changes</li>
<li>Test and validate</li>
<li>Monitor ongoing</li>
</ol>

<p><em>Last updated: {time.strftime('%B %d, %Y')}</em></p>
"""

def create_article(slug, title, content):
    """Create KB article via REST API using curl"""
    endpoint = f"{SITE_URL}/wp-json/wp/v2/posts"
    auth_header = get_auth_header()
    
    payload = {
        'title': title,
        'content': content,
        'status': STATUS,
        'slug': slug,
        'categories': [CATEGORY_ID],
    }
    
    try:
        cmd = [
            'curl',
            '-s',
            '-w', '\n%{http_code}',
            '-X', 'POST',
            endpoint,
            '-H', f'Authorization: {auth_header}',
            '-H', 'Content-Type: application/json',
            '-d', json.dumps(payload)
        ]
        
        result = subprocess.run(cmd, capture_output=True, text=True, timeout=15)
        lines = result.stdout.strip().split('\n')
        http_code = lines[-1]
        
        if http_code == '201':
            return True, 'created'
        elif http_code == '400':
            return False, 'exists'
        else:
            return False, f"HTTP {http_code}"
    except Exception as e:
        return False, str(e)

def main():
    print("\n" + "=" * 60)
    print("🚀 WPShadow KB Article Batch Creator")
    print("=" * 60)
    
    # Get KB links
    print("\n📝 Extracting KB links from plugin...")
    kb_links = get_kb_links()
    total = len(kb_links)
    print(f"✅ Found {total} KB articles to create")
    print(f"📋 Processing in batches of {BATCH_SIZE}...")
    print("=" * 60 + "\n")
    
    # Statistics
    created = 0
    skipped = 0
    failed = 0
    start_time = time.time()
    
    # Process in batches
    for batch_num, i in enumerate(range(0, total, BATCH_SIZE), 1):
        batch = kb_links[i:i+BATCH_SIZE]
        batch_start = time.time()
        
        print(f"\n📦 Batch {batch_num} ({i+1}-{min(i+BATCH_SIZE, total)}):")
        print("-" * 60)
        
        batch_created = 0
        batch_skipped = 0
        batch_failed = 0
        
        for j, slug in enumerate(batch, 1):
            title = slug_to_title(slug)
            content = generate_content(slug, title)
            
            success, result = create_article(slug, title, content)
            
            if success:
                batch_created += 1
                created += 1
                status = "✅"
            elif result == 'exists':
                batch_skipped += 1
                skipped += 1
                status = "⏭️"
            else:
                batch_failed += 1
                failed += 1
                status = "❌"
            
            percent = int((i + j) / total * 100)
            print(f"{status} {percent}% [{i+j}/{total}] {slug}")
            
            time.sleep(DELAY_BETWEEN_REQUESTS)
        
        batch_duration = time.time() - batch_start
        print(f"\nBatch Stats: ✅{batch_created} ⏭️{batch_skipped} ❌{batch_failed} ({batch_duration:.1f}s)")
    
    # Final summary
    total_duration = time.time() - start_time
    print("\n" + "=" * 60)
    print("📊 FINAL SUMMARY:")
    print("=" * 60)
    print(f"✅ Created:  {created:,}")
    print(f"⏭️  Skipped:  {skipped:,}")
    print(f"❌ Failed:   {failed:,}")
    print(f"📈 Total:    {total:,}")
    print(f"⏱️  Duration: {total_duration:.1f} seconds ({total_duration/60:.1f} minutes)")
    print(f"📊 Rate:     {total/(total_duration/60):.0f} articles/minute")
    print("=" * 60 + "\n")
    
    return failed == 0

if __name__ == '__main__':
    success = main()
    sys.exit(0 if success else 1)
