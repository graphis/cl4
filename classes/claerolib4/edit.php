<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
*   This file has the class ClaeroEdit which is used for creating edit and search forms and performs record updates and inserts
*
*   @author     Claero Systems <craig.nakamoto@claero.com> / XM Media Inc <dhein@xmmedia.net>
*   @copyright  Claero Systems / XM Media Inc  2004-2009
*   @version    $Id: class-claero_edit.php 772 2010-07-03 09:04:34Z dhein $
*/
//$libLoc = str_replace('/class-claero_edit.php','', __FILE__);
//require_once($libLoc . '/claero_config.php');
//require_once($libLoc . '/common.php');
//require_once($libLoc . '/class-claero_table.php');
//require_once($libLoc . '/class-claero.php');
//require_once($libLoc . '/class-claero_field.php');
//require_once($libLoc . '/class-claero_file.php');

/**
*   Creates edit and search forms and performs record updates and inserts
*
*   @author     Claero Systems <craig.nakamoto@claero.com> / XM Media Inc <dhein@xmmedia.net>
*   @copyright  Claero Systems / XM Media Inc  2004-2009
*
*   @see    class ClaeroError
*   @see    class ClaeroDb
*   @see    class Claero
*
*   @todo   make a away to say include all meta data for xyz table
*/
class Claerolib4_Edit extends Claerolib4_Base {
    /**
    *   The current mode for the object
    *   @var    string
    */
    private $mode;

    /**
    *   The number of records in the form (for when mutiple record editing is implemented)
    *   @var    int
    */
    private $recordCount = 0;

    /**
    *   Contains the name of the ClaeorField ojbect to instantiate while creating fields
    *   @var    string
    */
    private $claeroFieldObject;

    /**
    *   Contains the post data or data arrays for use in insert or update record
    *   @var    array
    */
    private $rawData = array();

    /**
    *   The id of the last record that was inserted using InsertRecord()
    *   @var    int
    */
    private $insertId = array();

    /**
    *   This hidden fields to be included in the next form creation
    *   @var    array
    */
    private $hiddenFields = array();

    /**
    *   The html generated
    *   @var    string
    */
    private $html = '';

    /**
    *   The field values in the current form
    *   @var    array
    */
    private $formValues = array();

    /**
    *   Prepares the edit, search or save (insert/update/delete)
    *   Checks to ensure the record exists and then if edit or search, creates fields
    *
    *   @param  string  $formName   name of form or table to prepare/create
    *   @param  array   $options    array of options for object
    *       ['claero_db'] => ClaerDb object
    *       ['mode'] => 'save' (edit record(s) or add new ones), 'search' (search within table using criteria)
    *       ['id'] = > id of record to view or edit or an array of ids (is overridden by ['table_name']['id']), not needed for 'save'
    *       ['table_name']['id'] => id of record to view or array of ids for each table (overrides ['id'])
    *       ['on_submit_event']
    *       ['file_options'] => array(
    *           options for use during file upload
    *           these can also be passed within the override_meta for a specific field
    *           ['private_flag'] => true if the files are in a private location (default: false)
    *           ['doc_root'] => the root of the site so that private/public can be figured out (during download) (default: current directory)
    *           ['file_location'] => the location to put the file (without filename) (default: uploads folder in current directory)
    *           ['filename_change'] => the type of name change to do (check ClaeroFile for the options) (default: timestamp)
    *           ['desination_file'] => a possible file prefix if the file_name_change needs a destination file name (default: not used); this is used when the filename_change is 'prepend', 'append', 'overwrite', or 'overwrite_all'; this will be added to the filename or overwrite the entire filename
    *           ['overwrite'] => if the destination exists, if it should be overwritten (default: false)
    *           ['original_filename_column'] => the name of the column which contains the original name of the file, the users file name (default: original_filename)
    *           ['download_file'] => the file that runs ClaeroFile::Download() to stream the file to browser (default: PRIVATE_DOWNLOAD_FILE)
    *           ['delete_files'] => if the file should be deleted that are attached to the record, if new file is upload, old one remove or record deleted (default: false)
    *           ['file_url'] => the url to directory where the uploads are contained when not doing private
    *           ['clean_filename'] => if true, any special characters in the users filename will be removed
    *           ['download_file_size'] => for use with download_file: adds the size get parameter to pass to the image_download.php file
    *       )
    *       ['override_meta'] => array('table_name' => array('column_name' => meta data)) overrides meta data; display_order will be not be override
    *           // ^ this can have a sub array 'claero_field_options' that gets merged with the default options determine within PrepareForm() overriding those values
    *           // ^ this can have a sub array 'file_options' that gets merged with the default and global ('file_options') options sending them to ClaeroFile (has the same options as the global file_options)
    *           // ^ this can also include 'modify_foreign'
    *       ['meta'] => same as override_meta, just shorter; replaces all values of override_meta
    *       ['modify_foreign'] => if true, then foreign records will be deleted or expired on deleted (default: false), can be included in the
    *       ['date_expired_column'] => the name of the expiry column
    *       ['data'] => array of data to save; if any value is a PHP null value, then null will be sent to MySQL
    *       ['criteria_source'] => default 'post', can be 'get', 'data', 'request', or 'post'
    *       not used: ['post_to'] => name of file to post to, otherwise just use current (without get parameters)
    *       ['date_expired_coilumn'] => the column name of the expiry date field in the table if delete record is using expiry instead of delete
    *       ['multiple_edit_layout'] => the layout to apply when editing multiple records: vertical or horizontal (default)
    *       ['checkmark_icons'] => if true, then images will be used instead of text while in view mode
    *       ['display_submit'] => if true, the submit buttons or other buttons will show
    *       ['text_area_br'] => used in FormatValueForDisplay() to determine if the nl lines should be changes to brs
    *       ['prepare_fields_without_values'] => creates the ClaeroFieldObject for any tables that don't have any records (useful for mutliple relationships)
    *       ['user_action'] => if this is set, then an additional hidden field will be added with this c_user_action and the name of the submit button will NOT be set (default: false)
    *       ['process_files'] => if set to false, then files uploaded or otherwise will not be processed (default true)
    *       ['additional_multiple'] => array('table_name' => rows) rows is the number of rows that will be shown (in edit or add mode) (default: 0 no rows)
    *       ['md5_password'] => if set to false, then the password will not be md5'd before being put in the database, useful when doing migration (default: true)
    *       ['simple_data'] => use this if you only want to update or save 1 table which is the same table as $formName; this will replace the data array
    *       ['data_merge'] => passing data in there (in the same format as the data you are merging with) will override those values; useful when receiving data directly from $_POST; if any value is a PHP null value, then null will be sent to MySQL
    *       ['add_file_prefix'] => if set to false, the extra display/edit options around the file edit (link, remove, replace with) will not be displayed (default: true)
    *       ['ignore_empty_data'] => if set to false (default), the object status will be changed to false and errors will be triggered when no data has been received to update a record, if set to true, then these errors and messages will not be triggered, in otherwords, the empty data will be ignored (default: false)
    *       ['custom_select_expressions'] => an array of custom selects to change the default all fields/expressions in the queries used to retrieve the data; each key in the array is a different table *** These are NOT escaped ***
    *       ['allowed_fields'] => fields that can either be inserted or updated when a saving a record; in format of table_name => array(field_name); if null (default) all fields be accepted
    *       ['dont_allow_fields'] => fields that can't be inserted or updates when saving a record; in format of table_name => array(field_name); if null (default) all fields will be allowed
    *       ['is_for_html'] => when set to false any extra HTML put around a field will be removed; useful for view mode when not using for HTML; default true
    *       ['select_default_0'] => when set to false, selects will not default to 0 when not value is received (default true)
    *       ['force_insert'] => for use when you are passing an ID into the object but still want to insert the record (instead of updating an existing one) (default false)
    *       ['include_id_in_insert'] => when set to true, the value of the id field will be used (default false)
    *
    *   @todo   add file support!!!, add param [table_name]['override']
    */
    public function __construct($formName, $options = array()) {

        parent::__construct($options);

        $this->formName = $formName;

        // try to determine a default url to post the form to
        if (!isset($_SERVER['SCRIPT_NAME'])) {
            $scriptName = '';
            trigger_error('Input Error: Failed to determine the form_url for the current object.', E_USER_WARNING);
        } else {
            // 20100707 CSN this does not work in Kohana: $scriptName = $_SERVER['SCRIPT_NAME'];
            $scriptName = '';
        }

        // assign options that need defaults
        $possibleOptions = array(
            'display_form_tag' => true,
            'display_submit' => true,
            'form_type' => 'post',
            'mode' => 'save',
            'replace_spaces' => false,
            'form_url' => $scriptName,
            'on_submit_event' => false,
            'field_name_prefix' => '', // for all fields, default is no prefix
            'enable_post_array' => true,
            'hidden' => array(),
            'form_name' => 'claeroForm',
            'form_id' => 'claeroForm',
            'date_expired_column' => CLAERO_EDIT_EXPIRY_COLUMN,
            'modify_foreign' => false,
            'multiple_edit_layout' => 'horizontal',
            'checkmark_icons' => true,
            'text_area_br' => false,
            'prepare_fields_without_values' => false,
            'user_action' => false,
            'process_files' => true,
            'additional_multiple' => array(),
            'md5_password' => true,
            'add_file_prefix' => true,
            'ignore_empty_data' => false,
            'custom_select_expressions' => array(),
            'allowed_fields' => null,
            'dont_allow_fields' => null,
            'is_for_html' => true,
            'select_default_0' => true,
            'force_insert' => false,
            'include_id_in_insert' => false,
            'table_options' => array(), // array of options merged with defaults for ClaeroTable
            'file_options' => array(), // array of options merge with defaults for ClaeroFile, can be overriden with the 'override_meta' => 'file_options' => array()
                // ^ additional option key 'destination' used for the destination file name use in ClaeroFile::Upload()
            // these are used as a global value for all the fields
            'load_defaults' => true, // this overrides all the table & column specific settings, unless sent in as an option specifically for the table or column
            'claero_field_options' => array(), // settings to send to ClaeroField object for all fields, overriden by override_meta
            // these are used for overriding meta data
            'override_meta' => array(),
                // ^ this can have a sub array 'claero_field_options' that gets merged with the default options determine within PrepareForm() overriding those values
                // ^ this can have a sub array 'file_options' that gets merged with the default and global ('file_options') options sending them to ClaeroFile (has the same options as the global file_options)
                // ^ this can also include 'modify_foreign'
            'meta' => array(), // same as override_meta, just shorter; replaces all values of override_meta
            'include_fields' => array(),
            'exclude_fields' => array(),
            // these are used when inserting or updating and will be in the format of data => array(table_name => array(columns))
            'data' => array(),
            'simple_data' => array(),
            'data_merge' => array(),
        );
        $this->SetObjectOptions($options, $possibleOptions);

        // set default table options (others are set in ClaeroTable class)
        $tableOptions = array(
            'cellspacing' => 1,
            'cellpadding' => 2,
            'odd_even' => true,
            'table_id' => 'claeroEdit',
            'table_class' => 'claeroEdit',
            'populate_all_cols' => false,
        );
        $this->options['table_options'] = array_merge($tableOptions, $this->options['table_options']);

        // set default file options (others are set in ClaeroFile class)
        $fileOptions = array(
            'private_flag' => false,
            'file_location' => 'uploads',
            'original_filename_column' => 'original_filename', // the location to store the user file name (if false, then it will not be stored)
            'download_file' => PRIVATE_DOWNLOAD_FILE,
            'delete_files' => false,
            'file_url' => '',
            'download_file_size' => null,
            'desination_file' => '',
        );
        $this->options['file_options'] = array_merge($fileOptions, $this->options['file_options']);

        $this->claeroFieldObject = isset($options['claero_field_object']) && class_exists($options['claero_field_object']) ? $options['claero_field_object'] : 'ClaeroField';
        if (!empty($this->options['meta'])) $this->options['override_meta'] = $this->options['meta'];

        $this->mode = $this->options['mode'];

        if (!empty($this->options['simple_data'])) {
            $this->options['data'] = array(
                $formName => $this->options['simple_data'],
            );
        }

        // use the $_POST data or the optional data array if set for the raw data
        if (count($this->options['data']) > 0) {
            // if there is "data_merge" then merge this array with the data that was received
            if (!empty($this->options['data_merge'])) {
                $this->rawData = ArrayMergeClobber($this->options['data'], $this->options['data_merge']);
            } else {
                $this->rawData = $this->options['data'];
            }
        } else if (isset($_POST[CLAERO_REQUEST_RECORD])) {
            // if there is "data_merge" then merge this array with the data that was found in the $_POST
            if (!empty($this->options['data_merge'])) {
                $this->rawData['record'] = ArrayMergeClobber($_POST[CLAERO_REQUEST_RECORD], $this->options['data_merge']);
            } else {
                $this->rawData['record'] = $_POST[CLAERO_REQUEST_RECORD];
            }
        } else if ($this->mode == 'save') {
            if (!$this->options['ignore_empty_data']) trigger_error('Input Error: No data received for saving record (insert or update)', E_USER_ERROR);
        }

        $this->ProcessMetaData(); // process the meta data and populate $this->formData

        $this->ProcessRelationships(); // process the foreign relationships, need to ProcessMetaData() first
        //echo "<h3>this->singleRelationships</h3>";
        //PrintR($this->singleRelationships);
        //echo "<h3>this->multipleRelationships</h3>";
        //PrintR($this->multipleRelationships);
        //echo "<h3>this->relationships</h3>";
        //PrintR($this->relationships);

        $this->ProcessIds($options); // process the ids and populate $this->ids, need to ProcessRelationships() first
        //PrintR($this->ids);

        // populate the record count if their are more than one id
        if (count($this->ids) > 0) {
            reset($this->ids);
            $firstTable = key($this->ids); // get a table out of the id array (doesn't matter what table)
            $this->recordCount = count($this->ids[$firstTable]);
        }

        if ($this->GetStatus()) {
            switch($this->mode) {
                case 'edit' :
                case 'add' :
                case 'search' :
                case 'add_row' :
                case 'edit_multiple' :
                case 'edit_request' :
                    $this->PrepareForm();
                    break;
                case 'save' : // this is the default if no mode is specified
                    $this->SaveRecord();
                    break;
                case 'delete' :
                    $this->DeleteRecord();
                    break;
                case 'view' :
                    $this->PrepareView();
                    break;
                default : // will never happen because 'save' is the default value
                    break;
            } // switch
        } else {
            trigger_error('Object Error: During the preparation of the ClaeroEdit object, there was an error that set the status as false and therefore the requested action cannot be performed.', E_USER_ERROR);
        }

    } // function __construct

    /**
    *   Returns the complete form in HTML based on $this->formData and %this->field
    *   Uses ClaeroTable to display the form
    *
    *   @return     string      HTML for display (possibly includes JS & CSS)
    *
    *   @todo   check and remove file fields if 'get' form type is used?  also change enctype for get / post
    */
    public function GetHtml() {
        if (in_array($this->mode, array('edit', 'add', 'search', 'add_row', 'edit_multiple', 'view', 'edit_request'))) {
            $this->GetFormHtml();
        }
        return $this->html;
    } // function GetHtml

    /**
    *   Returns the HTML for display of an individual field
    *
    *   @param      string      $tableName      name of table field is in
    *   @param      string      $fieldName      name of field
    *   @param      int         $recordNum      The record number of the field (counts from 0 of ids sent to object) (default: 0)
    *
    *   @return     string      HTML for display
    */
    public function GetField($tableName, $fieldName, $recordNum = 0) {
        if (isset($this->fields[$tableName][$recordNum][$fieldName])) {
            if ($this->mode != 'view') {
                return $this->fields[$tableName][$recordNum][$fieldName]->GetHtml();
            } else {
                return $this->fields[$tableName][$recordNum][$fieldName];
            }
        } else {
            trigger_error('Input Error: Unable to find field requested (table: ' . $tableName . ' field: ' . $fieldName . ' record_num: ' . $recordNum . ')', E_USER_ERROR);
            return '';
        }
    } // function GetField

    /**
    *   Returns the ClaeroField object for a field
    *
    *   @param      string      $tableName      name of table field is in
    *   @param      string      $fieldName      name of field
    *   @param      int         $recordNum      The record number of the field (counts from 0 of ids sent to object) (default: 0)
    *
    *   @return     string      the ClaeroField object, null if not found
    */
    public function GetFieldObject($tableName, $fieldName, $recordNum = 0) {
        if (isset($this->fields[$tableName][$recordNum][$fieldName])) {
            return $this->fields[$tableName][$recordNum][$fieldName];
        } else {
            trigger_error('Input Error: Unable to find field requested for object return (table: ' . $tableName . ' field: ' . $fieldName . ' record_num: ' . $recordNum . ')', E_USER_ERROR);
            return null;
        }
    } // function GetFieldObject

    /**
    *   Returns the field's current value, either from the database or the value passed in through options
    *
    *   @param      string      $tableName      The table containing the field
    *   @param      string      $fieldName      The name of the field
    *   @param      int         $recordNum      The record number of the field (counts from 0 of ids sent to object) (default: 0)
    *   @param      bool        $escape         If set to false, then the returned value will not be escaped for output as HTML; default true (it will be escaped by default)
    *   @param      bool        $viewMode       For use when not in view mode (likely in edit mode); it will allow you to retrieve the value of the field as you would if in view mode (instead of the int value for things like radios); default false
    *
    *   @return     var         The value of the field or null if it is not set or not found
    */
    public function GetFieldValue($tableName, $fieldName, $recordNum = 0, $escape = true, $viewMode = false) {
        if (isset($this->fields[$tableName][$recordNum][$fieldName])) {
            if ($this->mode == 'view') {
                if ($escape) return EscapeOutputForHtml($this->fields[$tableName][$recordNum][$fieldName]);
                else return $this->fields[$tableName][$recordNum][$fieldName];

            } else if ($viewMode) {
                $fieldValue = $this->GetFieldValueInView($tableName, $fieldName, $this->fields[$tableName][$recordNum][$fieldName]->GetValue(), $this->formData[$tableName][$fieldName]);

                if ($escape) return EscapeOutputForHtml($fieldValue);
                else return $fieldValue;

            } else {
                if ($escape) return EscapeOutputForHtml($this->fields[$tableName][$recordNum][$fieldName]->GetValue());
                else return $this->fields[$tableName][$recordNum][$fieldName]->GetValue();
            }
        } else {
            trigger_error('Input Error: Unable to find field value requested (table: ' . $tableName . ' field: ' . $fieldName . ' record_num: ' . $recordNum . ')', E_USER_ERROR);
            return null;
        }
    } // function GetFieldValue

    /**
    *   Returns the field's ID
    *
    *   @param      string      $tableName      name of table field is in
    *   @param      string      $fieldName      name of field
    *   @param      int         $recordNum      The record number of the field (counts from 0 of ids sent to object) (default: 0)
    *
    *   @return     var         The ID of the field, null if not found
    */
    public function GetFieldId($tableName, $fieldName, $recordNum = 0) {
        if (isset($this->fields[$tableName][$recordNum][$fieldName])) {
            if ($this->mode == 'view') {
                return $this->fields[$tableName][$recordNum][$fieldName];
            } else {
                return $this->fields[$tableName][$recordNum][$fieldName]->GetId();
            }
        } else {
            trigger_error('Input Error: Unable to find field ID requested (table: ' . $tableName . ' field: ' . $fieldName . ' record_num: ' . $recordNum . ')', E_USER_ERROR);
            return null;
        }
    } // function GetFieldId

    /**
    *   Sets the value of a field, overriding any value already existing
    *
    *   @param      var         $value          the value to set the field to (can be anything that will work for the type of field in ClaeroField)
    *   @param      string      $tableName      name of table field is in
    *   @param      string      $fieldName      name of field
    *   @param      int         $recordNum      The record number of the field (counts from 0 of ids sent to object) (default: 0)
    *
    *   @return     bool        true or false on success or failure
    */
    public function SetFieldValue($value, $tableName, $fieldName, $recordNum = 0) {
        if (isset($this->fields[$tableName][$recordNum][$fieldName]) && is_object($this->fields[$tableName][$recordNum][$fieldName])) {
            $this->fields[$tableName][$recordNum][$fieldName]->SetValue($value);
            return true;
        } else {
            trigger_error('Input Error: Unable to set the field value requested (table: ' . $tableName . ' field: ' . $fieldName . ' record_num: ' . $recordNum . ')', E_USER_ERROR);
            return false;
        }
    } // function SetFieldValue

    /**
    *   Helper function for ProcessIds, recursively try to find the ids of all related records using the relationships data
    */
    private function PopulateIds($childTableName, $childId, $count, &$id) {
        ++$count;
        if ($count > 20) exit; // just in case
        foreach ($this->relationships AS $tableName => $foreignData) {
            if (isset($foreignData['table_name']) && $foreignData['table_name'] == $childTableName) {
                // get the related record
                $sql = "SELECT {$foreignData['column_name']} FROM {$foreignData['table_name']} WHERE id = {$childId}";
                $query = $this->claeroDb->Query($sql);
                if ($query) {
                    $thisId = $query->GetOne();
                    $id[$foreignData['foreign_table']][0] = $thisId;
                    $this->PopulateIds($foreignData['foreign_table'],$thisId,0,$id);
                } else {
                    return false;
                } // if
            } // if
        } // foreach
    } // function PopulateIds

    /**
    *   Processes the ids
    *
    *   ProcessMetaData
    *
    *   we may get an id in the request vars, we may get it from the options, and we may have an array of ids in
    *   the case of 'edit multiple', and if we are dealing with a form(s) then we may have to get the ids for the
    *   non-primary table, etc.
    *
    *   @todo deal with field ordering, current if the include fields is set, then the tables and columns will be reordered based on it's order
    */
    private function ProcessIds($options) {

        // process the id(s)
        $id = array();

        // check options first, only valid for 'add' or 'insert', and not needed for 'save'
        if (isset($options['id'])) {
            if (is_array($options['id'])) {
                $id = $options['id']; // an array of table_name => array(id)
            } else {
                // one id was received, so we are only working with one set of data (maybe one table or custom form)
                // change it to an array with table name and look for any other ids
                if ($this->tableCount > 1) {
                    $id = array( $this->primaryTable => array($options['id']) ); // assign the primary table id
                    $this->PopulateIds($this->primaryTable,$options['id'],0,$id); // recursively loop through all tables and get the ids
                } else {
                    $id = array($this->formName => array($options['id']));
                }
            }
        } else {
            // in the case of save, get the ids from the raw data - if there are any - could be 'insert' case
            foreach ($this->formData as $tableName => $columns) {
                $tableData = $this->GetRecordData($tableName);
                foreach ($tableData as $recordNumber => $recordData) {
                    if (is_array($recordData) && isset($recordData['id'])) {
                        $id[$tableName][$recordNumber] = $recordData['id'];
                    } // if
                } // foreach
            } // foreach
        } // if

        // set the id within the options and remove any invalid ids
        $this->ids = $id;
        foreach ($this->ids as $tableName => $ids) {
            foreach ($ids as $key => $value) {
                if ($value === null || $value === 0 || $value === '0' || $value === false) {
                    unset($this->ids[$tableName][$key]);
                }
            }
        }

    } // function ProcessIds

    /**
    *   Populates the one to many and one to one relationship arrays
    */
    private function GetForeignRelationships() {

        if ($this->tableCount > 1) {

            // first get an array of the tables in the form
            $tables = array();
            foreach ($this->formData as $tableName => $columnData) {
                $tables[] = $tableName;
            }
            $tableList = $this->claeroDb->ImplodeEscape($tables);
            $relationshipSql = "
                SELECT * FROM `" . CLAERO_FOREIGN_TABLE . "` WHERE table_name IN (" . $tableList . ") AND foreign_table IN (" . $tableList . ")";
            //echo $relationshipSql;
            $relationshipQuery = $this->claeroDb->Query($relationshipSql);
            if ($relationshipQuery !== false) {
                while ($relationshipQuery->FetchInto($foreign)) {

                    switch ($foreign['table_name']) {
                        case 'multiple' :
                            $tableName = $foreign['table_name'];
                            $this->multipleRelationships[$tableName] = $foreign;

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
                            $tableName = $foreign['table_name'];
                            $this->singleRelationships[$tableName] = $foreign;
                            break;
                    } // switch

                    // set the foreign key column in the current table to be editable so it will be sent back
                    // also set the form_type to hidden so the foreign key can't be changed
                    $this->formData[$tableName][$foreign['column_name']]['edit_flag'] = true;
                    $this->formData[$tableName][$foreign['column_name']]['form_type'] = 'hidden';
                }
            } else {
                trigger_error('Query Error: Failed to retrieve any multiple relationships between tables: ' . $relationshipSql, E_USER_ERROR);
                $this->message[] = 'There was an error retrieving the all the data for this form. All the fields may not be displayed correctly.';
            } // if
        } // if
    } // function GetForeignRelationships

    /**
    *   Populates the fields within the form
    */
    private function PopulateFormValues($tableName, $recordNumber) {
        $this->formValues[$tableName] = array();

        // check how to populate the form field data values
        // load defaults (add similar, edit)
        if ($this->recordCount > 0 || $this->mode == 'edit_request') { // probably need to check to ensure the mode makes sense
            $this->formValues[$tableName] = $this->GetRecordData($tableName);

            // if we are edit_request mode, then don't get any data from the database
            if ($this->mode == 'edit_request') {
                return null;
            }

            $receivedValues = count($this->formValues[$tableName]);
            if (isset($this->multipleRelationships[$tableName])) {
                // there are multiple records
                $foreignData = $this->multipleRelationships[$tableName];
                $sql = "SELECT " . (isset($this->options['custom_select_expressions'][$tableName]) ? $this->options['custom_select_expressions'][$tableName] : '*') . " FROM `" . $this->claeroDb->EscapeString($foreignData['table_name']) . "` WHERE `" . $this->claeroDb->EscapeString($foreignData['column_name']) . "` = '" . $this->claeroDb->EscapeString($this->ids[$foreignData['foreign_table']][$recordNumber]) . "' ";
                if ($this->HasExpiryField($foreignData['table_name'])) {
                    $sql .= " AND (date_expired = 0 OR date_expired > NOW()) ";
                }
                $sql .= " ORDER BY id ASC";
                //echo $sql;
                $query = $this->claeroDb->Query($sql);
                if ( $query !== false ) {
                    // populate the formValues array with any existing values
                    $formValuesDb = array();
                    while ($query->FetchInto($values)) {
                        $formValuesDb[] = $values;
                    }
                    // determine how many values we found in the db
                    $dbValues = count($formValuesDb);
                    // if we received value and found vlaues, then merge them having the received override the db
                    if ($receivedValues > 0 && $dbValues > 0) {
                        $this->formValues[$tableName] = ArrayMergeClobber($formValuesDb, $this->formValues[$tableName]);
                    } else if ($dbValues > 0 && $receivedValues == 0) {
                        // we didn't receive any, but found some values so use the db values
                        $this->formValues[$tableName] = $formValuesDb;
                    } else if ($dbValues == 0 && $receivedValues == 0) {
                        $this->formValues[$tableName][] = array();
                    }
                } else {
                    trigger_error('Query Error: Failed to load multiple record from database for table ' . $tableName . ' and id ' . $this->ids[$foreignData['foreign_table']][$recordNumber] . ': ' . $sql, E_USER_ERROR);
                    $this->status = false;
                } //if

            } else if (isset($this->singleRelationships[$tableName])) {
                // there are single records in other tables we need to load
                $foreignData = $this->singleRelationships[$tableName];
                $sql = "SELECT " . (isset($this->options['custom_select_expressions'][$tableName]) ? $this->options['custom_select_expressions'][$tableName] : '*') . " FROM `" . $this->claeroDb->EscapeString($foreignData['table_name']) . "` WHERE `" . $this->claeroDb->EscapeString($foreignData['column_name']) . "` = '" . $this->claeroDb->EscapeString($this->formValues[$foreignData['foreign_table']][$recordNumber][$foreignData['foreign_column']]) . "'";
                //echo $sql;
                $query = $this->claeroDb->Query($sql);
                if ( $query !== false) {
                    if ($query->NumRows() === 1) {
                        $this->formValues[$foreignData['table_name']][$recordNumber] = $query->FetchRow();
                    } else {
                        $this->formValues[$foreignData['table_name']][$recordNumber][$foreignData['column_name']] = $this->formValues[$foreignData['foreign_table']][$recordNumber][$foreignData['foreign_column']];
                    }
                } else {
                    trigger_error('Query Error: Failed to load record from database for table ' . $tableName . ' and id ' . $this->ids[$foreignData['foreign_table']][$recordNumber] . ' (query failed or more than 1 record): ' . $sql, E_USER_ERROR);
                    $this->status = false;
                }
            } else {
                // populate data from existing record(s) (edit or add similar)
                if (count($this->ids[$tableName]) > 0) {
                    $sql = "SELECT " . (isset($this->options['custom_select_expressions'][$tableName]) ? $this->options['custom_select_expressions'][$tableName] : '*') . " FROM `" . $this->claeroDb->EscapeString($tableName) . "` WHERE id IN (" . $this->claeroDb->ImplodeEscape($this->ids[$tableName]) . ")";
                    $query = $this->claeroDb->Query($sql);
                    if ( $query !== false && $query->NumRows() > 0 ) {
                        // populate the formValues array with any existing values
                        while ($query->FetchInto($recordData)) {
                            $this->formValues[$tableName][$recordNumber] = $recordData;
                            ++$recordNumber;
                        }

                    } else {
                        if ($query === false) trigger_error('Query Error: Failed to load record from database for table ' . $tableName . ' and id ' . $this->ids[$tableName][$recordNumber] . ' (query failed): ' . $sql, E_USER_ERROR);
                        else trigger_error('Input Error: Failed to load record from database for table ' . $tableName . ' and id ' . $this->ids[$tableName][$recordNumber] . ' (0 records): ' . $sql, E_USER_ERROR);
                        $this->status = false;
                    } //if
                } else if ($this->primaryTable == $tableName) {
                    trigger_error('Input Error: To ids were pass to ClaeroEidt for loading (table ' . $tableName . ')', E_USER_ERROR);
                    $this->status = false;
                } else {
                    $this->formValues[$tableName][] = array();
                } // if
            } // if

            // search through form fields for 'password' type and set the password to empty
            // this avoids displaying the md5 hash of the password and the
            // potential to rehash the hashed password during the update
            if (isset($this->passwordColumns[$tableName])) {
                foreach ($this->passwordColumns[$tableName] as $columnName) {
                    foreach ($this->formValues[$tableName] as $recordNumber => $columnData) {
                        $this->formValues[$tableName][$recordNumber][$columnName] = '';
                    }
                } // foreach
            }

        // if load defaults is an array, then the load_defaults setting is in the meta data for every field
        } else if ( $this->mode == 'search' || (!$this->options['load_defaults'] && !is_array($this->options['load_defaults'])) ) {
            // populate the form with blank fields (search or 'don't load defaults' option)
            $this->formValues[$tableName][0] = array();
            foreach ($this->formData[$tableName] as $columnName2 => $columnData2) {
                $this->formValues[$tableName][0][$columnName2] = '';
            } // foreach

        } else if ($this->mode == 'add') {
            $this->formValues[$tableName][] = array();

        } else {
            // not 'edit' mode and id is empty - require id for view so throw exception
            //$result['statusMessage'] = "Missing record id for 'view' mode.";
            trigger_error('Input Error: Action is not a valid mode. No IDs have been received, but the action is not add.', E_USER_ERROR);
            $this->status = false;
        } // if
    } // function PopulateFormValues

    /**
    *   Creates the form in view mode
    */
    private function PrepareView() {

        foreach ($this->formData as $tableName => $columnData) {
            $recordNumber = 0;

            $this->PopulateFormValues($tableName, $recordNumber);

            foreach ($this->formValues[$tableName] as $recordNumber2 => $formValue) {
                // add additional rows, if mode is edit or add, is a multiple relationship table, and the user had requested additional fields
                if (isset($this->multipleRelationships[$tableName], $this->options['additional_multiple'][$tableName]) && $this->options['additional_multiple'][$tableName] > 0) {
                    $foreignData = $this->multipleRelationships[$tableName];
                    $foreignId = (isset($this->ids[$foreignData['foreign_table']][$recordNumber]) ? $this->ids[$foreignData['foreign_table']][$recordNumber] : null); // null will usually happen when we are adding
                    for ($i = 1; $i < $this->options['additional_multiple'][$tableName]; $i ++) {
                        $this->formValues[$tableName][] = array(
                            $foreignData['column_name'] => $foreignId,
                        );
                    } // for
                } // if
            }

            foreach ($this->formValues[$tableName] as $recordNumber2 => $formValue) {
                foreach ($columnData as $columnName => $metaRow) {

                    if (isset($metaRow['view_flag']) && $metaRow['view_flag']) {
                        // determine where to get the field value from
                        if ($this->recordCount > 0 && isset($formValue[$columnName])) {
                            // more than 0 records, there for use the data from record (pulled from db or passed in)
                            $fieldValue = $formValue[$columnName];
                        } else if ($metaRow['load_defaults']) {
                            // load defaults and 0 records, therefore use default
                            $fieldValue = $metaRow['form_value'];
                        } else {
                            // just null/empty
                            $fieldValue = null;
                        }

                        $this->fields[$tableName][$recordNumber2][$columnName] = $this->GetFieldValueInView($tableName, $columnName, $fieldValue, $metaRow);
                    } // if
                } // foreach
            } // foreach
        } // foreach
    } // function PrepareView

    /**
    *   Returns the value of the field formatted for use in field mode
    *   Used in PrepareView() and also GetFieldValue(), the later in $viewMode (when not in $this->mode == view)
    *
    *   @param  string      $tableName      The table containing the field
    *   @param  string      $fieldName      The name of the field
    *   @param  mixed       $fieldValue     The value of the field
    *   @param  array       $metaRow        The meta row used to determine how to format the field
    *
    *   @return string      The value of the field formatted for display in HTML
    */
    protected function GetFieldValueInView($tableName, $fieldName, $fieldValue, $metaRow) {
        switch ($metaRow['form_type']) {
            case 'select' :
            case 'select_grouped' :
            case 'radios' :
            case 'yes_no_radio' :
            case 'gender_radio' :
            case 'height_drop' :
            case 'number_drop' :
                $lookupValue = $this->FormatValueForDisplay($fieldValue, $metaRow['form_type'], $tableName, $fieldName, $metaRow);
                if (strlen($lookupValue) > 0) { // DH 20100221 - changed to not check for a null value because we are now escaping in FormatValueForDisplay() and nulls are escaped to an empty string
                    $fieldValue = $lookupValue;
                } else {
                    $fieldValue = $this->options['is_for_html'] ? '<span class="unknown">unknown</span>' : 'unknown';
                } //if
                break;

            case 'file' :
                if (strlen($fieldValue) > 0 && $this->options['is_for_html']) { // only prepare the link when there is a value and we want the HTML version
                    // there is any existing file
                    // determine the user's name of the file based on the original_filename column if it exists
                    if ($metaRow['file_options']['original_filename_column'] && isset($formValue[$metaRow['file_options']['original_filename_column']]) && $formValue[$metaRow['file_options']['original_filename_column']]) {
                        $fileName = $formValue[$metaRow['file_options']['original_filename_column']];
                    } else {
                        $fileName = $fieldValue;
                    }

                    // prepare a link to download the file
                    $link = '<a href="';
                    if ($metaRow['file_options']['private_flag']) {
                        $link .= $metaRow['file_options']['download_file'] . '?' . CLAERO_REQUEST_USER_ACTION . '=download&table_name=' . $tableName . '&column_name=' . $metaRow['column_name'] . '&record_id=' . $this->formValues[$tableName][$recordNumber2]['id'] . ($metaRow['file_options']['download_file_size'] != null ? '&size=' . $metaRow['file_options']['download_file_size'] : '') . '"';
                    } else {
                         $link .= $metaRow['file_options']['file_url'] . '/' . $fieldValue . '"';
                    }
                    $link .= ' title="Download: ' . $fileName . '" target="_blank">' . $fileName . '</a>';

                    $fieldValue = $link;
                } else if (strlen($fieldValue) == 0) {
                    $fieldValue = '';
                }
                break;

            default :
                $fieldValue = $this->FormatValueForDisplay($fieldValue, $metaRow['form_type']);
                break;
        } // switch

        return $fieldValue;
    } // function GetFieldValueInView

    /**
    *   Prepares edit form with meta data and ClaeroField, basically this function populates $this->fields
    *   HTML is contained within ClaeroField
    *   use GetHtml() to return a formated result, or GetField() to get individual fields
    *
    *   @todo   Make required js work
    *   @todo   Should the id field on an edit always be skipped or should be it be based on the meta data?
    */
    private function PrepareForm() {

        $addRowTable = claero::ProcessRequest('c_add_row', false);

        // process the fields one table at a time
        foreach ($this->formData as $tableName => $columnData) {

            $recordNumber = 0;
            $this->PopulateFormValues($tableName, $recordNumber);

            if ($addRowTable == $tableName && isset($this->multipleRelationships[$tableName]) && $this->mode == 'add_row') {
                $foreignData = $this->multipleRelationships[$tableName];
                $this->formValues[$tableName][] = array(
                    $foreignData['column_name'] => $this->ids[$foreignData['foreign_table']][$recordNumber],
                );
            }

            // add additional rows, if mode is edit or add, is a multiple relationship table, and the user had requested additional fields
            if (in_array($this->mode, array('add', 'edit')) && isset($this->multipleRelationships[$tableName], $this->options['additional_multiple'][$tableName]) && $this->options['additional_multiple'][$tableName] > 0) {
                $foreignData = $this->multipleRelationships[$tableName];
                $foreignId = (isset($this->ids[$foreignData['foreign_table']][$recordNumber]) ? $this->ids[$foreignData['foreign_table']][$recordNumber] : null); // null will usually happen when we are adding
                for ($i = 1; $i < $this->options['additional_multiple'][$tableName]; $i ++) {
                    $this->formValues[$tableName][] = array(
                        $foreignData['column_name'] => $foreignId,
                    );
                } // for
            } // if

            foreach ($this->formValues[$tableName] as $recordNumber2 => $formValue) {
                foreach ($columnData as $columnName => $metaRow) {
                    $this->PrepareFormField($tableName, $recordNumber2, $formValue, $columnName, $metaRow);
                } // foreach
            } // foreach
            if ($this->options['prepare_fields_without_values']) {
                foreach ($columnData as $columnName => $metaRow) {
                    if (!isset($this->fields[$tableName][0][$columnName])) {
                        $this->PrepareFormField($tableName, 0, null, $columnName, $metaRow);
                    }
                }
            }
            ++ $recordNumber;
        } // foreach ($this->formData as $tableName => $columnData)
    } // function PrepareForm

    /**
    *   Creates the ClaeroField object
    *
    *   @param      string      $tableName      the name of the table the field exists in
    *   @param      int         $recordNumber2  the record number from that table
    *   @param      array       $formValue      the array of values for the current record in the table
    *   @param      string      $columnName     the name of the table to prepare
    *   @param      array       $metaRow        the meta row to prepare the field from
    */
    private function PrepareFormField($tableName, $recordNumber2, $formValue, $columnName, $metaRow) {
        $claeroFieldObject = $this->claeroFieldObject;

        // skip the id column when performing an add
        // skip any fields that are not in edit when editing or adding
        // skip any fields that are not in search when searching
        if ($this->mode == 'add' && $columnName == 'id'
            || (($this->mode == 'edit' || $this->mode == 'edit_request' || $this->mode == 'add') && !$metaRow['edit_flag'])
            || ($this->mode == 'search' && !$metaRow['search_flag'])) return null;

        $recordId = isset($this->formValues[$tableName][$recordNumber2]['id']) ? $this->formValues[$tableName][$recordNumber2]['id'] : 0;

        // set up the parameters to create the field object
        $type = strtolower($metaRow['form_type']);
        if ($this->options['enable_post_array']) {
            $fieldName = CLAERO_REQUEST_RECORD . '[' . $tableName . '][' . $recordNumber2 . '][' . $metaRow['column_name'] . ']';
            $fieldId = $tableName . '_' . $metaRow['column_name'] . '_' . $recordNumber2;
        } else {
            $fieldName = $metaRow['column_name'];
            $fieldId = $metaRow['column_name'];
        }
        $options = array('id' => $fieldId);

        // determine where to get the field value from
        if (($this->recordCount > 0 || $this->mode == 'edit_request') && isset($formValue[$columnName])) {
            // more than 0 records, there for use the data from record (pulled from db or passed in)
            $fieldValue = $formValue[$columnName];
        } else if ($this->recordCount > 0 && $this->mode == 'edit_request') {
            $fieldValue = null;
        } else if ($metaRow['load_defaults']) {
            // load defaults and 0 records, therefore use default
            $fieldValue = $metaRow['form_value'];
        } else {
            // just null/empty
            $fieldValue = null;
        }

        if ($columnName == 'date_expired' && $fieldValue == null) {
            $fieldValue = false;
        }

        switch ($type) {
            case 'checkbox' :
            case 'phone' :
            case 'hidden' :
                // don't need to do anything for these
                break;

            case 'select' :
            case 'select_grouped' :
            case 'radios' :
                if ($metaRow['id_field'] != '') $options['source_id'] = $metaRow['id_field'];
                if ($metaRow['name_field'] != '') $options['source_value'] = $metaRow['name_field'];
                if ($metaRow['source_table'] != '') $options['source'] = $metaRow['source_table'];
                if (!isset($metaRow['select_none_flag']) || $metaRow['select_none_flag']) $options['select_none_flag'] = true;
                break;

            case 'height_drop' :
                if ($metaRow['id_field'] != '') $options['source_id'] = $metaRow['id_field'];
                if ($metaRow['name_field'] != '') $options['source_value'] = $metaRow['name_field'];
                $startEnd = explode('|', $metaRow['source_table']);
                if (count($startEnd) == 2) {
                    $options['height_start'] = $startEnd[0];
                    $options['height_end'] = $startEnd[1];
                }
                if (!isset($metaRow['select_none_flag']) || $metaRow['select_none_flag']) $options['select_none_flag'] = true;
                break;

            case 'number_drop' :
                if ($metaRow['id_field'] != '') $options['source_id'] = $metaRow['id_field'];
                if ($metaRow['name_field'] != '') $options['source_value'] = $metaRow['name_field'];
                $startEnd = explode('|', $metaRow['source_table']);
                $startEndCount = count($startEnd);
                if ($startEndCount == 2) {
                    $options['value_start'] = $startEnd[0];
                    $options['value_end'] = $startEnd[1];
                } else if ($startEndCount == 3) {
                    $options['value_start'] = $startEnd[0];
                    $options['value_end'] = $startEnd[1];
                    $options['value_increment'] = $startEnd[2];
                }
                if (!isset($metaRow['select_none_flag']) || $metaRow['select_none_flag']) $options['select_none_flag'] = true;
                break;

            case 'year_month' :
                if ($metaRow['id_field'] != '') $options['source_id'] = $metaRow['id_field'];
                if ($metaRow['name_field'] != '') $options['source_value'] = $metaRow['name_field'];
                $startEnd = explode('|', $metaRow['source_table']);
                if (count($startEnd) == 2) {
                    $options['year_start'] = $startEnd[0];
                    $options['year_end'] = $startEnd[1];
                }
                if (!isset($metaRow['select_none_flag']) || $metaRow['select_none_flag']) $options['select_none_flag'] = true;
                break;

            case 'checkboxes' :
                $fieldName .= '[]';
                if ($metaRow['id_field'] != '') $options['source_id'] = $metaRow['id_field'];
                if ($metaRow['name_field'] != '') $options['source_value'] = $metaRow['name_field'];
                if ($metaRow['source_table'] != '') $options['source'] = $metaRow['source_table'];
                if (!isset($metaRow['select_none_flag']) || $metaRow['select_none_flag']) $options['select_none_flag'] = true;
                break;

            case 'file' :
                $options['attributes'] = array('size' => $metaRow['field_size']);
                if (strlen($fieldValue) > 0 && $this->options['add_file_prefix']) {
                    // there is any existing file
                    // determine the user's name of the file based on the original_filename column if it exists
                    if ($metaRow['file_options']['original_filename_column'] && isset($formValue[$metaRow['file_options']['original_filename_column']]) && $formValue[$metaRow['file_options']['original_filename_column']]) {
                        $fileName = $formValue[$metaRow['file_options']['original_filename_column']];
                    } else {
                        $fileName = $fieldValue;
                    }

                    // prepare a link to download the file
                    $link = '<a href="';
                    if ($metaRow['file_options']['private_flag']) {
                        $link .= $metaRow['file_options']['download_file'] . '?' . CLAERO_REQUEST_USER_ACTION . '=download&table_name=' . $tableName . '&column_name=' . $metaRow['column_name'] . '&record_id=' . $recordId . ($metaRow['file_options']['download_file_size'] != null ? '&size=' . $metaRow['file_options']['download_file_size'] : '') . '"';
                    } else {
                         $link .= $metaRow['file_options']['file_url'] . '/' . $fieldValue . '"';
                    }
                    $link .= ' title="Download: ' . $fileName . '" target="_blank">' . $fileName . '</a>';

                    // create checkbox for the file
                    if ($this->options['enable_post_array']) {
                        $checkboxName = CLAERO_REQUEST_RECORD . '[' . $tableName . '][' . $recordNumber2 . '][' . $metaRow['column_name'] . '_remove_file]';
                    } else {
                        $checkboxName = $metaRow['column_name'] . '_remove_file';
                    }
                    $removeCheckbox = new ClaeroField('checkbox', $checkboxName, false, array('checkbox_display' => 'Remove existing file'));

                    // add some html to prefix the file upload field
                    $options['prefix_html'] = 'Existing File: ' . $link . HEOL;
                    $options['prefix_html'] .= $removeCheckbox->GetHtml() . HEOL;
                    $options['prefix_html'] .= 'Replace with: ';
                }
                break;

            case 'date_drop' :
                $options['select_one_flag'] = true;
                if (is_array($fieldValue)) {
                    $fieldValue = PrepareSpecialField('date_drop', $fieldValue);
                }
                $startEnd = explode('|', $metaRow['source_table']);
                if (count($startEnd) == 2) {
                    $options['year_start'] = $startEnd[0];
                    $options['year_end'] = $startEnd[1];
                }
                break;

            case 'date':
            case 'datetime':
            case 'datetime12' :
            case 'date_three_field' :
                if ($this->mode == 'edit_request' && $fieldValue == '') {
                    // because we are editing a request and the date field is empty we want to keep it empty by sending TRUE to ClaeroField which will not populate the field with the current date
                    $fieldValue = true;
                }
                break;

            case 'text_area' :
            case 'textarea' :
            case 'html' :
                $options['attributes'] = array('rows' => $metaRow['max_length'], 'cols' => $metaRow['field_size']);
                break;

            case 'table_select' :
                $options['select_none_flag'] = true;
                break;

            case 'password_confirm' :
                $options['password_confirm'] = array(
                    'name' => substr($fieldName, 0, -1) . '_confirm]',
                );
                // no break!!

            case 'password' :
                $fieldValue = '';
                // no break!!

            default :
                // text, password, hidden, textarea, etc.
                $options['attributes'] = array('size' => $metaRow['field_size'], 'maxlength' => $metaRow['max_length']);
                break;
        } // switch

        if (isset($metaRow['claero_field_options'])) $options = ArrayMergeClobber($options, $metaRow['claero_field_options']);

        if ($this->mode == 'search') $type .= '_search';

        // create the field object and add it to the field variable
        $options['claero_db'] = $this->claeroDb;
        $this->fields[$tableName][$recordNumber2][$columnName] = new $claeroFieldObject($type, $fieldName, $fieldValue, $options);
        //echo '<p>' . $metaRow['label']  . ' ' . $this->fields[$tableName][$columnName]->GetHtml() . '</p>';
    } // function PrepareFormField

    /**
    *   Creates the HTML for a form, in edit or view mode
    */
    private function GetFormHtml() {

        $html = ''; // holds the HTML that is returned from the function
        $javaScript = ''; // holds the javascript if validation is being used
        $hiddenFields = ''; // holds the hidden fields to be displayed at the end of the form
        $firstVisibleFieldName = '';
        $claeroFieldObject = $this->claeroFieldObject;

        if ($this->recordCount > 0) {
            $recordCount = $this->recordCount;
        } else if (in_array($this->mode, array('add', 'search'))) {
            $recordCount = 1;
        } else {
            $recordCount = 0;
        }

        $validateFlag = in_array($this->mode, array('edit', 'add', 'add_row')) ? true : false;
        switch ($this->mode) {
            case 'search' :
                $submitAction = 'Search';
                break;
            case 'view' :
                $submitAction = 'Return';
                break;
            default :
                $submitAction = 'Save';
                break;
        }

        // generate hidden form fields
        if (is_array($this->options['hidden'])) {
            foreach ($this->options['hidden'] as $fieldName => $value) {
                $this->hiddenFields[$fieldName] = new $claeroFieldObject('hidden', $fieldName, $value, array('id' => $fieldName));
            } // foreach
        } // if

        if ($this->options['user_action']) {
            $this->hiddenFields[CLAERO_REQUEST_USER_ACTION] = new $claeroFieldObject('hidden', CLAERO_REQUEST_USER_ACTION, $this->options['user_action'], array('id' => CLAERO_REQUEST_USER_ACTION));
        }

        // start the form
        if ($this->options['display_form_tag']) {
            $html .= '<form';
            $html .= ' method="' . $this->options['form_type'] . '" enctype="multipart/form-data" name="' . $this->options['form_name'] . '"';
            if ($this->options['form_id']) $html .= ' id="' . $this->options['form_id'] . '"';
            if ($this->options['form_url']) $html .= ' action="' . $this->options['form_url'] . '"';
            $onSubmitString = ($validateFlag) ? ' onSubmit="return Validate(this)"' : NULL;
            if ($this->options['on_submit_event']) $onSubmitString = ' onSubmit="' . $this->options['on_submit_event'] . '"'; // override built-in javascript
            $html .= $onSubmitString;
            $html .= '>' . EOL;
        }

        $hiddenFields .= '<input type="hidden" name="' . CLAERO_REQUEST_FORM_NAME . '" value="' . $this->formName . '" />' . EOL;

        if ($this->mode != 'view' && count($this->multipleRelationships) > 0) {
            $hiddenFields .= '<input type="hidden" name="c_add_row" id="c_add_row" value="" />' . EOL;
            $hiddenFields .= '<input type="hidden" name="' . CLAERO_REQUEST_USER_ACTION . '" value="add_row" />' . EOL;
        }

        // add optional (?) search parameters
        if ($this->mode == 'search') {
            $searchTypeOptions = array(
                'id' => CLAERO_REQUEST_SEARCH_TYPE,
                'clean_text' => false,
                'source' => array(
                    'AND' => '<em>all</em> of the following',
                    'OR' => '<em>any</em> of the following',
                ),
            );
            $searchType = new $claeroFieldObject('radios', CLAERO_REQUEST_SEARCH_TYPE, 'AND', $searchTypeOptions);
            $searchTypeHtml = $searchType->GetHtml();

            $likeTypeOptions = array(
                'id' => CLAERO_REQUEST_LIKE_TYPE,
                'clean_text' => false,
                'source' => array(
                    'beginning' => '<em>beginning</em> of the field',
                    'exact' => '<em>exact</em>',
                    'full_text' => '<em>full text</em>',
                ),
            );
            $likeType = new $claeroFieldObject('radios', CLAERO_REQUEST_LIKE_TYPE, 'beginning', $likeTypeOptions);
            $likeTypeHtml = $likeType->GetHtml();

            $html .= <<<EOA
    <div id="claeroTools"><fieldset id="search_type" style="border: 0; margin: 0;">
        Search with {$searchTypeHtml}<br />
        Search method: {$likeTypeHtml}
    </fieldset></div>
EOA;
        }
        // prepare javascript
        if ($this->mode != 'view') {
            $javaScript .= '<script language="JavaScript" type="text/javascript">' . EOL . '<!--//' . EOL;
            $javaScript .= 'function Validate(theForm) {' . EOL . '    var e = false;' . EOL;
            $javaScript .= '    if (cUserAction == "cancel") return true;' . EOL;
        }

        // array of tables for the edit form
        $formTables = array();

        $doHorz = ($this->recordCount > 1 && $this->options['multiple_edit_layout'] == 'horizontal') ? true : false;

        // for each record being edited
        $firstVisibleFieldId = false;
        $tableNum = 0;
        /*for ($i = 0; $i < $recordCount; $i++) {
            if ($i > 0 && $this->options['multiple_edit_layout'] == 'vertical') {
                ++$tableNum;
            }*/

            // process the fields one table at a time
            foreach ($this->formData as $tableName => $columnData) {

                $multipleTable = false;

                if (isset($this->multipleRelationships[$tableName])) {
                    // this a multiple record table, so create it's own table
                    ++$tableNum;
                    $multipleTable = true;
                } else if (isset($this->singleRelationships[$tableName]) || $this->options['multiple_edit_layout'] == 'vertical') {
                    // this is a new table, but not a multiple
                    // or multiple in vertical layout
                    ++$tableNum;
                }
                $columns = count($columnData) + 1;

                if (!isset($formTables[$tableNum])) $formTables[$tableNum] = new ClaeroTable($this->options['table_options']);

                if ($this->tableCount > 1) {
                    // there are more than 1 table, so add table name heading
                    if (isset($this->tableNames[$tableName])) $displayName = $this->tableNames[$tableName];
                    else $displayName = $tableName;
                    $rowNum = $formTables[$tableNum]->AddRow(array($displayName));
                    $formTables[$tableNum]->SetRowClass($rowNum, 'cTableHeading');
                    $formTables[$tableNum]->SetColSpan($rowNum, 0, $columns);
                }

                // process each column if there is a ClaeroField object
                if (isset($this->fields[$tableName][0])) {
                    $rowNumber = ($doHorz && isset($rowNumber) ? $rowNumber : 2);

                    if (($multipleTable || $doHorz) && $rowNumber === 2) {
                        // add empty cell for edit link column
                        $formTables[$tableNum]->AddCell(1, '&nbsp;');
                        // add column headings using the first row of data
                        foreach ($this->fields[$tableName][0] as $columnName => $field) {
                            if ($columnData[$columnName]['form_type'] != 'hidden' && $columnData[$columnName]['edit_flag']) {
                                $labelText = $this->options['replace_spaces'] ? str_replace(' ', '&nbsp;', $columnData[$columnName]['label']) : $columnData[$columnName]['label'];
                                $formTables[$tableNum]->AddCell(1, $labelText);
                            }
                        }
                        // set the header row class
                        $formTables[$tableNum]->SetRowClass(1, 'headerRow');

                    } else if ($this->recordCount > 1 && $this->options['multiple_edit_layout'] == 'vertical') {
                        $formTables[$tableNum]->AddCell(0, 'Record #' . ($i + 1));
                        $formTables[$tableNum]->SetColSpan(0,0);
                        $formTables[$tableNum]->SetRowClass(0, 'cTableHeading');
                    }

                    foreach ($this->fields[$tableName] as $recordNumber => $fields) {
                        if ($multipleTable) {
                            // add the edit links
                            $rowId = $tableName . '-' . $rowNumber;
                            $formTables[$tableNum]->AddCell($rowNumber, '<a href="javascript:RemoveMultipleRow(\'' . $rowId . '\', \'' . $tableName . '_' . $columnName . '_' . $recordNumber . '\');" title="Delete this row"><img src="/lib/claerolib_3/images/delete.png" width="16" height="16" border="0" alt="Delete this row" /></a>');
                            $formTables[$tableNum]->SetRowId($rowNumber, $rowId);
                        } else if ($doHorz) {
                            $formTables[$tableNum]->AddCell($rowNumber, 'Record #' . ($recordNumber + 1));
                        }

                        $col = 0;
                        foreach ($fields as $columnName => $field) {
                            $metaRow = $columnData[$columnName];

                            // save the hidden fields for the end and add the other fields to the table
                            if ($metaRow['form_type'] == 'hidden') {
                                if ($this->mode != 'view') $hiddenFields .= $field->GetHtml();
                            // add the field to the row because the headings have already been added
                            } else if ($multipleTable || $doHorz) {
                                if ($this->mode != 'view') {
                                    if ($metaRow['edit_flag']) {
                                        // set the tab index so the user can tab down each column
                                        $field->SetAttribute('tabindex', ($this->recordCount * $col) + $recordNumber);
                                        $formTables[$tableNum]->AddCell($rowNumber, $field->GetHtml());
                                    }
                                } else {
                                    $formTables[$tableNum]->AddCell($rowNumber, $field);
                                }
                            } else {
                                $labelText = $this->options['replace_spaces'] ? str_replace(' ', '&nbsp;', $metaRow['label']) : $metaRow['label'];
                                // set up default search options
                                if ($this->mode == 'search') {
                                    switch ($field->GetType()) {
                                        case 'date_search' :
                                        case 'datetime_search' :
                                        case 'date_drop_search' :
                                        case 'date_three_field_search' :
                                            $field->SetValue('empty'); //  make date fields start empty instead of today's date which is default
                                            break;
                                        case 'checkbox_search' :
                                            $field->SetValue('');
                                            break;
                                        default:
                                            break;
                                    }
                                }
                                if ($this->mode != 'view') {
                                    $formTables[$tableNum]->AddRow(array($labelText, $field->GetHtml()));
                                } else {
                                    $formTables[$tableNum]->AddRow(array($labelText, $field));
                                }
                            } // if

                            if ($this->mode != 'view') {
                                // set created form id name
                                $fieldId = $field->GetId();

                                // keep track of the first visible field for JavaScript
                                if ( !$firstVisibleFieldId && !in_array($metaRow['form_type'], array('checkbox', 'hidden')) ) {
                                    $firstVisibleFieldId = $fieldId;
                                } // if

                                // add JavaScript validation
                                if ($metaRow['required_flag']) {
                                    switch ($metaRow['form_type']) {
                                        case 'file':
                                            // file field is special case
                                            /* 20080226 CSN not done yet
                                            $javaScript .= "    if (theForm.existing_" . $fieldId . ".value == \"\" && theForm." . $metaRow['column_name'] . ".value == \"\") {" . EOL;
                                            $javaScript .= "        alert(\"Please enter a value for the \\\"" . $metaRow['label'] . "\\\" field.\");" . EOL;
                                            $javaScript .= "        theForm." . $metaRow['column_name'] . ".focus();" . EOL;
                                            $javaScript .= "        return (false);" . EOL;
                                            $javaScript .= "    }" . EOL;
                                            */
                                            break;
                                        // special handling for null value for the "select" type (drop down)
                                        default:
                                            $javaScript .= "    e = document.getElementById('" . $fieldId . "');" . EOL;
                                            $javaScript .= "    if (e.value == \"\") {" . EOL;
                                            $javaScript .= "        alert('Please enter a value for the \\'" . $metaRow['label'] . "\\' field" . ( $recordCount > 1 ? ' of record #' . $this->options[$tableName]['id'][$i] : '') . ".');" . EOL;
                                            $javaScript .= "        return false;" . EOL;
                                            $javaScript .= "    }" . EOL;
                                            if ($metaRow['form_type'] == 'select') {
                                                $javaScript .= "    if (e.value == \"null\") {" . EOL;
                                                $javaScript .= "        alert('Please select a value for the \\'" . $metaRow['label'] . "\\' field" . ( $recordCount > 1 ? ' of record #' . $this->options[$tableName]['id'][$i] : '') . ".');" . EOL;
                                                $javaScript .= "        return false;" . EOL;
                                                $javaScript .= "    }" . EOL;
                                            } // if
                                            break;
                                    } // switch
                                } // if

                                switch ($metaRow['form_type']) {
                                    case 'password_confirm':
                                        $javaScript .= "    var e1 = document.getElementById('" . $fieldId . "');" . EOL;
                                        $javaScript .= "    var e2 = document.getElementById('" . $fieldId . "_confirm');" . EOL;
                                        //$javaScript .= " alert(e1.value); alert(e2.value);";
                                        $javaScript .= "    if (e1.value != e2.value) {" . EOL;
                                        $javaScript .= "        alert('Confirmation password does not match. Please verify the passwords entered.');" . EOL;
                                        $javaScript .= "        e2.focus();" . EOL;
                                        $javaScript .= "        return false;" . EOL;
                                        $javaScript .= "    } " . EOL;
                                        break;
                                    case 'email_confirm':
                                        $javaScript .= "    var e1 = document.getElementById('" . $fieldId . "');" . EOL;
                                        $javaScript .= "    var e2 = document.getElementById('" . $fieldId . "_confirm');" . EOL;
                                        $javaScript .= "    if (e1.value != e2.value) {" . EOL;
                                        $javaScript .= "        alert('Confirmation email does not match. Please verify the email addresses entered.');" . EOL;
                                        $javaScript .= "        e2.focus();" . EOL;
                                        $javaScript .= "        return false;" . EOL;
                                        $javaScript .= "    } " . EOL;
                                        break;
                                } // switch
                            }

                            ++$col;
                        } // foreach ($fields as $columnName => $field)

                        ++$rowNumber;
                    } // foreach ($this->fields[$tableName] as $recordNumber => $fields)


                } else {
                    // error - missing meta data
                    $this->status = false;
                } // if

                if ($this->mode != 'view' && isset($this->multipleRelationships[$tableName])) {
                    $rowNum = $formTables[$tableNum]->AddRow(array('<a href="javascript:AddMultipleRow(\'' . $this->options['form_id'] . '\', \'' . $tableName . '\');"><img src="/lib/claerolib_3/images/add.png" width="16" height="16" border="0" alt="Add Row" title="Add Row" align="middle" />&nbsp;Add Row</a>'));
                    $formTables[$tableNum]->SetColSpan($rowNum, 0, $columns);
                } // if
            } // foreach ($this->formData as $tableName => $columnData)
        //} // for ($i = 0; $i < $recordCount; $i++)

        if ($this->mode != 'view') {
            // finish off the javascript code
            $javaScript .= "    return true;" . EOL;
            $javaScript .= "} // function Validate" . EOL . EOL;
            $javaScript .= "var cUserAction = 'submit';" . EOL;
            // add javascript to focus on first field
            if ($firstVisibleFieldId) {
                $javaScript .= "// position cursor in top form field" . EOL;
                $javaScript .= "var e = document.getElementById('" . $firstVisibleFieldId . "');" . EOL;
                $javaScript .= "if (e) { e.focus(); }" . EOL;
            }
            // finish off the javascript code
            $javaScript .= "//-->\n</script>" . EOL;
        } // if

        foreach ($this->hiddenFields as $field) {
            $hiddenFields .= $field->GetHtml() . EOL;
        }
        $html .= $hiddenFields;

        foreach ($formTables as $formTable) {
            $html .= $formTable->GetHtml();
        }

        if ($this->options['display_submit']) {
            // generate buttons
            $submitName = $this->options['user_action'] ? '' : CLAERO_REQUEST_USER_ACTION;
            $submitButton = new $claeroFieldObject('submit', $submitName, $submitAction, array('claero_db' => $this->claeroDb));
            //$submitButton = new $claeroFieldObject('submit', CLAERO_REQUEST_USER_ACTION, ucwords(strtolower($this->mode)), array('attributes' => array('on_click' => 'return Validate(this.form);')));
            if ($this->mode != 'view') $cancelButton = new $claeroFieldObject('submit', CLAERO_REQUEST_USER_ACTION, 'Cancel', array('claero_db' => $this->claeroDb, 'attributes' => array('on_click' => "cUserAction = 'cancel'")));

            $html .= '<div id="claeroTools">' . EOL;
            $html .= $submitButton->GetHtml() . '&nbsp;';
            if ($this->mode != 'view') $html .= $cancelButton->GetHtml();
            $html .= '</div>' . EOL;
        }
        if ($this->options['display_form_tag']) $html .= '</form>' . EOL;

        // add commenting and javascript
        $this->html .= EOL . EOL . '<!-- claerolib_3 ClaeroEdit GetHtml() begins -->' . EOL . EOL;
        $this->html .= $html;
        $this->html .= $validateFlag ? $javaScript : '';
        $this->html .= EOL . EOL . '<!-- claerolib_3 ClaeroEdit GetHtml() ends -->' . EOL . EOL;
    } // function GetFormHtml

    /**
    *   Saves record(s) running an InsertRecord() or UpdateRecord()
    *   If no ids, insert otherwise update
    *
    *   @return     bool/int    false if failed, if insert insert id, if update rows affected
    */
    private function SaveRecord() {

        $updateCount = $updateFailCount = $insertCount = $insertFailCount = $deleteCount = $deleteFailCount = 0;

        // go through each table first and insert the records, only one record and one table supported right now
        foreach ($this->formData as $tableName => $columns) {
            $recordData = $this->GetRecordData($tableName);
            foreach ($recordData as $recordNumber => $data) {
                if (isset($data['c_delete_flag']) && $data['c_delete_flag'] == '1') {
                    // this is the "delete a row from a multiple relationship" case

                    // if the id is not set, then it must be a new row that has not been saved yet, therefore don't do anything, otherwise delete the row from the db
                    if (isset($data['id']) && $data['id'] > 0) {
                        $deleteSql = "DELETE FROM `" . $this->claeroDb->EscapeString($tableName) . "` WHERE id = '" . $this->claeroDb->EscapeString($data['id']) . "' LIMIT 1";
                        $deleteQuery = $this->claeroDb->Query($deleteSql);
                        if ($deleteQuery === false) {
                            ++ $deleteFailCount;
                        } else if ($deleteQuery > 0) {
                            ++ $deleteCount;
                        }
                    }
                } else if (!$this->options['force_insert'] && ((isset($data['id']) && $data['id'] > 0) || (isset($this->ids[$tableName][$recordNumber]) && $this->ids[$tableName][$recordNumber] > 0))) {
                    // this is an update

                    // if the id is in the ids array then use it
                    if ((isset($this->ids[$tableName][$recordNumber]) && $this->ids[$tableName][$recordNumber] > 0)) $data['id'] = $this->ids[$tableName][$recordNumber];
                    if (isset($data['c_delete_flag'])) unset($data['c_delete_flag']); // remove the c_delete_flag used for multiple records
                    $update = $this->UpdateRecord($tableName, $columns, $recordNumber, $data);
                    if ($update !== false) {
                        ++ $updateCount;
                    } else {
                        ++ $updateFailCount;
                    }

                } else {
                    // this is an insert

                    if (isset($data['c_delete_flag'])) unset($data['c_delete_flag']); // remove the c_delete_flag used for multiple records
                    $insert = $this->InsertRecord($tableName, $columns, $recordNumber, $data);
                    if ($insert !== false) {
                        ++ $insertCount;
                    } else {
                        ++ $insertFailCount;
                    }
                }
            } // foreach
        } // foreach

        if ($updateFailCount > 0) $this->message[] = 'There was an error while saving ' . $updateFailCount . ' record' . ($updateCount != 1 ? 's' : '') . '.';
        if ($insertFailCount > 0) $this->message[] = 'There was an error while creating ' . $insertFailCount . ' new record' . ($updateCount != 1 ? 's' : '') . '.';
        if ($deleteFailCount > 0) $this->message[] = 'There was an error while deleting ' . $deleteFailCount . ' record' . ($updateCount != 1 ? 's' : '') . '.';
        if ($updateCount > 0) $this->message[] = $updateCount . ' record' . ($updateCount != 1 ? 's were' : ' was') . '  saved successfully.';
        if ($insertCount > 0) $this->message[] = $insertCount . ' record' . ($insertCount != 1 ? 's were' : ' was') . '  added successfully.';
        if ($deleteCount > 0) $this->message[] = $deleteCount . ' record' . ($deleteCount != 1 ? 's were' : ' was') . '  deleted successfully.';

    } // function SaveRecord

    /**
    *   Returns an array of data in format of array(record num => array(column = value)) for the received table
    *
    *   @param      string      $tableName      name of table to look for in raw data
    *
    *   @return     array       array of data for insert or update in the format of array(record num => array(column = value))
    */
    public function GetRecordData($tableName) {
        // look in the raw data for the information in 3 possible formats and then put it into the common format of:
        //      array(record num => array(column = value))
        if ($this->tableCount === 1 && isset($this->rawData[$tableName]) && !is_array($this->rawData[$tableName])) {
            // data in the format of column => value (no multiple tables)
            $recordData = array(0 => $this->rawData);
        } else if (isset($this->rawData[$tableName]) && is_array($this->rawData[$tableName])) {
            // data in the format of table => columns => value (1 or more tables)
            $recordData = array(0 => $this->rawData[$tableName]);
        } else if (isset($this->rawData['record'], $this->rawData['record'][$tableName]) && is_array($this->rawData['record'][$tableName])) {
            // data is likely from a post
            $recordData = $this->rawData['record'][$tableName];
        } else {
            if ($this->mode == 'saved') trigger_error('Input Error: The data for insert that was received is not in a format that SaveRecord understands. Please check the documentation for the valid formats.', E_USER_ERROR);
            $recordData = array();
        } // if

        return $recordData;
    } // function GetRecordData

    /**
    *   Inserts record(s) in db using $_POST (over supplied data), meta data, ClaeroField and ClaeroFile
    *
    *   @return     bool/int    false if failed, otherwise the insert id
    *
    *   @todo   figure out where and how to store the insert id
    */
    private function InsertRecord($tableName, $columns, $recordNumber, $data) {

        $return = false;
        $insertFieldNames = array();
        $insertFieldValues = array();
        $fileColumns = array();
        $fileColumnObjects = array();
        $destionationFilenamePossibilities = array('prepend', 'append', 'overwrite', 'overwrite_all'); // doesn't have keep and timestamp because neither modify the filename before sending it

        $numInsertFields = 0;

        if ($this->options['process_files']) {
            foreach ($columns as $columnName => $metaData) {
                if ((is_array($this->options['allowed_fields']) && isset($this->options['allowed_fields'][$tableName]) && !in_array($columnName, $this->options['allowed_fields'][$tableName])) // skip this field because it's NOT in the allowed fields
                || is_array($this->options['dont_allow_fields']) && isset($this->options['dont_allow_fields'][$tableName]) && in_array($columnName, $this->options['dont_allow_fields'][$tableName])) // skip this field because it's in the list of fields to be skipped
                    continue;

                // deal with files
                if ($metaData['form_type'] == 'file') {

                    // check to see if a file path has been passed and therefore copy the file
                    if (isset($data[$columnName])) {
                        if (isset($metaData['file_options']['filename_change']) && $metaData['file_options']['filename_change'] == 'id') continue; // skip because we don't need to worry about this type of filename change till after the record is inserted
                        // we have been passed a file path, so we want to copy the file
                        if (isset($metaData['file_options']['filename_change']) && in_array($metaData['file_options']['filename_change'], $destionationFilenamePossibilities)) {
                            $destinationFile = $metaData['file_options']['desination_file'];
                        } else {
                            $destinationFile = null; // default, no destination
                        }

                        $claeroFile = new ClaeroFile($metaData['file_options']);
                        // this line is key as it copies the file from the it's current location to the destination
                        $claeroFile->Copy($data[$columnName], $destinationFile);

                        if ($claeroFile->GetStatus() && $claeroFile->GetChange()) {
                            //PrintR($claeroFile->GetFileData());
                            $insertFieldNames[] = $columnName;
                            $insertValues[] = $claeroFile->GetDestFile();
                            ++$numInsertFields;
                            $fileColumns[] = $columnName;

                            if ($metaData['file_options']['original_filename_column'] != false) {
                                // insert original filename into the column designated by $metaData['file_options']['original_filename_column']
                                $insertFieldNames[] = $metaData['file_options']['original_filename_column'];
                                $insertValues[] = $claeroFile->GetFileData('user_file');
                                ++$numInsertFields;
                                $fileColumns[] = $metaData['file_options']['original_filename_column'];
                            } // if

                        } else if (!$claeroFile->GetStatus()) {
                            $this->status = false;
                            trigger_error('File System Error: Could not copy the file to it\'s new path: ' . $claeroFile->GetMessages(' '), E_USER_ERROR);
                            $this->message[] = $claeroFile->GetMessages();
                        } // if

                    } else {
                        if (isset($metaData['file_options']['filename_change']) && in_array($metaData['file_options']['filename_change'], $destionationFilenamePossibilities)) {
                            $destinationFile = $metaData['file_options']['desination_file'];
                        } else {
                            $destinationFile = null; // default, no destination
                        }

                        $claeroFile = new ClaeroFile($metaData['file_options']);
                        // this line is key as it selects the file from the $_FILES array based on the record number and column name
                        $claeroFile->Upload(array(CLAERO_REQUEST_RECORD, $tableName, $recordNumber, $columnName), $destinationFile);

                        if ($claeroFile->GetStatus() && $claeroFile->GetChange()) {
                            //PrintR($claeroFile->GetFileData());
                            $insertFieldNames[] = $columnName;
                            $insertValues[] = $claeroFile->GetDestFile();
                            ++$numInsertFields;
                            $fileColumns[] = $columnName;

                            if ($metaData['file_options']['original_filename_column'] != false) {
                                // insert original filename into the column designated by $metaData['file_options']['original_filename_column']
                                $insertFieldNames[] = $metaData['file_options']['original_filename_column'];
                                $insertValues[] = $claeroFile->GetFileData('user_file');
                                ++$numInsertFields;
                                $fileColumns[] = $metaData['file_options']['original_filename_column'];
                            } // if

                        } else if (!$claeroFile->GetStatus()) {
                            $this->status = false;
                            trigger_error('File System Error: Could not move uploaded file: ' . $claeroFile->GetMessages(' '), E_USER_ERROR);
                            $this->message[] = $claeroFile->GetMessages();
                        } // if
                    } // if isset data column

                    $fileColumnObjects[$tableName][$recordNumber][$columnName] = $claeroFile;
                } // if file
            } // foreach
        } // if

        if ($this->status) {
            if (is_array($data)) {
                $passedColumnNames = array_keys($data);
            } else {
                // failure: no post parameters
                trigger_error('Input Error: The data received was not in an array.', E_USER_ERROR);
                $this->message[] = 'There are no fields to update.';
                $this->status = false;
            } // if

            // loop through the fields to check for special field types because these will need to have data grabbed from different fields and combined into one
            $this->PrepareSpecialFields($tableName, $data);

            foreach ($columns as $columnName => $metaData) {
                // skip any they are not set and are not file columns (filename & original filename) and in the allowed columns or there are no allowed columns set and it's not in the dont_allow_fields or there are no dont_allow_fields set
                if (isset($data[$columnName]) && !in_array($columnName, $fileColumns)
                && ($this->options['allowed_fields'] == null || !isset($this->options['allowed_fields'][$tableName]) || in_array($columnName, $this->options['allowed_fields'][$tableName]))
                && ($this->options['dont_allow_fields'] == null || !isset($this->options['dont_allow_fields'][$tableName]) || !in_array($columnName, $this->options['dont_allow_fields'][$tableName]))) {

                    switch ($metaData['form_type']) {
                        case 'select' :
                        case 'select_grouped' :
                            $insertFieldNames[] = $columnName;
                            if ((!isset($metaData['select_none_flag']) || $metaData['select_none_flag']) && $data[$columnName] == 'none') {
                                $insertValues[] = ($this->options['select_default_0'] ? 0 : '');
                            } else {
                                $insertValues[] = $data[$columnName];
                            }
                            ++$numInsertFields;
                            break;
                        case 'password_confirm' :
                            // ensure the password fields are set and are the same, otherwise, don't make any changes
                            $confirmFieldName = $columnName . '_confirm';
                            if ( (!empty($data[$columnName]) && !empty($data[$confirmFieldName])) && ($data[$columnName] == $data[$confirmFieldName]) ) {
                                $insertFieldNames[] = $columnName;
                                $insertValues[] = ($this->options['md5_password'] ? md5($data[$columnName]) : $data[$columnName]);
                                ++$numInsertFields;
                            } // if
                            break;
                        case 'password':
                            $insertFieldNames[] = $columnName;
                            $insertValues[] = ($this->options['md5_password'] ? md5($data[$columnName]) : $data[$columnName]);
                            ++$numInsertFields;
                            break;
                        default :
                            $insertFieldNames[] = $columnName;
                            $insertValues[] = $data[$columnName];
                            ++$numInsertFields;
                            break;
                    }
                } // if
            } // foreach

            // if there are some valid fields, generate the SQL to do the insert
            if ($numInsertFields > 0) {

                $insertSql = "INSERT INTO `" . $this->claeroDb->EscapeString($tableName) . "` (";
                for ($i = 0; $i < $numInsertFields; $i++) {
                    if ($i != 0) $insertSql .= ', ';
                    $insertSql .= "`" . $this->claeroDb->EscapeString($insertFieldNames[$i]) . "`";
                } // for

                $insertSql .= " ) VALUES ( ";
                for ($i = 0; $i < $numInsertFields; $i++) {
                    if ($i != 0) $insertSql .= ', ';
                    if ($insertFieldNames[$i] == 'id' && !$this->options['include_id_in_insert']) {
                        $insertSql .= "''";
                    } else if ($insertValues[$i] === null) {
                        $insertSql .= "NULL";
                    } else {
                        $insertSql .= "'" . $this->claeroDb->EscapeString($insertValues[$i]) . "'";
                    }
                } // for

                $insertSql .= " ) ";
                //echo $insertSql;

                // preform the insert and check results
                $insertId = $this->claeroDb->Query($insertSql);
                if ($insertId !== false) {
                    // get the id of the new record
                    $this->insertId[$tableName][] = $insertId;
                    $return = 1;
                } else {
                    // error
                    trigger_error('Query Error: Failed to add new record to table "' . $tableName . '": ' . $this->claeroDb->GetLastQuery(), E_USER_ERROR);
                    $this->status = false;
                    $this->error[] = 'The new record was not created (query failure): ' . $insertSql;
                } // if

            } else {
                trigger_error('Input Error: The data received did not have any valid columns for table "' . $tableName . '" and record number "' . $recordNumber . '"', E_USER_ERROR);
                $this->status = false;
                $this->error[] = 'The new record was not created (no valid data received).';
            } // if
        } // if status

        // deal with files that need to be moved to their new ID based location
        if ($this->options['process_files'] && isset($insertId)) {
            foreach ($columns as $columnName => $metaData) {
                // the field must be a file type, it must have a filename_change type of id, the file object must be set and have a true status
                if ($metaData['form_type'] == 'file' && isset($metaData['file_options']['filename_change']) && $metaData['file_options']['filename_change'] == 'id' && isset($fileColumnObjects[$tableName][$recordNumber][$columnName]) && $fileColumnObjects[$tableName][$recordNumber][$columnName]->GetStatus()) {
                    // now need to move the file and update the record if the move is successful
                    $claeroFile = $fileColumnObjects[$tableName][$recordNumber][$columnName];
                    if (!$claeroFile->MoveToId($insertId)) {
                        $this->status = false;
                        trigger_error('File Error: The file could not be moved to the new ID based location', E_USER_ERROR);
                        $this->error[] = $claeroFile->GetMessages();
                    } else if ($claeroFile->GetDestFile() != '') {
                        // now we need to update the record
                        $fileUpdateSql = "UPDATE `" . $this->claeroDb->EscapeString($tableName) . "`
                            SET `" . $this->claeroDb->EscapeString($columnName) . "` = '" . $this->claeroDb->EscapeString($claeroFile->GetDestFile()) . "'
                            WHERE id = '" . $this->claeroDb->EscapeString($insertId) . "'";
                        $fileUpdateQuery = $this->claeroDb->Query($fileUpdateSql);
                        if ($fileUpdateQuery === false) {
                            $this->status = false;
                            trigger_error('Query Error: Failed to update the record with the ID based filename ' . $this->claeroDb->GetLastQuery(), E_USER_ERROR);
                            $this->error[] = 'The file was not attached to the record.';
                        } // if
                    } // if
                } // if
            } // foreach
        } // if

        return $return;
    } // function InsertRecord

    /**
    *   Updates record(s) in db using meta data, ClaeroField and ClaeroFile
    *
    *   @return     bool/int    false if failed, otherwise rows affected
    */
    private function UpdateRecord($tableName, $columns, $recordNumber, $data) {

        $return = false;

        $fileColumns = array();
        $updateData = array();
        $fileColumnObjects = array();
        $destionationFilenamePossibilities = array('prepend', 'append', 'overwrite', 'overwrite_all'); // doesn't have keep and timestamp because neither modify the filename before sending it

        // loop through the fields to check for special field types because these will need to have data grabbed from different fields and combined into one
        $this->PrepareSpecialFields($tableName, $data);

        if (!isset($data['id'])) {
            trigger_error('Input Error: No ID field received and therefore cannot update the record', E_USER_ERROR);
            $this->status = false;
            $this->error[] = "Update: The record was not updated (no valid data received).";
            return false;
        }

        $id = $data['id']; // store the id

        if ($this->options['process_files']) {
            foreach ($columns as $columnName => $metaData) {
                if ((is_array($this->options['allowed_fields']) && isset($this->options['allowed_fields'][$tableName]) && !in_array($columnName, $this->options['allowed_fields'][$tableName])) // skip this field because it's NOT in the allowed fields
                || is_array($this->options['dont_allow_fields']) && isset($this->options['dont_allow_fields'][$tableName]) && in_array($columnName, $this->options['dont_allow_fields'][$tableName])) // skip this field because it's in the list of fields to be skipped
                    continue;

                // deal with files
                if ($metaData['form_type'] == 'file') {
                    // check to see if a file path has been passed and therefore copy the file
                    if (isset($data[$columnName])) {
                        if (isset($metaData['file_options']['filename_change']) && $metaData['file_options']['filename_change'] == 'id') continue; // skip because we don't need to worry about this type of filename change till after the record is inserte
                        // check to see if the remove file field exists and is checked
                        if (isset($data[$columnName . '_remove_file']) && $data[$columnName . '_remove_file']) {
                            // add empty string for column
                            $updateData[$columnName] = '';
                            $fileColumns[] = $columnName;
                            // if the original filename column is also set for this file, remove it also
                            if ($metaData['file_options']['original_filename_column'] != false) {
                                $updateData[$metaData['file_options']['original_filename_column']] = '';
                                $fileColumns[] = $metaData['file_options']['original_filename_column'];
                            }

                            if ($metaData['file_options']['delete_files']) {
                                $this->DeleteFilesForRecord($tableName, $this->claeroDb->EscapeString($id), $columnName);
                            }
                        } // if

                        if (isset($metaData['file_options']['filename_change']) && in_array($metaData['file_options']['filename_change'], $destionationFilenamePossibilities)) {
                            $destinationFile = $metaData['file_options']['desination_file'];
                        } else {
                            $destinationFile = null; // default, no destination
                        }

                        $claeroFile = new ClaeroFile($metaData['file_options']);
                        // copy the file from the location that was passed to the new location
                        $claeroFile->Copy($data[$columnName], $destinationFile);

                        if ($claeroFile->GetStatus() && $claeroFile->GetChange()) {
                            //PrintR($claeroFile->GetFileData());
                            $updateData[$columnName] = $claeroFile->GetDestFile();
                            $fileColumns[] = $columnName;

                            if ($metaData['file_options']['delete_files']) {
                                // delete the existing file (can still select from the record because it hasn't been updated yet)
                                $this->DeleteFilesForRecord($tableName, $this->claeroDb->EscapeString($id), $columnName);
                            }

                            if ($metaData['file_options']['original_filename_column'] != false) {
                                // insert original filename into the column designated by $metaData['file_options']['original_filename_column']
                                $updateData[$metaData['file_options']['original_filename_column']] = $claeroFile->GetFileData('user_file');
                                $fileColumns[] = $metaData['file_options']['original_filename_column'];
                            } // if

                        } else if (!$claeroFile->GetStatus()) {
                            $this->status = false;
                            trigger_error('File System Error: Could not copy the file to it\'s new location: ' . $claeroFile->GetMessages(' '), E_USER_ERROR);
                        } // if

                    } else {
                        // check to see if the remove file field exists and is checked
                        if (isset($data[$columnName . '_remove_file']) && $data[$columnName . '_remove_file']) {
                            // add empty string for column
                            $updateData[$columnName] = '';
                            $fileColumns[] = $columnName;
                            // if the original filename column is also set for this file, remove it also
                            if ($metaData['file_options']['original_filename_column'] != false) {
                                $updateData[$metaData['file_options']['original_filename_column']] = '';
                                $fileColumns[] = $metaData['file_options']['original_filename_column'];
                            }

                            if ($metaData['file_options']['delete_files']) {
                                $this->DeleteFilesForRecord($tableName, $this->claeroDb->EscapeString($id), $columnName);
                            }
                        } // if

                        if (isset($metaData['file_options']['filename_change']) && in_array($metaData['file_options']['filename_change'], $destionationFilenamePossibilities)) {
                            $destinationFile = $metaData['file_options']['desination_file'];
                        } else {
                            $destinationFile = null; // default, no destination
                        }

                        $claeroFile = new ClaeroFile($metaData['file_options']);
                        // this line is key as it selects the file from the $_FILES array based on the record number and column name
                        $claeroFile->Upload(array(CLAERO_REQUEST_RECORD, $tableName, $recordNumber, $columnName), $destinationFile);

                        if ($claeroFile->GetStatus() && $claeroFile->GetChange()) {
                            //PrintR($claeroFile->GetFileData());
                            $updateData[$columnName] = $claeroFile->GetDestFile();
                            $fileColumns[] = $columnName;

                            if ($metaData['file_options']['delete_files']) {
                                // delete the existing file (can still select from the record because it hasn't been updated yet)
                                $this->DeleteFilesForRecord($tableName, $this->claeroDb->EscapeString($id), $columnName);
                            }

                            if ($metaData['file_options']['original_filename_column'] != false) {
                                // insert original filename into the column designated by $metaData['file_options']['original_filename_column']
                                $updateData[$metaData['file_options']['original_filename_column']] = $claeroFile->GetFileData('user_file');
                                $fileColumns[] = $metaData['file_options']['original_filename_column'];
                            } // if

                        } else if (!$claeroFile->GetStatus()) {
                            $this->status = false;
                            trigger_error('File System Error: Could not move uploaded file: ' . $claeroFile->GetMessages(' '), E_USER_ERROR);
                            $this->message = array_merge($this->message, $claeroFile->GetMessages('array'));
                        } // if
                    } // if data collumn

                    $fileColumnObjects[$tableName][$recordNumber][$columnName] = $claeroFile;
                } // if form_type file
            } // foreach columns
        } // if process files

        // loop through the valid table columns, if a post matches, and there is a value, then add to new array to insert
        $fileCounter = 1;
        foreach ($columns as $columnName => $metaData) {

            // skip file columns as they have been processed above and in the allowed columns or there are no allowed columns set and it's not in the dont_allow_fields or there are no dont_allow_fields set
            if (array_key_exists($columnName, $data) && !in_array($columnName, $fileColumns)
            && ($columnName == 'id' || $this->options['allowed_fields'] == null || !isset($this->options['allowed_fields'][$tableName]) || in_array($columnName, $this->options['allowed_fields'][$tableName]))
            && ($this->options['dont_allow_fields'] == null || !isset($this->options['dont_allow_fields'][$tableName]) || !in_array($columnName, $this->options['dont_allow_fields'][$tableName]))) {

                switch ($metaData['form_type']) {
                    case 'password_confirm' :
                        // ensure the password fields are set and are the same, otherwise, don't make any changes
                        $confirmFieldName = $columnName . '_confirm';
                        if ( !empty($data[$columnName]) && !empty($data[$confirmFieldName]) && ($data[$columnName] == $data[$confirmFieldName]) ) {
                            $updateData[$columnName] = md5($data[$columnName]);
                        } // if
                        break;
                    case 'password' :
                        if ( !empty($data[$columnName]) ) {
                            $updateData[$columnName] = md5($data[$columnName]);
                        }
                        break;
                    case 'select' :
                        if ($data[$columnName] === '') {
                            $updateData[$columnName] = ($this->options['select_default_0'] ? 0 : '');
                        } else {
                            $updateData[$columnName] = $data[$columnName];
                        }
                        break;
                    default :
                        if ($columnName != 'id') { // we already got the id above and it shouldn't be put into the update data (cause we don't want to change the ID on a record)
                            $updateData[$columnName] = $data[$columnName];
                        } // if
                } // switch

            } //if
        } // foreach

        // get the existing row
        $existingData = false;
        if (CLAERO_ONLY_UPDATE_CHANGED) {
            $existingSql = "SELECT * FROM `" . $this->claeroDb->EscapeString($tableName) . "` WHERE id = '" . $this->claeroDb->EscapeString($id) . "'";
            $existingQuery = $this->claeroDb->Query($existingSql);
            if ($existingQuery === false || $existingQuery->NumRows() != 1) {
                if ($existingQuery === false) trigger_error('Query Error: Failed to retrieve the existing data--updating all columns ' . $this->claeroDb->GetLastQuery(), E_USER_ERROR);
                else trigger_error('Input Error: 0 or more than 1 rows were found when getting existing data--updating all columns ' . $this->claeroDb->GetLastQuery(), E_USER_ERROR);
            } else {
                $existingData = $existingQuery->FetchRow();
            }
        }

        // if there are some valid fields, generate the SQL to do the update
        if (count($updateData) > 0 && isset($id)) {

            // generate the sql
            $updateSql = "UPDATE `" . $this->claeroDb->EscapeString($tableName) . "` SET ";
            $i = 0;
            foreach ($updateData as $field => $value) {
                // only update the field if (1) the existing query failed or (2) the value has changed
                if (!$existingData || (isset($existingData[$field]) && ($existingData[$field] != $value || $existingData[$field] === null))) {
                    if ($i > 0) $updateSql .= ', ';
                    $updateSql .= "`" . $this->claeroDb->EscapeString($field) . "` = ";
                    if ($value === null) {
                        $updateSql .= "NULL";
                    } else {
                        $updateSql .= "'" . $this->claeroDb->EscapeString($value) . "'";
                    }
                    ++$i;
                } // if
            } // for
            $updateSql .= " WHERE id = '" . $this->claeroDb->EscapeString($id) . "' LIMIT 1";
            //echo $updateSql;

            if ($i > 0) {
                // run the query and process the results
                $affectedRows = $this->claeroDb->Query($updateSql);
                if ($affectedRows === false) {
                    // there was an error while updating the record
                    $this->status = false;
                    trigger_error('Query Error: The update record query failed for table "' . $tableName . '" and record id "' . $id . '": ' . $this->claeroDb->GetLastQuery(), E_USER_ERROR);
                    $this->error[] = "Update: No records were affected (query failed): " . $updateSql;
                } else if ($affectedRows === 0) {
                    // no rows were changed
                    $this->error[] = "Update: No records were affected (no changes): " . $updateSql;
                    $this->affectedRows = $affectedRows;
                    $return = 0;
                } else if ($affectedRows === 1) {
                    // success! 1 row changed
                    $this->affectedRows = $affectedRows;
                    $return = 1;
                } // if
            } else {
                // success, although no rows were updated because there were no changes
                $this->affectedRows = 0;
                return 1;
            }

            // deal with files that need to be moved to their new ID based location
            if ($this->options['process_files']) {
                foreach ($columns as $columnName => $metaData) {
                    // the field must be a file type, it must have a filename_change type of id, the file object must be set and have a true status
                    if ($metaData['form_type'] == 'file' && isset($metaData['file_options']['filename_change']) && $metaData['file_options']['filename_change'] == 'id' && isset($fileColumnObjects[$tableName][$recordNumber][$columnName]) && $fileColumnObjects[$tableName][$recordNumber][$columnName]->GetStatus()) {
                        // now need to move the file and update the record if the move is successful
                        $claeroFile = $fileColumnObjects[$tableName][$recordNumber][$columnName];
                        if (!$claeroFile->MoveToId($id)) {
                            $this->status = false;
                            trigger_error('File Error: The file could not be moved to the new ID based location', E_USER_ERROR);
                            $this->error[] = $claeroFile->GetMessages();
                        } else if ($claeroFile->GetDestFile() != '') {
                            // now we need to update the record
                            $fileUpdateSql = "UPDATE `" . $this->claeroDb->EscapeString($tableName) . "`
                                SET `" . $this->claeroDb->EscapeString($columnName) . "` = '" . $this->claeroDb->EscapeString($claeroFile->GetDestFile()) . "'
                                WHERE id = '" . $this->claeroDb->EscapeString($id) . "'";
                            $fileUpdateQuery = $this->claeroDb->Query($fileUpdateSql);
                            if ($fileUpdateQuery === false) {
                                $this->status = false;
                                trigger_error('Query Error: Failed to update the record with the ID based filename ' . $this->claeroDb->GetLastQuery(), E_USER_ERROR);
                                $this->error[] = 'The file was not attached to the record.';
                            } // if
                        } // if
                    } // if
                } // foreach
            } // if

        } else if (!$this->options['ignore_empty_data']) {
            // error, no valid fields to update
            $this->status = false;
            $this->error[] = "Update: The record was not updated (no valid data received).";
        } // if

        return $return;

    } // function UpdateRecord

    /**
    *   Deletes or expires record and displays confirmation message
    *   No longer requires the expiry flag. Check to see if the table has a expiry_date column
    *
    *   @todo       option to disable
    *               is $this->affectedRows being used anywhere??
    *               support for multiple tables
    */
    public function DeleteRecord() {
        // make sure that user_action and confirm_delete are set
        $userAction = ProcessUserAction('dont\'t delete');
        $confirmDelete = strtolower(ProcessRequest(CLAERO_REQUEST_CONFIRM_DELETE, 'false'));
        $deleteId = ProcessRequest('id', '');

        $ids = $this->claeroDb->ImplodeEscape($this->ids[$this->formName]);
        $idText = (CLAERO_DEBUG ? "(id = '" . $ids . "') " : '');

        // get associated (will check to ensure the table is set to modify_foreign)
        $associatedTables = array();
        $this->GetAssociated($associatedTables, $this->formName, $ids);

        // 3 cases: del w/ confirm, (delete / expire), don't delete
        // if confirmDelete is nothing, that means user wasn't presented with confirmation yet
        if ($confirmDelete == 'true') {
            if ($userAction == 'delete') {
                $displayTable = $this->GetTableDisplay($this->formName);

                if ($this->options['file_options']['delete_files']) {
                    // we need to delete all the files related to this record
                    $this->DeleteFilesForRecord($this->formName, $ids);
                }

                $expire = $this->HasExpiryField();

                if ($expire) {
                    $deleteSql = "UPDATE `" . $this->claeroDb->EscapeString($this->formName) . "` SET `" . $this->claeroDb->EscapeString($this->options['date_expired_column']) . "` = NOW() WHERE id IN (" . $ids . ")";
                } else {
                    $deleteSql = "DELETE FROM `" . $this->claeroDb->EscapeString($this->formName) . "` WHERE id IN (" . $ids . ");";
                }

                $query = $this->claeroDb->Query($deleteSql);
                if ($query !== false) {
                    $this->html .= $query . ' record' . ($query != 1 ? 's' : '') . ' in "' . $displayTable . '" ' . ($query != 1 ? 'have' : 'has') . ' been ' . ($expire ? 'expired' : 'deleted') . '.' . HEOL;
                    $this->affectedRows += $query;
                } else {
                    // failure
                    trigger_error('Query Error: Failed to ' . ($expire ? 'expire' : 'delete') . ' the main record id ' . $ids . ' from ' . $this->formName . ': ' . $this->claeroDb->GetLastQuery(), E_USER_ERROR);
                    $this->html .= 'The record(s) in "' . $displayTable . '" were not ' . ($expire ? 'expired' : 'deleted') . ' because of an error.' . HEOL;
                    $this->error[] = 'Failed to ' . ($expire ? 'expire' : 'delete') . ' record (id: ' . $ids . ') from ' . $this->formName;
                    $this->status = false;
                } // if

                // check to see if the associated tables need to be deleted or expired
                foreach($associatedTables as $foreignTable => $tableDetail) {
                    if (strlen($tableDetail['ids']) > 0) {
                        $tableDisplay = $this->GetTableDisplay($foreignTable);

                        if ($this->options['file_options']['delete_files']) {
                            // we need to delete all the files related to this record
                            $this->DeleteFilesForRecord($foreignTable, $tableDetail['ids']);
                        }

                        // check to see if the table has an expiry field and the table is set to expiry
                        $expire = $this->HasExpiryField($foreignTable) && $tableDetail['expire_foreign_flag'] ? true : false;

                        // check to ensure the table has an expiry column and that the record should be expired
                        if ($expire) {
                            $associatedSql = "UPDATE `" . $foreignTable . "` SET `" . $this->claeroDb->EscapeString($this->options['date_expired_column']) . "` = NOW() WHERE id IN (" . $tableDetail['ids'] . ")";
                        } else {
                            $associatedSql = "DELETE FROM `" . $foreignTable . "` WHERE id IN (" . $tableDetail['ids'] . ")";
                        }

                        $assocaitedQuery = $this->claeroDb->Query($associatedSql);
                        if ($assocaitedQuery !== false) {
                            $this->html .= '&nbsp;&nbsp;' . $assocaitedQuery . ' associated record' . ($assocaitedQuery != 1 ? 's' : '') . ' in "' . $tableDisplay . '" ' . ($assocaitedQuery != 1 ? 'were' : 'was')  . ' ' . ($expire ? 'expired' : 'deleted') . '.' . HEOL;
                            $this->affectedRows += $assocaitedQuery;
                        } else {
                            // failure
                            trigger_error('Query Error: Failed to ' . ($expire ? 'expire' : 'delete') . ' foreign records in ' . $foreignTable . ': ' . $associatedSql, E_USER_WARNING);
                            $this->html .= 'Associated record(s) in "' . $tableDisplay . '" were not ' . ($expire ? 'expired' : 'deleted') . '.' . HEOL;
                            $this->error[] = 'Failed to ' . ($expire ? 'expire' : 'delete') . ' the associated record (id: ' . $tableDetail['ids'] . ') in ' . $tableDisplay;
                            $this->status = false;
                        } // if

                    }
                } // foreach

                $this->html .= 'A total of ' . $this->affectedRows . ' record' . ($this->affectedRows != 1 ? 's were' : ' was') . ' modified.' . HEOL;
            } else {
                $this->html .= 'The record(s) in "' . $this->GetTableDisplay($this->formName) . '" and any associted tables were not deleted or expired.' . HEOL;
            } // if

        } else {
            // find out how many records in the associated tables will be affected
            $affectedRowsHtml = '';
            foreach($associatedTables as $foreignTable => $tableDetail) {
                if (strlen($tableDetail['ids']) > 0) {
                    $sql = "SELECT COUNT(`" . $tableDetail['foreign_column'] . "`) AS num_records
                        FROM `" . $foreignTable . "`
                        WHERE `" . $tableDetail['foreign_column'] . "` IN (" . $tableDetail['ids'] . ")";
                    $query = $this->claeroDb->Query($sql);
                    if ($query !== false) {
                        while($query->FetchInto($recordCounts)) {
                            if ($recordCounts['num_records'] > 0) {
                                $affectedRowsHtml .= '&nbsp;&nbsp;' . $recordCounts['num_records'] . ' record' . ($recordCounts['num_records'] != 1 ? 's' : '') . ' in "' . $this->GetTableDisplay($foreignTable) . '" will be ' . ($tableDetail['expire_foreign_flag'] && $this->HasExpiryField($foreignTable) ? 'expired' : 'deleted') . '.' . HEOL;
                            }
                        } // while
                    } else {
                        // error in query
                        trigger_error('Query Error: Failed to retrieve the number of rows affected in a foreign table: ' . $sql, E_USER_WARNING);
                        $this->error[] = 'Unable to retrieve the number of records affected in a foreign table.';
                        $this->status = false;
                        $affectedRowsHtml .= 'Others records maybe affected by deleting or expiring this record, but there was an error while determining which ones.' . HEOL;
                    } // if
                } // if
            } // foreach

            $this->html .= EOL . '<div class="cStatus">' . EOL;
            if ($this->options['display_form_tag']) {
                $this->html .= '<form';
                $this->html .= ' method="' . $this->options['form_type'] . '" enctype="multipart/form-data" name="' . $this->options['form_name'] . '"';
                if ($this->options['form_id']) $this->html .= ' id="' . $this->options['form_id'] . '"';
                if ($this->options['form_url']) $this->html .= ' action="' . $this->options['form_url'] . '"';
                if ($this->options['on_submit_event']) $this->html .= ' onSubmit="' . $this->options['on_submit_event'] . '"';
                $this->html .= '>' . EOL;
            } // if

            $this->html .= 'Are you sure you want to delete this record? ' . EOL;
            $this->html .= '<input type="hidden" name="' . CLAERO_REQUEST_FORM_NAME . '" value="' . $this->formName . '" />' . EOL;
            $this->html .= '<input type="hidden" name="id" value="' . $deleteId . '" />' . EOL;
            $this->html .= '<input type="hidden" name="' . CLAERO_REQUEST_CONFIRM_DELETE . '" value="true" />' . EOL;
            $this->html .= '<input type="submit" name="' . CLAERO_REQUEST_USER_ACTION . '" value="Delete" />' . EOL;
            $this->html .= '<input type="submit" name="' . CLAERO_REQUEST_USER_ACTION . '" value="Don\'t Delete" />' . EOL;
            $this->html .= (strlen($affectedRowsHtml) > 0 ? HEOL : '') . $affectedRowsHtml;

            if ($this->options['display_form_tag']) {
                $this->html .= '</form>' . EOL;
            }
            $this->html .= '</div>' . EOL . EOL;
        }
    } // function DeleteRecord

    /**
    *   Deletes the files in all the file fields of the table or the 1 specified
    *   Looks for fields with form_type == 'file', selects the value of the field, combines with with the file_location and then attempts to unlink() the file
    *
    *   @param  string  $tableName  The table name to work on
    *   @param  string  $ids        Escaped list of ids to put directly into the SQL statement
    *   @param  string  $columnName The column name to look for the file name in; will ignore all other file fields in the table
    */
    private function DeleteFilesForRecord($tableName, $ids, $columnName = null) {
        // get and fields of file type
        $fileFields = array();
        if ($columnName === null) {
            foreach ($this->formData[$tableName] as $fieldName => $metaData) {
                if ($metaData['form_type'] == 'file') {
                    $fileFields[$fieldName] = $metaData;
                }
            } // foreach
        } else {
            $fileFields[$columnName] = $this->formData[$tableName][$columnName];
        } // if

        // create string of escaped table names
        $fileFieldsString = $this->claeroDb->ImplodeEscape(array_keys($fileFields), '`');

        // get the values of the fields
        $fileSql = "SELECT {$fileFieldsString} FROM `" . $this->claeroDb->EscapeString($this->formName) . "` WHERE id IN (" . $ids . ")";
        $fileQuery = $this->claeroDb->Query($fileSql);
        if ($fileQuery === false) {
            trigger_error('Query Error: Failed to get files for the records being deleted so no files will be deleted ' . $this->claeroDb->GetLastQuery(), E_USER_ERROR);
        } else if ($fileQuery->NumRows() == 0) {
            trigger_error('Input Error: The IDs sent cannot be found and therefore no files will be deleted ' . $this->claeroDb->GetLastQuery(), E_USER_ERROR);
        } else {
            // loop through rows and fields unlinking the fields if they aren't empty
            while ($fileQuery->FetchInto($fileRow)) {
                foreach ($fileFields as $columnName => $metaData) {
                    if ($fileRow[$columnName] != '') {
                        // there is a file mentioned, so try to delete it
                        if (!unlink($metaData['file_options']['file_location'] . '/' . $fileRow[$columnName])) {
                            trigger_error('File Error: Failed to delete the file associated to a record: ' . $metaData['file_options']['file_location'] . '/' . $fileRow[$columnName], E_USER_ERROR);
                        } // if
                    } // if
                } // foreach
            } // while
        } // if
    } // function DeleteFilesForRecord

    /**
    *   A recursive function to get any foreign tables that are delete or expire and then record ids within them
    *   *** IF THE TABLE IS SET TO MODIFY ASSOCIATED ***
    *   Returns the row within the foreign key table and the ids in the key 'ids' as a imploded array, escaped for SQL
    *   The objects status will be set to false if this fails
    *
    *   @param      array       &$associatedTables      The array of data, passed in by reference
    *   @param      string      $ids                    An **escaped** string of ids for using in SQL IN
    */
    private function GetAssociated(&$associatedTables, $tableName, $ids) {
        $firstColName = key($this->formData[$this->formName]);
        $tableName = $this->claeroDb->EscapeString($tableName);

        if ($this->formData[$this->formName][$firstColName]['modify_foreign']) {
            $sql = "SELECT cf.column_name, cf.foreign_table, cf.foreign_column, cf.delete_foreign_flag, cf.expire_foreign_flag
                FROM `" . $this->claeroDb->EscapeString(CLAERO_FOREIGN_TABLE) . "` AS cf
                WHERE cf.table_name = '" . $tableName . "'";
            $query = $this->claeroDb->Query($sql);
            if ($query !== false) {
                while($query->FetchInto($table)) {
                    // only proceed if the associated table needs to be deleted or modified and not already set
                    if (($table['delete_foreign_flag'] || $table['expire_foreign_flag']) && !isset($associatedTables[$table['foreign_table']])) {
                        // also populate the meta data for this table
                        $this->GetMetaData($table['foreign_table']);

                        $firstColName = key($this->formData[$table['foreign_table']]);

                        // only if it is set in the meta override to modify_foreign
                        if ($this->formData[$table['foreign_table']][$firstColName]['modify_foreign']) {
                            $associatedTables[$table['foreign_table']] = $table;
                            $associatedTables[$table['foreign_table']]['ids'] = '';

                            // now get the ids of the records we need to remove from this table
                            $idSql = "SELECT s.id
                                FROM `" . $tableName . "` AS p
                                    LEFT JOIN `" . $table['foreign_table'] . "` AS s ON (p.`" . $table['column_name'] . "` = s.id)
                                WHERE p.id IN (" . $ids . ")";
                            $idQuery = $this->claeroDb->Query($idSql);
                            if ($idQuery !== false) {
                                if ($idQuery->NumRows() > 0) {
                                    $subIds = $this->claeroDb->ImplodeEscape($idQuery->GetAllRows());
                                    $associatedTables[$table['foreign_table']]['ids'] = $subIds;

                                    // look for tables that are foreign to the current table
                                    $this->GetAssociated($associatedTables, $table['foreign_table'], $subIds);
                                }
                            } else {
                                trigger_error('Query Error: Failed to find affected records in foreign table: ' . $idSql, E_USER_WARNING);
                                $this->error[] = 'Failed to find affected records in foreign table: ' . $table['foreign_table'];
                                $this->status = false;
                            }
                        } // if modify_foreign
                    } // if
                } // while
            } else {
                // error in query
                $errorText = 'Unable to retrieve any relationship information for this table.';
                $this->error[] = $errorText . 'sql: ' . $sql;
                trigger_error('Query Error: ' . $errorText . ' ' . $sql, E_USER_ERROR);
                $this->message[] = $errorText;
            } // if
        }
    } // function GetAssociated

    /**
    *   Determines if the table has an expiry date field
    *   Uses $this->options['date_expired_column'] as the field name
    *
    *   @param      string      $tableName      The table name to look in, default is false and therefore $this->formName
    *
    *   @return     bool        true if there is an expiry field, false otherwise
    */
    public function HasExpiryField($tableName = false) {
        $hasExpiryField = false;
        if (!$tableName) {
            $tableName = $this->formName;
        }

        // loop through meta data and see if we have a expiry column
        foreach ($this->formData[$tableName] as $columnName => $data) {
            if (strtolower($columnName) == $this->options['date_expired_column']) {
                $hasExpiryField = true;
                break;
            }
        } // foreach

        return $hasExpiryField;
    } // function HasExpiryField

    /**
    *   Returns the id of the specified record inserted by the object
    *   If table and record number are no specified, then it will return the first record for of the primary table (or first table)
    *   If no records have been inserted, then null will be returned
    *
    *   @param      string      $tableName      the name of the table to get the insert id for
    *   @param      int         $recordNumber   the record number to get the insert id for
    *
    *   @return     int         the database id returned from InsertRecord
    */
    public function GetInsertId($tableName = false, $recordNumber = 0) {
        if (count($this->insertId) == 0) {
            // no records inserted
            return null;
        }

        if (!$tableName) {
            if (!$this->primaryTable) {
                reset($this->insertId);
                $tableName = key($this->insertId);
                if (isset($this->insertId[$tableName][$recordNumber])) return $this->insertId[$tableName][$recordNumber];
            } else {
                if (isset($this->insertId[$this->primaryTable][$recordNumber])) return $this->insertId[$this->primaryTable][$recordNumber];
            }
        } else {
            if (isset($this->insertId[$tableName][$recordNumber])) return $this->insertId[$tableName][$recordNumber];
        }

        return null;
    } // function GetStatus

    /**
    *   Returns the number of affected rows in the last query (if update or delete)
    *
    *   @return     int         the number of rows affected
    */
    public function GetAffectedRows() {
        return $this->affectedRows;
    } // function GetAffectedRows

    /**
    *   Outputs data regarding the fields in the object
    */
    public function GetFieldDebug() {
        echo '<pre>';
        foreach ($this->fields as $tableName => $records) {
            echo 'table: ' . $tableName . EOL;
            foreach ($records as $fields) {
                foreach ($fields as $name => $data) {
                    echo "\t" . 'field: ' . $name . EOL;
                    echo "\t\t" . 'type: ' . $data->GetType() . EOL;
                    echo "\t\t" . 'name: ' . $data->GetName() . EOL;
                    echo "\t\t" . 'id: ' . $data->GetId() . EOL;
                    echo "\t\t" . 'class: ' . $data->GetClass() . EOL;
                }
                break 1;
            }
        }
        echo '</pre>';
    } // function GetFieldDebug

} // class ClaeroEdit