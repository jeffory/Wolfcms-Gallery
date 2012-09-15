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

error_reporting(E_ALL);

@require_once('GalleryController.php');
@require_once('models/PluginRecord.php');
@require_once('models/GalleryItem.php');
@require_once('models/GalleryItemCat.php');
@require_once('models/GalleryCat.php');
GalleryController::uninstall();
exit();