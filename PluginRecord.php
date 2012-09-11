<?php
/**
 * Plugin Record extends Record further with table creation/deletion
 * and table join functionality.
 *
 * @package Plugins
 * @subpackage Gallery
 *
 * @author Keith McGahey
 */
class PluginRecord extends Record
{
	/**
	 * Create all tables needed for the plugin
	 *
	 * @return void
	 **/
	static public function createTable()
	{
		$model_class = get_called_class();

		$SQL = '';
		$table_primary_keys = array();

		if ( isset($model_class::$table_name) && !empty($model_class::$table_name) )
		{
			$table_name = TABLE_PREFIX. $model_class::$table_name;	// Add the table prefix, if any, to the table name
			
			$SQL .= "CREATE TABLE IF NOT EXISTS `$table_name` (\n";

			// eg. $table_details = [id] => Array([type] => num_id), [name] => Array([type] => string, [validation] => null, [empty] => null...)
			foreach ($model_class::$table_structure as $column_name => $column_details)
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
				$column_auto_increment = false;

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
							$table_primary_keys[] = $column_name;
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
					elseif ($column_option == 'autoinc')
					{
						$column_auto_increment = true;
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
				if ($column_auto_increment)
				{
					$SQL = trim($SQL). ' AUTO_INCREMENT ';
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

			if (isset($table_primary_keys) && !empty($table_primary_keys))
			{
				$SQL .= " PRIMARY KEY (";
				foreach ($table_primary_keys as $table_primary_key)
				{
					$SQL .= "`". $table_primary_key. "`";
				}
				$SQL .= ")\n";
			}

			// remove trailing comma(s)
			$SQL = rtrim($SQL, ', ');

			$SQL = trim($SQL). "\n);\n\n";

			if (isset($extra_SQL))
			{
				$SQL .= trim($extra_SQL). "\n\n";
			}

			$model_class::query($SQL);
			//echo "<pre>\n". $SQL. "</pre>\n";
		}
		else
		{
			throw new Exception('No table name specified on database schema.');
		}
	}

	/**
	 * Deletes all the table associated with the plugin
	 *
	 * @return void
	 **/
	static public function deleteTable()
	{
		$model_class = get_called_class();
		if (isset($model_class::$table_name) && !empty($model_class::$table_name))
		{
			$table_name = TABLE_PREFIX. $model_class::$table_name;	// Add the table prefix, if any, to the table name

			$SQL = "DROP TABLE IF EXISTS `$table_name`;\n\n";
			self::query($SQL);
		}
		else
		{
			throw new Exception('Unable to delete table.');
		}
	}

	/**
	 * Returns a basic schema of the table
	 *
	 * @return void
	 **/
	static public function getTableStructure($table_name)
	{
		$model_class = get_called_class();
		return $model_class::$table_structure;
	}

	/**
	 * Insert a row
	 * 
	 * @var array
	 *
	 * @return void
	 **/
	static public function insertRow($data)
	{
		$model_class = get_called_class();

		// Generate the SQL to insert the row
		$SQL = 'INSERT INTO `'. TABLE_PREFIX. $model_class::$table_name. '` (';

		foreach (self::getTableStructure() as $column_name => $column_details)
		{
			if (@$column_details['userinput'] === true || !isset($column_details['userinput']) || @$column_details['special'] === true)
			{
				if (isset($data[$column_name]))
				{
					$SQL .= '`'. $column_name. '`,';
					$args[] = $data[$column_name];
				}
			}
		}

		$SQL = rtrim($SQL, ', '). ') VALUES (';

		for ($i = 0; $i < count($args); $i++)
		{
			$SQL .= '?, ';
		}

		$SQL = rtrim($SQL, ', '). ');';

		$ret = $model_class::query($SQL, $args);

		return ($ret !== false);
	}

	/**
	 * Delete row(s) from the database via id(s)
	 * 
	 * @var integer
	 *
	 * @return void
	 **/
	static public function deleteRows($ids)
	{
		$model_class = get_called_class();

		$SQL = 'DELETE FROM `'. TABLE_PREFIX. $model_class::$table_name. '` WHERE `id` ';

		if (is_array($ids))
		{
			$SQL .= 'IN (';

			foreach ($ids as $id)
			{
				$SQL .= '?,';
			}

			$SQL .= rtrim($SQL, ', ');
			$SQL .= ')';
		}
		else
		{
			$SQL .= '= ?;';
			$ret = self::query($SQL, array($ids));
		}

		return ($ret !== false);
	}

	/**
	 * List items from the database
	 * 
	 * @var array
	 *
	 * @return void
	 **/
	public static function find($args = null)
	{
		$model_class = get_called_class();
		$table_name = TABLE_PREFIX. $model_class::$table_name;

		// Collect attributes...
		$where = isset($args['where']) ? trim($args['where']) : '';
		$order_by = isset($args['order']) ? trim($args['order']) : 'id DESC';

		$offset = isset($args['offset']) ? (int)$args['offset'] : 0;
		$limit = isset($args['limit']) ? (int)$args['limit'] : 0;

		// Prepare query parts
		$order_by_string = empty($order_by) ? '' : "ORDER BY $order_by";
		$limit_string = $limit > 0 ? "LIMIT $limit" : '';
		$offset_string = $offset > 0 ? "OFFSET $offset" : '';

		// Prepare SQL
		// @todo FIXME - do this in a better way (sqlite doesn't like empty WHEREs)
		if ($where != '')
		{
			$sql = "SELECT * FROM $table_name " .
				"WHERE $where $order_by_string $limit_string $offset_string";
		}
		else
		{
			$sql = "SELECT * FROM $table_name " .
				"$order_by_string $limit_string $offset_string";
		}

		$stmt = self::$__CONN__->prepare($sql);
		$stmt->execute();

		// Run!
		if ($limit == 1) {
			return $stmt->fetchObject($model_class);
		} else {
			$objects = array();
			while ($object = $stmt->fetchObject($model_class))
				$objects[] = $object;

			return $objects;
		}
	}
}
