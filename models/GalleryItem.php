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
class GalleryItem extends PluginRecord
{
    const TABLE_NAME = 'gallery_item';
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
     *   validation  - for validating form fields generated from table
     *   allowempty  - (true/false) null/not null, also if the field is optional in forms
     *   maxlength   - (number) table column size, form maxlength
     *   userinput   - (default: true, true/false) if the field allows user input, ie. if it shows in forms
     *   pkey        - primary key in table
     *   special     - currently reserved for the controller setting a value for the model
     *   autoinc     - Auto increment
     *   storeindb   - if type is file, it will store the filedata in the actual database
     *
     * TODO: Run this through a function to add default values, eg. strings: maxlength => 255
     * Then it can be used in forms and validation.
     * 
     * @var array
     **/
    public static $table_structure = array(
        'id' => array(
            'type' => 'integer',
            'maxlength' => 8,
            'pkey' => true,
            'userinput' => false,
            'autoinc' => true,
            'allowempty' => false,
            ),
        'name' => array(
            'type' => 'string',
            'validation' => '',
            'allowempty' => false,
            'caption' => 'Item name'
            ),
        'code' => array(
            'type' => 'string',
            'validation' => '',
            'allowempty' => false,
            'maxlength' => 8,
            'caption' => 'Product code'
            ),
        'description' => array(
            'type' => 'text',
            'allowempty' => true,
            'caption' => 'Description'
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
        'created' => array(
            'type' => 'datetime',
            'userinput' => false
            ),
        'modified' => array(
            'type' => 'datetime',
            'userinput' => false
            )
        );
    
    /**
     * Database table joins
     *
     * @var string
     **/
    public static $table_joins = array(
        array('leftjoin' => array('gallery_item.id', 'gallery_item_cat.item_id')),
        array('leftjoin' => array('gallery_item_cat.category_id', 'gallery_cat.id')),
        );


    /**
     * As well as deleting the item we need to delete the category relation(s) to it, not the category itself
     * 
     * @var mixed Accepts either a single id, ids in an array or a where statement (array key needs to be where)
     *
     * @return boolean 
     **/
    public static function deleteRows($args)
    {
        if (is_array($args))
        {
            if (!isset($args['where']))
            {
                $ids = implode(",", $args);

                GalleryItemCat::deleteRows(array('where' => '`item_id` IN ('. $ids. ')'));
            }
        }
        elseif (preg_match('/^[0-9]+$/', $args))
        {
            GalleryItemCat::deleteRows(array('where' => '`item_id` = '. $args));
        }

        return parent::deleteRows($args);
    }
}
