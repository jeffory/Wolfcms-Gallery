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

class GalleryController extends PluginController {

    public function __construct() {
        $this->setLayout('backend');
    }

    public function index() {
        $this->documentation();
    }

    function settings() {
        $this->display('gallery/views/settings', Plugin::getAllSettings('gallery'));
    }
}