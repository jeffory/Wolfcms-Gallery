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

define('GAL_ID', 'gallery');
define('GAL_ROOT', PLUGINS_URI.'gallery');
define('GAL_URL', '/gallery');

Plugin::setInfos(array(
    'id'          => 'gallery',
    'title'       => __('Gallery'),
    'description' => __('Adds a simple and easy to use gallery to a website.'),
    'version'     => '1.0',
   	'license'     => 'Commerical',
	'author'      => 'Keith McGahey',
    'website'     => 'http://www.keithmcgahey.com/',
    // 'update_url'  => 'http://www.keithmcgahey.com/',
    'require_wolf_version' => '0.7.3'
));

Plugin::addController(GAL_ID, __('Gallery'), 'administrator', true);
AutoLoader::addFolder(PLUGINS_ROOT. DS. GAL_ID. DS. 'models');      // Sometimes doesn't load?

// Load the model if the autoloader failed to (Note the second argument to prevent the autoloader from trying and failing again)
if (class_exists('Gallery', false))
{
    AutoLoader::addFile('Gallery', PLUGINS_ROOT. DS. GAL_ID. DS. 'models'. DS. 'Gallery.php');
}

Dispatcher::addRoute(array(
    GAL_URL. '(|/)'           => '/plugin/gallery/test',
    GAL_URL. '/test'          => '/plugin/gallery/test',
));

