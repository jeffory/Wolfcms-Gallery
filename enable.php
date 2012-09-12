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

/*
 * Any code below gets executed each time the plugin is enabled.
 */

Plugin::addController(GAL_C_CLASS, __(GAL_TITLE), 'administrator', true);
GalleryController::enable();
exit();