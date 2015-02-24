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
}