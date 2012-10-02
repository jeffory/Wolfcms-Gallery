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
    public static function createTable()
    {
        $model_class = get_called_class();

        $SQL = '';
        $extra_SQL = '';
        $table_primary_keys = array();

        if ( isset($model_class::$table_name) && !empty($model_class::$table_name) )
        {
            $table_name = TABLE_PREFIX. $model_class::$table_name;  // Add the table prefix, if any, to the table name
            
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
                            if (isset($column_details['storeindb']) && $column_details['storeindb'] == true)
                            {
                                $column_type = 'longblob';
                            }
                            else
                            {
                                $column_type = 'text';
                            }
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
                    if ($column_type == 'int' || $column_type == 'varchar') // should be any column that takes a column size, eg. varchar(255)
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
                    $extra_SQL .= "\nCREATE TRIGGER `{$table_name}_modified` BEFORE UPDATE ON `{$table_name}` FOR EACH ROW SET NEW.{$column_name} = NOW();";
                }

                $SQL = trim($SQL). ",";
            } // END foreach column in table

            if (isset($table_primary_keys) && !empty($table_primary_keys))
            {
                $SQL .= " PRIMARY KEY (";
                foreach ($table_primary_keys as $table_primary_key)
                {
                    $SQL .= "`". $table_primary_key. "`,";
                }
                $SQL = rtrim($SQL, ', ');
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
    public static function deleteTable()
    {
        $model_class = get_called_class();

        // TODO: Delete all the files associated with items?
        if (isset($model_class::$table_name) && !empty($model_class::$table_name))
        {
            $table_name = TABLE_PREFIX. $model_class::$table_name;  // Add the table prefix, if any, to the table name

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
    public static function getTableStructure($column = null, $row = null)
    {
        $model_class = get_called_class();
        $table_structure = $model_class::$table_structure;

        if ($column != null && $row == null)
        {
            if (isset($table_structure[$column]))
            {
                return $table_structure[$column];
            }
            else
            {
                return false;
            }
        }
        elseif ($column != null && $row != null)
        {
            if (isset($table_structure[$column][$row]))
            {
                return $table_structure[$column][$row];
            }
            else
            {
                return false;
            }
        }
        elseif (isset($table_structure))
        {
            return $table_structure;
        }
        else
        {
            return false;
        }
    }

    /**
     * Insert a row
     * 
     * @var array
     *
     * @return void
     **/
    public static function insertRow($data)
    {
        $model_class = get_called_class();

        // Generate the SQL to insert the row
        $SQL = 'INSERT INTO `'. TABLE_PREFIX. $model_class::$table_name. '` (';

        foreach ($model_class::getTableStructure() as $column_name => $column_details)
        {
            if (!isset($column_details['userinput']) || $column_details['userinput'] === true || @$column_details['special'] === true)
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
     * @var mixed Accepts either a single id, ids in an array or a where statement (array key needs to be where)
     *
     * @return void
     **/
    public static function deleteRows($args)
    {
        $model_class = get_called_class();
        $where = '';

        if (is_array($args) && !empty($args['where']))
        {
            $where = $args['where'];
        }
        else
        {
            $where = '`id` ';

            if (is_array($args))
            {
                $where .= 'IN (';

                foreach ($args as $id)
                {
                    $where .= '?,';
                }

                $sql_args = $args;
                $where = rtrim($where, ', ');
                $where .= ')';
            }
            else
            {
                $where .= '= ?;';
                $sql_args = array($args);
            }
        }

        // Clean up old associated files
        foreach ($model_class::getTableStructure() as $column_name => $column_details)
        {
            if ((strcasecmp($column_details['type'], 'file') == 0) && (!isset($column_details['storeindb']) || $column_details['storeindb'] == false))
            {
                $del_SQL = 'SELECT `'. $column_name. '` FROM `'. TABLE_PREFIX. $model_class::$table_name. '` WHERE '. $where;
                $ret = isset($sql_args) ? self::query($del_SQL, $sql_args) : self::query($del_SQL);

                if (isset($ret[0]) && file_exists($ret[0]->$column_name))
                {
                    unlink($ret[0]->$column_name);
                }
            }
        }

        $SQL = 'DELETE FROM `'. TABLE_PREFIX. $model_class::$table_name. '` WHERE '. $where;

        $ret = isset($sql_args) ? self::query($SQL, $sql_args) : self::query($SQL);
        
        return ($ret !== false);
    }

    /**
     * Find items in the database by arguments
     * 
     * @var array
     *
     * @return void
     **/
    public static function find($args = null)
    {
        $model_class = get_called_class();
        $table_name = TABLE_PREFIX. $model_class::$table_name;

        $select = '';
        $mm_sep = "|";       // Seperator for MYSQL returning multiple rows, originally a comma.
        $mm_cols = array();

        $group_by_string = '';

        if (isset($args['select']))
        {
            // String -> Array
            if (!is_array($args['select'])) $args['select'] = array($args['select']);

            foreach ($args['select'] as $col)
            {
                // Is there NOT a table name for the columns
                if (!preg_match('#([a-z0-9-_]+)\.([a-z0-9-_]+)#i', $col, $matches))
                {
                    $col = $table_name. '.'. $col;
                }
                else
                {
                    // If not the current table
                    if (strcasecmp($table_name, $matches[1]) != 0)
                    {
                        $mm_cols[] = $matches[2];
                        $mm_sep = (isset($mm_sep)) ? $mm_sep : ',';
                        $col = 'GROUP_CONCAT('. $col. ' SEPARATOR "'. $mm_sep. '") AS '. $matches[2];
                        $group_by_string = 'GROUP BY '. $table_name. '.id';
                    }
                }

                $select .= $col. ', ';
            }
        }
        else
        {
            $select = '*';
        }

        $select = rtrim($select, ', ');

        // Collect attributes...
        $where = isset($args['where']) ? trim($args['where']) : '';

        if (isset($args['order']))
        {
            $order_by = trim($args['order']);
        }
        else
        {
            // Table has joins?
            if (isset($model_class::$table_joins))
            {
                $order_by = $model_class::$table_name. '.id ASC';
            }
            else
            {
                $order_by = 'id ASC';
            }
        }
        //$order_by = isset($args['order']) ? trim($args['order']) : 'id ASC';

        $offset = isset($args['offset']) ? (int)$args['offset'] : 0;
        $limit = isset($args['limit']) ? (int)$args['limit'] : 0;

        // Prepare query parts
        $order_by_string = empty($order_by) ? '' : "ORDER BY $order_by";
        $limit_string = $limit > 0 ? "LIMIT $limit" : '';
        $offset_string = $offset > 0 ? "OFFSET $offset" : '';

        // Tables joins...
        $join_string = trim(self::generateJoin());

        // Prepare SQL
        // @todo FIXME - do this in a better way (sqlite doesn't like empty WHEREs)
        if ($where != '')
        {
            $sql = "SELECT $select FROM $table_name $join_string " .
                "WHERE $where $group_by_string $order_by_string $limit_string $offset_string";
        }
        else
        {
            $sql = "SELECT $select FROM $table_name $join_string " .
                "$group_by_string $order_by_string $limit_string $offset_string";
        }

        //echo $sql;
        Record::logQuery($sql);
        $stmt = self::$__CONN__->prepare($sql);
        $stmt->execute();

        // Explode (into arrays) the Many2Many rows
        $explode_cols = function ($object, $columns, $seperator)
            {
                if (!empty($object))
                {
                    foreach ($object as $col => $val)
                    {
                        if (in_array($col, $columns))
                        {
                            $object->$col = explode($seperator, $val);
                        }
                    }
                    return $object;
                }
            };

        // Run!
        if ($limit == 1)
        {
            $objects = $stmt->fetchObject($model_class);
            $objects = $explode_cols($objects, $mm_cols, $mm_sep);
        }
        else
        {
            $objects = array();
            while ($object = $stmt->fetchObject($model_class))
                $objects[] = $object;

            // Divide up columns with multiple rows
            foreach ($objects as $index => $object)
            {
                $objects[$index] = $explode_cols($object, $mm_cols, $mm_sep);
            }
        }

        return $objects;
    }

    /**
     * Generate the join(s) on the database table
     *
     * @return void
     **/
    private static function generateJoin()
    {
        $model_class = get_called_class();
        if (isset($model_class::$table_joins))
        {
            $joins = '';

            foreach ($model_class::$table_joins as $table_joins)
            {
                foreach ($table_joins as $table_join => $fields)
                {
                    if (strcasecmp('leftjoin', $table_join) == 0)
                    {
                        list($field1, $field2) = $fields;

                        $table1 = substr($field1, 0, strpos($field1, '.'));
                        $table2 = substr($field2, 0, strpos($field2, '.'));

                        $joins .= 'LEFT JOIN '. $table2. ' ON '. $field1. ' = '. $field2. ' ';

                    }
                }
            }
            return trim($joins);
        }
        else
        {
            return false;
        }
    }

    /**
     * Stores the file or path in the database
     *
     * @return array Database data to be added
     **/
    public static function prepareFile($field_name, $filepath, $details, $storepath)
    {
        $model_class = get_called_class();

        // Pass extra information through to the model if needed
        $data[$field_name. '_name'] = $details['name'];
        $data[$field_name. '_type'] = $details['type'];
        $data[$field_name. '_size'] = $details['size'];

        // Storing image in database or file?
        if ($model_class::getTableStructure($field_name, 'storeindb') != true)
        {
            // Move file, store filepath
            $new_file = $storepath. DS. strtolower($details['name']);
            $pre_file = pathinfo($new_file, PATHINFO_DIRNAME). DS. pathinfo($new_file, PATHINFO_FILENAME);
            $suf_file = pathinfo($new_file, PATHINFO_EXTENSION);
            $i = 1;

            // Need a non-existant filename
            while (file_exists($new_file))
            {
                $new_file =  $pre_file. ''. $i. '.'. $suf_file. "\n";
                
                if ($i > 1) break;
                $i++;
            }

            // TODO: Delete previous associated file
            rename($details['tmp_name'], $new_file);
            chmod($new_file, 0755);
            $data[$field_name] = $new_file;
        }
        else
        {
            // Storing file in db often makes requests very slow
            $data[$field_name] = file_get_contents($details['tmp_name']);
            $new_file = $details['tmp_name'];
        }

        // Thumbnail?
        if (in_array($details['type'], array('image/jpeg', 'image/png', 'image/gif')))
        {
            // Divide the file path into parts
            $pre_file = pathinfo($new_file, PATHINFO_DIRNAME). DS. pathinfo($new_file, PATHINFO_FILENAME);
            $suf_file = pathinfo($new_file, PATHINFO_EXTENSION);
            $thumb_file = $pre_file. '_thumb.'. $suf_file;

            require_once(GAL_ROOT. DS. 'Image.php');

            if ($image = new Bedeabza\Image($new_file))
            {
                $image->resize(250, 250, $image::RESIZE_TYPE_RATIO);
                $image->save($thumb_file, 60);
                chmod($thumb_file, 0755);
            }

            // Store the thumbnail as a filepath or data?
            if ($model_class::getTableStructure($field_name, 'storeindb') != true)
            {
                $data[$field_name. '_thumb'] = $thumb_file;
            }
            else
            {
                $data[$field_name. '_thumb'] = file_get_contents($thumb_file);
            }
        }

        return $data;
    }

    /**
     * Updates an existing record in the database. Modified to check columns first to see if they exist.
     *
     * @return array Database data to be added
     **/
    public static function update($class_name, $data, $where, $values = array())
    {
        $model_class = get_called_class();

        // Remove any fields from update query that aren't in the table structure
        foreach ($data as $column => $value)
        {
            if (!in_array($column, array_keys($model_class::getTableStructure())))
            {
                unset($data[$column]);
            }
        }

        return parent::update($class_name, $data, $where, $values = array());
    }

    /**
     * Get the column count
     *
     * @return void
     **/
    public static function countRows()
    {
        $model_class = get_called_class();
        return self::countFrom($model_class);
    }
}
