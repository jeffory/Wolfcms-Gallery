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
					'NOT NULL',
					'AUTO_INCREMENT',
					'PRIMARY KEY'
				),
			'name' => array(
					'varchar(255)',
					'NOT NULL'
				),
			'code' => array(
					'varchar(255)'
				),
			'description' => array(
					'text',
					'NOT NULL'
				),
			'image_url' => array(
					'varchar(255)',
					'NOT NULL'
				),
			'created' => array(
					'DATETIME',
				),
			'modified' => array(
					'DATETIME',
				)
			),
		self::CATEGORY_TABLE => array(
			'id' => array(
					'int(16)',
					'NOT NULL',
					'AUTO_INCREMENT',
					'PRIMARY KEY'
				),
			'category' => array(
					'varchar(255)',
					'NOT NULL'
				)
			)
		);

	/**
	 * Create all tables needed for the plugin
	 *
	 * @return void
	 **/
	static public function createTables()
	{
		$SQL = '';
		$table_primary_key = '';

		foreach (self::$database_schema as $table_name => $table_details)
		{
			$extra_SQL = '';

			if (isset($table_name) && !empty($table_name))
			{
				$table_name = TABLE_PREFIX. $table_name;	// Add the table prefix, if any, to the table name

				$SQL .= "CREATE TABLE IF NOT EXISTS `$table_name` (\n";
				$table_primary_key = null;

				foreach ($table_details as $column_name => $column_details)
				{
					$SQL .= "  `$column_name`";

					// Create any triggers or additional functions based off column names
					if ($column_name == 'created')
					{
						$extra_SQL .= "\nCREATE TRIGGER `{$table_name}_creation` BEFORE INSERT ON `{$table_name}` FOR EACH ROW SET NEW.{$column_name} = NOW();";
					}
					elseif ($column_name == 'modified')
					{
						$extra_SQL .= "\nCREATE TRIGGER `{$table_name}_modified` BEFORE UPDATE ON `{$table_name}` FOR EACH ROW SET NEW.{$column_name} = NOW();";
					}


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
					$SQL .= "  PRIMARY KEY (`$table_primary_key`)\n";
				}
				$SQL = trim($SQL). "\n);\n". $extra_SQL. "\n\n";
			}
			else
			{
				throw new Exception('No table name specified on database schema.');
			}
		} // END foreach table in schema

		echo '<pre>'. $SQL. '</pre>';
		self::execSQL($SQL);
	}

	/**
	 * Deletes all the table associated with the plugin
	 *
	 * @return void
	 **/
	static public function deleteTables()
	{
		$SQL = '';

		foreach (self::$database_schema as $table_name => $table_details)
		{
			if (isset($table_name) && !empty($table_name))
			{
				$table_name = TABLE_PREFIX. $table_name;	// Add the table prefix, if any, to the table name

				$SQL .= "DROP TABLE IF EXISTS `$table_name`;\n\n";
			}
		}
		echo '<pre>'. $SQL. '</pre>';
		self::execSQL($SQL);
	}


	/**
	 * Execute a SQL query, return the result object
	 *
	 * @param string sql query
	 * @param string sql arguments (optional)
	 *
	 * @return boolean returns true if no errors
	 **/
	static private function execSQL($sql_query, $arguments=null, $quotes=true)
	{
		$orig_query = $sql_query;

		if ($pdo = Record::getConnection())
		{
			if (!empty($arguments)) {
				for ($i = 0; $i < count($arguments); $i++) {
					if ($quotes === true)
					{
						$arguments[$i] = "'". addslashes(htmlspecialchars($arguments[$i], ENT_QUOTES)). "'";
					}
					else
					{
						$arguments[$i] = addslashes(htmlspecialchars($arguments[$i], ENT_QUOTES));
					}
				}

				$sql_query = vsprintf(str_replace(self::ARG_CHAR, '%s', $sql_query), $arguments);
			}

			$GLOBALS['sqlqueries'][] = $sql_query;

			if ($result = $pdo->query($sql_query))
			{
				$errored = $result->errorCode();

				// if ($errored)
				// {
				//     throw new Exception('Unable to run SQL query. "'. $sql_query. '"<br>'. 'Error: "'. $errored. ': '. $result->errorInfo. '"');
				// }
				// else
				// {
				return $result;
				// }
			}
			else
			{
				throw new Exception('Unable to run SQL query. "'. (!empty($sql_query) ? $sql_query : $orig_query). '"<br>'. (!empty($result->errorInfo) ? ('Error: "'. $result->errorInfo. '"') : 'No error returned!' ));
				return false;
			}

		}
		else
		{
			throw new Exception('Unable to connect to Database/PDO Instance.');
			return false;
		}
	}
} // END class Gallery