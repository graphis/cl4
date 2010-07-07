<?php
/**
*   This file has the class Claero which is used in other classes requiring some basic variables used within the class
*
*   @author     Claero Systems <craig.nakamoto@claero.com> / XM Media Inc <dhein@xmmedia.net>
*   @copyright  Claero Systems / XM Media Inc  2004-2009
*   @version    $Id: class-claero.php 730 2010-02-18 19:30:00Z cnakamoto $
*/

//$libLoc = str_replace('/class-claero.php', '', __FILE__);
//require_once($libLoc . '/claero_config.php');
//require_once($libLoc . '/common.php');
//require_once($libLoc . '/class-claero_db.php');
//require_once($libLoc . '/class-claero_error.php');

/**
*   Does checking and preparation of variables for other classes
*
*   @author     Claero Systems <craig.nakamoto@claero.com> / XM Media Inc <dhein@xmmedia.net>
*   @copyright  Claero Systems / XM Media Inc  2004-2009
*/
class Claerolib4_Base {
    /**
    *   The ClaerDb object (with db connection)
    *   @var    object
    */
    protected $claeroDb = false;

    /**
    *   Status of current display
    *   @var    bool
    */
    protected $status = true;

    /**
    *   Stores the error messages generated in the object, use EOL to seperate messages in the string
    *   this string should be written for internal use with lots of detail, will not be displayed to the user
    *   false if not set, string otherwise
    *   @var    string/bool
    */
    protected $error = array(); // *** NOT DISPLAYED TO USER ***

    /**
    *   Stores the current status messages, should be written for a user audience (could be displayed to user), use EOL to seperate messages in the string
    *   for example, it could be 'Login was successful.' or 'The user has timed out due to inactivity.'
    *   false if not set, string otherwise
    *   @var    string/bool
    */
    protected $message = array(); // *** DISPLAYED TO USER ***

    /**
    *   The name of the custom form in the claero_form db table, or a table name from the database that is in the meta table
    *   @var    array
    */
    protected $formName = '';

    /**
    *   The number of rows affected by the last action (e.g. DeleteRecord(), UpdateRecord(), setc . in class ClaeroEdit...)
    *   @var    int
    */
    protected $affectedRows = 0;

    /**
    *   Array of ids for the relevant records, could be multiple if
    *   $this->ids['tablename'] = array(ids);
    *   @var    array (false if not set)
    */
    protected $ids = array();

    /**
    *   an array of meta data used to create the $field array below
    *   $this->$formData['table_name']['column_name'] = meta_data
    *   @var    array
    */
    protected $formData = array();

    /**
    *   array of ClaeroField objects
    *   $this->fields['table_name']['column_name'] = ClaeroField object
    *   @var    array
    */
    protected $fields = array();

    /**
    *   Contains the db table name to the user table name
    *   @var    array
    */
    protected $tableNames = array();

    /**
    *   The primary table name for a form
    *   @var    string
    */
    protected $primaryTable;

    /**
    *   contains the password columns in formData
    *   $this->passwordColumns['table_name'] = array(columns)
    *   @var    array
    */
    protected $passwordColumns = array();

    /**
    *   The number of tables being worked with (will be > 0 if it's a form or custom form)
    *   @var    int
    */
    protected $tableCount = 0;

    /**
    *   Options for the object
    *   @var    string
    */
    protected $options = array();

    /**
    *   Data to display the values of fields for selects, radios and others
    *   @var    array
    */
    protected $lookupData = array();

    /**
    *   Any fields that have a relationship
    *   @var    array
    */
    protected $relationships = array(); // 20080830 CSN not sure if this is being used right now

    /**
    *   Any fields that have a one to multiple relationship in the form
    *   @var    array
    */
    protected $multipleRelationships = array();

    /**
    *   Any fields that have a one to one relationship in the form
    *   @var    array
    */
    protected $singleRelationships = array();

    /**
    *   Prepares object setting ClaerDb and other properties of object
    *   To store the meta data, saving on load time:
    *       - to store all meta data, set $claeroMetaDataStore to true
    *       - to store specific tables and/or forms make $claeroMetaDataStore an array with the table and/or form names as the values
    *
    *   @param  array   $options    array of options for object
    *       claero_db => ClaerDb object
    */
    public function __construct($options = array()) {
    
        global $claeroDb, $db, $claeroMetaData, $claeroMetaDataStore;

        // these 2 lines are for phped so it will auto complete the ClaeroDb object
        if (false) $this->claeroDb = $db;
        if (false) $this->claeroDb = $claeroDb;

        if (isset($options['claero_db']) && isset($options['claero_db']->connection) && claero::CheckMysqlConnection($options['claero_db']->connection)) {
            $this->claeroDb = $options['claero_db'];
        } else if (isset($claeroDb) && isset($claeroDb->connection) && claero::CheckMysqlConnection($claeroDb->connection)) {
            $this->claeroDb = $claeroDb;
        } else if (isset($db) && isset($db->connection) && claero::CheckMysqlConnection($db->connection)) {
            $this->claeroDb = $db;
        } else {
            $this->status = false;
            trigger_error('Input Error: No database connection found, therefore Claerolib4_Base class cannot be used', E_USER_ERROR);
            // create ClaeroDb object, connecting to db??
        } // if

        if (!isset($claeroMetaDataStore)) $claeroMetaDataStore = false;
        if (!isset($claeroMetaData)) $claeroMetaData = array();
        
    } // function __construct

    /**
    *   Get the meta data for the given table
    */
    protected function GetMetaData($tableName, $columnName = false) {
    
        global $claeroMetaData, $claeroMetaDataStore;

        // no custom form with this name, try to get the table data from claero_meta
        $sql = "SELECT * FROM `" . CLAERO_META_TABLE . "` WHERE table_name = '" . $this->claeroDb->EscapeString($tableName) . "' ";
        if ($columnName != false) $sql .= " AND column_name = '" . $this->claeroDb->EscapeString($columnName) . "' ";
        $sql .= " ORDER BY display_order";
        //echo $sql;
        $query = $this->claeroDb->Query($sql);
        if ($query === false) {
            $this->status = false;
            trigger_error('Query Failed: Failed to retrieve meta data: ' . $sql);
        } else if ($query->NumRows() == 0) {
            $this->status = false;
            trigger_error('Input Error: No meta records found for table ' . $tableName . ' (and possible column ' . $columnName . '): ' . $sql, E_USER_ERROR);
        } else {
            if ($claeroMetaDataStore !== false && ($claeroMetaDataStore === true || in_array($tableName, $claeroMetaDataStore))) {
                $claeroMetaData[$tableName] = array();
                $storeMeta = true;
            } else {
                $storeMeta = false;
            }
            while($query->FetchInto($field)) {
                if ($storeMeta) {
                    $claeroMetaData[$tableName][$tableName][$field['column_name']] = $field;
                }
                $this->ProcessMetaRow($field);
            } // while
        } // if
        
    } // function GetMetaData

    /**
    *   returns an array containing the column names of a database table
    */
    protected function GetFormData($formName) {
    
        global $claeroMetaData, $claeroMetaDataStore;

        // check to see if the meta data is already cached in the object, and if so, just re-use it, otherwise, get it
        if (isset($claeroMetaData[$formName])) {
            foreach ($claeroMetaData[$formName] as $tableName => $columns) {
                foreach ($columns as $columnName => $meta) {
                    $this->ProcessMetaRow($meta);
                }
            }
        } else {
            // get form information and populate $this->formData
            $sql = "SELECT * FROM (SELECT fm.table_name, fm.column_name, fm.label, ff.search_flag, ff.edit_flag, ff.display_flag, fm.view_flag, ff.required_flag,
                        fm.form_type, fm.source_table, fm.id_field, fm.name_field, fm.form_value, fm.field_size, fm.max_length, fm.min_width,
                        IF(CHAR_LENGTH(ff.reg_exp) > 0, ff.reg_exp, fm.reg_exp) AS reg_exp,
                        ff.display_order AS display_order,
                        (SELECT display_order
                            FROM `" . CLAERO_FORM_TABLE_TABLE . "`
                            WHERE table_name = fm.table_name
                                AND claero_form_id = f.id) AS table_display_order
                    FROM `" . CLAERO_FORM_TABLE . "` AS f
                        LEFT JOIN `" . CLAERO_FORM_FIELD_TABLE . "` AS ff ON (ff.claero_form_id = f.id)
                        LEFT JOIN `" . CLAERO_META_TABLE . "`       AS fm ON ((ff.table_name = fm.table_name AND ff.column_name = fm.column_name) OR (ff.table_name = fm.table_name AND fm.column_name = ''))
                    WHERE f.name = '" . $this->claeroDb->EscapeString($formName) . "' AND fm.id IS NOT NULL

                    UNION

                    SELECT tm.table_name, tm.column_name, tm.label, tm.search_flag, tm.edit_flag, tm.display_flag, tm.view_flag, tm.required_flag,
                        tm.form_type, tm.source_table, tm.id_field, tm.name_field, tm.form_value, tm.field_size, tm.max_length, tm.min_width,
                        tm.reg_exp AS reg_exp,
                        tm.display_order as display_order, ft.display_order AS table_display_order
                    FROM `" . CLAERO_FORM_TABLE . "` AS f
                        LEFT JOIN `" . CLAERO_FORM_TABLE_TABLE . "` AS ft ON (ft.claero_form_id = f.id)
                        LEFT JOIN `" . CLAERO_META_TABLE . "`       AS tm ON (ft.table_name = tm.table_name)
                    WHERE f.name = '" . $this->claeroDb->EscapeString($formName) . "' AND tm.id IS NOT NULL) AS combined_data

                    GROUP BY table_name, column_name

                    ORDER BY table_display_order ASC, display_order ASC";
            //echo $sql;
            $query = $this->claeroDb->Query($sql);
            if ($query) {
                if ($query->NumRows() > 0) {
                    // got a custom form
                    // check to see if we are caching the meta data
                    if ($claeroMetaDataStore !== false && ($claeroMetaDataStore === true || in_array($formName, $claeroMetaDataStore))) {
                        $claeroMetaData[$formName] = array();
                        $storeMeta = true;
                    } else {
                        $storeMeta = false;
                    }
                    // process the data
                    while($query->FetchInto($field)) {
                        if ($storeMeta) {
                            $claeroMetaData[$formName][$field['table_name']][$field['column_name']] = $field;
                        }
                        $this->ProcessMetaRow($field);
                    } // while
                } else {
                    // no custom form, should have got a table
                    $this->GetMetaData($formName);
                } // if
            } else {
                // query failed
                $this->status = false;
                trigger_error('Query Failed: Failed to retrieve form data: ' . $sql);
            } // if
        }

    } // function GetFormData

    /**
    *   Processes the meta data based on field type, ensuring meta data is correct and setting other values based on options passed in
    *
    *   @param      array       $field      Array of meta data including column and table name
    */
    protected function ProcessMetaRow($field) {
    
        if ($field['column_name'] != '') {
        
            $this->formData[$field['table_name']][$field['column_name']] = $field;

            switch ($field['form_type']) {
                case 'text' :
                    // check for field sizes that are too large
                    if ($this->formData[$field['table_name']][$field['column_name']]['field_size'] > TEXT_MAX_SIZE) $this->formData[$field['table_name']][$field['column_name']]['field_size'] = TEXT_MAX_SIZE;
                    if ($this->formData[$field['table_name']][$field['column_name']]['max_length'] > TEXT_MAX_LENGTH) $this->formData[$field['table_name']][$field['column_name']]['max_length'] = TEXT_MAX_LENGTH;
                    break;

                case 'textarea' :
                    // check for field sizes that are too large
                    if ($this->formData[$field['table_name']][$field['column_name']]['field_size'] > TEXTAREA_MAX_COLS) $this->formData[$field['table_name']][$field['column_name']]['field_size'] = TEXTAREA_MAX_COLS;
                    if ($this->formData[$field['table_name']][$field['column_name']]['max_length'] > TEXTAREA_MAX_ROWS) $this->formData[$field['table_name']][$field['column_name']]['max_length'] = TEXTAREA_MAX_ROWS;
                    break;

                case 'file' :
                    // set the defaults for the file (these will be merge with overrides through the override_meta in the options in ProcessMetaData())
                    $this->formData[$field['table_name']][$field['column_name']]['file_options'] = $this->options['file_options'];
                    break;

                case 'password' :
                    // add the password field to an array
                    $this->passwordColumns[$field['table_name']][] = $field['column_name'];
                    // disable loading a default value for the password field
                    $this->formData[$field['table_name']][$field['column_name']]['load_defaults'] = false;
                    break;
            } // switch

            // add aditional default settings (except for password fields)
            if ($field['form_type'] != 'password') {
                // load defaults is set, so figure out if the current column is loading a default
                if (isset($this->options['load_defaults'])) {
                    if (!is_array($this->options['load_defaults'])) {
                        // not an array, so default for entire form
                        $this->formData[$field['table_name']][$field['column_name']]['load_defaults'] = $this->options['load_defaults'];
                    } else if (isset($this->options['load_defaults'][$field['table_name']][$field['column_name']])) {
                        // column is set, so specific setting for column
                        $this->formData[$field['table_name']][$field['column_name']]['load_defaults'] = $this->options['load_defaults'][$field['table_name']][$field['column_name']];
                    } else if (isset($this->options['load_defaults'][$field['table_name']])) {
                        // table is set, so specific setting for whole table
                        $this->formData[$field['table_name']][$field['column_name']]['load_defaults'] = $this->options['load_defaults'][$field['table_name']];
                    }
                }

                if (isset($this->options['modify_foreign'])) {
                    $this->formData[$field['table_name']][$field['column_name']]['modify_foreign'] = $this->options['modify_foreign'];
                }
            }

        } else {
            $this->tableNames[$field['table_name']] = $field['label'];
        }
        
    } // function ProcessMetaRow

    /**
    *   Gets the display name of the table or if it isn't set, the name in the database
    *   Uses an record in the meta data with an empty column name
    *
    *   @param      string      $tableName      The table name to look for
    *
    *   @return     string      The name of the table for display to the user
    */
    protected function GetTableDisplay($tableName) {
    
        if (isset($this->tableNames[$tableName])) {
            return $this->tableNames[$tableName];
        }  else {
            return $tableName;
        } // if
        
    } // function GetTableDisplay

        /**
    *   Processes all the meta data related options
    *   load_defaults is dealt with in GetMetaData()
    *
    *   @todo deal with field ordering, current if the include fields is set, then the tables and columns will be reordered based on it's order
    */
    protected function ProcessMetaData() {

        // get the form data (single table meta data, or multiple tables if custom form)
        $this->GetFormData($this->formName);

        $metaDefaults = array(
            'table_name' => 'unknown',
            'column_name' => 'unknown',
            'label' => 'Unknown',
            'search_flag' => true,
            'edit_flag' => true,
            'display_flag' => true,
            'required_flag' => false,
            'form_type' => 'text',
            'form_value' => '',
            'field_size' => 30,
            'max_length' => 255,
            'load_defaults' => false,
        );

        if (isset($this->options['load_defaults']) && !is_array($this->options['load_defaults'])) {
            $metaDefaults['load_defaults'] = $this->options['load_defaults'];
        }

        // get the fields that are set as included in the current form
        if (isset($this->options['include_fields']) && count($this->options['include_fields']) > 0) {
            $newFormData = array();
            foreach ($this->options['include_fields'] as $tableName => $columns) {
                if (!isset($this->formData[$tableName])) {
                    // the table is not by default in the current for, therefore grab the meta data and set it within $this->formData
                    $this->GetMetaData($tableName);
                } // if

                if (!is_array($columns)) {
                    // include the whole table
                    $newFormData[$tableName] = $this->formData[$tableName];
                } else {
                    foreach ($columns as $columnName) {
                        if (isset($this->formData[$tableName][$columnName])) $newFormData[$tableName][$columnName] = $this->formData[$tableName][$columnName];
                    } // foreach
                } // if
            } // foreach
            // $newFormData now contains the meta data for the form
            $this->formData = $newFormData;
        }

        // remove any tables/fields that are marked as exclude
        if (isset($this->options['exclude_fields'])) {
            foreach ($this->options['exclude_fields'] as $tableName => $columns) {
                if (!is_array($columns)) {
                    // remove the whole table
                    if (isset($this->formData[$tableName])) unset($this->formData[$tableName]);
                } else {
                    // loop through the columns
                    foreach ($columns as $columnName) {
                        if (isset($this->formData[$tableName][$columnName])) unset($this->formData[$tableName][$columnName]);
                    } // foreach
                } // if
            } // foreach
        }

        // override the meta data as / if requested
        if (count($this->options['override_meta']) > 0 || (isset($this->options['claero_field_options']) && count($this->options['claero_field_options']) > 0)) {
            // first merge all the overrides into the formData
            $this->formData = ArrayMergeClobber($this->formData, $this->options['override_meta']);

            // loop through the form data to ensure the defaults are set
            foreach ($this->formData as $tableName => $fields) {
                foreach ($fields as $columnName => $meta) {
                    $this->formData[$tableName][$columnName] = ArrayMergeClobber($metaDefaults, $meta);
                    if (isset($meta['claero_field_options'])) {
                        $this->formData[$tableName][$columnName]['claero_field_options'] = ArrayMergeClobber($this->options['claero_field_options'], $meta['claero_field_options']);
                    } else {
                        $this->formData[$tableName][$columnName]['claero_field_options'] = $this->options['claero_field_options'];
                    }
                } // foreach
            } // foreach
        } // if

        // set the number of tables within this form (only > 1 when we are working with form)
        $this->tableCount = count($this->formData);
    } // function ProcessMetaData

    /**
    *   Gets the status in the current object, uses $this->status
    *
    *   @return     bool        true or false on status of object
    */
    public function GetStatus() {
        return $this->status;
    } // function GetStatus


    /**
    *   Returns the last errors, not meant to be displayed to the user
    *   formats them as specified and empties out the error array
    *   @see    $notReason desc
    *
    *   @param      string      $eol        the line ending to use after each error (default: HEOL)
    *
    *   @return     string      string for display (debug only)
    */
    public function GetError($eol = null) {
        if ($eol === null) $eol = HEOL;

        $returnHtml = '';
        foreach ($this->error as $error) {
            $returnHtml .= $error . $eol;
        }

        $this->error = array();

        return trim($returnHtml);
    } // function GetError

    /**
    *   Returns a string for display with the current status message that can be displayed to the user
    *   formats them as specified and empties out the messages array
    *   @see    $notReason desc
    *
    *   @param      string      $eol        the line ending to use after each error (default: HEOL)
    *
    *   @return     string      string for display
    */
    public function GetMessage($eol = null) {
        if ($eol === null) $eol = HEOL;

        $returnHtml = '';
        $i = 0;
        foreach ($this->message as $message) {
            if ($i > 0) $returnHtml . $eol;
            $returnHtml .= $message . ' ';
            ++$i;
        }

        $this->message = array();

        return trim($returnHtml);
    } // function GetMessage

    /**
    *   Sets the options within the object based on if the option is set within the $options array and if it's a possible option
    *
    *   @param      array   $options            array of options received by constructor
    *   @param      array   $possibleOptions    array of possible options to check for
    *   @param      bool    $useDefaults        sets wether or not to use the default in the array if not set in passed options (default: true)
    */
    protected function SetObjectOptions($options, $possibleOptions, $useDefaults = true) {
        if (!is_array($options)) {
            trigger_error('Did not receive an array for $options, therefore no options were set within object.');
            $options = (array) $options;
        }
        foreach ($possibleOptions as $possibleOption => $default) {
            if (isset($options[$possibleOption])) $this->options[$possibleOption] = $options[$possibleOption];
            else if ($useDefaults) $this->options[$possibleOption] = $default;
        }
    } // function SetObjectOptions

    /**
    *   Loops through all the fields in the table running the formatting function on it
    *
    *   @param      string      $tableName      The name of the table
    *   @param      array       $data           The data array to be formatted (passed by reference)
    *
    *   @todo   revise this and the display of the fields so the fields don't have keys of 0, 1, etc for date time fields
    */
    protected function PrepareSpecialFields($tableName, &$data) {
        // loop through the meta data to check for special field types because these will need to have data grabbed from different fields and combined into one
        foreach ($this->formData[$tableName] as $columnName => $metaData) {
            if (isset($data[$columnName])) {
                $return = claero::PrepareSpecialField($metaData['form_type'], $data[$columnName]);
                if ($return !== null) $data[$columnName] = $return;
            }
        } // foreach
    } // function PrepareSpecialFields

    /**
    *   Formats a value for display based on its datatype (as stored in the metatable)
    *
    *   @param      var         $data       the value that should be formatted
    *   @param      string      $type       the datatype (ex: "checkbox", "date", "text", "money", etc.)
    *   @param      string      $tableName  the name of the table where the key is for select (not product but where product_id is)
    *   @param      string      $columnName the name of the column being formatted
    *   @param      array       $metaRow    the meta data for the fields (required for select, radios, and the like)
    *
    *   @return     string      HTML for display
    */
    public function FormatValueForDisplay($data, $type, $tableName = '', $columnName = '', $metaRow = array()) {
        $r = '';

        // check to see if the type is a known value
        switch($type) {
            case 'password' :
                $r = '<span class="info">&lt;hidden&gt;</span>';
                break;

            case 'checkbox' :
                if ($this->options['checkmark_icons']) {
                    if ($data) {
                        $r = '<span class="checked">&nbsp;</span>';
                    } else {
                        $r = '&nbsp;';
                    }
                } else {
                    if ($data) {
                        $r = 'Y';
                    } else {
                        $r = 'N';
                    }
                }
               break;

            case 'link' :
                $r = '<a href="' . $data . '" target="_blank">' . $data . '</a>';
                break;

            case 'datetime':
                $r = $this->FormatDate($data, 'M j, Y H:i:s');
                break;

            case 'datetime12' :
                $r = $this->FormatDate($data, 'M j, Y h:i:s A');
                break;

            case 'date':
            case 'date_three_field' :
                $r = $this->FormatDate($data, 'M j, Y');
                break;

            case 'year_month' :
                if ($data == '0000-00-00' || $data == '0000-00-00 00:00:00') {
                    $r = '';
                } else {
                    $unix = strtotime($data);
                    if ($unix) $r = date('Y-F', $unix);
                    else $r = '';
                }
                break;

            case 'phone' :
                $r = $this->FormatPhone($data);
                break;

            case 'email':
                if (! empty($data)) {
                    $r = '<a href="mailto:' . $data . '" title="Email \'' . $data. '\'" class="email">' . $data . '</a>';
                } else {
                    $r = $data;
                }
                break;

            case 'url':
                if (strpos($data,'http') === false || strpos($data,'http') > 0){
                    $data = "http://".$data;
                }

                $r = '<a class="url" href="' . $data . '" title="Visit \'' . $data . '\'" target="blank">' . $data. '</a>';
                break;

            case 'percentage':
                $r = sprintf("%01.2f %%", $data);
                break;

            case 'money':
                $r = '$' . number_format($data, 2);
                break;

            case 'text_area' :
            case 'html' :
                if ($this->options['text_area_br']) $r = nl2br(EscapeOutputForHtml($data));
                else $r = $data;
                break;

            case 'select' :
            case 'radios' :
                if (!isset($this->lookupData[$tableName][$columnName])) {
                    $this->lookupData[$tableName][$columnName] = array();

                    if (stripos($metaRow['source_table'], 'select') === false && strpos($metaRow['source_table'], '|')) {
                        // the data is in the format of value|name||value|name||...
                        $this->lookupData[$tableName][$columnName] = GetSourceArray($metaRow['source_table']);

                    } else {
                        $nameField = ($metaRow['name_field'] != '') ? $metaRow['name_field'] : 'name';
                        $idField = ($metaRow['id_field'] != '') ? $metaRow['id_field'] : 'id';
                        if (stripos($metaRow['source_table'], 'select') === false) {
                            // form_source_table is a table, therefore perform simple query on this table
                            // lookup all the id / name pairs in the lookup table for this select field
                            $lookupSql = "SELECT `" . $this->claeroDb->EscapeString($idField) . "`, `" . $this->claeroDb->EscapeString($nameField) . "` FROM `" . $this->claeroDb->EscapeString($metaRow['source_table']) . "` ORDER BY `" . $this->claeroDb->EscapeString($nameField) . "`";
                        } else {
                            // form_source_table contains SQL SELECT for this select form field
                            // perform query, the pertinent result parameters are form_id_field and form_name_field
                            $lookupSql = $metaRow['source_table'];
                        } // if

                        $lookupQuery = $this->claeroDb->Query($lookupSql);
                        if ($lookupQuery !== false) {
                            if ($lookupQuery->NumRows() > 0) {
                                // get the first row of the result
                                $lookupQuery->FetchInto($row);
                                // check to see if the id field isset within the row
                                if (!isset($row[$idField])) {
                                    $this->status = false;
                                    trigger_error('Input Error: The id field (' . $idField . ') is not set in the SQL for ' . $columnName . ' sql: ' . $this->claeroDb->GetLastQuery(), E_USER_ERROR);
                                }
                                // check to see if the value field isset within the row
                                if (!isset($row[$nameField])) {
                                    $this->status = false;
                                    trigger_error('Input Error: The name field (' . $nameField . ') is not set in the SQL for ' . $columnName . ' sql: ' . $this->claeroDb->GetLastQuery(), E_USER_ERROR);
                                }

                                if ($this->status) {
                                    $lookupQuery->DataSeek(); // move query back to first row

                                    // create an array to track these values
                                    // VERY INEFFICIENT FOR LARGE LOOKUP TABLES!!!  SHOULD BE DONE IN A MORE DETAILED SELECT INSTEAD
                                    while ($lookupQuery->FetchInto($lookupTableData)) {
                                        // the first index is the column name and the next index is the ID, the value is the name
                                        $this->lookupData[$tableName][$columnName][$lookupTableData[$idField]] = $lookupTableData[$nameField];
                                    } // while
                                }
                            } else {
                                $this->lookupData[$tableName][$columnName] = array();
                            }
                        } else {
                            // failure to get source data
                            $errorText = 'Query Error: Lookup query failed: Failed to get source data for ' . $columnName . ' using SQL: ' . $this->claeroDb->GetLastQuery();
                            trigger_error($errorText, E_USER_ERROR);
                            $this->status = false;
                            $this->message[] = 'Search failed, please try again.';
                            $this->error[] = $errorText;
                        } // if
                    } // if
                } // if lookup data already found

                $r = claero::EscapeOutputForHtml($this->GetLookupData($tableName, $columnName, $data));
                break;

            case 'select_grouped' :
                if (!isset($this->lookupData[$tableName][$columnName])) {
                    $this->lookupData[$tableName][$columnName] = array();
                    $nameField = ($metaRow['name_field'] != '') ? $metaRow['name_field'] : 'name';
                    $idField = ($metaRow['id_field'] != '') ? $metaRow['id_field'] : 'id';

                    $lookupQuery = $this->claeroDb->Query($metaRow['source_table']);
                    if ($lookupQuery !== false) {
                        // get the first row of the result
                        $lookupQuery->FetchInto($row);
                        // check to see if the id field isset within the row
                        if (!isset($row[$idField])) {
                            $this->status = false;
                            trigger_error('Input Error: The id field (' . $idField . ') is not set in the SQL for ' . $columnName . ' sql: ' . $this->claeroDb->GetLastQuery(), E_USER_ERROR);
                        }
                        // check to see if the value field isset within the row
                        if (!isset($row[$nameField])) {
                            $this->status = false;
                            trigger_error('Input Error: The name field (' . $nameField . ') is not set in the SQL for ' . $columnName . ' sql: ' . $this->claeroDb->GetLastQuery(), E_USER_ERROR);
                        }
                        if (!isset($row['parent'])) {
                            $this->status = false;
                            trigger_error('Input Error: The parent field (parent) is not set in the SQL for ' . $columnName . ' sql: ' . $this->claeroDb->GetLastQuery(), E_USER_ERROR);
                        }

                        if ($this->status) {
                            $lookupQuery->DataSeek(); // move query back to first row

                            // create the multi-dimensional array to track these values
                            // VERY INEFFICIENT FOR LARGE LOOKUP TABLES!!!  SHOULD BE DONE IN A MORE DETAILED SELECT INSTEAD
                            while ($lookupQuery->FetchInto($lookupTableData)) {
                                // the first index is the column name and the next index is the ID, the value is the name
                                $this->lookupData[$tableName][$columnName][$lookupTableData[$idField]] = $lookupTableData['parent'] . ' - ' . $lookupTableData[$nameField];
                            } // while
                        }
                    } else {
                        // failure to get source data
                        $errorText = 'Query Error: Lookup query failed: Failed to get source data for ' . $columnName . ' using SQL: ' . $this->claeroDb->GetLastQuery();
                        trigger_error($errorText, E_USER_ERROR);
                        $this->status = false;
                        $this->message[] = 'Search failed, please try again.';
                        $this->error[] = $errorText;
                    } // if
                } // if lookup data already found

                $r = claero::EscapeOutputForHtml($this->GetLookupData($tableName, $columnName, $data));
                break;

            case 'yes_no_radio' :
                if (!isset($this->lookupData[$tableName][$columnName])) {
                    $this->lookupData[$tableName][$columnName] = array(
                        1 => 'Yes',
                        2 => 'No',
                    );
                }
                $r = $this->GetLookupData($tableName, $columnName, $data);
                break;

            case 'gender_radio' :
                if (!isset($this->lookupData[$tableName][$columnName])) {
                    $this->lookupData[$tableName][$columnName] = array(
                        1 => 'Male',
                        2 => 'Female',
                    );
                }
                $r = $this->GetLookupData($tableName, $columnName, $data);
                break;

            case 'height_drop' :
                $r = floor($data / 12). '\'' . ($data % 12) . '" or ' . round($data * 2.54, 0) . 'cm';
                if (!isset($this->lookupData[$tableName][$columnName])) {
                    $startEnd = explode('|', $metaRow['source_table']);
                    if (count($startEnd) == 2) {
                        $this->lookupData[$tableName][$columnName]['start'] = $startEnd[0];
                        $this->lookupData[$tableName][$columnName]['end'] = $startEnd[1];
                    } else {
                        $this->lookupData[$tableName][$columnName]['start'] = 59; // this is also in ClaeroField
                        $this->lookupData[$tableName][$columnName]['end'] = 84; // this is also in ClaeroField
                    }
                }
                if ($data == $this->lookupData[$tableName][$columnName]['start']) $r .= ' or under';
                else if ($data == $this->lookupData[$tableName][$columnName]['end']) $r .= ' or over';
                break;

            // if the type is unknown, then simply return the data as it is
            default:
                $r = claero::EscapeOutputForHtml($data);
                break;
        } // switch

        return $r;
    } // function FormatValueForDisplay

    /**
    *   Looks inside $this->lookupData for the value for the current table, column and value
    *   Returns null if it isn't found
    *
    *   @param      string      $tableName  the name of the table where the key is for select (not product but where product_id is)
    *   @param      string      $columnName the name of the column being formatted
    *   @param      var         $id         the value to look for
    *
    *   @return     var         The value found in lookupData or null if not found
    */
    protected function GetLookupData($tableName, $columnName, $id) {
        if (isset($this->lookupData[$tableName][$columnName][$id])) $r = $this->lookupData[$tableName][$columnName][$id];
        else $r = null;
        return $r;
    } // function GetLookupData

    public function FormatPhone($data) {

        if (strlen($data) == 7) {
            $r = substr($data, 0, 3) . '-' . substr($data, 3, 4);
        } else {
            $areaCode = substr($data, 0, 3);
            $prefix = substr($data, 3, 3);
            $line = substr($data, 6, 4);
            $r = ((strlen($areaCode) == 0) && (strlen($prefix) == 0) && (strlen($line) == 0)) ? '' : '(' . $areaCode . ') ' . $prefix . '-' . $line;
        }

        return $r;
    } // function FormatPhone

    public function FormatDate($data, $format) {
        if ($data == '0000-00-00' || $data == '0000-00-00 00:00:00') {
            $r = '';
        } else {
            $unix = strtotime($data);
            if ($unix) $r = date($format, $unix);
            else $r = '';
        }

        return $r;
    } // function FormatDate

    /**
    *   Checks the custom form relationships and gets the info / ids for the foreign tables
    */
    protected function ProcessRelationships() {

        if ($this->tableCount > 1) {
            $tables = array(); // get a list of the tables
            // get the relationships from the claero_form table
            $relationships = array();
            $sql = "SELECT ft.table_name, ft.primary_flag, ft.relationship
                    FROM `" . CLAERO_FORM_TABLE . "` AS f
                        LEFT JOIN `" . CLAERO_FORM_TABLE_TABLE . "` AS ft ON (ft.claero_form_id = f.id)
                    WHERE f.name = '" . $this->claeroDb->EscapeString($this->formName) . "'
                    ORDER BY ft.display_order";
            //echo $sql;
            $query = $this->claeroDb->Query($sql);
            if ($query) {
                if ($query->NumRows() > 0) {
                    $allowedRelationships = array('single','multiple');
                    while($query->FetchInto($field)) {
                        $tableName = $field['table_name'];
                        $tables[] = $tableName;
                        if ($field['primary_flag'] && !$this->primaryTable) {
                            $this->primaryTable = $tableName;
                            $this->relationships[$tableName]['primary'] = true;
                        }
                        if (in_array($field['relationship'], $allowedRelationships)) {
                            $this->relationships[$tableName]['type'] = $field['relationship'];
                        }
                    } // while
                } else {
                    // no custom form
                }
            } else {
                // query failed
                $this->status = false;
                trigger_error('Query Failed: Failed to retrieve form table data: ' . $sql);
            }
            if (sizeof($this->relationships) > 0) {
                // now get the foreign relationships and data
                $tableList = $this->claeroDb->ImplodeEscape($tables); // get a list of the tables
                $relationshipSql = "
                    SELECT table_name, column_name, foreign_table, foreign_column
                    FROM `" . CLAERO_FOREIGN_TABLE . "`
                    WHERE table_name IN (" . $tableList . ") AND foreign_table IN (" . $tableList . ")";
                //echo $relationshipSql;
                $relationshipQuery = $this->claeroDb->Query($relationshipSql);
                if ($relationshipQuery !== false) {
                    while ($relationshipQuery->FetchInto($foreignData)) {
                        $tableName = $foreignData['table_name'];
                        switch ($this->relationships[$foreignData['table_name']]['type']) {
                            case 'multiple' :
                                $this->multipleRelationships[$tableName] = $foreignData;
                                // add a hidden field called c_delete_flag that is set when a row is deleted
                                $this->formData[$tableName]['c_delete_flag'] = array(
                                    'edit_flag' => true,
                                    'column_name' => 'c_delete_flag',
                                    'form_type' => 'hidden',
                                    'form_value' => 0,
                                    'load_defaults' => true,
                                    'required_flag' => false,
                                    'field_size' => 0,
                                    'max_length' => 0,
                                );
                                break;

                            case 'single' :
                                $this->singleRelationships[$tableName] = $foreignData;
                                break;
                        } // switch

                        foreach ($foreignData AS $key=>$value) {
                            $this->relationships[$tableName][$key] = $value;
                        } // foreach

                        // set the foreign key column in the current table to be editable so it will be sent back
                        // also set the form_type to hidden so the foreign key can't be changed
                        $this->formData[$tableName][$foreignData['column_name']]['edit_flag'] = true;
                        $this->formData[$tableName][$foreignData['column_name']]['form_type'] = 'hidden';
                    }
                } else {
                    trigger_error('Query Error: Failed to retrieve any multiple relationships between tables: ' . $relationshipSql, E_USER_ERROR);
                    $this->message[] = 'There was an error retrieving the all the data for this form. All the fields may not be displayed correctly.';
                } // if
            } // if
        } // if
    } // function ProcessRelationships

} // class ClaeroBase