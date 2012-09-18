<?php
/* Security measure */
if (!defined('IN_CMS')) { exit(); }

/**
 * Simple, easy to setup and use gallery plugin for WolfCMS
 *
 * @package Plugins
 * @subpackage Gallery
 *
 * @author Keith McGahey
 */

// Renaming this allows the Gallery to be used as say a products list or a download listing and so on...
define('GAL_ID', 'gallery');                        // For the Plugin section in admin
define('GAL_ROOT', PLUGINS_ROOT. DS. 'gallery');    // Directory root to the plugin
define('GAL_C_CLASS', 'gallery');                   // Class names, (ie. gallery = GalleryController) Needs to be lowercase!

define('GAL_URL', 'gallery');                       // Base URL to use the plugin
define('GAL_TITLE', 'Gallery');                     // Title of the plugin (for views and what not)

Plugin::setInfos(array(
    'id'                    => GAL_ID,
    'title'                 => __(GAL_TITLE),
    'description'           => __('Adds a simple and easy to manipulate/use '. __(GAL_TITLE). '.'),
    'version'               => '1.0',
   	'license'               => 'Commerical',
	'author'                => 'Keith McGahey',
    'website'               => 'http://www.keithmcgahey.com/',
    // 'update_url'         => 'http://www.keithmcgahey.com/',
    'require_wolf_version'  => '0.7.3',
    'type'                  => 'both',
));

AutoLoader::addFile('PluginRecord', GAL_ROOT. DS. 'PluginRecord.php');
//AutoLoader::addFile('GalleryItem', GAL_ROOT. DS. 'models'. DS. 'GalleryItem.php');

Plugin::addController(GAL_C_CLASS, __(GAL_TITLE), 'administrator', true);
AutoLoader::addFolder(GAL_ROOT. DS. 'models');      // Sometimes doesn't load?

// A lot of the functions have to be re-routed to allow for different plugin names
Dispatcher::addRoute(array(
    '/'. GAL_URL. '(|/)'                                        => '/plugin/'. GAL_C_CLASS. '/front_category_index',
    '/admin/plugin/'.GAL_URL. '(|/)'                            => '/plugin/'. GAL_C_CLASS. '/index',
    '/admin/plugin/'.GAL_URL. '/categories(|/)'                     => '/plugin/'. GAL_C_CLASS. '/category_index',
    '/admin/plugin/'.GAL_URL. '/categories/page:([0-9]+)'       => '/plugin/'. GAL_C_CLASS. '/category_index/$1',
    '/admin/plugin/'.GAL_URL. '/categories/delete/([0-9]+)'     => '/plugin/'. GAL_C_CLASS. '/category_delete/$1',
    '/admin/plugin/'.GAL_URL. '/page:([0-9]+)'                  => '/plugin/'. GAL_C_CLASS. '/index/$1',
    '/admin/plugin/'.GAL_URL. '/add(|/)'                            => '/plugin/'. GAL_C_CLASS. '/add',
    '/admin/plugin/'.GAL_URL. '/edit/([0-9]+)'                  => '/plugin/'. GAL_C_CLASS. '/edit/$1',
    '/admin/plugin/'.GAL_URL. '/delete/([0-9]+)'                => '/plugin/'. GAL_C_CLASS. '/delete/$1',
    '/admin/plugin/'.GAL_URL. '/clearall(|/)'                       => '/plugin/'. GAL_C_CLASS. '/clearall',
    '/'. GAL_URL. '/file/([0-9a-z-]+)/([0-9]+)(.?(?:[a-z]+)|)'  => '/plugin/'. GAL_C_CLASS. '/file/$1/$2',
));