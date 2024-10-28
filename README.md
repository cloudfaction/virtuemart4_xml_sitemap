# VirtueMart XML Sitemap Plugin for Joomla

## Description
This plugin generates an XML sitemap specifically for VirtueMart products. It shows the latest 1000 products with SEF (Search Engine Friendly) URLs and includes dynamic priorities based on product age and modification dates.

## Features
- Generates XML sitemap for VirtueMart products
- Supports SEF (Search Engine Friendly) URLs
- Shows latest 1000 products (easy to change in the SQL statement)
- Dynamic priority calculation based on product age
- Includes product modification dates
- Supports multilingual sites
- Configurable change frequency
- Configurable default priority
- No additional configuration needed after installation

## Requirements
- Joomla 3.x or 4.x
- VirtueMart 3.x or 4.x
- PHP 7.2 or higher
- SEF URLs enabled in Joomla (recommended)
- mod_rewrite enabled on your server (for SEF URLs)

## Installation
1. Download the plugin files
2. Create directory structure in your Joomla installation:
   ```
   /plugins/system/virtuemartsitemap/
   ```
3. Place these files in the directory:
   - virtuemartsitemap.php
   - virtuemartsitemap.xml
4. Install through Joomla's Extension Manager:
   - Go to Extensions → Manage → Install
   - Choose "Install from Folder"
   - Point to the plugin directory
5. Enable the plugin:
   - Go to Extensions → Plugins
   - Find "System - VirtueMart Sitemap"
   - Enable it

## Usage
Access your sitemap at:
```
your-site.com/index.php?option=com_ajax&plugin=virtuemartsitemap&group=system&format=xml
```

## Configuration
In the plugin settings, you can configure:
- Change Frequency (default: weekly)
  - Options: always, hourly, daily, weekly, monthly, yearly, never
- Default Priority (default: 0.8)
  - Range: 0.0 to 1.0

## Dynamic Priority System
The plugin automatically adjusts priorities based on product age:
- Last 7 days: 1.0
- Last 30 days: 0.9
- Last 90 days: 0.8
- Older products: 0.7

## URL Format
With SEF URLs enabled:
```
https://your-site.com/shop/category-name/product-name.html
```

Without SEF:
```
https://your-site.com/index.php/shop/category-name/product-name.html
```

## Optimizing for Search Engines
1. Enable SEF URLs in Joomla:
   - System → Global Configuration → SEO Settings
   - Set "Search Engine Friendly URLs" to Yes
   - Set "URL Rewriting" to Yes

2. Ensure proper .htaccess file (Apache servers):
   ```apache
   RewriteEngine On
   RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
   RewriteCond %{REQUEST_URI} !^/index\.php
   RewriteCond %{REQUEST_FILENAME} !-f
   RewriteCond %{REQUEST_FILENAME} !-d
   RewriteRule .* index.php [L]
   ```

## Troubleshooting
1. **Empty Sitemap**
   - Check if VirtueMart products are published
   - Verify database tables exist and have content
   - Check user permissions

2. **404 Error**
   - Ensure plugin is enabled
   - Check URL parameters are correct
   - Verify .htaccess configuration

3. **Non-SEF URLs**
   - Check if SEF is enabled in Joomla configuration
   - Verify mod_rewrite is enabled
   - Check .htaccess file exists and is properly configured

## Support
For issues and feature requests, please create an issue in the GitHub repository or contact the developer.

## License
GNU General Public License version 2 or later

## Credits
Developed by Jmodules
Copyright (C) 2024 Jmodules. All rights reserved.
