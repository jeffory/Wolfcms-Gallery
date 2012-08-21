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
class Gallery extends Record
{
	/**
	 * Gallery items database table name
	 *
	 * @const string
	 **/
	const ITEMS_TABLE = 'gallery_item';

	/**
	 * Gallery category database table name
	 *
	 * @const string
	 **/
	const CATEGORY_TABLE = 'gallery_cat';

	/**
	 * Argument character for SQL queries
	 *
	 * @const string
	 **/
	const ARG_CHAR = '?';

	/**
	 * Create gallery tables
	 *
	 * @return void
	 **/
	static public function createTables()
	{
		// gallery_item
		if (
			!self::execSQL(
			'CREATE TABLE IF NOT EXISTS `'. TABLE_PREFIX. self::ITEMS_TABLE. '` (
			`id` int(16) NOT NULL AUTO_INCREMENT,
			PRIMARY KEY (`id`)
			);')
		)
		{
			sf_error('Unable to create '. TABLE_PREFIX. self::ITEMS_TABLE. ' table.');
		}

		// gallery_cat
		if (
			!self::execSQL(
			'CREATE TABLE IF NOT EXISTS `'.TABLE_PREFIX. self::CATEGORY_TABLE. '` (
			`id` int(16) NOT NULL AUTO_INCREMENT,
			PRIMARY KEY (`id`)
			);')
		)
		{
			sf_error('Unable to create '. TABLE_PREFIX. self::CATEGORY_TABLE. ' table.');
		}
	}

	/**
	 * Delete store detail/option tables
	 *
	 * @return void
	 **/
	static public function deleteTables()
	{
		// Remove database tables associated with store
		self::dropTable(TABLE_PREFIX. self::ITEMS_TABLE);
		self::dropTable(TABLE_PREFIX. self::CATEGORY_TABLE);
	}
} // END class Gallery