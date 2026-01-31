#!/usr/bin/env python3

"""
WPShadow KB Article Batch Creator
Creates well-structured KB articles on the live server for all KB links referenced in the plugin
"""

import subprocess
import json
import sys
import time
import base64
from pathlib import Path

class KBArticleCreator:
    def __init__(self, site_url, username, password, category_id=3, status='draft'):
        self.site_url = site_url.rstrip('/')
        self.username = username
        self.password = password
        self.category_id = category_id
        self.status = status
        
        # Create Basic Auth header
        credentials = f"{username}:{password}"
        encoded = base64.b64encode(credentials.encode()).decode()
        self.auth_header = f"Basic {encoded}"
        
        # Statistics
        self.created = 0
        self.skipped = 0
        self.failed = 0
        self.total = 0
    
    def get_kb_links(self):
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
    
    def slug_to_title(self, slug):
        """Convert slug to readable title"""
        # Replace hyphens with spaces
        title = slug.replace('-', ' ')
        # Capitalize each word
        title = ' '.join(word.capitalize() for word in title.split())
        return title
    
    def generate_content(self, slug, title):
        """Generate KB article content"""
        # Determine category from slug
        if 'woocommerce' in slug:
            category = 'WooCommerce'
            context = 'ecommerce platform'
        elif 'wordfence' in slug:
            category = 'Security'
            context = 'WordPress security'
        elif 'wordpress' in slug or 'wp-' in slug:
            category = 'WordPress Core'
            context = 'WordPress functionality'
        elif 'optimization' in slug or 'performance' in slug or 'cache' in slug:
            category = 'Performance'
            context = 'site performance'
        elif 'security' in slug or 'ssl' in slug or 'https' in slug:
            category = 'Security'
            context = 'website security'
        elif 'seo' in slug or 'schema' in slug or 'sitemap' in slug:
            category = 'SEO'
            context = 'search engine optimization'
        elif 'accessibility' in slug or 'wcag' in slug:
            category = 'Accessibility'
            context = 'web accessibility'
        elif 'privacy' in slug or 'gdpr' in slug or 'data' in slug:
            category = 'Privacy & GDPR'
            context = 'data privacy and compliance'
        else:
            category = 'General'
            context = 'WordPress site management'
        
        content = f"""<p>This knowledge base article provides comprehensive guidance on <strong>{title}</strong> to help optimize your {context}.</p>

<h2>Overview</h2>
<p>Understanding and properly implementing {title.lower()} is essential for maintaining a healthy, secure, and performant WordPress website.</p>

<h2>Category</h2>
<p><em>{category}</em></p>

<h2>Key Concepts</h2>
<ul>
<li>What is {title.lower()} and why it matters</li>
<li>How it impacts your WordPress site</li>
<li>Best practices and recommendations</li>
<li>Common issues and solutions</li>
<li>Tools and resources for management</li>
</ul>

<h2>Implementation Steps</h2>
<ol>
<li>Assess your current {title.lower()} configuration</li>
<li>Identify areas for improvement</li>
<li>Follow recommended best practices</li>
<li>Test and validate changes</li>
<li>Monitor ongoing performance</li>
</ol>

<h2>Troubleshooting</h2>
<p>If you encounter issues with {title.lower()}:</p>
<ul>
<li>Check your WordPress error logs</li>
<li>Verify plugin/theme compatibility</li>
<li>Review recent configuration changes</li>
<li>Consult the WPShadow diagnostics dashboard</li>
<li>Contact support with relevant details</li>
</ul>

<h2>Resources</h2>
<ul>
<li><a href="https://wpshadow.com/kb/{slug}">WPShadow Knowledge Base</a></li>
<li><a href="https://wordpress.org/support/">WordPress Support</a></li>
<li>Related documentation and guides</li>
</ul>

<h2>Next Steps</h2>
<p>After implementing the recommendations in this article:</p>
<ul>
<li>Run WPShadow diagnostics to verify improvements</li>
<li>Monitor site performance metrics</li>
<li>Review related KB articles for additional optimization</li>
</ul>

<p><em>Last updated: {time.strftime('%B %d, %Y')}</em></p>
<p><em>This article is part of the WPShadow Knowledge Base and may be updated with new information.</em></p>
"""
        return content
    
    def create_article(self, slug, title, content):
        """Create KB article via REST API using curl"""
        endpoint = f"{self.site_url}/wp-json/wp/v2/posts"
        
        payload = {
            'title': title,
            'content': content,
            'status': self.status,
            'slug': slug,
            'categories': [self.category_id],
            'comment_status': 'open',
            'ping_status': 'open'
        }
        
        try:
            # Use curl to make the request
            cmd = [
                'curl',
                '-s',
                '-w', '\n%{http_code}',
                '-X', 'POST',
                endpoint,
                '-H', f'Authorization: {self.auth_header}',
                '-H', 'Content-Type: application/json',
                '-d', json.dumps(payload)
            ]
            
            result = subprocess.run(cmd, capture_output=True, text=True, timeout=10)
            
            # Parse response
            lines = result.stdout.strip().split('\n')
            http_code = lines[-1]
            response_body = '\n'.join(lines[:-1])
            
            if http_code == '201':
                try:
                    data = json.loads(response_body)
                    return True, data.get('id')
                except:
                    return True, 'created'
            elif http_code == '400':
                if 'already exists' in response_body.lower():
                    return False, 'exists'
                return False, f"Error: {response_body[:100]}"
            else:
                return False, f"HTTP {http_code}"
        except Exception as e:
            return False, str(e)
    
    def run(self):
        """Main execution"""
        print("\n🚀 WPShadow KB Article Creator")
        print("=" * 50)
        
        # Get KB links
        print("\n📝 Extracting KB links from plugin...")
        kb_links = self.get_kb_links()
        self.total = len(kb_links)
        print(f"✅ Found {self.total} KB articles to create\n")
        
        # Create articles
        print("📋 Creating KB articles...\n")
        for i, slug in enumerate(kb_links, 1):
            title = self.slug_to_title(slug)
            content = self.generate_content(slug, title)
            
            success, result = self.create_article(slug, title, content)
            
            if success:
                self.created += 1
                print(f"✅ [{i}/{self.total}] Created: {slug}")
                print(f"   📄 Title: {title}")
                print(f"   🔗 Post ID: {result}\n")
            elif result == 'exists':
                self.skipped += 1
                print(f"⏭️  [{i}/{self.total}] Skipped: {slug} (already exists)\n")
            else:
                self.failed += 1
                print(f"❌ [{i}/{self.total}] Failed: {slug}")
                print(f"   ⚠️  Error: {result}\n")
            
            # Rate limiting to avoid overwhelming the server
            time.sleep(0.5)
        
        # Print summary
        print("\n" + "=" * 50)
        print("📊 Summary:")
        print(f"✅ Created:  {self.created}")
        print(f"⏭️  Skipped:  {self.skipped}")
        print(f"❌ Failed:   {self.failed}")
        print(f"📈 Total:    {self.total}")
        print("=" * 50 + "\n")
        
        return self.failed == 0


if __name__ == '__main__':
    creator = KBArticleCreator(
        site_url='https://wpshadow.com',
        username='github',
        password='github',
        category_id=3,  # Adjust KB category ID as needed
        status='draft'
    )
    
    success = creator.run()
    sys.exit(0 if success else 1)
