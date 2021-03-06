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

require_once(rtrim(dirname($file), '/'). '/index.php');
GalleryController::enable();
mkdir(GAL_IMAGES_ROOT);
exit();