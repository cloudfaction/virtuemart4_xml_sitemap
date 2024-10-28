<?php
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;

class PlgSystemVirtuemartSitemap extends CMSPlugin
{
    protected $app;
    protected $db;

    public function onAfterInitialise()
    {
        $this->app = Factory::getApplication();
        
        if ($this->app->isClient('administrator')) {
            return;
        }

        if ($this->app->input->getCmd('option') === 'com_ajax' && 
            $this->app->input->getCmd('plugin') === 'virtuemartsitemap' && 
            $this->app->input->getCmd('group') === 'system' && 
            $this->app->input->getCmd('format') === 'xml') {
            
            $this->generateSitemap();
        }
    }

    protected function generateSitemap()
    {
        // Load VirtueMart configuration
        if (!class_exists('VmConfig')) {
            require_once(JPATH_ROOT . '/administrator/components/com_virtuemart/helpers/config.php');
        }
        VmConfig::loadConfig();

        // Load VirtueMart router
        if (!class_exists('VirtueMartRouter')) {
            require_once(JPATH_ROOT . '/components/com_virtuemart/router.php');
        }

        header('Content-Type: application/xml; charset=utf-8');
        
        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        $db = Factory::getDbo();
        $lang = Factory::getLanguage();
        $langTag = str_replace('-', '_', $lang->getTag());
        $langTag = 'nl_nl';

        // Get products with category information
        $query = $db->getQuery(true)
            ->select([
                'p.virtuemart_product_id',
                'p.modified_on',
                'p.created_on',
                'l.slug as product_slug',
                'l.product_name',
                'c.virtuemart_category_id',
                'cl.slug as category_slug',
                'cl.category_name'
            ])
            ->from('#__virtuemart_products p')
            ->join('LEFT', '#__virtuemart_products_' . $langTag . ' l ON l.virtuemart_product_id = p.virtuemart_product_id')
            ->join('LEFT', '#__virtuemart_product_categories pc ON pc.virtuemart_product_id = p.virtuemart_product_id')
            ->join('LEFT', '#__virtuemart_categories c ON c.virtuemart_category_id = pc.virtuemart_category_id')
            ->join('LEFT', '#__virtuemart_categories_' . $langTag . ' cl ON cl.virtuemart_category_id = c.virtuemart_category_id')
            ->where('p.published = 1')
            ->order('CASE 
                        WHEN p.modified_on != "0000-00-00 00:00:00" THEN p.modified_on 
                        ELSE p.created_on 
                    END DESC')
            ->setLimit(1000);

        $db->setQuery($query);
        $products = $db->loadObjectList();

        $changefreq = $this->params->get('changefreq', 'weekly');
        $priority = $this->params->get('priority', '0.8');

        // Get base URL without /administrator if present
        $base = Uri::base();
        $base = rtrim($base, '/');
        if (strpos($base, '/administrator') !== false) {
            $base = str_replace('/administrator', '', $base);
        }

        // Remove index.php from URL if SEF is enabled
        if (JFactory::getConfig()->get('sef') == '1') {
            $base = str_replace('index.php', '', $base);
            $base = rtrim($base, '/');
        }

        if (!empty($products)) {
            foreach ($products as $product) {
                if (!empty($product->virtuemart_product_id)) {
                    // Build SEF URL
                    $url = 'index.php?option=com_virtuemart&view=productdetails';
                    $url .= '&virtuemart_product_id=' . $product->virtuemart_product_id;
                    $url .= '&virtuemart_category_id=' . $product->virtuemart_category_id;
                    
                    if (!empty($product->product_slug)) {
                        $url .= '&name=' . $product->product_slug;
                    }
                    
                    // Convert to SEF URL
                    $url = Route::_($url, true, Route::TLS_IGNORE, true);
                    
                    // Ensure URL is absolute
                    if (strpos($url, 'http') !== 0) {
                        if (substr($url, 0, 1) !== '/') {
                            $url = '/' . $url;
                        }
                        $url = $base . $url;
                    }

                    // Calculate age-based priority
                    $lastModified = (!empty($product->modified_on) && $product->modified_on != "0000-00-00 00:00:00") 
                        ? strtotime($product->modified_on) 
                        : strtotime($product->created_on);
                    
                    $age = time() - $lastModified;
                    $ageDays = floor($age / (60 * 60 * 24));
                    
                    $dynamicPriority = $priority;
                    if ($ageDays <= 7) {
                        $dynamicPriority = '1.0';
                    } elseif ($ageDays <= 30) {
                        $dynamicPriority = '0.9';
                    } elseif ($ageDays <= 90) {
                        $dynamicPriority = '0.8';
                    } else {
                        $dynamicPriority = '0.7';
                    }
                    
                    echo "\t<url>\n";
                    echo "\t\t<loc>" . htmlspecialchars($url) . "</loc>\n";
                    
                    $lastmod = (!empty($product->modified_on) && $product->modified_on != "0000-00-00 00:00:00")
                        ? $product->modified_on
                        : $product->created_on;
                    
                    if (!empty($lastmod) && $lastmod != "0000-00-00 00:00:00") {
                        echo "\t\t<lastmod>" . date('Y-m-d', strtotime($lastmod)) . "</lastmod>\n";
                    }
                    
                    if ($ageDays <= 7) {
                        echo "\t\t<changefreq>daily</changefreq>\n";
                    } else {
                        echo "\t\t<changefreq>" . $changefreq . "</changefreq>\n";
                    }
                    
                    echo "\t\t<priority>" . $dynamicPriority . "</priority>\n";
                    echo "\t</url>\n";
                }
            }
        }

        echo '</urlset>';
        
        $this->app->close();
    }
}