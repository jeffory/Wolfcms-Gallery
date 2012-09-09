<?php
/* Security measure */
if (!defined('IN_CMS')) { exit(); }

// For printing constants easily in strings

/**
 * Simple, easy to setup and use gallery plugin for WolfCMS
 *
 * @package Plugins
 * @subpackage Gallery
 *
 * @author Keith McGahey
 */

// 
define('GAL_ID', 'gallery');
// Where the folder containing the plugin is...
define('GAL_ROOT', PLUGINS_ROOT. DS. GAL_ID);
define('GAL_URL', 'gallery');
define('GAL_TITLE', 'Gallery');

Plugin::setInfos(array(
    'id'                    => GAL_ID,
    'title'                 => __(GAL_TITLE),
    'description'           => __('Adds a simple and easy to manipulate/use '. __(GAL_TITLE). ' to a website.'),
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

Plugin::addController(GAL_ID, __('Gallery'), 'administrator', true);
AutoLoader::addFolder(PLUGINS_ROOT. DS. GAL_ID. DS. 'models');      // Sometimes doesn't load?

Dispatcher::addRoute(array(
    '/'.GAL_URL. '(|/)'                             => '/plugin/'. GAL_ID. '/front_index',
    '/'.GAL_URL. '/file/([0-9a-z-]+)/([0-9]+)'      => '/plugin/'. GAL_ID. '/file/$1/$2',
));