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
        if (!class_exists('Gallery')) {
            require_once(PLUGINS_ROOT. DS. GAL_ID. DS. 'models'. DS. 'gallery.php');
        }
        
        if (defined('CMS_BACKEND'))
        {
            self::_checkPermission();
            $this->setLayout('backend');
        }
        else
        {
            $this->setLayout('Wolf');	/* TODO: Should be the name of the layout going to be used */
        }
    }

    /**
     * Set function for admin tab
     *
     * @return void\
     **/
    public function index()
    {
        $this->settings();
    }

	/**
     * blah
     *
     * @return void
     **/
    public function test()
    {
        echo 'Deleting tables...';
        Gallery::deleteTables();

        echo 'Creating tables...';
        Gallery::createTables();
    }

	/**
     * Admin settings tab
     *
     * @return void
     **/
    public function settings()
    {
        $this->display(
        	'gallery/views/settings',
        	Plugin::getAllSettings('gallery')
        	);
    }

    /**
     * WolfCMS display hack/fix
     * 
     * I'm not sure how this came about, I wrote this a very long time ago, but the plugin fails without it.
     * 
     * @param boolean part
     * @param boolean inherit
     *
     * @return mixed returns content or false if content isn't available
     **/
    public function content($part=false, $inherit=false)
    {
        return (!$part) ? $this->content : false;
    }
    
    /**
     * WolfCMS frontend view filepath fix
     *
     * Differentiates between the frontend and backend to give a correct path to views
     *
     * @param string View id
     * @param string Variables for in the View.
     * @param boolean Exit PHP process when done?
     *
     * @return mixed Rendered content or nothing when $exit is true.
     **/
    public function display($view, $vars=array(), $exit=true)
    {
        parent::display((defined('CMS_BACKEND') ? '/' : '../../plugins/'). $view, $vars, $exit);
    }
}