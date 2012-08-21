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
class GalleryController extends PluginController
{
	/**
     * Checks if a user is logged in (used in backend functions), if not redirects them to the login screen
     *
     * @return void
     **/
    private static function _checkPermission()
    {
        AuthUser::load();

        if (!AuthUser::isLoggedIn())
            redirect(get_url('login'));
    }

    /**
     *  Constructor: includes models, determines layout
     *
     * @return void
     **/
    public function __construct()
    {
        $this->title = 'Gallery';

        // self::enable();

        if (defined('CMS_BACKEND'))
        {
            self::_checkPermission();
            $this->setLayout('backend');
        }
        else
        {
            $this->setLayout('');	/* TODO: Should be the name of the layout going to be used */
        }
    }

	/**
     * Admin settings tab
     *
     * @return void
     **/
    function settings()
    {
        $this->display(
        	'gallery/views/settings',
        	Plugin::getAllSettings('gallery')
        	);
    }
}