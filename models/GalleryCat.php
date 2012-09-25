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
            'allowempty' => false,
            'caption' => 'Category Name',
            'userinput' => true
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
    public static function setItemCategories($item_id, $categories, $remove_old = false)
    {
        $model_class = get_called_class();
        error_reporting(E_ALL);

        $current_categories = GalleryItem::find(array(
            'where' => 'gallery_item.id = '. (int) $item_id,
            'select' => array('gallery_cat.id', 'gallery_cat.category_name')
            ));

        // Combine the two seperate arrays (must have something to do with them being from different tables)
        $current_categories = array_combine($current_categories[0]->id,  $current_categories[0]->category_name);


        // START FUNCTIONS: Case insensitive version of in_array
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
        // END FUNCTIONS


        // Check for new categories
        foreach ((!is_array($categories) ? array($categories) : $categories) as $category)
        {
            if (!in_iarray($category, $current_categories))
            {
                // Check if category exists in table, if so grab the id, if it doesn't, create it. In either instance return the category_id
                if ($found_category = parent::findOneFrom($model_class, 'category_name = "'. $category. '"'))
                {
                    // Create just the link, category already exists
                    GalleryItemCat::insertRow(array(
                        'item_id' => $item_id,
                        'category_id' => $found_category->id,
                        ));
                }
                else
                {
                    // Create the category and the link
                    GalleryCat::insertRow(array(
                        'category_name' => $category,
                        ));

                    $cat_id = GalleryCat::lastInsertId();

                    GalleryItemCat::insertRow(array(
                        'item_id' => $item_id,
                        'category_id' => $cat_id,
                        ));
                    
                }
                $new_categories[] = $category;
            }
        }

        // Delete old ones?
        if ($remove_old == true)
        {
            foreach ($current_categories as $current_category_id => $current_category)
            {
                foreach ((!is_array($categories) ? array($categories) : $categories) as $category)
                {
                    // Remove categories not specified
                    if (!in_iarray($current_category, $categories))
                    {
                        self::deleteRows($current_category_id);
                    }
                }
            }
        }

        return true;
    }
}