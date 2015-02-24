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
class GalleryImage extends PluginRecord
{
    const TABLE_NAME = 'gallery_image';
    /**
     * Associated database table 
     *
     * @var string
     **/
    public static $table_name = self::TABLE_NAME;
    
    /**
     * Database schema, dynamic generates the SQL used for the tables and forms.
     * 
     * Type (Required):
     * integer, string, text, file, datetime
     * 
     * Options:
     *   validation - for validating form fields generated from table
     *   allowempty - (true/false) null/not null, also if the field is optional in forms
     *   maxlength - (number) table column size, form maxlength
     *   userinput - (default: true, true/false) if the field allows user input, ie. if it shows in forms
     *   pkey - primary key in table
     *   special - currently reserved for the controller setting a value for the model
     *   autoinc - Auto increment
     *
     * TODO: Run this through a function to add default values, eg. strings: maxlength => 255
     * Then it can be used in forms and validation.
     * 
     * @var array
     **/
    public static $table_structure = array(
        'id' => array(
            'type' => 'integer',
            'autoinc' => true,
            'allowempty' => false,
            'userinput' => false,
            'pkey' => true
            ),
        'item_id' => array(
            'type' => 'integer',
            ),
        'image' => array(
            'type' => 'file',
            'allowempty' => true,
            'caption' => 'Image',
            'allowed_mimes' => array('image/jpeg', 'image/png', 'image/gif'),
            'maxres' => 1280
            ),
        'image_thumb' => array(
            'type' => 'file',
            'allowempty' => true,
            'userinput' => false,
            'special' => true
            ),
        'image_type' => array(
            'type' => 'string',
            'allowempty' => true,
            'userinput' => false,
            'special' => true
            ),
        'order' => array(
            'type' => 'integer',
            'userinput' => false
            ),
        );

    /**
     * Set the images for an item
     *
     * @var integer Item ID
     * @var mixed String/Array Images for the item to be added to
     * @var boolean Remove any Images not specified in previous array
     * 
     * @return void
     **/
    public static function setItemImages($item_id, $images, $remove_old = false)
    {
        $model_class = get_called_class();
        error_reporting(E_ALL);

        // Get the current images associated with the item
        $current_images = GalleryItem::find(array(
            'where' => 'gallery_item.id = '. (int) $item_id,
            'select' => array('gallery_image.image')
            ));

        // Check for new images
        foreach ((!is_array($images) ? array($images) : $images) as $image)
        {
            if (!in_iarray($image['image'], $current_images[0]))
            {
                // Create the image and the link
                GalleryImage::insertRow(array(
                    'item_id' => $item_id,
                    'image' => $image['image'],
                    'image_type' => $image['image_type'],
                    'image_thumb' => $image['image_thumb']
                    ));

                // $cat_id = GalleryCat::lastInsertId();
                    
                // $new_categories[] = $image;
            }
        }

        // // Delete old ones?
        // if ($remove_old == true)
        // {
        //     foreach ($current_categories as $current_category_id => $current_category)
        //     {
        //         foreach ((!is_array($categories) ? array($categories) : $categories) as $category)
        //         {
        //             // Remove categories not specified
        //             if (!in_iarray($current_category, $categories))
        //             {
        //                 //GalleryItemCat::delete(GalleryItemCat::$table_name, '');
        //                 self::deleteWhere('GalleryItemCat', 'item_id = ? AND category_id = ?', array($item_id, $current_category_id));
        //             }
        //         }
        //     }
        // }

        return true;
    }
}