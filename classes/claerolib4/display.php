<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
*   This file has the class ClaeroDisplay which is used for displaying records within the db using meta data
*
*   @author     Claero Systems <craig.nakamoto@claero.com> / XM Media Inc <dhein@xmmedia.net>
*   @copyright  Claero Systems / XM Media Inc  2004-2009
*   @version    $Id: class-claero_display.php 757 2010-04-28 17:00:39Z dgaudreault $
*/
//$libLoc = str_replace('/class-claero_display.php', '', __FILE__);
//require_once($libLoc . '/claero_config.php');
//require_once($libLoc . '/common.php');
//require_once($libLoc . '/class-claero_table.php');
//require_once($libLoc . '/class-claero.php');

/**
*   Displays records within the db using meta data
*
*   Example of use
*   $display = new ClaeroDisplay('table');
*   if ($display->GetStatus()) {
*       $bodyHtml .= $display->GetHtml();
*   } else {
*       $display->getError();
*   }
*
*   @author     Claero Systems <craig.nakamoto@claero.com> / XM Media Inc <dhein@xmmedia.net>
*   @copyright  Claero Systems / XM Media Inc  2004-2009
*
*   @see    class ClaeroError
*   @see    class ClaeroDb
*   @see    class Claero
*/
class Claerolib4_Display extends Claerolib4_Base {
    /**
    *   The current mode for the object
    *   @var    string
    */
    protected $mode = 'browse';

    /**
    *   Criteria used for search query, possibly populated directly out of the $_POST
    *   @var    array (false if not set)
    */
    protected $criteria = false;

    /**
    *   Search results from the query
    *   @var    array (false if not set)
    */
    protected $searchResults = array();

    /**
    *   Search SQL where clause
    *   @var    string
    */
    protected $searchFilterSql = '';

    /**
    *   The number of rows in the current display
    *   @var    int
    */
    protected $displayNumRows = 0;

    /**
    *   Prepares object setting ClaerDb and other properties of object
    *   uses options to ensure meta data exists and runs search or select query to get record data
    *
    *   @param  string  $formName   name of form or table to prepare/create
    *   @param  array   $options    array of options for object
    *       ['claero_db'] => ClaerDb object
    *       ['mode'] => 'browse' (generate html to view a page of results, default), 'csv' (create a csv file)
    *       ['ids'] => the ids to put into a CSV, if empty, the entire table will be put into the CSV
    *       ['criteria'] => array of fields with their values to place within a where clause for search
    *           (if not set and mode search, will use post)
    *       ['criteria_source'] => default 'post', can be 'get', 'criteria', 'request', or 'post'
    *       ['post_to'] => name of file to post to, otherwise just use current (without get parameters)
    *       ['file_options'] => array(
    *           options for use during file upload
    *           ['private_flag'] => true if the files are in a private location
    *           ['file_location'] => the location to put the file (without filename) (default: uploads folder in current directory)
    *           ['original_filename_column'] => the name of the column which contains the original name of the file, the users file name (default: original_filename)
    *           ['download_file'] => the file that runs ClaeroFile::Download() to stream the file to browser (default: PRIVATE_DOWNLOAD_FILE)
    *           ['doc_root'] => the root of the site so that private/public can be figured out (during download) (default: current directory)
    *           ['file_url'] => the url to directory where the uploads are contained when not doing private
    *       )
    *       ['text_area_br'] => used in FormatValueForDisplay() to determine if the nl lines should be changes to brs
    *       ['action_buttons'] => an array of which buttons/links to enable on each row or at the top
    *           ['edit'] => enables the edit link (default true)
    *           ['delete'] => enables the delete link (default true)
    *           ['add'] => enabled the add similar link (default true)
    *           ['detail'] => enables the detail/view link with the i icon (default true)
    *           ['view'] => enabled the detail/view link with the magnifing glass (default false)
    *           ['checkbox'] => enables the checkbox (default true)
    *           ['add_button'] => enabled the add button in the top row (default true)
    *           ['edit_button'] => enabled the edit button in the top row (default true)
    *           ['export'] => enables the export button in the top row (default false)
    *           ['search'] => enables the search button in the top row (default true)
    *       ['hide_top_row_buttons'] => if set to true, the top row (div#claeroTools) will be not be displayed (default false)
    *       ['custom_select_expressions'] => a custom select to change the default all fields/expressions in the display query *** These are NOT escaped ***
    *       ['apply_date_expired'] => if set to true (default) values that are expired will be hidden; false will not apply the date_expired column
    *       ['hidden_fields'] => an array of hidden fields to be added to the form when displaying a list of records; these will be sent through GET when add, search, or the edit button is clicked with multiple rows; the name will also be the id of the field
    *       ['generate_row_id'] => This will add the current data row id to the ID tag of the tr tag.
    *       ['row_id_prefix'] => This will allow to specify a custom row id prefix.
    *   @todo   add support for forms
    */
    public function __construct($formName, $options = array()) {

        parent::__construct($options);

        $this->formName = $formName;

        //$this->options = $options; // temporary until we clean this up and figure out how to do the display-specifc options

        // check to see if the page offset is not set in options, but is in a request
        if (!isset($options['page_offset']) && isset($_REQUEST['offset'])) {
            $options['page_offset'] = $_REQUEST['offset'];
        }
        // check to see if the num rows per page is not set in options, but is in a request
        if (!isset($options['page_max_rows']) && isset($_REQUEST['page_max_rows']) && $_REQUEST['page_max_rows'] != '') {
            $options['page_max_rows'] = $_REQUEST['page_max_rows'];
        }

        // set defaults if not passed
        $defaultOptions = array(
            'mode' => 'browse',
            'new_search_flag' => false,
            'page_offset' => 0,
            'page_max_rows' => DEFAULT_ROWS_PER_PAGE,
            'sort_by' => true,
            'sort_by_order' => '',
            'sort_by_column' => '',
            'field_filter' => array(),
            'other_actions' => false,
            'replace_spaces' => false,
            'display_nav_options' => array(),
            'action_buttons' => array(), // check below for a list of the default actions
            'action_buttons_custom' => array(),
            'hide_top_row_buttons' => false,
            'use_post_to_for_nav' => false,
            'nav_right' => false,
            'display_no_rows' => true,
            'add_numrows_dropdown' => false,
            'where_sql' => '',
            'custom_select_expressions' => '*',
            'checkmark_icons' => true,
            'post_to' => '',
            'post' => false,
            'text_area_br' => false,
            'ids' => false,
            'apply_date_expired' => true,
            'hidden_fields' => array(),
            'table_options' => array(), // these are merged with the defaults in GetHtml() and can be any option supported by ClaeroTable
            'file_options' => array(), // array of options merge with defaults for ClaeroFile, can be overriden with the 'override_meta' => 'file_options' => array()
            // these are used as a global value for all the fields
            'load_defaults' => true, // this overrides all the table & column specific settings, unless sent in as an option specifically for the table or column
            'claero_field_options' => array(), // settings to send to ClaeroField object for all fields, overriden by override_meta
            // these are used for overriding meta data
            'override_meta' => array(),
                // ^ this can have a sub array 'claero_field_options' that gets merged with the options determine within PrepareForm() overriding those values
                // ^ this can have a sub array 'file_options' that gets merged with the default and global ('file_options') options sending them to ClaeroFile
            'meta' => array(), // same as override_meta, just shorter; replaces all values of override_meta
            'include_fields' => array(),
            'exclude_fields' => array(),
            'generate_row_id' => false,
            'row_id_prefix' => '',
        );
        $this->SetObjectOptions($options, $defaultOptions);

        if (!$this->options['sort_by_order'] && !$this->options['sort_by_column']) {
            if (isset($_GET['sort_by_order'])) {
                $this->options['sort_by_order'] = $_GET['sort_by_order'];
            } else {
                $this->options['sort_by_order'] = 'DESC';
            }
            if (isset($_GET['sort_by_column'])) {
                $this->options['sort_by_column'] = $_GET['sort_by_column'];
            } else {
                $this->options['sort_by_column'] = 'id';
            }
        } // if

        $this->options['page_offset'] = intval($this->options['page_offset']); // do these for security reason so there can't be SQL inject because these values can't be escaped/quoted within the SQL
        $this->options['page_max_rows'] = intval($this->options['page_max_rows']);

        $this->mode = $this->options['mode'];

        if (!empty($this->options['meta'])) $this->options['override_meta'] = $this->options['meta'];

        $actionDefaults = array(
            'edit' => true,
            'delete' => true,
            'add' => true,
            'detail' => true,
            'view' => false,
            'checkbox' => true,
            'add_button' => true,
            'edit_button' => true,
            'export' => false,
            'search' => true,
        );
        $this->options['action_buttons'] = array_merge($actionDefaults, $this->options['action_buttons']);

        if (!isset($options['button_class'])) {
            $this->options['buttonClass'] = 'cSmallButton';
        } // if

        // set default file options (others are set in ClaeroFile class)
        $fileOptions = array(
            'private_flag' => false,
            'file_location' => 'uploads',
            'original_filename_column' => 'original_filename', // the location to store the user file name (if false, then it will not be stored)
            'download_file' => PRIVATE_DOWNLOAD_FILE,
            'file_url' => '',
        );
        $this->options['file_options'] = array_merge($fileOptions, $this->options['file_options']);

        if ($this->options['ids'] !== false) $this->ids = $this->options['ids'];

        // $options['']
        // $options['group_by']
        // $options['override_meta']
        // $options['button_class']
        // $options['action_buttons']
        // $options['action_buttons_custom']

        // Table options (optional) defaults set in GetHtml()
        // $options['table_id']
        // $options['width']
        // $options['spacer']
        // $options['transpose']
        // $options['debug']
        // $options['cellspacing']
        // $options['cellpadding']
        // $options['stripe']
        // $options['debug']

        // get the form data (single table meta data, or multiple tables if custom form
        $this->GetFormData($this->formName);
        $this->ProcessMetaData();

        $this->ProcessRelationships(); // process the foreign relationships, need to ProcessMetaData() first
        //PrintR($this->singleRelationships);
        //PrintR($this->multipleRelationships);
        //PrintR($this->relationships);

        // prepare the search results
        $this->PrepareResult();

    } // function __construct

    /**
    *   Performs search query and stores results in $this->searchResults
    *   use GetHtml() to return a formated result, or GetField() to get individual fields
    *   use DisplayNav to prepare navigation on results
    *
    *   @todo   only pull the fields from the table that we are actually displaying
    *   @todo   implement sorting for select type fields, so the sorting is done on the name field from the foreign table based on the meta data
    *   @todo   implement search for custom forms instead of just tables
    *   @todo   seems to perform a search when cancelling out of an edit form
    */
    protected function PrepareResult() {

        // set a default tablename for now, this is a hack
        $allTables = array_keys($this->formData);

        if ($this->tableCount > 1) {
            // we have more than 1 table, so we need to determine which is the primary table so we can use it to query
            $sql = "SELECT table_name
                    FROM `" . CLAERO_FORM_TABLE . "` AS f
                        LEFT JOIN `" . CLAERO_FORM_TABLE_TABLE . "` AS ft ON (ft.claero_form_id = f.id)
                    WHERE f.name = '" . $this->claeroDb->EscapeString($this->formName) . "'
                        AND ft.primary_flag = 1
                    LIMIT 1";
            //echo $sql;
            $query = $this->claeroDb->Query($sql);
            if ($query) {
                if ($query->NumRows() > 0) {
                    $this->primaryTable = $query->GetOne();
                } else {
                    $this->primaryTable = $allTables[0]; // use first table in form
                }
            } else {
                // query failed
                $this->status = false;
                trigger_error('Query Failed: Failed to retrieve the primary table: ' . $this->claeroDb->GetLastQuery(), E_USER_ERROR);
            }
        } else {
            // only 1 table, so use the first table in the list
            $this->primaryTable = $allTables[0]; // use first table in form
        }

        $i = 0;
        $tableName = $this->primaryTable;
        $searchOperand = 'AND';

        // establish search criteria if supplied
        if ($this->options['new_search_flag']) {

            $this->searchFilterSql = ''; // start with no search criteria

            // get the search criteria
            if (is_array($this->options['post'])) {
                $rawData = $this->options['post'];
            } else if (isset($_POST)) {
                $rawData = $_POST;
            } else {
                $rawData = array();
            }

            // make sure it looks like we have a valid search, if not, just get everything
            if (isset($rawData[CLAERO_REQUEST_RECORD]) && count($rawData[CLAERO_REQUEST_RECORD]) > 0) {

                // find out if we are searching by "AND" or "OR"
                $searchOperands = array('OR', 'AND');
                if (isset($rawData[CLAERO_REQUEST_SEARCH_TYPE]) && in_array($rawData[CLAERO_REQUEST_SEARCH_TYPE], $searchOperands)) {
                    $searchOperand = $rawData[CLAERO_REQUEST_SEARCH_TYPE];
                }

                // find out if we are searching by 'beginning', 'exact', 'full_text'
                $searchTypes = array('beginning', 'exact', 'full_text');
                if (isset($rawData[CLAERO_REQUEST_LIKE_TYPE]) && in_array($rawData[CLAERO_REQUEST_LIKE_TYPE], $searchTypes)) {
                    $searchType = $rawData[CLAERO_REQUEST_LIKE_TYPE];
                } else {
                    $searchType = 'beginning';
                }

                // generate the search SQL
                $op = 0;
                $notNull = 0;
                foreach ($this->formData as $tableName => $columns) {
                    $postData = $rawData['c_record'][$tableName][0];
                    $postColumns = array_keys($postData);
                    //PrintR($postData);
                    foreach ($columns as $columnName => $columnData) {
                        // make sure the post paramter is a column name
                        if (in_array($columnName, $postColumns) && $postData[$columnName] != '' && $postData[$columnName] != 'null') {

                            $skipSql = false;
                            $where = '';
                            switch($columnData['form_type']) {
                                case 'datetime' : // has
                                    $skipSql = true;
                                    // if it's an array, then it's been sent as a before & after (do special stuff!)
                                    if (is_array($postData[$columnName]) && isset($postData[$columnName]['date']) && $postData[$columnName]['date'] != '') { // not implemented:  && isset($postData[$columnName]['type']) && $postData[$columnName]['type'] == 'beforeAfter'
                                        $dateSearchStr = $postData[$columnName]['date'];

                                        $hourIsset = false;
                                        $minuteIsset = false;
                                        if (isset($postData[$columnName]['hour']) && $postData[$columnName]['hour'] != '') {
                                            $hourIsset = true;
                                            $hourAdd = isset($postData[$columnName]['modulation']) && $postData[$columnName]['modulation'] == 'pm' ? 12 : 0;
                                            $dateSearchStr .= ' ' . ($postData[$columnName]['hour'] + $hourAdd);
                                        }
                                        if (isset($postData[$columnName]['min']) && $postData[$columnName]['min'] != '') {
                                            $minuteIsset = true;
                                            if (!$hourIsset) $dateSearchStr .= ' 00';
                                            $dateSearchStr .= ':' . $postData[$columnName]['min'];
                                        } else if ($hourIsset && (!isset($postData[$columnName]['min']) || $postData[$columnName]['min'] == '')) {
                                            $dateSearchStr .= ':00';
                                        }
                                        if (isset($postData[$columnName]['sec']) && $postData[$columnName]['sec'] != '') {
                                            if (!$hourIsset) $dateSearchStr .= ' 00';
                                            if (!$minuteIsset) $dateSearchStr .= ':00';
                                            $dateSearchStr .= ':' . $postData[$columnName]['sec'];
                                        } else if (($hourIsset || $minuteIsset) && (!isset($postData[$columnName]['sec']) || $postData[$columnName]['sec'] == '')) {
                                            $dateSearchStr .= ':00';
                                        }

                                        $where = ' ' . ($i != 0 ? $searchOperand : '') . ' `' . $this->claeroDb->EscapeString($tableName) . '`.`' . $this->claeroDb->EscapeString($columnName) . "` = '" . $this->claeroDb->EscapeString($dateSearchStr) . "'";

                                    } else if (is_array($postData[$columnName]) && isset($postData[$columnName]['date']) && $postData[$columnName]['date'] == '' && ((isset($postData[$columnName]['hour']) && $postData[$columnName]['hour'] != '') || (isset($postData[$columnName]['minute']) && $postData[$columnName]['minute'] != '') || (isset($postData[$columnName]['second']) && $postData[$columnName]['second'] != ''))) {
                                        $this->message[] = 'The date search was ignored because no date was entered.';
                                    } // if
                                    break;

                                case 'select':
                                    if ($postData[$columnName] == 'none') continue 2;
                                case 'radio':
                                case 'date' :
                                case 'date_three_radio' :
                                    $op = '=';
                                    break;

                                case 'checkbox':
                                    $skipSql = true;
                                    $where = ' ' . ($i != 0 ? $searchOperand : '') . ' `' . $this->claeroDb->EscapeString($tableName) . '`.`' . $this->claeroDb->EscapeString($columnName) . '` = ';
                                    switch ($postData[$columnName]) {
                                        // empty string is either so don't add where clause
                                        case '1' : // checked
                                            $where .= ' 1 ';
                                            break;
                                        case '2' : // unchecked
                                            $where .= ' 0 ';
                                            break;
                                    }
                                    break;

                                default:
                                    $op = 'LIKE';
                                    break;
                            } // switch

                            if (!$skipSql) {
                                if ($i != 0) $this->searchFilterSql .= ' ' . $searchOperand;

                                $valuePrefix = $valueSuffix = '';
                                if ($op == 'LIKE') {
                                    // determine what and where the %'s should go
                                    switch ($searchType) {
                                        case 'beginning' :
                                            $valueSuffix = '%';
                                            break;
                                        case 'exact' :
                                            // no additions
                                            break;
                                        case 'full_text' :
                                            $valuePrefix = $valueSuffix = '%';
                                            break;
                                    }
                                } // if
                            }

                            if ($skipSql) {
                                // this has a specific sql created above
                                $this->searchFilterSql .= $where;
                            } else {
                                $this->searchFilterSql .= ' `' . $this->claeroDb->EscapeString($tableName) . '`.`' . $this->claeroDb->EscapeString($columnName) . '` ' . $op . ' "' . $valuePrefix . $this->claeroDb->EscapeString($postData[$columnName]) . $valueSuffix . '" ' . $where;
                            }
                            ++$i;
                        } //if
                    } // foreach
                } // foreach
            } // if

            // remember search sql for later
            $_SESSION[CLAERO_SESSION_CURRENT_SEARCH] = $this->searchFilterSql;
            $_SESSION[CLAERO_SESSION_CURRENT_TABLE] = $tableName; // $this->formName; // shouldn't all these references to tablename be to form name?

        } else {
            // use the stored search query if we are still dealing with the same table
            if (isset($_SESSION[CLAERO_SESSION_CURRENT_SEARCH]) && isset($_SESSION[CLAERO_SESSION_CURRENT_TABLE]) && $_SESSION[CLAERO_SESSION_CURRENT_TABLE] == $tableName) {
                $this->searchFilterSql = $_SESSION[CLAERO_SESSION_CURRENT_SEARCH];
            } else {
                $this->searchFilterSql = '';
                unset($_SESSION[CLAERO_SESSION_CURRENT_SEARCH]);
                unset($_SESSION[CLAERO_SESSION_CURRENT_TABLE]);
            } // if
        } // if

        if ($this->options['apply_date_expired'] && isset($this->formData[$tableName][CLAERO_EDIT_EXPIRY_COLUMN])) {
            // add the date expired column where values equal 0 (therefore not set) or are in the future (remove in the future)
            $this->searchFilterSql .= ($this->searchFilterSql != '' ? $searchOperand : '') . " (`" . $this->claeroDb->EscapeString(CLAERO_EDIT_EXPIRY_COLUMN) . "` = 0 OR `" . $this->claeroDb->EscapeString(CLAERO_EDIT_EXPIRY_COLUMN) . "` > NOW()) ";
        }

        if ($this->mode == 'csv' && isset($this->ids[$this->formName]) && is_array($this->ids[$this->formName]) && count($this->ids[$this->formName]) > 0) {
            // add additional id clause
            if ($i != 0) $this->searchFilterSql .= ' ' . $searchOperand;
            $this->searchFilterSql .= ' `' . $this->claeroDb->EscapeString($this->formName) . '`.`id` IN (' . $this->claeroDb->ImplodeEscape($this->options['ids'][$this->formName]) . ') ';
        }

        // append searchFilterSql to whereSql
        if (!empty($this->options['where_sql'])) {
            $this->searchFilterSql = !empty($this->searchFilterSql) ? $this->options['where_sql'] . ' AND ( ' . $this->searchFilterSql . ' )' : $this->options['where_sql'];
        } else {
            $this->searchFilterSql = $this->searchFilterSql != '' ? ' WHERE ' . $this->searchFilterSql : null;
        } // if

        if (!empty($this->options['group_by'])) {
            $this->searchFilterSql .= ' GROUP BY ' . $this->options['group_by'] . ' ';
        } // if

        // add sorting to search clause if optional sortByColumn is not empty
        $this->searchFilterSql .= ' ORDER BY ';
        if (!empty($this->options['sort_by_column'])) {
            $this->searchFilterSql .= $this->options['sort_by_column'] . ' ' . $this->options['sort_by_order'];
        } else {
            $this->searchFilterSql .= ' id DESC';
        } // if

        // get the data
        $searchSql = "SELECT " . $this->options['custom_select_expressions'] . " FROM `" . $this->claeroDb->EscapeString($tableName) . "` " . $this->searchFilterSql; // what about custom form instead of single table?

        // add LIMIT clause
        if ($this->mode == 'browse' && $this->options['page_max_rows'] != 0) $searchSql .= " LIMIT {$this->options['page_offset']},{$this->options['page_max_rows']} ";

        // do the search and make sure there is more than 1 row of data and populate the search results variable
        //echo $searchSql;
        $searchQuery = $this->claeroDb->Query($searchSql);
        if ($searchQuery !== false) {
            $this->displayNumRows = $searchQuery->NumRows();
            if ($searchQuery->NumFields() == 0 ) {
                // no results from query
                $this->error[] = "No results were retrieved from the table ($tableName).  The query was {$searchSql}.";
                $this->message[] = "No results were found.";
            } else {
                // good to go, populate the search results
                $this->searchResults = array();
                while ($searchQuery->FetchInto($temp)) {
                    $this->searchResults[] = $temp;
                } // while
                $this->message[] = "Your search was completed successfully.";
            } // if
        } else {
            // query failed
            trigger_error('Query Failed: The search query failed: ' . $searchSql, E_USER_ERROR);
            $this->status = false;
            $this->error[] = "Query failed.  The query was: $searchSql";
            $this->message[] = "The search query failed.";
        } // if (searchQuery)

        return $this->GetStatus();

    } // function PrepareResult

    /**
    *   Returns formated HTML of the view record or search results
    *   Uses ClaeroTable to display table
    *
    *   @return     string      HTML for display (possibly includes JS & CSS)
    *
    *   @todo   create a proper query that can order by and get all the data in one go instead of multiple queries
    */
    public function GetHtml() {
    
        //fire::log($this->options);

        $html = '';

        // set up table options for display
        // these options are merged with those sent in the options array into the object
        $tableOptions = array(
            'table_id' => 'claeroContent',
            'table_class' => 'claeroContent',
            'stripe' => true,
            'sort_by' => true,
        );
        $formType = array();
        $column = array();

        // set up first column
        if ($this->options['action_buttons']['checkbox']) {
            $tableOptions['heading'][] = '<input type="checkbox" id="c_check_all" onClick="MULTIPLE_EDIT = CheckAllCheckBoxes(\'claero_form\', \'ids[]\', this.checked); ClickMultipleEdit(this.checked);" title="Check All / Toggle" />';
        } else {
            $tableOptions['heading'][] = '&nbsp;';
        }
        $tableOptions['min_width'][0] = 15;
        $formType[] = null;
        $column[] = '';
        // loop through each of the action links/buttons adding 14 px to the first col width
        foreach ($this->options['action_buttons'] as $value) {
            // 20100707 CSN this is adding space for the action bar buttons and the column0 buttons, no good: if ($value) $tableOptions['min_width'][0] += 15;
        }
        $tableOptions['min_width'][0] += 90;
        foreach ($this->options['action_buttons_custom'] as $value) {
            if ($value) $tableOptions['min_width'][0] += 15;
        }

        // get the data to be displayed
        $i = -1;
       
        foreach ($this->formData as $tableName => $columns) {
            // for now only display the primary table in multiple relationships
            if (isset($this->multipleRelationships[$tableName])) {
                // don't display this data because it could contain multiple records which we have not even captured yet
            } else {
                foreach ($columns as $columnName => $columnData) {
                    if ($columnData['display_flag']) {
                        ++$i;
                        // record the meta data arrays to pass to DisplayTable
                        $column[] = $columnName;

                        // calculate the hyperlinked column title (for sorting)
                        $postToStrip = substr($this->options['post_to'], 0, strpos($this->options['post_to'], '?')); // strip existing GET params
                        $prepGet = claero::PrepareGetString(array('sort_by_column','sort_by_order')); // recreate GET params w/o sort_by_column & sort_by_order
                        $postToNew = strlen($prepGet) > 0 ? $postToStrip . '?' . $prepGet . '&' : '?' . $postToStrip; // assemble everything except sortByColumn
                        $postToNew .= 'sort_by_column=' . $columnName;
                        $postToNew .= '&sort_by_order=';
                        // determine if we are already sorting by this column and therefore need to sort in the opposite direction
                        $sortBy = !empty($this->options['sort_by_column']) && $this->options['sort_by_column'] == $columnName && !strcasecmp($this->options['sort_by_order'], 'ASC') ? 'DESC' : 'ASC';
                        $postToNew .= $sortBy;
                        $tableOptions['heading'][] = ($this->options['sort_by'] ? '<a href="' . $postToNew . '">' : '<a href="#">') . $columnData['label'] . '</a>';
                        $tableOptions['min_width'][] = $columnData['min_width'];

                        // set sort and order by options
                        if ($columnName == $this->options['sort_by_column']) {
                            $tableOptions['sort_by'] = $i + 1;
                            $tableOptions['order_by'] = $this->options['sort_by_order'];
                        }

                        $currentType = $columnData['form_type'];
                        $formType[] = $currentType;
                        $targetTableName = $tableName; // remember this for use later, will be tablename, or primary tablename if multiple tables
                    } // if
                } // foreach
            } // if
        } // foreach

        // check to see if all columns are not displayable?

        // need to create js variable containing the name of the user_action field
        $userActionName = CLAERO_REQUEST_USER_ACTION;
        $html .= <<<EOA
<script type="text/javascript">
var CLAERO_REQUEST_USER_ACTION = '{$userActionName}';
</script>
EOA;

        // start the results with a form
        $html .= '<form action="' . $this->options['post_to'] . '" method="get" name="claero_form" id="claero_form" style="margin-bottom:0; padding-bottom: 0;">' . EOL;
        if (!$this->options['hide_top_row_buttons']) {
            $html .= '<div id="claeroTools">' . EOL;
            $html .= '    <input type="hidden" name="id" id="id" value="0" />' . EOL;
        }

        // these will be overridden when we submit the form
        $html .= '    <input type="hidden" name="' . CLAERO_REQUEST_USER_ACTION . '" id="' . CLAERO_REQUEST_USER_ACTION . '" value="" />' . EOL;
        $html .= '    <input type="hidden" name="' . CLAERO_REQUEST_FORM_NAME . '" id="' . CLAERO_REQUEST_FORM_NAME . '" value="' . $this->formName . '" />' . EOL;

        // loop through the array of hidden fields
        foreach ($this->options['hidden_fields'] as $name => $value) {
            $html .= '    <input type="hidden" name="' . $name . '" id="' . $name . '" value="' . $value . '" />' . EOL;
        }

        if (!$this->options['hide_top_row_buttons']) {
            // set up the main buttons
            if ($this->options['action_buttons']['search'] == true) {
                $html .= '    <input type="button" value="SEARCH" onClick="SubmitSearch()"';
                $html .= !empty($this->options['button_class']) ? ' class="' . $this->options['button_class'] . '"' : ' class="cSmallButton"';
                $html .= ' />' . EOL;

                // set up clear search button
                if (!empty($_SESSION[CLAERO_SESSION_CURRENT_SEARCH])) {
                    $html .= '    <input type="button" value="CLEAR SEARCH" onClick="CancelSearch()"';
                    $html .= !empty($this->options['button_class']) ? ' class="' . $this->options['button_class'] . '"' : ' class="cSmallButton"';
                    $html .= ' />' . EOL;
                } // if
            }

            // set up action button
            if (($this->options['action_buttons']['add'] == true && $this->options['action_buttons']['add_button'] == true) || ($this->options['action_buttons']['add'] == false && $this->options['action_buttons']['add_button'] == true)) {
                $html .= '    <input type="button" value="ADD" onClick="SubmitAdd()"';
                $html .= !empty($this->options['button_class']) ? ' class="' . $this->options['button_class'] . '"' : ' class="cSmallButton"';
                $html .= ' />' . EOL;
            } // if

            // set up multiple edit button
            if (($this->options['action_buttons']['edit'] == true && $this->options['action_buttons']['checkbox'] == true) || $this->options['action_buttons']['edit_button'] == true) {
                $html .= '    <input type="button" value="EDIT" onClick="EditRecords()" id="submit_multiple_edit" disabled="disabled"';
                $html .= !empty($this->options['button_class']) ? ' class="' . $this->options['button_class'] . '"' : ' class="cSmallButton"';
                $html .= ' />' . EOL;
            } // if

            // set up multiple edit button
            if ($this->options['action_buttons']['export'] == true) {
                $html .= '    <input type="button" value="EXPORT" onClick="ExportRecords()" id="submit_multiple_edit" ';
                $html .= !empty($this->options['button_class']) ? 'class="' . $this->options['button_class'] . '"' : 'class="cSmallButton"';
                $html .= ' />' . EOL;
            } // if

            // set up other actions
            if ($this->options['other_actions'] != false) {
                if (! is_array($this->options['other_actions'])) { $this->options['other_actions'] = array($this->options['other_actions']); }
                foreach ($this->options['other_actions'] as $action) {
                    $html .= '    <input type="button" value="' . $action['value'] . '" onClick="' . $action['onclick'] . '" ' . (isset($action['id']) ? 'id="' . $action['id'] . '" ' : '');
                    $html .= !empty($this->options['button_class']) ? 'class="' . $this->options['button_class'] . '"' : 'class="cSmallButton"';
                    $html .= " />" . EOL;
                }
            }

            $html .= '</div>' . EOL;
        } // if hide top row buttons

        $navHtml = '<div class="claeroNav">';

        // display previous / next navigation if applicable
        // this will not work with forms, just tables
        $offset = $this->options['page_offset'];
        if (is_array($this->options['display_nav_options'])) { // $this->options['display_nav_options'] is always an array at this point, the default is array()
            $navOptions = $this->options['display_nav_options'];
        } else {
            $navOptions = array();
        } // if
        if (!isset($navOptions['results_per_page'])) $navOptions['results_per_page'] = $this->options['page_max_rows'];
        if (!isset($navOptions['table_name'])) $navOptions['table_name'] = $this->primaryTable;
        if (!isset($navOptions['where_sql'])) $navOptions['where_sql'] = $this->searchFilterSql;
        if ($this->options['use_post_to_for_nav']) $navOptions['post_to'] = $this->options['post_to'];
        if (!isset($navOptions['remove_tags'])) $navOptions['remove_tags'] = array();
        $navOptions['remove_tags'][] = 'page_max_rows';
        $navResult = array();
        $navHtml .= claero::DisplayNav($this->claeroDb, $offset, $navOptions, $navResult);
        $navHtml .= '<span style="float:right;">';
        if ($this->options['add_numrows_dropdown']) {
            $navHtml .= 'Display <select name="nav_num_rows[]" onchange="location.href=\'' . $this->options['post_to'] . $navResult['nav_url'] . '&page_max_rows=\' + this.options[this.selectedIndex].value">';
            $navHtml .= '<option value="" selected>select one</option><option value="50">50</option><option value="100">100</option><option value="500">500</option><option value="0">All</option>';
            $navHtml .= '</select>&nbsp;rows&nbsp;&nbsp;&nbsp;';
        } // if
        $navHtml .= 'Showing ' . $this->displayNumRows . ' of ' . $navResult['total_records'] . ' records';
        $navHtml .= '</span>';
        $navHtml .= '</div>';

        // option could be used to insert 'rows per page' drop down or similar
        if ($this->options['nav_right']) {
            $html .= EOL . '    <div style="display:block; float:right; text-align:right;">' . $this->options['nav_right'] . '</div>' . EOL;
        } // if
        $html .= EOL . $navHtml . EOL;

        if ($this->options['display_no_rows'] && $this->GetDisplayNumRows() == 0) {
            $html .= '<div class="noRows">0 rows found</div>';
        }

        // generate the table
        $this->options['table_options'] = claero::ArrayMergeClobber($tableOptions, $this->options['table_options']);
        $contentTable = new claerotable($this->options['table_options']);

        //check to see if a row_id_prefix was supplied and build the rowId Prefix accordingly
        if ($this->options['row_id_prefix'] != ''){
            $rowId = $this->options['row_id_prefix'];
        } else {
            $rowId = $tableName . 'Row';
        }

        // prepare the data values to generate the results table
        $j = 0;
        //while ($searchData = db_fetch_assoc($searchQuery)) {
        foreach ($this->searchResults as $searchData) {

            // first add our extra column at the beginning with the buttons
            $id = $searchData['id'];
            $newRow = '';

            //check to see if we want to generate a row id, if so, add it to our table
            if ($this->options['generate_row_id']){
                $contentTable->SetAttribute($j, false, 'id="' . $rowId . '-' . $id . '"');
            }

            // add custom buttons
            foreach ($this->options['action_buttons_custom'] as $customButtonAction => $customButtonInformation) {
                if (isset($customButtonInformation['target'])){
                    $target = "{$customButtonInformation['target']}";
                } else {
                    $target = '_self';
                }
                $newRow .= "<a href=\"javascript:RecordCustom('{$id}','{$customButtonAction}','{$target}');\" title=\"{$customButtonInformation['title']}\" class=\"{$customButtonInformation['buttonIconClass']}\">&nbsp;</a>";
            }

            // add 'start of row' buttons as dictated by $this->options['action_buttons'] array:
            if ($this->options['action_buttons']['view']) {
                $newRow .= '<a href="?' . CLAERO_REQUEST_FORM_NAME . '=' . $this->formName . '&' . CLAERO_REQUEST_USER_ACTION . '=view&id=' . $id . '" title="View this record" class="magnify">&nbsp;</a>';
            } // if

            if ($this->options['action_buttons']['detail']) {
                $newRow .= '<a href="?' . CLAERO_REQUEST_FORM_NAME . '=' . $this->formName . '&' . CLAERO_REQUEST_USER_ACTION . '=view&id=' . $id . '" title="View this record" class="view">&nbsp;</a>';
            } // if

            if ($this->options['action_buttons']['edit']) {
                $newRow .= '<a href="?' . CLAERO_REQUEST_FORM_NAME . '=' . $this->formName . '&' . CLAERO_REQUEST_USER_ACTION . '=edit&id=' . $id . '" title="Edit this record" class="edit">&nbsp;</a>';
            }

            if ($this->options['action_buttons']['delete']) {
                $newRow .= '<a href="?' . CLAERO_REQUEST_FORM_NAME . '=' . $this->formName . '&' . CLAERO_REQUEST_USER_ACTION. '=delete&id=' . $id . '" title="Delete this record" class="delete">&nbsp;</a>';
            }

            if ((($this->options['action_buttons']['add'] == true) && ($this->options['action_buttons']['add_button'] == true)) || (($this->options['action_buttons']['add'] == true) && ($this->options['action_buttons']['add_button'] == false))) {
                $newRow .= '<a href="?' . CLAERO_REQUEST_FORM_NAME . '=' . $this->formName . '&' . CLAERO_REQUEST_USER_ACTION . '=add&id=' . $id . '" title="Add new record" class="add">&nbsp;</a>';
            }

            // multiple edits
            if ((($this->options['action_buttons']['edit'] == false) && ($this->options['action_buttons']['checkbox'] == true)) || (($this->options['action_buttons']['edit'] == true) && ($this->options['action_buttons']['checkbox'] == true))) {
                $newRow .= '<input type="checkbox" name="ids[]" onclick="ClickMultipleEdit(this.checked);" value="' . $id . '" />';
            } // if
/*  20090410 CSN maybe we should change this to a list?
            // first add our extra column at the beginning with the buttons
            $id = $searchData['id'];
            $newRow = '<ul class="rowActions">';

            // add custom buttons
            foreach ($this->options['action_buttons_custom'] as $customButtonAction => $customButtonInformation) {
                if (isset($customButtonInformation['target'])){
                    $target = "{$customButtonInformation['target']}";
                } else {
                    $target = '_self';
                }
                $newRow .= "<li><a href=\"javascript:RecordCustom('$id','$customButtonAction','$target');\" title=\"{$customButtonInformation['title']}\" class=\"{$customButtonInformation['buttonIconClass']}\">&nbsp;</a></li>";
            }

            // add 'start of row' buttons as dictated by $this->options['action_buttons'] array:
            if ($this->options['action_buttons']['view']) {
                $newRow .= '<li><a href="?' . CLAERO_REQUEST_FORM_NAME . '=' . $this->formName . '&' . CLAERO_REQUEST_USER_ACTION . '=view&id=' . $id . '" title="View this record" class="magnify"></a></li>';
            } // if

            if ($this->options['action_buttons']['detail']) {
                $newRow .= '<li><a href="?' . CLAERO_REQUEST_FORM_NAME . '=' . $this->formName . '&' . CLAERO_REQUEST_USER_ACTION . '=view&id=' . $id . '" title="View this record" class="view"></a></li>';
            } // if

            if ($this->options['action_buttons']['edit']) {
                $newRow .= '<li><a href="?' . CLAERO_REQUEST_FORM_NAME . '=' . $this->formName . '&' . CLAERO_REQUEST_USER_ACTION . '=edit&id=' . $id . '" title="Edit this record" class="edit"></a></li>';
            }

            if ($this->options['action_buttons']['delete']) {
                $newRow .= '<li><a href="?' . CLAERO_REQUEST_FORM_NAME . '=' . $this->formName . '&' . CLAERO_REQUEST_USER_ACTION. '=delete&id=' . $id . '" title="Delete this record" class="delete"></a></li>';
            }

            if ((($this->options['action_buttons']['add'] == true) && ($this->options['action_buttons']['add_button'] == true)) || (($this->options['action_buttons']['add'] == true) && ($this->options['action_buttons']['add_button'] == false))) {
                $newRow .= '<li><a href="?' . CLAERO_REQUEST_FORM_NAME . '=' . $this->formName . '&' . CLAERO_REQUEST_USER_ACTION . '=add&id=' . $id . '" title="Add new record" class="add"></a></li>';
            }

            // multiple edits
            if ((($this->options['action_buttons']['edit'] == false) && ($this->options['action_buttons']['checkbox'] == true)) || (($this->options['action_buttons']['edit'] == true) && ($this->options['action_buttons']['checkbox'] == true))) {
                $newRow .= '<li><input type="checkbox" name="ids[]" onclick="ClickMultipleEdit(this.checked);" value="' . $id . '" /></li>';
            } // if
            $newRow .= '</ul>';
*/
            $data[$j][0] = $newRow;
            $i = 0;

            // then add the rest of the data fields from the query
            foreach ($this->formData as $tableName => $columns) {
                // for now only display the primary table in multiple relationships
                if (isset($this->multipleRelationships[$tableName])) {
                    // don't display this data because it could contain multiple records which we have not even captured yet
                } else {
                    foreach ($columns as $columnName => $metaRow) {
                        if ($metaRow['display_flag']) {

                            ++$i;
                            $currentData = (isset($searchData[$columnName])) ? $searchData[$columnName] : ''; // 20080830 CSN not sure about this, was generating errors so I added in the isset

                            switch ($metaRow['form_type']) {
                                case 'select' :
                                case 'radios' :
                                case 'yes_no_radio' :
                                case 'gender_radio' :
                                case 'select_grouped' :
                                    $lookupValue = $this->FormatValueForDisplay($currentData, $metaRow['form_type'], $tableName, $columnName, $metaRow);
                                    if ($lookupValue !== null) {
                                        $data[$j][$i] = $lookupValue;
                                    } else {
                                        $data[$j][$i] = '<span class="unknown">unknown</span>';
                                    } //if
                                    break;

                                case 'file' :
                                    $currentColumn = $column[$i];

                                    if ($metaRow['file_options']['original_filename_column'] && isset($searchData[$metaRow['file_options']['original_filename_column']]) && $searchData[$metaRow['file_options']['original_filename_column']]) {
                                        $fileName = $searchData[$metaRow['file_options']['original_filename_column']];
                                    } else {
                                        $fileName = $currentData;
                                    }

                                    // prepare a link to download the file
                                    $data[$j][$i] = '<a href="';
                                    if ($metaRow['file_options']['private_flag']) {
                                        $data[$j][$i] .= $metaRow['file_options']['download_file'] . '?' . CLAERO_REQUEST_USER_ACTION . '=download&table_name=' . $tableName . '&column_name=' . $metaRow['column_name'] . '&record_id=' . $searchData['id'] . '"';
                                    } else {
                                         $data[$j][$i] .= $metaRow['file_options']['file_url'] . '/' . $currentData . '"';
                                    }
                                    $data[$j][$i] .= ' title="Download: ' . $fileName . '" target="_blank">' . $fileName . '</a>';
                                    break;

                                default:
                                    $data[$j][$i] = $this->FormatValueForDisplay($currentData, $metaRow['form_type']);
                                    break;
                            } // switch $formType[$i]

                            // implement option to replace spaces for better formatting
                            if ($this->options['replace_spaces']) {
                                if (!in_array($metaRow['form_type'], array('checkbox', 'textarea', 'file'))) {
                                    $data[$j][$i] = str_replace(' ', '&nbsp;', $data[$j][$i]) . '&nbsp;&nbsp;'; // adds extra spaces for padding on right side of every column
                                } // if
                            } // if

                        } // if
                    } // foreach
                } // if
            } // foreach

            ++$j;

        } // foreach

        /* 20071228 CSN not implmented yet
        // check to see if the sorted column is in the list of foreign_keys; if it is, re-sort it because the replacement of the index with a textual description will throw everything off
        if (in_array($this->options['sort_by_column'], $foreignKeys)) {
            $SORT_INDEX = $tableOptions['sortby'];
            $SORT_DIRECTION = $tableOptions['orderby'];
            usort($data, "cmp");
        } else {

        }
        */

        // because we've mucked up the ordering of our rows by replacing foreign-key elements
        if (isset($data) && is_array($data)) {
            //$data = array_slice($data, $this->options['pageOffset'], $this->options['pageMaxRows']);
        } else {
            // no records found
            $data = array();
            $this->message[] = 'No records matched your search criteria. Please try again.';
            $tableOptions['heading'][0] = '<span style="font-weight: bold; color: #fff;">Search Results: </span>' . EOL;
        }

        $html .= '<div class="' . $tableName . 'Table">' . "\n";

        // add the title and data rows to the table

        foreach ($data as $key=>$value) {
            $rowNumber = $contentTable->addRow($value);
        } // foreach

        $html .= $contentTable->GetHtml();

        $html .= '</div>' . EOL;
        $html .= '</form>' . EOL;

        // add navigation
        $html .= $navHtml;

        // 20090410 CSN commented these out while implementing css sprites
        // add image preload for hover button images
        //$html .= '<img src="/lib/claerolib_3/images/hover/add.png" style="display:none;">' . EOL;
        //$html .= '<img src="/lib/claerolib_3/images/hover/x.png" style="display:none;">' . EOL;
        //$html .= '<img src="/lib/claerolib_3/images/hover/pencil.png" style="display:none;">' . EOL;
        //$html .= '<img src="/lib/claerolib_3/images/hover/info.png" style="display:none;">' . EOL;

        return $html;

    } // function GetHtml

    /**
    *   Returns the number of rows in the current display
    *
    *   @return     int     The number of rows in the current display
    */
    public function GetDisplayNumRows() {
        return $this->displayNumRows;
    } // function GetDisplayNumRows

    /**
    *   Returns a CSV of results, if some are checked passed in, then only the checked ones, otherwise the entire result set
    *
    *   @param      bool        if set to true, then the raw unformated values will be return (the exact values from the db)
    *
    *   @return     bool        the status of the generation
    */
    public function GetCsv($raw = false) {
        // include ClaeroCsv
        $libLoc = str_replace('/class-claero_display.php', '', __FILE__);
        require_once($libLoc . '/class-claero_csv.php');

        // disable the display of the load time at the bottom of the file
        if (!defined('HIDE_LOAD_TIME')) define('HIDE_LOAD_TIME', true);
        if (!defined('HIDE_MEM_USAGE')) define('HIDE_MEM_USAGE', true);

        // prepare csv file
        $csv = new ClaeroCsv('write');
        if (!$csv->GetStatus()) {
            trigger_error('File System Error: Failed to prepare for writing of CSV file', E_USER_ERROR);
            return false;
        }

        $this->options['checkmark_icons'] = false;

        $headings = array();
        foreach ($this->formData as $tableName => $columns) {
            foreach ($columns as $columnName => $columnData) {
                if ($columnData['view_flag']) {
                    $headings[] = $columnData['label'];
                    $colData[$columnName] = $columnData;
                }
            }
        } // foreach
        $csv->AddRow($headings);
        if (!$csv->GetStatus()) {
            trigger_error('CSV Error: Failed to add header row', E_USER_ERROR);
            return false;
        }

        foreach ($this->searchResults as $searchData) {
            $row = array();

            // then add the rest of the data fields from the query
            foreach($colData as $columnName => $columnData) {
                $currentData = $searchData[$columnName];

                if ($raw) {
                    // want the raw data, so just add the data straight from the db
                    $row[] = $currentData;
                    continue;
                }

                switch ($columnData['form_type']) {
                    case 'password' :
                        $row[] = 'hidden';
                        break;

                    case 'select' :
                    case 'radios' :
                    case 'yes_no_radio' :
                    case 'gender_radio' :
                        $lookupValue = $this->FormatValueForDisplay($currentData, $columnData['form_type'], $this->formName, $columnName, $this->formData[$this->formName][$columnName]);
                        if ($lookupValue !== null) {
                            $row[] = $lookupValue;
                        } else {
                            $row[] = '';
                        } //if
                        break;

                    case 'file' :
                        if (isset($this->formData[$this->formName][$columnName])) {
                            $metaRow = $this->formData[$this->formName][$columnName];

                            if ($metaRow['file_options']['original_filename_column'] && isset($searchData[$metaRow['file_options']['original_filename_column']]) && $searchData[$metaRow['file_options']['original_filename_column']]) {
                                $row[] = $searchData[$metaRow['file_options']['original_filename_column']];
                            } else {
                                $row[] = $currentData;
                            }
                        } else {
                            $row[] = $currentData;
                        }
                        break;

                    default:
                        $row[] = $this->FormatValueForDisplay($currentData, $columnData['form_type']);
                } // switch $formType[$i]
            } // foreach

            $csv->AddRow($row);
            if (!$csv->GetStatus()) {
                trigger_error('CSV Error: Failed to add row to csv file', E_USER_ERROR);
            }
        } // foreach

        $csv->CloseCsv();
        if (!$csv->GetStatus()) {
            trigger_error('CSV Error: Failed to close CSV', E_USER_ERROR);
            return false;
        }

        $csv->GetCsv($this->formName . '-' . time() . '.csv');
        if (!$csv->GetStatus()) {
            trigger_error('CSV Error: Failed to retrieve CSV', E_USER_ERROR);
            return false;
        }

        return true;
    } // function GetCsv
} // class ClaeroDisplay
