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
	 *
	 * TODO: Run this through a function to add default values, eg. strings: maxlength => 255
	 * Then it can be used in forms and validation.
	 * 
	 * @var array
	 **/
	public static $database_schema = array(
		self::ITEMS_TABLE => array(
			'id' => array(
				'type' => 'integer',
				'maxlength' => 8,
				'pkey' => true,
				'userinput' => false
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
				'caption' => 'Image'
				),
			'image_type' => array(
				'type' => 'string',
				'allowempty' => true,
				'userinput' => false,
				'special' => true
				),
			'thumbnail' => array(
				'type' => 'file',
				'allowempty' => true,
				'userinput' => false
				),
			'created' => array(
				'type' => 'datetime',
				'userinput' => false
				),
			'modified' => array(
				'type' => 'datetime',
				'userinput' => false
				)
			),
		self::CATEGORY_TABLE => array(
			'id' => array(
				'type' => 'integer',
				'pkey' => true,
				),
			'category_name' => array(
				'type' => 'string',
				'allowempty' => false
				)
			),

		self::ITEMS_CATEGORY_TABLE => array(
			'item_id' => array(
				'type' => 'integer',
				'pkey' => true
				),
			'category_id' => array(
				'type' => 'integer',
				'pkey' => true
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
					
					$SQL .= " `$column_name`";

					/* =============== Column defaults =============== */
					$column_type = null;
					$column_size = null;
					$column_allow_empty = true;

					// die(print_r($column_details));

					foreach ($column_details as $column_option => $column_value)
					{
						if ($column_option == 'type')
						{
							/* =============== Find: Column types =============== */
							if ($column_value == 'string')
							{
								$column_type = 'varchar';
							}
							elseif ($column_value == 'integer')
							{
								$column_type = 'int';
							}
							elseif ($column_value == 'datetime' || $column_value == 'text')
							{
								$column_type = $column_value;
							}
							elseif ($column_value == 'file')
							{
								$column_type = 'longblob';
							}
						}
						/* =============== Find: Extra column attributes =============== */
						elseif ($column_option == 'pkey')
						{
							if ($column_value === true)
							{
								$table_primary_key = $column_name;
							}
						}
						elseif ($column_option == 'maxlength')
						{
							$column_size = $column_value;
						}
						elseif ($column_option == 'allowempty')
						{
							$column_allow_empty = (bool)$column_value;
						}
					}

					if (isset($column_type))
					{
						$SQL .= ' '. $column_type;

						/* =============== Default Column sizes =============== */
						if ($column_type == 'int' || $column_type == 'varchar')	// should be any column that takes a column size, eg. varchar(255)
						{
							if (!isset($column_size))
							{
								if ($column_type == 'varchar')
								{
									$column_size = 255;
								}
								elseif ($column_type == 'int')
								{
									$column_size = 10;
								}
							}

							$SQL .= '('. $column_size . ') ';
						}
					}

					/* ===============  Column attributes =============== */
					if ($column_allow_empty)
					{
						$SQL = trim($SQL). ' NULL ';
					}
					else
					{
						$SQL = trim($SQL). ' NOT NULL ';
					}

					/* =============== Special column types =============== */
					if ($column_name == 'created')
					{
						$extra_SQL .= "\nCREATE TRIGGER `{$table_name}_creation` BEFORE INSERT ON `{$table_name}` FOR EACH ROW SET NEW.{$column_name} = NOW();";
					}
					elseif ($column_name == 'modified')
					{
						$extra_SQL .= "\nCREATE TRIGGER `{$table_name}_creation` BEFORE UPDATE ON `{$table_name}` FOR EACH ROW SET NEW.{$column_name} = NOW();";
					}

					$SQL = trim($SQL). ",";
				} // END foreach column in table

				if (isset($table_primary_key))
				{
					$SQL .= " PRIMARY KEY (`$table_primary_key`)\n";
				}

				// remove trailing comma(s)
				$SQL = rtrim($SQL, ', ');

				$SQL = trim($SQL). "\n);\n\n";

				if (isset($extra_SQL))
				{
					$SQL .= trim($extra_SQL). "\n\n";
				}

				self::execSQL($SQL);
				//echo "<pre>\n". $SQL. "</pre>\n";
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
		foreach (self::$database_schema as $table_name => $table_details)
		{
			if (isset($table_name) && !empty($table_name))
			{
				$table_name = TABLE_PREFIX. $table_name;	// Add the table prefix, if any, to the table name

				$SQL = "DROP TABLE IF EXISTS `$table_name`;\n\n";
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
	 * Add an item to the database
	 * 
	 * @var array
	 *
	 * @return void
	 **/
	static public function addItem($data)
	{
		echo '<pre>';
		print_r($data);
		
		foreach (self::$database_schema[self::ITEMS_TABLE] as $column_name => $column_details)
		{
			if (@$column_details['userinput'] === true || !isset($column_details['userinput']) || @$column_details['special'] === true)
			{
				if (isset($data[$column_name]))
				{
					echo $column_name. ' ';
				}
			}
		}

		echo '</pre>';
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