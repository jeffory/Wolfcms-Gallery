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
class GalleryItemCat extends PluginRecord
{
	/**
	 * Associated database table 
	 *
	 * @var string
	 **/
	public static $table_name = 'gallery_item_cat';
    
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
    	'item_id' => array(
			'type' => 'integer',
			'pkey' => true
			),
		'category_id' => array(
			'type' => 'integer',
			'pkey' => true
			)
		);
}