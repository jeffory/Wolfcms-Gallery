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
class GalleryCat extends PluginRecord
{
    const TABLE_NAME = 'gallery_cat';
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
        'category_name' => array(
            'type' => 'string',
            'allowempty' => false
            )
        );

    /**
     * As well as deleting the category we need to clean up old table relations to it
     * 
     * @var mixed Accepts either a single id, ids in an array or a where statement (array key needs to be where)
     *
     * @return void
     **/
    public static function deleteRows($args)
    {
        if (is_array($args))
        {
            if (!isset($args['where']))
            {
                $ids = implode(",", $args);

                GalleryItemCat::deleteRows(array('where' => '`category_id` IN ('. $ids. ')'));
            }
        }
        elseif (preg_match('/^[0-9]+$/', $args))
        {
            GalleryItemCat::deleteRows(array('where' => '`category_id` = '. $args));
        }

        return parent::deleteRows($args);
    }

    /**
     * Set the categories for an item
     *
     * @var integer Item ID
     * @var mixed String/Array Categories for the item to be added to
     * @var boolean Remove any categories not specified in previous array
     * 
     * @return void
     **/
    public static function setItemCategories($id, $categories, $remove_old = false)
    {
        $in_categories = GalleryItem::find(array(
            'where' => 'gallery_item.id = '. (int) $id,
            'select' => array('gallery_cat.id', 'gallery_cat.category_name')
            ));

        $in_categories = array_combine($in_categories[0]->id,  $in_categories[0]->category_name);


        // FUNCTION: Case insensitive version of in_array
        function in_iarray ($needle, $haystack) {
            foreach ($haystack as $haystack_item)
            {
                foreach ((!is_array($needle) ? array($needle) : $needle) as $needle_item)
                {
                    if (strcasecmp($needle_item, $haystack_item) == 0)
                    {
                        return true;
                    }
                }
            }
            return false;
        }

        print_r($in_categories);

        foreach ((!is_array($categories) ? array($categories) : $categories) as $category)
        {
            if (in_iarray($category, $in_categories))
            {
                echo $category;
            }
            else
            {
                if ($remove_old == true)
                {
                    self::deleteRows();
                }
            }
        }
    }
}