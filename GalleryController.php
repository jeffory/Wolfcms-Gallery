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
		$this->title = GAL_TITLE;

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
    	self::_checkPermission();
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
		self::_checkPermission();
		$this->assignToLayout('sidebar', new View('../../plugins/'. GAL_URL. '/views/sidebar'));
		$items = Gallery::listItems();

		$this->display(
			GAL_ID. "/views/list-items",
			array('items' => $items)
			);
	}

	/**
	 * Admin settings tab
	 *
	 * @return void
	 **/
	public function settings()
	{
		self::_checkPermission();
		$this->display(
			GAL_ID. "/views/settings",
			Plugin::getAllSettings(GAL_ID)
			);
	}

	/**
	 * Add an item to the Gallery
	 *
	 * @return void
	 **/
	public function add()
	{
		self::_checkPermission();
		$data = $_POST;

		if (isset($_POST) && !empty($_POST))
		{
			// Sort out uploading the files
			foreach ($_FILES as $field_name => $details)
			{
				if ($details['error'] == 0)
				{
					$data[$field_name] = base64_encode( file_get_contents( $details['tmp_name']) );

					// Pass extra information through to the model
					$data[$field_name. '_name'] = $details['name'];
					$data[$field_name. '_type'] = $details['type'];
					$data[$field_name. '_size'] = $details['size'];
				}
			}

			//die(print_r($data));
			if (Gallery::addItem($data))
			{
				Flash::set('success', __('Added successfully!'));
			}
			else
			{
				Flash::set('error', __('There appears to be a problem adding the new item!'));
			}

			redirect(get_url('plugin/'. GAL_URL));
		}

		$this->display(
			GAL_URL. "/views/add-item",
			array('item_fields' => Gallery::getTableStructure(Gallery::ITEMS_TABLE))
			);
	}

	/**
	 * Delete an item
	 *
	 * @return void
	 **/
	public function delete($id)
	{
		self::_checkPermission();
		if (Gallery::deleteItem($id))
		{
			Flash::set('success', __('Item# '. $id. ' was deleted.'));
		}
		else
		{
			Flash::set('error', __('Item# '. $id. ' could not be deleted!'));
		}
		redirect(get_url('plugin/'. GAL_ID));
	}


	/* Return a file from the database
	 *
	 * @return void
	 **/
	public function file($col, $id)
	{
		echo $col. '/'. $id;
		print_r( Gallery::find(array('where' => 'id = '. (int) $id)) );
	}


	/**
	 * Empty and recreate tables
	 *
	 * @return void
	 **/
	static public function clearall()
	{
		self::_checkPermission();
		self::uninstall();
		self::enable();

		redirect(get_url('plugin/'. GAL_ID));
	}

	/**
     * Uninstalling plugin, delete associated tables 
     *
     * @return void
     **/
    static public function uninstall()
    {
    	self::_checkPermission();
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