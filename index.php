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
define('GAL_ROOT', PLUGINS_URI.GAL_ID);
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

Plugin::addController(GAL_ID, __('Gallery'), 'administrator', true);
AutoLoader::addFolder(PLUGINS_ROOT. DS. GAL_ID. DS. 'models');      // Sometimes doesn't load?

// Load the model if the autoloader failed to (Note the second argument to prevent the autoloader from trying and failing again)
if (class_exists('Gallery', false))
{
    AutoLoader::addFile('Gallery', PLUGINS_ROOT. DS. GAL_ID. DS. 'models'. DS. 'Gallery.php');
}

//http://hamlinsacc.com.au/new/gallery/file

Dispatcher::addRoute(array(
    '/'.GAL_URL. '(|/)'                             => '/plugin/'. GAL_ID. '/test',
    '/'.GAL_URL. '/'                                => '/plugin/'. GAL_ID. '/test',
    '/'.GAL_URL. '/file/([0-9a-z-]+)/([0-9]+)'      => '/plugin/'. GAL_ID. '/file/$1/$2',
));