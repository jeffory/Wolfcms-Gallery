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

Plugin::setInfos(array(
    'id'          => 'wolfcms-gallery',
    'title'       => __('Gallery'),
    'description' => __('Adds a simple and easy to use gallery to a website.'),
    'version'     => '1.0',
   	'license'     => 'Commerical',
	'author'      => 'Keith McGahey',
    'website'     => 'http://www.keithmcgahey.com/',
    'update_url'  => 'http://www.keithmcgahey.com/',
    'require_wolf_version' => '0.5.5'
));

Plugin::addController('gallery', __('Gallery'), 'administrator', false);