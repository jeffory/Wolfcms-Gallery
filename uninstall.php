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
GalleryController::uninstall();
if (count(glob(GAL_IMAGES_ROOT.  "/*")) === 0) @rmdir(GAL_IMAGES_ROOT);
exit();