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

		if (defined('CMS_BACKEND'))
		{
			self::_checkPermission();
			$this->setLayout('backend');
		}
		else
		{
			$this->setLayout('Hamlins');	/* TODO: Should be the name of the layout going to be used */
		}
	}

	/**
     * Run on enabling plugin 
     *
     * @return void
     **/
    static public function enable()
    {
        Gallery::deleteTables();
        Gallery::createTables();
    }

	/**
	 * Set function for admin tab
	 *
	 * @return void
	 **/
	public function index()
	{
		$this->settings();
	}

	/**
	 * Admin settings tab
	 *
	 * @return void
	 **/
	public function settings()
	{
		$this->assignToLayout('sidebar', new View('../../plugins/'. GAL_ID. '/views/sidebar'));

		$this->display(
			GAL_ID. "/views/settings",
			Plugin::getAllSettings(GAL_ID)
			);
	}

	/**
	 * Set function for admin tab
	 *
	 * @return void
	 **/
	public function add()
	{
		$data = $_POST;

		if (isset($_POST) && !empty($_POST))
		{
			// Sort out uploading the files
			foreach ($_FILES as $field_name => $details)
			{
				$data[$field_name] = base64_encode( file_get_contents( $details['tmp_name']) );
				$data[$field_name. '_name'] = $details['name'];
			}

			//die(print_r($data));
			Gallery::addItem($data);
		}

		$this->display(
			GAL_URL. "/views/add-item",
			array('item_fields' => Gallery::getTableStructure(Gallery::ITEMS_TABLE))
			);
	}

	/**
	 * Empty and recreate tables
	 *
	 * @return void
	 **/
	public function clearall()
	{
		self::uninstall();
		self::enable();

		redirect(get_url('plugin/gallery'));
	}

	/**
     * Uninstalling plugin, delete associated tables 
     *
     * @return void
     **/
    static public function uninstall()
    {
        Gallery::deleteTables();
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
	 *
	 * @return mixed Rendered content or nothing when $exit is true.
	 **/
	public function display($view, $vars=array(), $exit=true)
	{
		parent::display((defined('CMS_BACKEND') ? '/' : '../../plugins/'). ltrim($view, '/'), $vars, $exit);
	}
}