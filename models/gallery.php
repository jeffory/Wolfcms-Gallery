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
			'id',
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
			'id',
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
			$SQL = '';
			$extra_SQL = '';

			if (isset($table_name) && !empty($table_name))
			{
				$table_name = TABLE_PREFIX. $table_name;	// Add the table prefix, if any, to the table name

				$SQL .= "CREATE TABLE IF NOT EXISTS `$table_name` (\n";
				$table_primary_key = null;

				foreach ($table_details as $column_name => $column_details)
				{
					// If an array has no values the key becomes the index (integer) and the value is then changed to the original key, this switches it back
					if (is_int($column_name))
					{
						$column_name = $column_details;
						$column_details = array();
					}
					
					$SQL .= "  `$column_name`";

					// Check for column rules
					foreach (self::$database_columnrules as $column_match => $column_rules)
					{
						if (preg_match('/'. $column_match. '/i', $column_name))
						{
							foreach ($column_rules as $column_rule => $column_ruleset)
							{
								switch ($column_rule)
								{
									case 'details':
										$column_details = $column_ruleset;
										break;
									
									case 'append':
										$replacement_vars = array(
											'{%table_name%}' => $table_name,
											'{%column_name%}' => $column_name,
											);

										$extra_SQL .= str_replace(array_keys($replacement_vars), array_values($replacement_vars), $column_ruleset);
										break;

									default:
										# code...
										break;
								}
							}
						}
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
				$errored = $result->errorInfo();

				print_r($sql_query);

				// if ($errored)
				// {
				//     throw new Exception('Unable to run SQL query. "'. $sql_query. '"<br>'. 'Error: "'. $errored. ': '. $result->errorInfo(). '"');
				// }
				// else
				// {
				return $result;
				// }
			}
			else
			{
				throw new Exception('Unable to run SQL query. "'. (!empty($sql_query) ? $sql_query : $orig_query). '"<br>'. (@!empty($result->errorInfo) ? ('Error: "'. @$result->errorInfo. '"') : 'No error returned!' ));
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