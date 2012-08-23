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
	 * All the common supported data types by the database
	 *
	 * @var array
	 **/
	public static $database_datatypes = array(
		'int', 'tinyint', 'smallint', 'mediumint', 'bigint', 'float', 'double', 'decimal', 										// Numbers
		'date', 'datetime', 'timestamp', 'time', 'year',																		// Time
		'char', 'varchar', 'blob', 'text', 'tinyblob', 'tinytext', 'mediumblob', 'mediumtext','longblob', 'longtext', 'enum'	// Strings
		);

	/**
	 * All the common supported column definitions by the database
	 *
	 * @var array
	 **/
	public static $database_columndefs = array(
		'AUTO_INCREMENT', 'PRIMARY KEY', 'NOT NULL', 'DEFAULT', 'ON UPDATE'
		);

	/**
	 * Database schema, setting it like this allows the class to easily access the structure
	 *
	 * @var array
	 **/
	public static $database_schema = array(
		self::ITEMS_TABLE => array(
			'id' => array(
					'int(16)',
					'AUTO_INCREMENT',
					'PRIMARY KEY'
				),
			'name' => array(
					'varchar(256)',
					'NOT NULL'
				),
			'code' => array(
					'varchar(256)'
				),
			'description' => array(
					'text',
					'NOT NULL'
				),
			'image_url' => array(
					'varchar(256)',
					'NOT NULL'
				),
			'date_uploaded' => array(
					'TIMESTAMP',
					'DEFAULT CURRENT_TIMESTAMP'
				),
			'date_modified' => array(
					'TIMESTAMP',
					'DEFAULT CURRENT_TIMESTAMP',
					'ON UPDATE CURRENT_TIMESTAMP'
				)
			),
		self::CATEGORY_TABLE => array(
			'id' => array(
					'int(16)',
					'AUTO_INCREMENT',
					'PRIMARY KEY'
				),
			'category' => array(
					'varchar(256)',
					'NOT NULL'
				)
			)
		);

	/**
	 * Create gallery tables
	 *
	 * @return void
	 **/
	static public function createTables()
	{
		$SQL = '';
		$table_primary_key = '';

		foreach (self::$database_schema as $table_name => $table_details)
		{
			if (isset($table_name) && !empty($table_name))
			{
				// Add the table prefix, if any, to the table name
				$table_name = TABLE_PREFIX. $table_name;

				$SQL .= "CREATE TABLE IF NOT EXISTS `$table_name` (\n";
				$table_primary_key = null;

				foreach ($table_details as $column_name => $column_details)
				{
					$SQL .= "  `$column_name`";

					foreach ($column_details as $column_detail)
					{
						// Check for a valid column datatype
						if (preg_match("/^(". implode('|', self::$database_datatypes). ")/i", $column_detail))
						{
							$SQL .= ' '. $column_detail;
						}
						// Check for a valid column definition
						elseif (preg_match("/^(". implode('|', self::$database_columndefs). ")/i", $column_detail))
						{
							if (preg_match('/PRIMARY KEY/i', $column_detail))
							{
								$table_primary_key = $column_name;
							}
							else
							{
								$SQL .= ' '. $column_detail;
							}
						}
					} // END foreach column detail in column
					$SQL .= ",\n";
				} // END foreach column in table

				if (isset($table_primary_key))
				{
					$SQL .= "PRIMARY KEY (`$table_primary_key`)\n";
				}
				$SQL = trim($SQL). ";\n\n";
			}
			else
			{
				throw new Exception('No table name specified on database schema.');
			}
		} // END foreach table in schema

		echo '<pre>'. $SQL. '</pre>';
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