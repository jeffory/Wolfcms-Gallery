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
    public function index($page=1, $limit=15)
    {
        self::_checkPermission();
        
        $this->assignToLayout('sidebar', new View(GAL_ROOT. '/views/items-index-sidebar'));

        // Delete multiple items?
        if (isset($_POST['remove']))
        {
            GalleryItem::deleteRows($_POST['remove']);
        }

        $items = GalleryItem::find(array(
            'select' => array('gallery_item.id', 'gallery_item.name', 'gallery_item.code', 'gallery_item.description', 'gallery_cat.category_name'),
            'limit' => $limit,
            'offset' => ($page - 1) * $limit
            ));

        $total = GalleryItem::countRows();

        $this->display(
            GAL_ID. "/views/items-index",
            array(
                'items' => $items,
                'page' => $page,
                'limit' => $limit,
                'total' => $total
                )
            );
    }

    /**
     * Frontend index view
     *
     * @return void
     **/
    public function front_items_index($category_id)
    {
        $items = GalleryItem::find(array(
            'select' => array('id', 'name', 'code', 'description', 'gallery_cat.category_name'),
            'where' => 'gallery_cat.id = '. $category_id
            ));

        $this->display(
            basename(GAL_ROOT). "/views/front-items-index",
            array(
                'item_fields' => GalleryItem::getTableStructure(),
                'items' => $items
                )
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
            basename(GAL_ID). "/views/settings",
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
        self::_checkPermission();
        $data = $_POST;

        if (isset($_POST) && !empty($data))
        {
            // Sort out uploading the files
            foreach ($_FILES as $field_name => $details)
            {
                // Security check, see: http://php.net/manual/en/function.is-uploaded-file.php
                if ($details['error'] == UPLOAD_ERR_OK && is_uploaded_file($details['tmp_name']))
                {
                    // Pass extra information through to the model if needed
                    $data[$field_name. '_name'] = $details['name'];
                    $data[$field_name. '_type'] = $details['type'];
                    $data[$field_name. '_size'] = $details['size'];

                    // Are we handling items?
                    if (in_array($details['type'], array('image/jpeg', 'image/png', 'image/gif')))
                    {
                        // Include PHP Image class
                        //require_once('image.php');
                    }

                    // Storing image in database or file?
                    if (GalleryItem::getTableStructure($field_name, 'storeindb') != true)
                    {
                        // Move file, store filepath

                        // Need a non-existant filename
                        $new_file = GAL_IMAGES_ROOT. DS. strtolower($details['name']);
                        $pre_file = pathinfo($new_file, PATHINFO_DIRNAME). DS. pathinfo($new_file, PATHINFO_FILENAME);
                        $suf_file = pathinfo($new_file, PATHINFO_EXTENSION);
                        $i = 1;

                        while (file_exists($new_file))
                        {
                            $new_file =  $pre_file. '_('. $i. ').'. $suf_file. "\n";
                            
                            if ($i > 1) break;
                            $i++;
                        }

                        rename($details['tmp_name'], $new_file);
                        chmod($new_file, 0755);
                        $data[$field_name] = $new_file;
                    }
                    else
                    {
                        // Storing file in db often makes requests very slow
                        $data[$field_name] = file_get_contents($details['tmp_name']);
                        $new_file = $details['tmp_name'];
                    }

                    // Thumbnail?
                    if (in_array($details['type'], array('image/jpeg', 'image/png', 'image/gif')))
                    {
                        // Divide the file path into parts
                        $pre_file = pathinfo($new_file, PATHINFO_DIRNAME). DS. pathinfo($new_file, PATHINFO_FILENAME);
                        $suf_file = pathinfo($new_file, PATHINFO_EXTENSION);
                        $thumb_file = $pre_file. '_thumb.'. $suf_file;

                        require_once(GAL_ROOT. DS. 'Image.php');

                        if ($image = new Bedeabza\Image($new_file))
                        {
                            $image->resize(250, 250, $image->RESIZE_TYPE_RATIO);
                            $image->save($thumb_file, 60);
                        }

                        // Store the thumbnail as a filepath or data?
                        if (GalleryItem::getTableStructure($field_name, 'storeindb') != true)
                        {
                            $data[$field_name. '_thumb'] = $thumb_file;
                        }
                        else
                        {
                            $data[$field_name. '_thumb'] = file_get_contents($thumb_file);
                        }
                    }
                }
                else
                {
                    Flash::set('error', __('Bad file upload.'));
                }
            }

            if (GalleryItem::insertRow($data))
            {
                $item_id = GalleryItem::lastInsertId();

                if (GalleryCat::setItemCategories($item_id, $data['category_name'], true))
                {
                    Flash::set('success', __('Added successfully!'));
                    redirect(get_url('plugin/'. GAL_URL));
                }
                else
                {
                    Flash::setNow('success', __('Added item successfully, but category could not be added!'));
                }

            }
            else
            {
                Flash::setNow('error', __('There appears to be a problem adding the new item!'));
            }
        }

        $this->assignToLayout('sidebar', new View(GAL_ROOT. '/views/items-add-sidebar'));

        $item_fields = GalleryItem::getTableStructure();

        $categories = '';

        foreach ( GalleryCat::find(array('select' => 'category_name')) as $category )
        {
            $categories[] = $category->category_name;
        }

        // Add categories field
        $item_fields['category_name'] = array(
            'type' => 'list',
            'allowempty' => 1,
            'caption' => 'Categories'
            );

        $this->display(
            basename(GAL_ROOT). "/views/items-add",
            array(
                'item_fields' => $item_fields,
                'categories' => $categories
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
        self::_checkPermission();


        $data = $_POST;

        if (isset($_POST) && !empty($data))
        {
            $categories = $data['category_name'];
            unset($data['category_name']);

            if (GalleryItem::update('GalleryItem', $data, 'id = '. $id))
            {
                if (GalleryCat::setItemCategories($id, $categories, true))
                {
                    Flash::set('success', __('Edited item successfully!'));
                    redirect(get_url('plugin/'. GAL_URL));
                }
                else
                {
                    Flash::set('success', __('Edited item successfully, but category could not be edited!'));
                }
            }
            else
            {
                Flash::set('error', __('There appears to be a problem editing the item!'));
            }
            
        }

        $this->assignToLayout('sidebar', new View(GAL_ROOT. '/views/items-add-sidebar'));

        $data = GalleryItem::find(array(
            'where' => 'gallery_item.id = '. (int) $id,
            'select' => array('id', 'name', 'code', 'description', 'image', 'gallery_cat.category_name')
            ));

        $item_fields = GalleryItem::getTableStructure();

        // Add categories field
        $item_fields['category_name'] = array(
            'type' => 'list',
            'allowempty' => 1,
            'caption' => 'Categories'
            );

        $categories = '';

        foreach ( GalleryCat::find(array('select' => 'category_name')) as $category )
        {
            $categories[] = $category->category_name;
        }

        $this->display(
            basename(GAL_ROOT). "/views/items-add",
            array(
                'item_fields' => $item_fields,
                'data' => (array)$data[0],           // Object -> Array, gotta love PHP sometimes
                'categories' => $categories
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
        if ($item = GalleryItem::find(array('where' => 'gallery_item.id = '. (int) $id)))
        {
            // A thumbnail's going to have the same content type as it's original
            $col_type = preg_replace('/\_thumb$/is', '', $col) .'_type';

            if (@isset($item[0]->$col_type) && !empty($item[0]->$col_type))
            {
                header('Content-Type: '. $item[0]->image_type);
            }

            // Check if filename or data
            if (GalleryItem::getTableStructure($col, 'storeindb') != true)
            {
                readfile($item[0]->$col);
            }
            else
            {
                echo $item[0]->$col;
            }
        }
        else
        {
            header('HTTP/1.0 404 Not Found');
        }
    }


    /**
     * Frontend index view
     *
     * @return void
     **/
    public function category_index($page=1, $limit=15)
    {
        self::_checkPermission();

        // Delete multiple categories?
        if (isset($_POST['remove']))
        {
            GalleryCat::deleteRows($_POST['remove']);
        }

        $this->assignToLayout('sidebar', new View(GAL_ROOT. '/views/categories-sidebar'));

        $categories = GalleryCat::find(array(
            'limit' => $limit,
            'offset' => ($page - 1) * $limit
            ));

        $total = GalleryCat::countRows();

        foreach ($categories as $category)
        {
            $category->items_count = GalleryItemCat::countFrom('GalleryItemCat', 'category_id = '. $category->id);
        }

        $this->display(
            basename(GAL_ROOT). "/views/categories-index",
            array(
                'category_fields' => GalleryCat::getTableStructure(),
                'categories' => $categories,
                'page' => $page,
                'limit' => $limit,
                'total' => $total
                )
            );
    }

    /**
     * Frontend category index view
     *
     * @return void
     **/
    public function front_category_index()
    {
        $categories = GalleryCat::find();

        $this->display(
            basename(GAL_ROOT). "/views/front-categories-index",
            array(
                'category_fields' => GalleryCat::getTableStructure(),
                'categories' => $categories
                )
            );
    }

    /**
     * Add an category
     * 
     * @var integer category id
     * 
     * @return void
     **/
    public function category_add()
    {
        self::_checkPermission();

        $data = $_POST;

        if (isset($_POST) && !empty($data))
        {
            if (GalleryCat::insertRow($data))
            {
                Flash::set('success', __('Added successfully!'));
                redirect(get_url('plugin/'. GAL_URL. '/categories'));
            }
            else
            {
                Flash::setNow('error', __('There appears to be a problem adding the new item!'));
            }
        }

        $cat_fields = GalleryCat::getTableStructure();

        $this->display(
            basename(GAL_ROOT). "/views/categories-add",
            array(
                'item_fields' => $cat_fields
                )
            );
    }

    /**
     * Edit an category
     * 
     * @var integer category id
     * 
     * @return void
     **/
    public function category_edit($id)
    {
        self::_checkPermission();

        $data = $_POST;

        if (isset($_POST) && !empty($data))
        {
            if (GalleryCat::update('GalleryCat', $data, 'id = '. $id))
            {
                echo 'sup';
                Flash::set('success', __('Edited successfully!'));
                redirect(get_url('plugin/'. GAL_URL. '/categories'));
            }
            else
            {
                echo 'sup';
                Flash::setNow('error', __('There appears to be a problem editing the item!'));
            }
        }

        $data = GalleryCat::find(array(
            'where' => 'gallery_cat.id = '. (int) $id
            ));

        $cat_fields = GalleryCat::getTableStructure();

        $this->display(
            basename(GAL_ROOT). "/views/categories-add",
            array(
                'item_fields' => $cat_fields,
                'data' => (array)$data[0]
                )
            );
    }

    /**
     * Delete an category
     * 
     * @var integer category id
     * 
     * @return void
     **/
    public function category_delete($id)
    {
        self::_checkPermission();

        if (GalleryCat::deleteRows($id))
        {
            Flash::set('success', __('Category# '. $id. ' was deleted.'));
        }
        else
        {
            Flash::set('error', __('Category# '. $id. ' could not be deleted!'));
        }

        redirect(get_url('plugin/'. GAL_URL. '/categories'));
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
        self::_checkPermission();

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
