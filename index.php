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
    'require_wolf_version' => '0.5.5'
));

AutoLoader::addFolder(PLUGINS_ROOT. DS. GAL_ID. DS. 'models'. DS);
Plugin::addController(GAL_ID, __('Gallery'), 'administrator', false);

Dispatcher::addRoute(array(
    GAL_URL. '(|/)'           => '/plugin/gallery/test',
    GAL_URL. '/test'          => '/plugin/gallery/test',
));