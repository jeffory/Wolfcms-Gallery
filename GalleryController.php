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
			// TODO: Should be the name of the layout going to be used
			$this->setLayout('Hamlins');
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
		// Drop any related tables
		self::uninstall();

		GalleryItem::createTable();
		GalleryItemCat::createTable();
		GalleryCat::createTable();
	}

	/**
	 * Run on disabling plugin 
	 *
	 * @return void
	 **/
	static public function disable()
	{
		self::_checkPermission();
	}

	/**
	 * Set function for admin tab
	 *
	 * @return void
	 **/
	public function index()
	{
		self::_checkPermission();
		
		$this->assignToLayout('sidebar', new View(GAL_ROOT. '/views/sidebar'));

		// Delete multiple items?
		if (isset($_POST['remove']))
		{
			GalleryItem::deleteRows($_POST['remove']);
		}

		$items = GalleryItem::find(array(
			'select' => array('gallery_item.id', 'gallery_item.name', 'gallery_item.code', 'gallery_item.description', 'gallery_cat.category_name')
			));

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
	 * Add an item
	 *
	 * @return void
	 **/
	public function add()
	{
		$store_in_db = false;

		self::_checkPermission();
		$data = $_POST;

		if (isset($_POST) && !empty($_POST))
		{
			// Sort out uploading the files
			foreach ($_FILES as $field_name => $details)
			{
				if ($details['error'] == 0)
				{
					GalleryItem::query('SELECT COUNT(*) FROM gallery_item');
					//$ret = GalleryItem::query('SELECT LAST_INSERT_ID() as last_id FROM gallery_item');

					// if (!file_exists(  lastInsertId ))
					$data[$field_name] = file_get_contents( $details['tmp_name']);

					// Pass extra information through to the model
					$data[$field_name. '_name'] = $details['name'];
					$data[$field_name. '_type'] = $details['type'];
					$data[$field_name. '_size'] = $details['size'];
				}
			}

			//die(print_r($data));
			if (GalleryItem::insertRow($data))
			{
				Flash::set('success', __('Added successfully!'));
			}
			else
			{
				Flash::set('error', __('There appears to be a problem adding the new item!'));
			}

			redirect(get_url('plugin/'. GAL_URL));
		}

		$item_fields = GalleryItem::getTableStructure(GalleryItem::$table_name);

		// Add categories field
		$item_fields['category_name'] = array(
			'type' => 'list',
			'allowempty' => 1,
			'caption' => 'Categories'
			);

		$this->display(
			basename(GAL_ROOT). "/views/add-item",
			array(
				'item_fields' => $item_fields
				)
			);
	}

	/**
	 * Edit an item
	 * 
	 * @var integer item id
	 *
	 * @return void
	 **/
	public function edit($id)
	{
		$data = GalleryItem::find(array(
			'where' => 'gallery_item.id = '. (int) $id,
			'select' => array('gallery_item.id', 'gallery_item.name', 'gallery_item.code', 'gallery_item.description', 'gallery_item.image', 'gallery_cat.category_name')
			));

		$item_fields = GalleryItem::getTableStructure(GalleryItem::$table_name);

		// Add categories field
		$item_fields['category_name'] = array(
			'type' => 'list',
			'allowempty' => 1,
			'caption' => 'Categories'
			);

		$this->display(
			basename(GAL_ROOT). "/views/add-item",
			array(
				'item_fields' => $item_fields,
				'data' => (array)$data[0]           // Object -> Array, gotta love PHP sometimes
				)
			);
	}

	/**
	 * Delete an item
	 * 
	 * @var integer item id
	 * 
	 * @return void
	 **/
	public function delete($id)
	{
		self::_checkPermission();
		if (GalleryItem::deleteRows($id))
		{
			Flash::set('success', __('Item# '. $id. ' was deleted.'));
		}
		else
		{
			Flash::set('error', __('Item# '. $id. ' could not be deleted!'));
		}
		redirect(get_url('plugin/'. GAL_ID));
	}


	/**
	 * Return a file
	 * 
	 * @var string column of the file to output
	 * @var integer item id of the associated file
	 *
	 * @return void
	 **/
	public function file($col, $id)
	{
		$item = GalleryItem::find(array('where' => 'id = '. (int) $id));

		if (isset($item[0]->$col_type) && !empty($item[0]->$col_type))
		{
			header('Content-Type: '. $item[0]->image_type);
		}

		echo $item[0]->$col;
	}

	/**
	 * Frontend index view
	 *
	 * @return void
	 **/
	public function front_index()
	{
		$items = GalleryItem::find(array(
			'select' => array('gallery_item.id', 'gallery_item.name', 'gallery_item.code', 'gallery_item.description', 'gallery_cat.category_name')
			));

		$this->display(
			basename(GAL_ROOT). "/views/front-index",
			array(
				'item_fields' => GalleryItem::getTableStructure(GalleryItem::$table_name),
				'items' => $items
				)
			);
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

		redirect(get_url('plugin/'. GAL_URL));
	}

	/**
	 * Add sample data
	 *
	 * @return void
	 **/
	static public function addsamples()
	{
		for ($i = 0; $i < 10; $i++)
		{
			$rand = mt_rand(1,100);
			$rand2 = mt_rand(1,1897897);
			GalleryItem::insertRow(array(
				'name' => 'test item '. $rand,
				'code' => 'K'. $rand2,
				'description' => 'This is the description for item #'. $rand. '.'
				));

			$rand3 = mt_rand(1,3);

			$item_id = GalleryItem::lastInsertId();

			for($x = 0; $x < $rand3; $x++)
			{
				GalleryCat::insertRow(array(
					'category_name' => 'test item '. $rand. ' category '. $x,
					));

				$cat_id = GalleryCat::lastInsertId();

				GalleryItemCat::insertRow(array(
					'item_id' => $item_id,
					'category_id' => $cat_id,
					));
			}

		}

		redirect(get_url('plugin/'. GAL_URL));
	}

	/**
	 * Uninstalling plugin, delete associated tables 
	 *
	 * @return void
	 **/
	static public function uninstall()
	{
		self::_checkPermission();
		GalleryItem::deleteTable();
		GalleryItemCat::deleteTable();
		GalleryCat::deleteTable();
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
