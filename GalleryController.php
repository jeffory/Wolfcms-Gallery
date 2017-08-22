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
    var $layout_headers;
    var $slug = GAL_SLUG;

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
            $this->setLayout(GAL_LAYOUT);
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
        GalleryImage::createTable();
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
            'select' => array('gallery_item.id', 'gallery_item.name', 'gallery_item.description', 'gallery_cat.category_name'),
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
     * Frontend item view
     *
     * @return void
     **/
    public function front_item($item_id, $category_id)
    {
        $item = GalleryItem::find(array(
            'select' => array('id', 'name', 'description', 'gallery_cat.category_name', 'gallery_image.id ORDER BY gallery_image.order AS image_id'),
            'where' => 'gallery_item.id = '. $item_id,
            'limit' => 1
            ));

        set_layout_header($this, array(
                array(
                'tag' => 'meta',
                'property' => 'og:title',
                'content' => $item->name. ' | Turbo Finance'
                ),
                array(
                'tag' => 'meta',
                'property' => 'og:image',
                'content' => URL_PUBLIC. GAL_URL. '/file/image/'. $item->image_id[0]
                ),
            array(
                'tag' => 'meta',
                'property' => 'og:description',
                'content' => $item->description
                ),
            )
        );

        $cat_name = GalleryCat::find(array(
            'select' => array('category_name AS name'),
            'where' => 'id = '. $category_id,
            'limit' => 1
            ));

        $this->display(
            basename(GAL_ROOT). "/views/front-item",
            array(
                'item_fields' => GalleryItem::getTableStructure(),
                'item' => $item,
                'cat_id' => $category_id,
                'cat_name' => $cat_name->category_name,
                'cat_slug' => Node::toSlug($cat_name->category_name)
                )
            );
    }

    /**
     * Frontend index view
     *
     * @return void
     **/
    public function front_items_index($category_id, $category_slug)
    {
        $items = GalleryItem::find(array(
            'select' => array('id', 'name', 'description', 'gallery_cat.category_name', 'gallery_image.id  ORDER BY gallery_image.order AS image_id'),
            'where' => 'gallery_cat.id = '. $category_id
            ));

        $this->display(
            basename(GAL_ROOT). "/views/front-items-index",
            array(
                'cat_id' => $category_id,
                'cat_slug' => $category_slug,
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

        if (isset($data) && !empty($data))
        {
            $categories = $data['category_name'];
            unset($data['category_name']);

            // Sort out uploading the files
            $images = array();

            foreach ($_FILES as $field_name => $cur_files)
            {
                for ($i = 0; $i < count($cur_files['name']); $i++)
                {
                    $upload = array(
                                'name' => $cur_files['name'][$i],
                                'type' => $cur_files['type'][$i],
                                'tmp_name' => $cur_files['tmp_name'][$i],
                                'error' => $cur_files['error'][$i],
                                'size' => $cur_files['size'][$i]
                                );

                    if ($upload['error'] != UPLOAD_ERR_NO_FILE)
                    {
                        // Security check, see: http://php.net/manual/en/function.is-uploaded-file.php
                        if ($upload['error'] == UPLOAD_ERR_OK && is_uploaded_file($upload['tmp_name']))
                        {
                            $images[] = GalleryItem::prepareFile($field_name, $upload['tmp_name'], $upload, GAL_IMAGES_ROOT);
                            //$data = array_merge($data, GalleryItem::prepareFile($field_name, $upload['tmp_name'], $upload, GAL_IMAGES_ROOT));
                        }
                        else
                        {
                            Flash::set('error', __('Bad file upload.'));
                        }
                    }
                }
            }

            // $data = array_merge($data, $files);

            if (GalleryItem::insertRow($data))
            {
                $id = GalleryItem::lastInsertId();

                if (GalleryCat::setItemCategories($id, $categories, true) && GalleryImage::setItemImages($id, $images, true))
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

        $categories = array();

        // For autocomplete
        foreach (GalleryCat::find(array('select' => 'category_name')) as $category)
        {
            $categories[] = htmlspecialchars($category->category_name, ENT_QUOTES);
        }

        // Add image fields
        $item_fields['image'] = array(
            'type' => 'filelist',
            'allowempty' => 1,
            'caption' => 'Images'
            );

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

        if (isset($data) && !empty($data))
        {
            $categories = $data['category_name'];
            unset($data['category_name']);

            // Sort out uploading the files
            $images = array();

            foreach ($_FILES as $field_name => $cur_files)
            {
                for ($i = 0; $i < count($cur_files['name']); $i++)
                {
                    $upload = array(
                                'name' => $cur_files['name'][$i],
                                'type' => $cur_files['type'][$i],
                                'tmp_name' => $cur_files['tmp_name'][$i],
                                'error' => $cur_files['error'][$i],
                                'size' => $cur_files['size'][$i]
                                );

                    if ($upload['error'] != UPLOAD_ERR_NO_FILE)
                    {
                        // Security check, see: http://php.net/manual/en/function.is-uploaded-file.php
                        if ($upload['error'] == UPLOAD_ERR_OK && is_uploaded_file($upload['tmp_name']))
                        {
                            $images[] = GalleryItem::prepareFile($field_name, $upload['tmp_name'], $upload, GAL_IMAGES_ROOT);
                            //$data = array_merge($data, GalleryItem::prepareFile($field_name, $upload['tmp_name'], $upload, GAL_IMAGES_ROOT));
                        }
                        else
                        {
                            Flash::set('error', __('Bad file upload.'));
                        }
                    }
                }
            }


            // Check for removed images
            $cur_images = GalleryItem::find(array(
                'where' => 'gallery_item.id = '. (int) $id,
                'select' => array('gallery_image.id AS image')
            ));

            if (!empty($cur_images)){
                foreach ($cur_images[0]->image as $image_id)
                {
                    if (!in_array($image_id, $data['image_keep']) && in_array($image_id, $data['image_org']))
                    {
                        GalleryImage::deleteRows($image_id);
                    }
                }
            }

            // Update image order
            foreach ($data['image_keep'] as $index => $image_id) {
                // GalleryItem::update('GalleryImage', array('order' => $index), 'id = '. $image_id);
                GalleryItem::query('UPDATE `gallery_image` SET `order` = :order WHERE `id` = :id', array('order' => $index, 'id' => $image_id));
            }

            // Update item
            if (GalleryItem::update('GalleryItem', $data, 'id = '. $id))
            {
                if (GalleryCat::setItemCategories($id, $categories, true) && GalleryImage::setItemImages($id, $images, true))
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
            'select' => array('id', 'name', 'description', 'gallery_cat.category_name', 'gallery_image.id ORDER BY gallery_image.order ASC AS image')
            ));

        $item_fields = GalleryItem::getTableStructure();

        // Add image fields
        $item_fields['image'] = array(
            'type' => 'filelist',
            'allowempty' => 1,
            'caption' => 'Images'
            );

        // Add categories field
        $item_fields['category_name'] = array(
            'type' => 'list',
            'allowempty' => 1,
            'caption' => 'Categories'
            );

        $categories = '';

        foreach ( GalleryCat::find(array('select' => 'category_name')) as $category )
        {
            $categories[] = htmlspecialchars($category->category_name, ENT_QUOTES);
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
        if ($item = GalleryImage::find(array('where' => 'gallery_image.id = '. (int) $id)))
        {
            $headers = apache_request_headers();

            // A thumbnail's going to have the same content type as it's original
            $col_type = preg_replace('/\_thumb$/is', '', $col) .'_type';
            @$modified = filemtime($item[0]->$col);
            @$filesize = filesize($item[0]->$col);
            @$etag = md5($modified);

            if (@isset($item[0]->$col_type) && !empty($item[0]->$col_type))
                $content_type = $item[0]->image_type;

            // Check if image is in browser cache...
            if (isset($headers['If-None-Match']) && $headers['If-None-Match'] == $etag && gmstrftime("%a, %d %b %Y %T %Z", $modified) == $headers['If-Modified-Since'])
            {
                header('HTTP/1.1 304 Not Modified');
                header("Cache-Control: public");
                if (isset($content_type)) header("Content-Type: ". $content_type);
                header('ETag: "asset-'. $etag. '"');
                exit();
            }
            else
            {
                if (isset($filesize) && !empty($filesize)) header("Content-Length: ". $filesize);
                header("Cache-Control: public");
                if (isset($content_type)) header("Content-Type: ". $content_type);
                if (isset($modified) && !empty($modified)) header("Last-Modified: ". gmstrftime("%a, %d %b %Y %T %Z", $modified));
                if (isset($etag) && !empty($etag)) header('ETag: '. $etag);

                // Check if filename or data
                if (GalleryItem::getTableStructure($col, 'storeindb') != true)
                {
                    if (file_exists($item[0]->$col))
                    {
                        readfile($item[0]->$col);
                    }
                    elseif (file_exists(GAL_IMAGES_ROOT. $item[0]->$col))
                    {
                        readfile(GAL_IMAGES_ROOT. $item[0]->$col);
                    }
                    else
                    {
                        header('HTTP/1.0 404 Not Found');
                    }
                }
                else
                {
                    echo $item[0]->$col;
                }
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
                Flash::set('success', __('Edited successfully!'));
                redirect(get_url('plugin/'. GAL_URL. '/categories'));
            }
            else
            {
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
        GalleryImage::deleteTable();
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
