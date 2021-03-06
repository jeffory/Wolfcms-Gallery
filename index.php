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
define('GAL_ID', 'gallery');                            // For the Plugin section in admin
define('GAL_ROOT', PLUGINS_ROOT. DS. 'gallery');        // Directory root to the plugin
define('GAL_C_CLASS', 'gallery');                       // Class names, (ie. gallery = GalleryController) Needs to be lowercase!

define('GAL_URL', 'gallery');                          // Base URL to use the plugin
define('GAL_TITLE', 'Gallery');                        // Title of the plugin (for views and what not)
define('GAL_SLUG', Node::toSlug(GAL_TITLE));

define('GAL_IMAGES_ROOT', CMS_ROOT. '/public/files');      // Directory where images not stored in the database will be stored

define('GAL_LAYOUT', 'Wolf');

Plugin::setInfos(array(
    'id'                    => GAL_ID,
    'title'                 => __(GAL_TITLE),
    'description'           => __('Adds a simple and easy to use'). ' '. __(GAL_TITLE). '.',
    'version'               => '1.0',
   	'license'               => 'GPLv3',
	'author'                => 'Keith McGahey',
    'website'               => 'http://www.keithmcgahey.com/',
    'update_url'            => 'https://raw.githubusercontent.com/jeffory/Wolfcms-Gallery/master/plugin_version.xml',
    'require_wolf_version'  => '0.7.3',
    'type'                  => 'both',
));

AutoLoader::addFile('PluginRecord', GAL_ROOT. DS. 'PluginRecord.php');
//AutoLoader::addFile('GalleryItem', GAL_ROOT. DS. 'models'. DS. 'GalleryItem.php');

Plugin::addController(GAL_C_CLASS, __(GAL_TITLE), 'admin_view', true);
AutoLoader::addFolder(GAL_ROOT. DS. 'models');      // Sometimes doesn't load? Problem with xampp it seems

// A lot of the functions have to be re-routed to allow for different plugin names
Dispatcher::addRoute(array(
    '/'. GAL_URL. '(|/)'                                                                => '/plugin/'. GAL_C_CLASS. '/front_category_index',
    '/'. GAL_URL. '/([0-9]+)(|/[a-z0-9-]+)'                                             => '/plugin/'. GAL_C_CLASS. '/front_items_index/$1/$2',
    '/'. GAL_URL. '/([0-9]+)(?:|/[a-z0-9-]+)/([0-9]+)(?:|/[a-z0-9-]+)'                  => '/plugin/'. GAL_C_CLASS. '/front_item/$2/$1',
    '/admin/plugin/'.GAL_URL . '(|/)'                                                   => '/plugin/'. GAL_C_CLASS. '/index',
    '/admin/plugin/'.GAL_URL. '/categories(|/)(?:|)(|page)(|/[0-9]+)'                   => '/plugin/'. GAL_C_CLASS. '/category_index$1$2',
    '/admin/plugin/'.GAL_URL. '/categories/page:([0-9]+)'                               => '/plugin/'. GAL_C_CLASS. '/category_index/$1',
    '/admin/plugin/'.GAL_URL. '/categories/(add|delete|edit)(?:\:|)([0-9]+|)'           => '/plugin/'. GAL_C_CLASS. '/category_$1/$2',
    '/admin/plugin/'.GAL_URL. '/page:([0-9]+)'                                          => '/plugin/'. GAL_C_CLASS. '/index/$1/$2',
    '/admin/plugin/'.GAL_URL. '/(add|edit|delete|addsamples|clearall)(?:\:|)([0-9]+|)'  => '/plugin/'. GAL_C_CLASS. '/$1/$2',
    '/'. GAL_URL. '/file/([0-9a-z-_]+)/([0-9]+)(.?(?:[a-z]+)|)'                         => '/plugin/'. GAL_C_CLASS. '/file/$1/$2',
));

/**
 * Singularises a word, so functions = function
 *
 * @var string pural word
 * 
 * @return string singular word
 **/
function singularise($word)
{
    $singularize_rules = array(
        '/([a-zA-Z-]+)s$/i' => '$1',
        '/([a-zA-Z-]+)ies$/i' => '$1y'
        );

    $word = preg_replace(array_keys($singularize_rules), array_values($singularize_rules), $word);

    return $word;
}

/**
 * Case insensitive version of in_array
 *
 * @var string the searched value
 * @var array the array to search
 * 
 * @return boolean if needle found in array
 **/
function in_iarray($needle, $haystack) {
    return in_array(strtolower($needle), array_map('strtolower', $haystack));
}

function get_layout_headers($class)
{
    return $class->layout_headers;
}

function set_layout_header($class, $contents)
{
    foreach ($contents as $content)
    {
        $class->layout_headers[] = $content;
    }
}

function dump_layout_headers($class) {
    $headers = '';

    foreach ($class->layout_headers as $index => $tag) {
        $headers .= '<'. $tag['tag']. ' ';
        unset($tag['tag']);

        foreach ($tag as $tag_attribute => $tag_value) {
            $headers .= $tag_attribute. '="'. str_replace("\n", "", $tag_value). '" ';
        }
        $headers = trim($headers). ">\n";
    }

    return $headers;
}