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
	 * Gallery category database table name
	 *
	 * @const string
	 **/
	const ITEMS_CATEGORY_TABLE = 'gallery_item_cat';

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
		'int' => array('int', 'tinyint', 'smallint', 'mediumint', 'bigint', 'float', 'double', 'decimal'), 											// Numbers
		'time' => array('date', 'datetime', 'timestamp', 'time', 'year'),																			// Time
		'str' => array('char', 'varchar', 'blob', 'text', 'tinyblob', 'tinytext', 'mediumblob', 'mediumtext','longblob', 'longtext', 'enum')		// Strings
		);

	/**
	 * All the common supported column definitions by the database
	 *
	 * @var array
	 **/
	public static $database_columndefs = array(
		'AUTO_INCREMENT', 'PRIMARY KEY', 'NOT NULL', 'NULL', 'DEFAULT', 'ON UPDATE'
		);

	/**
	 * Predefined SQL column rules and their conditions
	 *
	 * @var array
	 **/
	public static $database_columnrules = array(
		'id' => array(
				'details' => array(
					'int(16)',
					'NOT NULL',
					'AUTO_INCREMENT',
					'PRIMARY KEY'
				)
			),
		'created' => array(
			'append' => "\nCREATE TRIGGER `{%table_name%}_creation` BEFORE INSERT ON `{%table_name%}` FOR EACH ROW SET NEW.{%column_name%} = NOW();"
			),
		'modified' => array(
			'append' => "\nCREATE TRIGGER `{%table_name%}_modification` BEFORE UPDATE ON `{%table_name%}` FOR EACH ROW SET NEW.{%column_name%} = NOW();"
			),
		);

	/**
	 * Database schema, setting it like this allows the class to easily access the structure
	 *
	 * @var array
	 **/
	public static $database_schema = array(
		self::ITEMS_TABLE => array(
			'id' => array(
				'type' => 'num_id'
				),
			'name' => array(
				'type' => 'string',
				'validation' => '',
				'allowempty' => false
				),
			'code' => array(
				'type' => 'string',
				'validation' => '',
				'allowempty' => false,
				'maxlength' => 8
				),
			'description' => array(
				'type' => 'text',
				'allowempty' => true
				),
			'image_url' => array(
				'type' => 'string',
				'allowempty' => true
				),
			'created' => array(
				'type' => 'datetime',
				),
			'modified' => array(
				'type' => 'datetime',
				)
			),
		self::CATEGORY_TABLE => array(
			'id',
			'category_name' => array(
				'type' => 'string',
				'allowempty' => false
				)
			),
		self::ITEMS_CATEGORY_TABLE => array(
			'item_id' => array(
				'type' => 'num_id'
				),
			'category_id' => array(
				'type' => 'num_id'
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
			$SQL = '';
			$extra_SQL = '';

			if (isset($table_name) && !empty($table_name))
			{
				$table_name = TABLE_PREFIX. $table_name;	// Add the table prefix, if any, to the table name
				
				$SQL .= "CREATE TABLE IF NOT EXISTS `$table_name` (\n";
				$table_primary_key = null;

				// eg. $table_details = [id] => Array([type] => num_id), [name] => Array([type] => string, [validation] => null, [empty] => null...)
				foreach ($table_details as $column_name => $column_details)
				{
					// If an array has no values the key becomes the index (integer) and the value is then changed to the original key, this switches it back
					if (is_int($column_name))
					{
						$column_name = $column_details;
						$column_details = array();
					}
					
					$SQL .= "  `$column_name`";

					// die(print_r($column_details));

					foreach ($column_details as $column_option => $column_value)
					{
						if ($column_option == 'type')
						{
							if ($column_value == 'string')
							{
								$SQL .= ' varchar';
							}
							else
							{

							}
						}
						elseif ($column_option == 'maxlength') {
							
						}
						elseif ($column_option == 'allowempty')
						{
							
						}
					}

				// 	// Check for column rules
				// 	foreach (self::$database_columnrules as $column_match => $column_rules)
				// 	{
				// 		if (preg_match('/'. $column_match. '/i', $column_name))
				// 		{
				// 			foreach ($column_rules as $column_rule => $column_ruleset)
				// 			{
				// 				switch ($column_rule)
				// 				{
				// 					case 'details':
				// 						$column_details = $column_ruleset;
				// 						break;
									
				// 					case 'append':
				// 						$replacement_vars = array(
				// 							'{%table_name%}' => $table_name,
				// 							'{%column_name%}' => $column_name,
				// 							);

				// 						$extra_SQL .= str_replace(array_keys($replacement_vars), array_values($replacement_vars), $column_ruleset);
				// 						break;

				// 					default:
				// 						# code...
				// 						break;
				// 				}
				// 			}
				// 		}
				// 	}

				// 	foreach ($column_details as $column_detail)
				// 	{
				// 		// Check for a valid column datatype
				// 		if (preg_match("/^(". implode('|', self::getSupportedDatatypes()). ")/i", $column_detail))
				// 		{
				// 			$SQL .= ' '. $column_detail;
				// 		}
				// 		// Check for a valid column definition
				// 		elseif (preg_match("/^(". implode('|', self::$database_columndefs). ")/i", $column_detail))
				// 		{
				// 			if (preg_match('/PRIMARY KEY/i', $column_detail))
				// 			{
				// 				$table_primary_key = $column_name;
				// 			}
				// 			else
				// 			{
				// 				$SQL .= ' '. $column_detail;
				// 			}
				// 		}
				// 	} // END foreach column detail in column
				// 	$SQL .= ",\n";
				} // END foreach column in table

				if (isset($table_primary_key))
				{
					$SQL .= "  PRIMARY KEY (`$table_primary_key`)\n";
				}
				$SQL = trim($SQL). "\n);\n". $extra_SQL;
				self::execSQL($SQL);
				echo "<pre>\n". $SQL. "</pre>\n";
			}
			else
			{
				throw new Exception('No table name specified on database schema.');
			}
		} // END foreach table in schema
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
			self::execSQL($SQL);
		}
	}

	/**
	 * Returns a basic schema of the table
	 *
	 * @return void
	 **/
	static public function getTableStructure($table_name)
	{
		
		return self::$database_schema[$table_name];
	}

	/**
	 * Get all the registered supported types of the database minus the categories
	 *
	 * @return void
	 **/
	static public function getSupportedDatatypes()
	{
		$supported_datatypes = array();

		foreach (self::$database_datatypes as $category => $datatypes)
		{
			foreach ($datatypes as $datatype)
			{
				$supported_datatypes[] = $datatype;
			}
		}

		return $supported_datatypes;
	}

	/**
	 * Add an item to the database
	 *
	 * @return void
	 **/
	static public function addItem()
	{
		
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
				$errored = $pdo->errorInfo();

				//print_r($sql_query);

				// if ($errored)
				// {
				//     throw new Exception('Unable to run SQL query. "'. $sql_query. '"<br>'. 'Error: "'. $errored. ': '. $result->errorInfo(). '"');
				// }
				// else
				// {
				return $result;
				// }
			}
			elseif ($error = $pdo->errorInfo())
			{
				throw new Exception('Unable to run SQL query:<br>"'. (!empty($sql_query) ? $sql_query : $orig_query). '"<br><br>'. (@!empty($error[2]) ? ('Error:<br>'. @$error[2]) : 'No error message!' ));
				return false;
			}
			else
			{
				throw new Exception('Unknown error while executing:<br>"'. (!empty($sql_query) ? $sql_query : $orig_query));
			}

		}
		else
		{
			throw new Exception('Unable to connect to Database/PDO Instance.');
			return false;
		}
	}
} // END class Gallery