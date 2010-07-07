<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
*   This file contains commonly used functions within the libraries and within the sites using the libraries
*
*   @author     Claero Systems <craig.nakamoto@claero.com> / XM Media Inc <dhein@xmmedia.net>
*   @copyright  Claero Systems / XM Media Inc  2004-2009
*   @version    $Id: common.php 766 2010-06-18 01:56:47Z dhein $
*/

class claero
{

    public $libLoc = ''; // = str_replace('/common.php', '', __FILE__);
    
    /**
     * The session object.
     */
    private static $session = null;
    
    /**
     * Sets a value in flash session storage.
     *
     * @param string $key   The key to store the value as.
     * @param mixed  $value The value to store.
     */
    public static function flash_set($key, $value) {
        // If we don't already have it, get a copy of the session
        self::$session = isset(self::$session) ? self::$session : Session::instance();
        
        self::$session->set($key, $value);
    }
    
    /**
     * Gets and destroys a value in flash session storage.
     *
     * @param string  $key   The value to retrive.
     * @param boolean $$keep Whether to keep the value in storage.
     */
    public static function flash_get($key, $keep = false) {
        // If we don't already have it, get a copy of the session
        self::$session = isset(self::$session) ? self::$session : Session::instance();
        
        $value = self::$session->get($key, null);
        
        $keep ? null : self::$session->delete($key);
        
        return $value;
    }
    
    /**
    *   This function defines constants and other settings used within the claero libraries
    */
    public static function initialize_claero_lib() {

        
        if (!defined('IS_CLAEROLIB_3')) {
            /**
            *   CONST :: If this is set to true, then then current site is or has included Claerolib 3 files
            *   @var    bool
            */
            define('IS_CLAEROLIB_3', true);
        } // if
        
        if (!defined('URL_ROOT')) {
            /**
            *   CONST :: the url root of the site
            *   @var    string
            */
            if (isset($_SERVER['SCRIPT_URI'])) $cUrlRoot = str_replace($_SERVER['SCRIPT_NAME'], '', $_SERVER['SCRIPT_URI']); // browser
            else $cUrlRoot = ''; // probably command line and therefore we don't know the URL to the site
            define('URL_ROOT', $cUrlRoot);
        } // if
        
        if (!defined('FILE_ROOT')) {
            /**
            *   CONST :: the file root of the site *** INSIDE THE DOC ROOT!! ***
            *   @var    string
            */
            define('FILE_ROOT', str_replace($_SERVER['SCRIPT_NAME'], '', __FILE__));
        } // if
        
        if (!defined('CLAERO_URL')) {
            /**
            *   CONST :: claero lib path
            *   @var    string
            */
            define('CLAERO_URL', '/lib/claerolib_3');
        } // if
        
        if (!defined('EOL')) {
            /**
            *   CONST :: end of line
            *   @var    string
            */
            define('EOL', "\r\n");
        } // if
        
        if (!defined('HEOL')) {
            /**
            *   CONST :: HTML line ending with new line
            *   @var    string
            */
            define('HEOL', "<br />\r\n");
        } // if
        
        if (!defined('CLAERO_DEBUG')) {
            /**
            *   CONST :: enable/disable debugging within Claero libraries
            *   @var    bool
            */
            define('CLAERO_DEBUG', false);
        } // if
        
        if (!defined('CLAERO_DEBUG_DETAIL')) {
            /**
            *   CONST :: enable/disable debugging within Claero libraries
            *   @var    bool
            */
            define('CLAERO_DEBUG_DETAIL', false);
        } // if
        
        if (!defined('CLAERO_EDIT_EXPIRY_COLUMN')) {
            /**
            *   CONST :: name of date expired column
            *   @var    string
            */
            define('CLAERO_EDIT_EXPIRY_COLUMN', 'date_expired');
        } // if
        
        if (!defined('CLAERO_EDIT_DISPLAY_COLUMN')) {
            /**
            *   CONST :: name of display order column
            *   @var    string
            */
            define('CLAERO_EDIT_DISPLAY_COLUMN', 'display_order');
        } // if
        
        if (!defined('CLAERO_META_TABLE')) {
            /**
            *   CONST :: meta table name
            *   @var    string
            */
            define('CLAERO_META_TABLE', 'claero_meta');
        } // if
        
        if (!defined('CLAERO_CHANGE_LOG_TABLE')) {
            /**
            *   CONST :: change log table name
            *   @var    string
            */
            define('CLAERO_CHANGE_LOG_TABLE', 'claero_change');
        } // if
        
        if (!defined('CLAERO_FOREIGN_TABLE')) {
            /**
            *   CONST :: foreign key table name
            *   @var    string
            */
            define('CLAERO_FOREIGN_TABLE', 'claero_foreign');
        } // if
        
        if (!defined('CLAERO_FORM_TABLE')) {
            /**
            *   CONST :: form table name
            *   @var    string
            */
            define('CLAERO_FORM_TABLE', 'claero_form');
        } // if
        
        if (!defined('CLAERO_FORM_FIELD_TABLE')) {
            /**
            *   CONST :: form field table name
            *   @var    string
            */
            define('CLAERO_FORM_FIELD_TABLE', 'claero_form_field');
        } // if
        
        if (!defined('CLAERO_FORM_TABLE_TABLE')) {
            /**
            *   CONST :: form table table name
            *   @var    string
            */
            define('CLAERO_FORM_TABLE_TABLE', 'claero_form_table');
        } // if
        
        if (!defined('CLAERO_ONLY_UPDATE_CHANGED')) {
            /**
            *   CONST :: form table table name
            *   @var    string
            */
            define('CLAERO_ONLY_UPDATE_CHANGED', false);
        } // if
        
        if (!defined('DB_FETCH_MODE_ARRAY')) {
            /**
            *   CONST :: Column data indexed by numbers, ordered from 0 and up
            *   @var     string
            */
            define('DB_FETCH_MODE_ARRAY', 'mysql_fetch_array');
        } // if
        
        if (!defined('DB_FETCH_MODE_NUMBERED')) {
            /**
            *   CONST :: Column data indexed by numbers, ordered from 0 and up
            *   @var     string
            */
            define('DB_FETCH_MODE_NUMBERED', 'mysql_fetch_row');
        } // if
        
        if (!defined('DB_FETCH_MODE_ASSOC')) {
            /**
            *   CONST :: Column data indexed by column names
            *   @var     string
            */
            define('DB_FETCH_MODE_ASSOC', 'mysql_fetch_assoc');
        } // if
        
        if (!defined('DB_FETCH_MODE_OBJECT')) {
            /**
            *   CONST :: Column data as object properties
            *   @var     string
            */
            define('DB_FETCH_MODE_OBJECT', 'mysql_fetch_object');
        } // if
        
        if (!defined('UPLOAD_ERR_CANT_WRITE')) {
            /**
            *   CONST :: Constant used for a possible error during file upload
            *           Failed to write to disk. Introduced in 5.1.0
            *   @var    int
            */
            define('UPLOAD_ERR_CANT_WRITE', 7);
        } // if
        
        if (!defined('UPLOAD_ERR_EXTENSION')) {
            /**
            *   CONST :: Constant used for a possible error during file upload
            *           File upload stopped by extension. Introduced in 5.2.0
            *   @var    int
            */
            define('UPLOAD_ERR_EXTENSION', 8);
        } // if
        
        if (!defined('TEXT_MAX_SIZE')) {
            /**
            *   CONST :: The maximum max length of a input type text
            *   @var    int
            */
            define('TEXT_MAX_SIZE', 100);
        } // if
        
        if (!defined('TEXT_MAX_LENGTH')) {
            /**
            *   CONST :: The maximum max length of a input type text
            *   @var    int
            */
            define('TEXT_MAX_LENGTH', 7000);
        } // if
        
        if (!defined('TEXTAREA_MAX_COLS')) {
            /**
            *   CONST :: The maximum number of cols in a text area
            *   @var    int
            */
            define('TEXTAREA_MAX_COLS', 150);
        } // if
        
        if (!defined('TEXTAREA_MAX_ROWS')) {
            /**
            *   CONST :: The maximum number of rows in a text area
            *   @var    int
            */
            define('TEXTAREA_MAX_ROWS', 50);
        } // if
        
        if (!defined('DEFAULT_ROWS_PER_PAGE')) {
            /**
            *   CONST :: The default number of rows on a page
            *   @var    int
            */
            define('DEFAULT_ROWS_PER_PAGE', 50);
        } // if
        
        if (!defined('CLAERO_REQUEST_USER_ACTION')) {
            /**
            *   CONST :: The name of the form_name field within a post or get
            *   @var    string
            */
            define('CLAERO_REQUEST_USER_ACTION', 'c_user_action');
        } // if
        
        if (!defined('CLAERO_REQUEST_FORM_NAME')) {
            /**
            *   CONST :: The name of the form_name field within a post or get
            *   @var    string
            */
            define('CLAERO_REQUEST_FORM_NAME', 'c_form_name');
        } // if
        
        if (!defined('CLAERO_REQUEST_RECORD')) {
            /**
            *   CONST :: The name of the post/get array that contains the tables, columns and values
            *   @var    string
            */
            define('CLAERO_REQUEST_RECORD', 'c_record');
        } // if
        
        if (!defined('CLAERO_REQUEST_SEARCH_TYPE')) {
            /**
            *   CONST :: The name of the search_type field in the get/post used to AND/OR
            *   @var    string
            */
            define('CLAERO_REQUEST_SEARCH_TYPE', 'c_search_type');
        } // if
        
        if (!defined('CLAERO_REQUEST_LIKE_TYPE')) {
            /**
            *   CONST :: The name of the search_type field in the get/post used to AND/OR
            *   @var    string
            */
            define('CLAERO_REQUEST_LIKE_TYPE', 'c_like_type');
        } // if
        
        if (!defined('CLAERO_REQUEST_CONFIRM_DELETE')) {
            /**
            *   CONST :: The name of the confirm delete hidden field in the delete record form
            *   @var    string
            */
            define('CLAERO_REQUEST_CONFIRM_DELETE', 'c_confirm_delete');
        } // if
        
        if (!defined('CLAERO_SESSION_CURRENT_SEARCH')) {
            /**
            *   CONST :: The key name of the current where clause for used within ClaeroDisplay
            *   @var    string
            */
            define('CLAERO_SESSION_CURRENT_SEARCH', 'c_current_search');
        } // if
        
        if (!defined('CLAERO_SESSION_CURRENT_TABLE')) {
            /**
            *   CONST :: The key name of the current table used within ClaeroDisplay
            *   @var    string
            */
            define('CLAERO_SESSION_CURRENT_TABLE', 'c_current_table');
        } // if
        
        if (!defined('CLAERO_SESSION_LOGIN_RETRIES')) {
            /**
            *   CONST :: The key name of the login retry count used in ClaeroAuth
            *   @var    string
            */
            define('CLAERO_SESSION_LOGIN_RETRIES', 'c_login_retries');
        } // if
        
        if (!defined('CLAERO_SESSION_TIMESTAMP')) {
            /**
            *   CONST :: The key name of the login retry count used in ClaeroAuth
            *   @var    string
            */
            define('CLAERO_SESSION_TIMESTAMP', 'c_access_timestamp');
        } // if
        
        if (!defined('PRIVATE_DOWNLOAD_FILE')) {
            /**
            *   CONST :: Users path to the private file download
            *   @var    string
            */
            define('PRIVATE_DOWNLOAD_FILE', '/lib/claerolib_3/file_download.php');
        } // if
        
        if (!defined('MYSQL_DATE_FORMAT')) {
            /**
            *   CONST :: The format in which mysql needs a date
            *   @var    string
            */
            define('MYSQL_DATE_FORMAT', 'Y-m-d H:i:s');
        } // if
            
    }

    // generate a url friendly version of a string
    public static function make_slug($phrase, $maxLength = 255) {
    
        $result = UTF8::strtolower(UTF8::trim($phrase));
        $result = preg_replace(array('/\s/', '/[$.+!*\'(),"]/'), array('-', ""), $result);
    
        return $result;
    }
    
    public static function format_textarea_for_html($content) {
    
        $formattedContent = nl2br($content);
    
        // replace 's with proper apostrophe
        $formattedContent = str_replace("'s","&rsquo;s",$formattedContent);
        
        // replace - with proper character
        $formattedContent = str_replace(" - "," – ",$formattedContent);
    
        return $formattedContent;
        
    }
    
    /**
    *   Creates navigation based on query count, number of results or array count
    *
    *   @param      object      $claeroDb       Claero db object
    *   @param      int         $offset         Current position within results
    *   @param      array       $options        Addition settings for function
    *       offset_field => field name for get parameter that contains the new offset (default: offset)
    *       append_get => additional get parameters
    *       remove_tags => tags to remove from get parameters
    *       post_to => the page to post to
    *       results_per_page => number of results on each page (default: 30)
    *       search_list => an array of results from else where to count
    *       num_records => a number of records to base the nav on
    *       nav_type => the type of navigation to create (default: nice nav) options: basic
    *       table_name => the table name to count in if running a query, this will be needed
    *       where_sql => the where clause to put in the sql, default is nothing therefore all records in table
    *       select_append => additional sql to put within select portion of SQL
    *       count_field => the field to count in SQL (default: *)
    *       custom_sql => a custom SQL to use for counting the records; if the SQL has "GROUP BY" then the number of rows will be used, if not, then key 0 will be used for the total records
    *       no_get_prep => uses the post_to for the entire get string and just concats the offset to the end (instead of preparing your own)
    *       add_span => adds a span with class "claeroNav" around the navigation (default true)
    *       add_sq_bracket => adds square brackets around the navigation (default true)
    *       add_prev_next_text => add "Prev" and "Next" in the previous and next links, false will only add a no breaking space (default true)
    *   @param      array       $result         An array passed by reference that includes additional data about the nav
    *       total_records => the total number of records
    *       number_of_pages => the total number of pages possible
    *       results_per_page => the number of results per page (comes from $optionsresults_per_page)
    *       results_on_page => the number of results on the current page
    *       nav_url => the url used in the prev / next links
    *
    * @return       string      HTML string of navigation for display
    */
    public static function DisplayNav($claeroDb, $offset, $options, &$result) {
        $html = '';
        $result = array(
            'total_records' => 0,
            'number_of_pages' => 0,
            'results_per_page' => 0,
            'results_on_page' => 0,
        );
    
        $possibleOptions = array(
            'offset_field' => 'offset',
            'append_get' => null, // note: need preceding &
            'remove_tags' => array(),
            'post_to' => '',
            'results_per_page' => DEFAULT_ROWS_PER_PAGE,
            'search_list' => null,
            'num_records' => null,
            'nav_type' => null, // set to 'basic' for basic navigation
            'table_name' => '',
            'where_sql' => '',
            'select_append' => '',
            'count_field' => '*',
            'custom_sql' => '',
            'no_get_prep' => false,
            'add_span' => true,
            'add_sq_bracket' => true,
            'add_prev_next_text' => true,
        );
        $options = claero::SetFunctionOptions($options, $possibleOptions);
    
        $resultsPerPage = $options['results_per_page'];
    
        $numberOfRecords = 0;
        // check to see if we received a list of something, if so count them
        if ($options['search_list'] !== null && is_array($options['search_list'])) {
            $numberOfRecords = count($options['search_list']);
    
        // check to see if we received a number of records
        } else if ($options['num_records'] !== null) {
            $numberOfRecords = $options['num_records'];
    
        // check to see if we have received a custom SQL
        } else if (!empty($options['custom_sql'])) {
            // calculate number of pages when we haven't received the number
            $countQuery = $claeroDb->Query($options['custom_sql']);
    
            if ($countQuery !== false) {
                if (stripos($options['custom_sql'], 'GROUP BY') !== false) {
                    // statement contains a GROUP BY statement, so COUNT(*) may not be the right value - use the number of rows instead
                    $numberOfRecords = $countQuery->NumRows();
                } else {
                    $countQuery->FetchInto($row, DB_FETCH_MODE_ARRAY);
                    $numberOfRecords = $row['0'];
                } // if
    
            } else {
                // query failed
                trigger_error('Query Failed: Failed to perform count for DisplayNav() ' . $claeroDb->GetLastQuery());
            } // if
    
        // we didn't receive data, we have to use a query to find it
        } else if ($options['table_name']) {
            // calculate number of pages when we haven't received the number
            if (!strpos($options['table_name'], ' ', 2)) {
                $options['table_name'] = '`' . $claeroDb->EscapeString($options['table_name']) . '`';
            }
            $countSql = "SELECT COUNT(" . $options['count_field'] . ")" . $options['select_append'] . " FROM " . $options['table_name'] . " " . $options['where_sql'];
            $countQuery = $claeroDb->Query($countSql);
    
            if ($countQuery !== false) {
                if (stripos($options['where_sql'], 'GROUP BY') !== false) {
                    // statement contains a GROUP BY statement, so COUNT(*) may not be the right value - use the number of rows instead
                    $numberOfRecords = $countQuery->NumRows();
                } else {
                    $countQuery->FetchInto($row, DB_FETCH_MODE_ARRAY);
                    $numberOfRecords = $row['0'];
                } // if
    
            } else {
                // query failed
                trigger_error('Query Failed: Failed to perform count for DisplayNav() ' . $claeroDb->GetLastQuery());
            } // if
        } else {
            trigger_error('Input Error: Appropriate data was not received to generate the navigations.', E_USER_ERROR);
        } // if
    
        // calculate pages & set results vars
        $numberOfPages = $resultsPerPage > 0 ? ceil($numberOfRecords / $resultsPerPage) : 1;
        $result['total_records'] = $numberOfRecords;
        $result['number_of_pages'] = $numberOfPages;
        $result['results_per_page'] = $resultsPerPage;
        $result['results_on_page'] = $resultsPerPage;
    
        // create a new GET string, remove the offset variable
        if ($options['no_get_prep']) {
            $getString = $options['post_to'];
        } else {
            $tmpArray = array();
            if ($options['offset_field'] != null) {
                $tmpArray[] = $options['offset_field'];
            } // if
            $newGetString = claero::PrepareGetString(array_merge($tmpArray, $options['remove_tags']));
            $newGetString .= ($options['append_get']) ? $options['append_get'] : '';
            $result['nav_url'] = $options['post_to'] . '?' . $newGetString;
            if (strlen($newGetString) > 0) $newGetString .= '&';
            $getString = $options['post_to'] . '?' . $newGetString . $options['offset_field'] . '=';
        }
    
        // display navigation when there is more than 1 page
        if ($numberOfPages > 1) {
            if ($options['add_span']) $html .= EOL . '<!-- DisplayNav(): START -->' . EOL . '<span class="claeroNav">';
            // display the prev link
            if ($offset >= $resultsPerPage) {
                $newOffset = $offset - $resultsPerPage;
                $html .= '<a class="previous" href="' . $getString . $newOffset . '">' . ($options['add_prev_next_text'] ? 'Prev' : '&nbsp;') . '</a>';
            } else {
                $html .= '<span class="noPrevious">' . ($options['add_prev_next_text'] ? 'Prev' : '&nbsp;') . '</span>';
            } // if
            if ($options['add_sq_bracket']) $html .= ' [ ' . EOL;
    
            if ($options['nav_type'] == 'basic') {
                // simple nav
                $html .= DisplayBasicNav($getString, $numberOfPages, $resultsPerPage, $offset);
    
                if ($offset == ($resultsPerPage * $numberOfPages - $resultsPerPage)) {
                    $result['results_on_page'] = $numberOfRecords % $resultsPerPage;
                }
    
            } else if ($options['nav_type'] == 'small') {
                // only displays the previous and next pages in addition to the next/prev buttons
                if ($numberOfPages > 3) {
                    // there are more than 3 pages
                    if ($offset == 0) {
                        // at position 1
                        $html .= '1 ' . EOL;
    
                        // display the next 2 pages with links
                        for ($i = 2; $i <= 3; $i++) {
                            $newOffset = ($i-1) * $resultsPerPage;
                            $html .= '<a href="' . $getString . $newOffset . '">' . $i . '</a> ' . EOL;
                        } // for
    
                        $html .= ' <span class="ellipsis">...</span> ';
    
                    } else if ($offset == ($resultsPerPage * $numberOfPages - $resultsPerPage)) {
                        // at last page
                        $html .= ' <span class="ellipsis">...</span> ' . EOL;
    
                        // display the 5 pages before the last page
                        for ($i=$numberOfPages - 2; $i<$numberOfPages; $i++) {
                            $newOffset = ($i-1) * $resultsPerPage;
                            $html .= '<a href="' . $getString . $newOffset . '">' . $i . '</a> ' . EOL;
                        } // for
    
                        // display the last page
                        $html .= $numberOfPages . EOL;
    
                        $result['results_on_page'] = $numberOfRecords % $resultsPerPage;
    
                    } else {
                        // somewhere in the middle
                        $currentPage = ($offset / $resultsPerPage) + 1; // find our current page
                        $currentPageRound = round($currentPage, -1); // round it out to find the actualy page
    
                        // there are previous pages
                        if ($currentPage - 1 > 1) {
                            $html .= ' <span class="ellipsis">...</span> ';
                        }
    
                        $html .= '<a href="' . $getString . (($currentPage - 2) * $resultsPerPage) . '">' . ($currentPage - 1) . '</a> ' . EOL;
                        $html .= $currentPage . ' ' . EOL;
                        $html .= '<a href="' . $getString . (($currentPage) * $resultsPerPage) . '">' . ($currentPage + 1) . '</a> ' . EOL;
    
                        // display ...
                        if (($currentPage + 1) < $numberOfPages) {
                            $html .= ' <span class="ellipsis">...</span> ';
                        }
                    } // if
                } else {
                    $html .= DisplayBasicNav($getString, $numberOfPages, $resultsPerPage, $offset);
    
                    if ($offset == ($resultsPerPage * $numberOfPages - $resultsPerPage) && $numberOfRecords % $resultsPerPage != 0) {
                        $result['results_on_page'] = $numberOfRecords % $resultsPerPage;
                    }
                } // if
    
            } else {
                // start custom navigation
                if ($numberOfPages > 20) {
                    // there are more than 20 pages
                    if ($offset == 0) {
                        // at position 1
                        $html .= '1 ' . EOL;
    
                        // display the next 5 pages with links
                        for ($i = 2; $i <= 6; $i++) {
                            $newOffset = ($i-1) * $resultsPerPage;
                            $html .= '<a href="' . $getString . $newOffset . '">' . $i . '</a> ' . EOL;
                        } // for
    
                        // display the next 3 links as 10, 20, 30
                        for ($i=10; $i<=30; $i+=10) {
                            if ($i > 1 && $i < ($numberOfPages - 1)) {
                                $newOffset = ($i-1) * $resultsPerPage;
                                $html .= ' <span class="ellipsis">...</span> <a href="' . $getString . $newOffset . '">' . $i . '</a> ' . EOL;
                            }
                        } // for
    
                        // dipslay the last page
                        $newOffset = ($numberOfPages * $resultsPerPage) - $resultsPerPage;
                        $html .= ' <span class="ellipsis">...</span> <a href="' . $getString . $newOffset . '">' . $numberOfPages . '</a> ' . EOL;
    
                    } else if ($offset == ($resultsPerPage * $numberOfPages - $resultsPerPage)) {
                        // at last page
                        // provide link to first page
                        $html .= '<a href="' . $getString . '0">1</a> <span class="ellipsis">...</span> ' . EOL;
    
                        // display 10 page links backwards from the last page
                        for ($i=$numberOfPages - 30; $i<=$numberOfPages - 10; $i+=10) {
                            if ($i > 1 && $i < ($numberOfPages - 1)) {
                                $newOffset = ($i-1) * $resultsPerPage;
                                $html .= '<a href="' . $getString . $newOffset . '">' . $i . '</a> <span class="ellipsis">...</span> ' . EOL;
                            }
                        } // for
    
                        // display the 5 pages before the last page
                        for ($i=$numberOfPages - 5; $i<$numberOfPages; $i++) {
                            $newOffset = ($i-1) * $resultsPerPage;
                            $html .= '<a href="' . $getString . $newOffset . '">' . $i . '</a> ' . EOL;
                        } // for
    
                        // display the last page
                        $html .= $numberOfPages . EOL;
    
                        $result['results_on_page'] = $numberOfRecords % $resultsPerPage;
    
                    } else {
                        // somewhere in the middle
                        $currentPage = ($offset / $resultsPerPage) + 1; // find our current page
                        $currentPageRound = round($currentPage, -1); // round it out to find the actualy page
    
                        // display the first page
                        $html .= '<a href="' . $getString . '0">1</a> ' . EOL;
    
                        // when we are more than 2 pages from first one, display ...
                        if ($currentPage - 3 > 2) {
                            $html .= ' <span class="ellipsis">...</span> ';
                        }
    
                        // display the 10, 20, 30 ensuring we are not going over the number of pages
                        for ($i=$currentPageRound - 30; $i<=$currentPageRound - 10; $i+=10) {
                            if ($i > 1 && $i < ($numberOfPages - 1)) {
                                $newOffset = ($i-1) * $resultsPerPage;
                                $html .= '<a href="' . $getString . $newOffset . '">' . $i . '</a> <span class="ellipsis">...</span> ' . EOL;
                            }
                        } // for
    
                        // display the 3 pages before and after the current page
                        for ($i = $currentPage - 3; $i < $currentPage + 4; $i++) {
                            if ($i > 1 && $i < ($numberOfPages)) {
                                if ( (($i -1) * $resultsPerPage) == $offset ) {
                                    $html .= $i . ' ' . EOL;
                                } else {
                                    $newOffset = ($i-1) * $resultsPerPage;
                                    $html .= '<a href="' . $getString . $newOffset . '">' . $i . '</a> ' . EOL;
                                } // if
                            }
                        } // for
    
                        // display ... to separate the numbers in the middle and the ones at the end
                        if ($i < $numberOfPages) {
                            $html .= ' <span class="ellipsis">...</span> ';
                        }
    
                        // display the 90, 100, 110 at the end
                        for ($i=$currentPageRound + 10; $i<=$currentPageRound + 30; $i+=10) {
                            if ($i > 1 && $i < ($numberOfPages - 1)) {
                                $newOffset = ($i-1) * $resultsPerPage;
                                $html .= '<a href="' . $getString . $newOffset . '">' . $i . '</a> <span class="ellipsis">...</span> ' . EOL;
                            }
                        } // for
    
                        // display ...
                        if ($i < $numberOfPages) {
                            $html .= ' <span class="ellipsis">...</span> ';
                        }
    
                        // display the last page
                        $newOffset = ($numberOfPages * $resultsPerPage) - $resultsPerPage;
                        $html .= '<a href="' . $getString . $newOffset . '">' . $numberOfPages . '</a> ' . EOL;
                    } // if
                } else {
                    $html .= claero::DisplayBasicNav($getString, $numberOfPages, $resultsPerPage, $offset);
    
                    if ($offset == ($resultsPerPage * $numberOfPages - $resultsPerPage) && $numberOfRecords % $resultsPerPage != 0) {
                        $result['results_on_page'] = $numberOfRecords % $resultsPerPage;
                    }
                } // if
            } // if basic nav
    
            if ($options['add_sq_bracket']) $html .= '] ';
    
            // display the next link
            if ($offset != $resultsPerPage * ($numberOfPages - 1)) { //
                $newOffset = $offset + $resultsPerPage;
                $html .= '<a class="next" href="' . $getString . $newOffset . '">' . ($options['add_prev_next_text'] ? 'Next' : '&nbsp;') . '</a>';
            } else {
                $html .= '<span class="noNext">' . ($options['add_prev_next_text'] ? 'Next' : '&nbsp;') . '</span>';
            } // if
    
            if ($options['add_span']) $html .= '</span>' . EOL . '<!-- DisplayNav(): END -->' . EOL;
        } else {
            $result['results_on_page'] = $numberOfRecords;
        } // if > 1 pages
    
        return $html;
    } // function DisplayNav
    
    /**
    *   Creates the basic navigation (just 1 to the number of pages, nothing special)
    *
    *   @param      string      $getString      The get string ending with the offset field and =
    *   @param      int         $numberOfPages  The number of pages total
    *   @param      int         $resultsPerPage The number of results on each page (used to calculate the offset)
    *   @param      int         $offset         The current position within the results
    *
    *   @return     string      HTML string of navigation for display
    */
    public static function DisplayBasicNav($getString, $numberOfPages, $resultsPerPage, $offset) {
        $html = '';
    
        // simple navigation
        for ($i = 1; $i <= $numberOfPages; $i++) {
            if ( (($i-1) * $resultsPerPage) == $offset ) {
                $html .= $i . ' ' . EOL;
            } else {
                $newOffset = ($i-1) * $resultsPerPage;
                $html .= '<a href="' . $getString . $newOffset . '">' . $i . '</a> ' . EOL;
            } // if
        } // for
    
        return $html;
    } // function DisplayBasicNav
    
    /**
    *   Check to see if the the variable passed is a resource and is a mysql link
    *
    *   @param      resource        $connection     Database connection
    *
    *   @return     bool        True if is mysql connection or false if not
    */
    public static function CheckMysqlConnection($connection) {
        if (is_resource($connection)) {
            if (get_resource_type($connection) == 'mysql link') {
                return true;
            } else {
                //trigger_error('A proper database connection was not received.', E_USER_ERROR);
                return false;
            }
        } else {
            return false;
        }
    } // function CheckMysqlConnection
    
    /**
    *   Does a print_r() with <pre> tags around it
    */
    public static function PrintR($var) {
        echo '<pre style="text-align:left;">';
        print_r($var);
        echo '</pre>';
    } // function PrintR
    
    /**
    *   Merges the values of two multidimensional arrays, matching keys in $a2 override $a1
    *
    *   @param      array      $a1      name of table field is in
    *   @param      array      $a2      name of field
    *
    *   @return     string      merged array
    */
    public static function ArrayMergeClobber($a1, $a2) {
    
        // taken from user notes on php.net
        // (http://www.php.net/manual/en/function.array-merge-recursive.php)
        // like php native array_merge_recursive, but matching keys in a2 'clobber'
        // those in a1
    
        if (!is_array($a1) || !is_array($a2)) return false;
        $newArray = $a1;
        foreach ($a2 as $key => $val) {
            if (!isset($newArray[$key])) $newArray[$key] = array();
            if (is_array($val) && is_array($newArray[$key])) {
                $newArray[$key] = ArrayMergeClobber($newArray[$key], $val);
            } else {
                $newArray[$key] = $val;
            }  // if
        }  // foreach
    
        return $newArray;
    
    } // function ArrayMergeClobber
    
    /**
    *   Attemps to find the table name within a SQL statement, if it comes after $findString
    *
    *   @param      string      $query          The SQL statement to "search"
    *   @param      string      $findString     The string to find the table name after
    *
    *   @return     string      The found table name
    */
    public static function GetTableFromQuery($query, $findString) {
        // determine the table name by finding the $findString then the table following it
        $tableStartPos = stripos($query, $findString) + strlen($findString) + 1;
        $tableEndPos = (strlen($query) < $tableStartPos ? false :stripos($query, ' ', $tableStartPos));
        if ($tableEndPos === false) $tableEndPos = strlen($query);
        $tableName = str_replace('`', '', substr($query, $tableStartPos, ($tableEndPos - $tableStartPos)));
    
        return trim($tableName); // trim to remove any spaces or line breaks
    } // function GetTableFromQuery
    
    /**
    *   Attempts to find the id of the record that being interacted with found after an "id = "
    *
    *   @param      string      $query      The SQL statement to "search"
    *   @param      int         $startPos   The position to start search for "id = "
    *
    *   @return     int         The id of the record being interacted with (if found)
    */
    public static function GetIdFromQuery($query, $startPos) {
        $recordId = null;
    
        // look for "id = " after $startPos
        $idStrings = array('id = ', 'id =', 'id=');
        foreach ($idStrings as $idString) {
            $idPos = stripos($query, $idString, $startPos);
            if ($idPos !== false) {
                $idPos = $idPos + strlen($idString);
                $spacePos = stripos($query, ' ', $idPos);
                if ($spacePos === false) $spacePos = strlen($query);
                $recordId = str_replace(array('`', '"', "'"), '', substr($query, $idPos, $spacePos));
                break;
            }
        } // foreach
    
        return $recordId;
    } // function GetIdFromQuery
    
    /**
    *   Returns an array containing the column names of a database table
    *
    *   @return     array/boolean   array of meta data values in form $data[column_name][meta_column] = meta_data
    */
    public static function GetTableColumns($claeroDb, $tableName) {
    
        $query = $claeroDb->Query('DESCRIBE ' . $tableName);
        if ($query) {
            $columns = array();
            while ($query->FetchInto($thisRow)) {
                array_push($columns, $thisRow['Field']);
            }
        } else {
            // query failed
            $columns = FALSE;
        } // if
        return $columns;
    
    } // function GetTableColumns
    
    /**
    *   Checks the GET and then the POST for the key
    *   If it exists, then it returns that value from the Request
    *   If it doesn't, it returns the default
    *
    *   @param      string/array    $requestKey     The key within the Request to look for or any array of keys, of which the final one overrides any previous ones
    *   @param      var             $default        The default value if the key is not found
    *   @param      bool            $onlyPost       True of only want to look in the post (default: false)
    *
    *   @return     var             The value either from the POST or GET or the default
    */
    public static function ProcessRequest($requestKey, $default = null, $onlyPost = false) {
        $return = $default;
    
        if (is_array($requestKey)) {
            foreach ($requestKey as $key) {
                if (!$onlyPost && isset($_GET[$key])) $return = $_GET[$key];
                if (isset($_POST[$key])) $return = $_POST[$key]; // POST take priority over GET
            }
        } else {
            if (!$onlyPost && isset($_GET[$requestKey])) $return = $_GET[$requestKey];
            if (isset($_POST[$requestKey])) $return = $_POST[$requestKey]; // POST take priority over GET
        }
    
        return $return;
    } // function ProcessRequest
    
    /**
    *   For command line use, retrieves the value from the argv variables
    *   variable must come as argument in the format of name=value (in this case pass name as $name and it will return value)
    *
    *   @param      string      $name       the name of argument to retrieve
    *   @param      var         $default    the value to return if it can't be found
    *
    *   @return     var         The value found in the argv's or the default
    */
    public static function GetArgvValue($name, $default = null) {
        $return = $default;
    
        if (isset($_SERVER['argv'])) {
            foreach ($_SERVER['argv'] as $value) {
                if (strpos($value, $name . '=') === 0) {
                    $return = substr($value, strlen($name) + 1);
                    break; // we have found the argument, so end loop
                }
            }
        }
    
        return $return;
    } // function GetArgvValue
    
    /**
    *   Checks the GET and then the POST for the key
    *   If it exists, then it returns that value from the Request
    *   If it doesn't, it returns the default
    *
    *   @param      array           $arrayKeys      array of keys of which the final one overrides any previous ones (e.g. array('c_record','time','0','id') )
    *   @param      var             $default        The default value if the key is not found
    *   @param      bool            $onlyPost       True of only want to look in the post (default: false)
    *
    *   @return     var             The value either from the POST or GET or the default
    */
    public static function ProcessRequestArray($arrayKeys, $default = null, $onlyPost = false) {
        $return = $default;
    
        $arrayKeys = array_reverse($arrayKeys); // in order to use array_pop
    
        $firstKey = array_pop($arrayKeys);
        if (count($arrayKeys) > 0) {
            if (!$onlyPost && isset($_GET[$firstKey])) {
                $thisReturn = RecursiveGetArrayValue(0, $arrayKeys, $_GET[$firstKey]);
                if ($thisReturn != '!!!!NO DATA FOUND!!!!') $return = $thisReturn;
            }
            if (isset($_POST[$firstKey])) {
                $thisReturn = RecursiveGetArrayValue(0, $arrayKeys, $_POST[$firstKey]);
                if ($thisReturn != '!!!!NO DATA FOUND!!!!') $return = $thisReturn;
            }
        } else {
            if (!$onlyPost) $return = (isset($_GET[$firstKey])) ? $_GET[$firstKey] : $return;
            $return = (isset($_POST[$firstKey])) ? $_POST[$firstKey] : $return;
        } // if
    
        return $return;
    
    } // function ProcessRequestArray
    
    /**
    *   Loops through an array recursively (destroying it as it does) to find a value within a sub array if it's set
    *
    *   @param      int         $counter        the number of loop run
    *   @param      array       $arrayKeys      array of keys to look for from the deepest to the closest
    *   @param      array       $lastArray      the array to look in
    */
    public static function RecursiveGetArrayValue($counter, $arrayKeys, $lastArray) {
    
        if ( $counter > 20 || !is_array($lastArray) || !is_array($arrayKeys) ) return null;
    
        if (count($arrayKeys) == 1) {
            $popedKey = array_pop($arrayKeys);
            if (isset($lastArray[$popedKey])) {
                return $lastArray[$popedKey];
            } else {
                return '!!!!NO DATA FOUND!!!!';
            }
    
        } else {
            ++$counter;
            $newKey = array_pop($arrayKeys);
            if (isset($lastArray[$newKey])) {
                return RecursiveGetArrayValue($counter, $arrayKeys, $lastArray[$newKey]);
            } else {
                return '!!!!NO DATA FOUND!!!!';
            }
        } // if
    
    } // function RecursiveGetArrayValue
    
    /**
    *   Prepare the GET string
    *
    *   @param      array       $removeVar     the keys to remove from the current GET string
    *
    *   @return     var         the new get string
    */
    public static function PrepareGetString($removeVar = array()) {
    
        // create a new GET string with the existing parameters and remove the $removeVars parameter(s)
        // eg. content.php?vara=1&amp;varb=2&amp;varc=3 if $removeVar = 'vara', becomes "vara=1&amp;&varc=3"
    
        $newGetString = '&amp;';
        $k = 0;
        foreach ($_GET as $key => $value) {
            if (!in_array($key, $removeVar)) {
                if ($k != 0) $newGetString .= '&amp;';
                if (is_array($value)) {
                    foreach ($value as $arrayValue) {
                        $newGetString .=  $key . '&#91;&#93;=' . $arrayValue;
                        if ($k != 0) $newGetString .= '&amp;';
                        ++$k;
                    }
                } else {
                    $newGetString .=  $key . '=' . $value;
                }
                ++$k;
            } // if
        } // foreach
    
        return $newGetString;
    
    } // function PrepareGetString
    
    /**
    *   Takes an array and converts it into a get string (with urlencode) **Recursively**
    *   Supports one level of sub array
    *
    *   @param      array       $array      Array of keys & values to create the get string out of
    *
    *   @return     string      The get string (with & in front)
    */
    public static function ArrayToGetRecursive($array) {
        $return = '';
    
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $subKey => $subValue) {
                    if (is_array($subValue)) {
                        $return .= ArrayToGetRecursive($subValue);
                    } else {
                        $return .= '&' . urlencode($key) . '[' . urlencode($subKey) . ']=' . urlencode($subValue);
                    }
                }
            } else {
                $return .= '&' . urlencode($key) . '=' . urlencode($value);
            }
        }
    
        return $return;
    } // function ArrayToGetRecursive
    
    /**
    *   Takes an array and converts it into a get string (with urlencode)
    *   Supports one level of sub array
    *
    *   @param      array       $array      Array of keys & values to create the get string out of
    *
    *   @return     string      The get string (with & in front)
    */
    public static function ArrayToGet($array) {
        $return = '';
    
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $subKey => $subValue) {
                    $return .= '&' . urlencode($key) . '[' . urlencode($subKey) . ']=' . urlencode($subValue);
                }
            } else {
                $return .= '&' . urlencode($key) . '=' . urlencode($value);
            }
        }
    
        return $return;
    } // function ArrayToGet
    
    /**
    *   Formats the value returned by filesize() to KiB and MiB, etc
    *
    *   @param      int     $val        the value returned from filesize()
    *   @param      int     $digits     the number of digits to include after the decimal
    *   @param      string  $mode       either SI or IEC, IEC will use 1024, SI will use 1000 and then will use the related list of abbreviations
    *   @param      string  $bB         big B or little b (bytes or bits)
    *
    *   @return     string      the formatted byte or bit string
    */
    public static function FormatBytes($val, $digits = 3, $mode = 'SI', $bB = 'B'){ //$mode == 'SI'|'IEC', $bB == 'b'|'B'
        $si = array('', 'k', 'M', 'G', 'T', 'P', 'E', 'Z', 'Y');
        $iec = array('', 'Ki', 'Mi', 'Gi', 'Ti', 'Pi', 'Ei', 'Zi', 'Yi');
        switch (strtoupper($mode)) {
            case 'IEC' :
                $factor = 1024;
                $symbols = $iec;
                break;
            case 'SI' :
            default :
                $factor = 1000;
                $symbols = $si;
                break;
        }
        switch($bB) {
            case 'b' :
                $val *= 8;
                break;
            default :
                $bB = 'B';
                break;
        }
        $symbolCount = count($symbols) - 1;
        for($i = 0; $i < $symbolCount && $val >= $factor; $i++) {
            $val /= $factor;
        }
        $p = strpos($val, '.');
    
        if($p !== false && $p > $digits)
            $val = round($val);
        else if($p !== false)
            $val = round($val, $digits - $p);
    
        return round($val, $digits) . ' ' . $symbols[$i] . $bB;
    } // function FormatBytes
    
    /**
    *   Generates a random password without any special characters (only alpha numeric) $length characters long
    *
    *   @param      bool        $lettersOnly        only use letters, no numbers
    *   @param      int         $length             the length to generate
    *
    *   @return     string      The password
    */
    public static function GeneratePassword($lettersOnly = false, $length = 7) {
        // another possible way of generating passwords, but requires you to check for valid characters and remove them
        //$pass = strtolower(substr(crypt(uniqid(rand(), true)), 0, $length));
    
        // abcdefghijkmnprstuvwxyz  <-- allowed
        // 0123456789  <-- allowed
        // loq  <-- skipped
    
        if ($lettersOnly) $allowed = array('a','b','c','d','e','f','g','h','i','j','k','m','n','p','r','s','t','u','v','w','x','y','z');
        else $allowed = array('a','b','c','d','e','f','g','h','i','j','k','m','n','p','r','s','t','u','v','w','x','y','z','0','1','2','3','4','5','6','7','8','9');
    
        $maxRand = count($allowed) - 1;
        $pass = '';
    
        for($i = 0; $i < $length; $i ++) {
            $pass .= $allowed[mt_rand(0, $maxRand)];
        }
    
        return $pass;
    } // function GeneratePassword
    
    /**
    *   Generates a random key of X length (default 16) using ASCII characters 33 to 126
    *   This may not be the fastest solution, but it provides the largest variety of characters
    *
    *   @param  int     $length             The required length
    *   @param  bool    $validationString   If set to true, then all the chacaters between 33 and 126 will be used; false will use only numbers and letter (upper and lower case)
    *
    *   @return     string      The random key/string
    */
    public static function GenerateKey($length = 16, $specialCharacters = true) {
        $key = '';
    
        if ($specialCharacters) {
            for($i = 0; $i < $length; $i ++) {
                $key .= chr(mt_rand(33, 126));
            }
        } else {
            $allowed = array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
            for($i = 0; $i < $length; $i ++) {
                $key .= $allowed[mt_rand(0, 61)];
            }
        }
    
        return $key;
    } // function GenerateKey
    
    /**
    *   Generates a random key without any special characters (only alpha numeric) $length characters long
    *   This function will guarantee a unique key, unless there is probably with the web service
    *   It will query key.xmmedia.net to get a key which keeps track of all previous keys
    *   This is not recommended for passwords as the key is retrieved in the open, although kept securely on the server
    *
    *   @param      bool        $lettersOnly        only use letters, no numbers
    *   @param      int         $length             the length to generate
    *
    *   @return     string      The key
    */
    public static function GenerateUniqueKey($lettersOnly = false, $length = 7) {
        // make call to password.xmmedia.net to get the password (should be the only thing returned)
        $curlOpt = array(
            'CURLOPT_POSTFIELDS' => 'length=' . $length . '&letters_only=' . (int) $lettersOnly,
            'CURLOPT_HEADER' => false
        );
        $password = CurlExec('http://key.xmmedia.net', $curlOpt);
        if (!$password) {
            trigger_error('Input Error: Could not connect to key.xmmedia.net to get unique key. Using self generation', E_USER_ERROR);
            return GeneratePassword($lettersOnly, $length);
        } // if
    
        return $password;
    } // function GenerateUniqueKey
    
    /**
    *   Returns a number with the st, th, or rd behind it
    *
    *   @param      int         $n      The number
    *
    *   @return     string      Something like "1st", "2nd", "345654th"
    */
    public static function NumberSuffix($n) {
        $mod = $n % (($n > 20)?10:20);
        return $n . (($mod==0)?"th":date("S",mktime(0,0,0,1,$mod,2000)));
    } // function NumberSuffix
    
    /**
    *   Creates the path specified, using mkdir()'s recursive option, checking to see if the path already exists first
    *
    *   @param      string      $path       the path to create
    *   @param      int         $mode       The permissions to give the new folder
    *
    *   @return     bool        true if successful, false otherwise
    */
    public static function CreatePath($path, $mode = 0777) {
    
        $status = true;
    
        $path = rtrim($path, ' /');
    
        if (!file_exists($path) && !mkdir($path, $mode, true)) {
            $status = false;
            trigger_error('File Error: Failed to create new path: ' . $path, E_USER_ERROR);
        }
    
        return $status;
    
    } // function CreatePath()
    
    /**
    *   Converts a string such as "key|value||key|value" to an array
    *
    *   @param      string      $selectSource       a string like "key|value||key|value" to convert
    *
    *   @return     array       the array version of the string
    */
    public static function GetSourceArray($selectSource) {
        // we have array in the source that we need to explode in the format of "m|Male||f|Female"
        // explode the groups of key/value
        $items = explode('||', $selectSource);
        $selectSource = array();
        foreach ($items as $item) {
            // explode the key and value
            list($key, $value) = explode('|', $item);
            $selectSource[$key] = $value;
        }
    
        return $selectSource;
    } // function GetSourceArray
    
    /**
    *   Adds $msg to the $_SESSION['status_message']
    *
    *   @param      array/string        $msg        The message or messages to add (if array, then it will add them one after the other with an HEOL after each)
    */
    public static function AddStatusMsg($msg) {
        if (!isset($_SESSION['status_message'])) $_SESSION['status_message'] = '';
        if (is_array($msg)) {
            foreach ($msg as $i) {
                $_SESSION['status_message'] .= (strlen($_SESSION['status_message']) > 0 ? HEOL : '') . trim($i);
            }
        } else {
            $_SESSION['status_message'] .= (strlen($_SESSION['status_message']) > 0 ? HEOL : '') . trim($msg);
        }
    } // function AddStatusMsg
    
    /**
    *   Returns in div and empties the status_message key in the session
    */
    public static function DisplayStatusMsg() {
        $return = '';
    
        if (isset($_SESSION['status_message']) && $_SESSION['status_message'] != '') {
            $return = '<div class="statusMessage">' . $_SESSION['status_message'] . '</div>';
            $_SESSION['status_message'] = '';
        }
    
        return $return;
    } // function DisplayStatusMsg
    
    /**
    *   Displays the appropriate class if the field has an error in the $errors array
    *
    *   @param      string          $tableName      name of table the field is located in
    *   @param      string/array    $field          name of field to check for or any array of fields to check for (only 1 needs to be set to return as true)
    *   @param      int             $row            row number to check (default 0)
    *
    *   @return     string      html or empty (class="formError")
    */
    public static function GetFormError($tableName, $field, $row = 0) {
        global $errors, $dontShowErrorClass;
    
        if (!isset($dontShowErrorClass)) $dontShowErrorClass = false;
    
        if (is_array($field)) {
            foreach ($field as $f) {
                if (isset($errors[$tableName][$row][$f])) {
                    if ($dontShowErrorClass) {
                        return ' fieldError';
                    } else {
                        return ' class="fieldError"';
                    }
                }
            } // foreach
    
        } else if (isset($errors[$tableName][$row][$field])) {
            if ($dontShowErrorClass) {
                return ' fieldError';
            } else {
                return ' class="fieldError"';
            } // if
    
        } else {
            return '';
        } // if
    } // function GetFormError
    
    /**
    *   Redirects the user to $path using header(location), putting URL_ROOT infront
    *   If you want to redirect to the current page with not get, don't pass any parameters to the function or just an array as $path
    *
    *   @param      string/array    $path       the path to redirect to prefixed with URL_ROOT, can also be a get string starting with a ? or an array to made into a get string
    *   @param      bool            $exit       if an exit should occur too
    */
    public static function Redirect($path = array(), $exit = true) {
        global $error;
    
        $path = claero::PreparePath($path);
        $path = strpos($path, 'http') === false ? URL_ROOT . $path : $path;
    
        if (CLAERO_DEBUG && $exit && $error->GetErrorCount(array(E_USER_WARNING, E_USER_ERROR, E_ERROR, E_NOTICE, E_WARNING)) > 0) {
            echo 'There have been errors. <a href="' . $path . '">Click here to continue</a>. <strong>Status Message:</strong><br />';
            echo claero::DisplayStatusMsg();
            include_once(FILE_ROOT . '/private/footer-debug.php');
        } else {
            if (headers_sent()) {
                echo '<a href="' . $path . '">Click here to continue</a>';
            } else {
                header('Location:' . $path);
            }
        }
    
        if ($exit) exit;
    } // function Redirect
    
    /**
    *   Takes the path and prepares an actual path and possibly get string
    *
    *   @param      string/array    $path       the path to be prepared (if it's a proper path, then nothing is done to it), can also be a get string starting with a ? or an array to made into a get string
    *
    *   @return     string          the prepare path
    */
    public static function PreparePath($path) {
        if (is_array($path)) {
            $newPath = '?';
            foreach ($path as $key => $value) {
                if (is_array($value)) {
                    foreach ($value as $subValue) {
                        $newPath .= rawurlencode($key) . '=' . rawurlencode($subValue) . '&';
                    }
                } else {
                    $newPath .= rawurlencode($key) . '=' . rawurlencode($value) . '&';
                }
            }
            $path = $newPath;
        }
    
        if (strpos($path, '?') === 0) {
            // the path begins with a ? therefore send to the current page
            $path = $_SERVER['SCRIPT_NAME'] . $path;
        }
    
        return $path;
    } // function PreparePath
    
    /**
    *   Creates a path prefixed with URL_ROOT
    *
    *   @param      string/array    $path       the path to generate to prefixed with URL_ROOT, can also be a get string starting with a ? or an array to made into a get string
    *
    *   @return     string      the prepared url (or uri)
    */
    public static function UrlGen($path) {
        $path = PreparePath($path);
    
        return URL_ROOT . $path;
    } // function UrlGen
    
    /**
    *   Shuffles an array keeping the key=>value assocations
    *
    *   @param      array       $array      The array to be shuffule (passed by reference)
    */
    public static function ShuffleAssoc(&$array) {
        $arrayCount = count($array);
        if ($arrayCount > 1) {
            $keys = array_rand($array, $arrayCount);
    
            foreach ($keys as $key) {
                $new[$key] = $array[$key];
            }
    
            $array = $new;
        }
    } // function ShuffleAssoc
    
    /**
    *   Runs ProcessRequest() for c_user_action (CLAERO_REQUEST_USER_ACTION)
    *
    *   @see    ProcessRequest
    */
    public static function ProcessUserAction($default = null, $postOnly = false) {
        return strtolower(claero::ProcessRequest(CLAERO_REQUEST_USER_ACTION, $default, $postOnly));
    } // function ProcessUserAction
    
    /**
    *   Creates table cells with image spacers inside of them to the specified widths
    *
    *   @param      array       $widths     An array of widths
    *
    *   @return     string      The html tr and table cells
    */
    public static function CreateTdSpacers($widths, $rowClass = false) {
        $html = '<tr' . ($rowClass ? ' class="' . $rowClass . '"' : '') . '>' . EOL;
    
        foreach ($widths as $width) {
            $html .= '   <td><img src="/images/spacer.gif" width="' . $width . '" height="1" /></td>' . EOL;
        }
    
        return $html . '</tr>';
    } // function CreateTdSpacers
    
    /**
    *   Formats fields that are arrays
    *
    *   @param      string      $type       The type of field
    *   @param      var         $value      The value to format, can be an array
    *
    *   @return     string      The prepared field
    */
    public static function PrepareSpecialField($type, $value) {
        $return = null;
    
        switch ($type) {
            case 'datetime' :
                if (is_string($value)) {
                    $return = $value;
    
                } else if (isset($value['date']) && strlen($value['date']) == 10
                && isset($value['hour']) && strlen($value['date']) > 0
                && isset($value['min']) && strlen($value['min']) > 0
                && isset($value['modulation']) && strlen($value['modulation']) == 2) {
                    // concats the date pieces together and then coverts them to a MySQL dateformat
                    $time = $value['date'] . ' ' . $value['hour'] . ':' . $value['min'];
                    if (isset($value['sec']) && strlen($value['sec']) == 2) $time .= ':' . $value['sec'];
                    $time .= ' ' . $value['modulation'];
                    $return = date(MYSQL_DATE_FORMAT, strtotime($time));
    
                } else {
                    $return = '';
                }
                break;
    
            case 'datetime12' :
                trigger_error('Input Error: Datetime12 data prep has not been completed', E_USER_ERROR);
                /*if ((isset($data[$columnName . '_hour'])) && (isset($data[$columnName . '_minute']))) {
                    $data[$columnName] = strlen($data[$columnName]) > 0 ? $data[$columnName] : '0000-00-00';
                    $time = $data[$columnName . '_hour'] . ':' . $data[$columnName . '_minute'] . ':00';
                    $data[$columnName] = $data[$columnName] . ' ' . $time;
                    if ($time != '00:00:00') {
                        $return = date('Y-m-d H:i:s', strtotime($data[$columnName] . ' ' . $data[$columnName . '_meridiem']));
                    }
                }*/
                break;
    
            case 'date_drop' :
            case 'year_month' :
            case 'date_three_field' :
                if (is_string($value)) {
                    $return = $value;
    
                } else if (isset($value['year']) && strlen($value['year']) == 4
                && isset($value['month']) && strlen($value['month']) > 0
                && isset($value['day']) && strlen($value['day']) > 0) {
                    $return = $value['year'] . '-' . sprintf('%02d', $value['month']) . '-' . sprintf('%02d', $value['day']);
    
                } else {
                    $return = '';
                }
                break;
    
            case 'phone' :
                trigger_error('Input Error: phone data prep has not been completed', E_USER_ERROR);
                /*if ((isset($data[$columnName . '_prefix'])) && (isset($data[$columnName . '_line']))) {
                    $return = $data[$columnName] . $data[$columnName . '_prefix'] . $data[$columnName . '_line'];
                }*/
                break;
        } // switch
    
        return $return;
    } // function PrepareSpecialField
    
    /**
    *   Executes a curl operation
    *
    *   @param    string      $url    url to request
    *   @param    array       $options    options to set different url parameters
    *
    *   @return   string      the data retrieved during the curl call
    */
    public static function CurlExec($url, $options = array()) {
        $data = false;
        $optionsDefault = array(
            'CURLOPT_REFERER' => false,
            'CURLOPT_WRITEHEADER' => false,
            'CURLOPT_FOLLOWLOCATION' => false,
            'CURLOPT_POST' => false,
            'CURLOPT_POSTFIELDS' => false,
            'CURLOPT_HEADER' => true,
        );
    
        $userAgent = 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; .NET CLR 1.1.4322; .NET CLR 2.0.50727; .NET CLR 3.0.04506.30)';
        $connectionAttempts = 5;
        $connectionTimeout = 5;
        $timeout = 10;
        $httpdCodes = parse_ini_file('curl_http_codes.ini');
    
        $options = array_merge($optionsDefault, $options);
        //PrintR($options);
    
        $successful = false;
        $iteration = 0;
        while ($successful == false && $iteration < $connectionAttempts) {
            trigger_error('Attempt #' . $iteration  . ': ' . $url);
    
            // first get the login page so we can get the session
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $connectionTimeout); // timeout for connect in seconds
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout); // overall timout in seconds
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            //if ($this->cookie != '') curl_setopt($ch, CURLOPT_COOKIE, $this->cookie);
            curl_setopt($ch, CURLOPT_HEADER, true);
            if ($options['CURLOPT_REFERER']) curl_setopt($ch, CURLOPT_REFERER, $options['CURLOPT_REFERER']);
            if ($options['CURLOPT_WRITEHEADER']) curl_setopt($ch, CURLOPT_WRITEHEADER, $options['CURLOPT_WRITEHEADER']);
            if ($options['CURLOPT_FOLLOWLOCATION']) curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            if ($options['CURLOPT_POST']) curl_setopt($ch, CURLOPT_POST, true);
            if ($options['CURLOPT_POSTFIELDS']) curl_setopt($ch, CURLOPT_POSTFIELDS, $options['CURLOPT_POSTFIELDS']);
            if (!$options['CURLOPT_HEADER']) curl_setopt($ch, CURLOPT_HEADER, $options['CURLOPT_HEADER']);
            $data = curl_exec($ch);
    
            $curlErrorNo = curl_errno($ch);
    
            if ($curlErrorNo == 0) {
                $successful = true;
                //$this->connected = true;
    
                $curlInfo = curl_getinfo($ch);
                $infoStr = '';
                //$infoStr = EOL . print_r($curlInfo, true);
    
                if (empty($data)) {
                    // some kind of an error happened
                    //$this->connected = true;
                    trigger_error('Error Message: #' . $curlErrorNo . ', ' . curl_error($ch) . $infoStr);
                } else {
                    //$infoStr .= EOL . substr($data, 0, $curlInfo['header_size']);
                    if (empty($curlInfo['http_code'])) {
                        //$this->connected = true;
                        trigger_error('Error: No HTTP code was returned.' . $infoStr, E_USER_ERROR);
                    /*} else if ($curlInfo['http_code'] == '302' && in_array($url, $this->no302Pages)) {
                        //$this->connected = false;
                        trigger_error('Error: The connection to be SPIN has been lost. Now receiving 302 error codes (probably past operating times). Page: ' . $url, E_USER_ERROR);*/
                    } else {
                       // load the HTTP codes (future)
                       trigger_error('The server responded: ' . $curlInfo['http_code'] . " " . $httpdCodes[$curlInfo['http_code']] . $infoStr);
                   } // if
                } // if
            } else {
                //echo $data;
                trigger_error('Error Message: #' . $curlErrorNo . ', ' . curl_error($ch));
                //$curlInfo = curl_getinfo($ch);
                //PrintR($curlInfo);
                //$this->connected = false;
            }
    
            curl_close ($ch);
    
            $timeLastQuery = time();
    
            ++$iteration;
        } // while
    
        //$this->pagesSinceLogin++;
    
        return $data;
    } // function CurlExec
    
    /**
    *   Calculates the age in years from the current date
    *
    *   @param      string      $birthDate      The date to calculate the age from
    *
    *   @return     int         the age in years (could be negative)
    */
    public static function CalculateAge($birthDate) {
        return floor((time() - strtotime($birthDate)) / (365 * 24 * 60 * 60));
    } // function CalculateAge
    
    /**
    *   Converts a number of seconds to hours:mins:seconds
    *
    *   @param      int     $seconds        The total number of seconds
    *
    *   @return     string      formatted such as hh:mm:ss
    */
    public static function FormatSeconds($seconds) {
        $mins = floor($seconds / 60);
        if ($mins > 60) {
            $hours = floor($mins / 60);
            $mins = $mins % 60;
        } else {
            $hours = 0;
        }
        $secs = $seconds % 60;
        return sprintf('%d:%02d:%02d', $hours, $mins, $secs);
    } // function FormatSeconds
    
    /**
    *   Returns, based on $count, 's' or ''
    *
    *   @param      int     $count      the count
    *
    *   @return     string      the string based on the count
    */
    public static function GetS($count) {
        return ($count == 1 ? '' : 's');
    } // function GetS
    
    /**
    *   Returns, based on $count, 'ies' or 'y'
    *
    *   @param      int     $count      the count
    *
    *   @return     string      the string based on the count
    */
    public static function GetIes($count) {
        return ($count == 1 ? 'y' : 'ies');
    } // function GetIes
    
    /**
    *   Returns, based on $count, 'was' or 'were'
    *
    *   @param      int     $count      the count
    *
    *   @return     string      the string based on the count
    */
    public static function GetWas($count) {
        return ($count == 1 ? 'was' : 'were');
    } // function GetWas
    
    /**
    *   Returns, based on $count, 'has' or 'have'
    *
    *   @param      int     $count      the count
    *
    *   @return     string      the string based on the count
    */
    public static function GetHave($count) {
        return ($count == 1 ? 'has' : 'have');
    } // function GetHave
    
    /**
    *   Returns, based on $count, 'is' or 'are'
    *
    *   @param      int     $count      the count
    *
    *   @return     string      the string based on the count
    */
    public static function GetAre($count) {
        return ($count == 1 ? 'is' : 'are');
    } // function GetAre
    
    /**
    *   Returns, based on $count, 'this' or 'these'
    *
    *   @param      int     $count      the count
    *
    *   @return     string      the string based on the count
    */
    public static function GetThese($count) {
        return ($count == 1 ? 'this' : 'these');
    } // function GetAre
    
    /**
    *   Returns a string with ... at the end if greater than $len
    *
    *   @param      string      $string     the string to check and add "..." to
    *   @param      int         $len        the max length of the string
    *
    *   @return     string      the string, possibly with "..."
    */
    public static function EllipsisString($string, $len = 30) {
        if (strlen($string) > $len) {
            return substr($string, 0, $len - 3) . '...';
        } else {
            return $string;
        }
    } // function EllipsisString
    
    /**
    *   Removes \/ "', from a filename and makes it lower case
    */
    public static function CleanFilename($filename) {
        return str_replace(array('\\', '/', '"', '\'', ' ', ','), '_', strtolower($filename));
    } // function CleanFilename
    
    /**
    *   Returns an array of options for a function, where $options are the options passed by the user and $possibleOptions are those that are available
    *
    *   @param      array   $options            array of options received by function
    *   @param      array   $possibleOptions    array of possible options to check for
    *   @param      bool    $useDefaults        sets wether or not to use the default in the array if not set in passed options (default: true)
    */
    public static function SetFunctionOptions($options, $possibleOptions, $useDefaults = true) {
        if (!is_array($options)) {
            trigger_error('Did not receive an array for $options, therefore no options were set within object.');
            $options = (array) $options;
        }
    
        $returnOptions = array();
    
        foreach ($possibleOptions as $possibleOption => $default) {
            if (isset($options[$possibleOption])) $returnOptions[$possibleOption] = $options[$possibleOption];
            else if ($useDefaults) $returnOptions[$possibleOption] = $default;
        }
    
        return $returnOptions;
    } // function SetFunctionOptions
    
    /**
    *   Streams a file for download
    *
    *   @param      string      $filename       the path to the file to stream
    *   @param      string      $type           the mime type
    *   @param      string      $userFilename   the name of the the file to pass to the user (run through CleanFilename() first) default: the same as $filename <-- not recommended
    *
    *   @return     bool        false on failure, true otherwise
    */
    public static function StreamFileDownload($filename, $type, $userFileName = false) {
        if (headers_sent()) {
            trigger_error('Input Error: Cannot stream file because headers have already been sent', E_USER_ERROR);
            return false;
    
        } else {
            if (!$userFileName) $userFileName = $filename;
    
            header('Content-type: ' . $type);
            header('Content-length: ' . (string) filesize($filename));
            header('content-disposition: inline; filename=' . CleanFilename($userFileName));
            //header('Content-Type: text/tddownload');
            header('Pragma: Public');
            header('Cache-control: private');
    
            session_write_close(); // this will write the session file so the user can continue to use the site
    
            if (!readfile($filename)) {
                trigger_error('File System Error: Failed to read file ' . $filename, E_USER_ERROR);
                return false;
            }
        }
    
        return true;
    } // function StreamFileDownload
    
    /**
    *   Returns true of the passed array is an associative array, false otherwise (such as numeric keys starting at 0)
    *
    *   @param      array       $array      the array to check
    *
    *   @return     bool        true if associative, false otherwise
    */
    public static function IsAssocArray($array) {
        return array_keys($array) != range(0, count($array) - 1);
    } // function IsAssocArray
    
    /**
    *   Optimizes all tables within the current database
    *
    *   @param      object      $db     ClaeroDb object
    *
    *   @return     bool        false if there are any errors, true otherwise
    */
    public static function OptimizeAllTables($db) {
        $status = true;
    
        $tablesSql = "SHOW TABLES";
        $tablesQuery = $db->query($tablesSql);
        if ($tablesQuery === false || $tablesQuery->NumRows() == 0) {
            trigger_error('Could not get the list of tables in the database. Database has not been optimized! ' . $db->GetLastQuery(), E_USER_ERROR);
            $status = false;
        } else {
            $tablesQuery->SetFetchMode(DB_FETCH_MODE_ARRAY);
            while ($tablesQuery->FetchInto($tableName)) {
                $tableName = $tableName[0];
                $checkSql = "CHECK TABLE `{$tableName}`";
                $checkQuery = $db->query($checkSql);
                if ($checkQuery === false) {
                    trigger_error('Could not check table: ' . $tableName, E_USER_ERROR);
                } else {
                    $checkQuery->FetchInto($checkData);
                    if ($checkData['Msg_text'] != 'OK') {
                        trigger_error('There was a problem while checking the table `' . $tableName . '`: ' . $checkData['Msg_text'], E_USER_ERROR);
                        $status = false;
                        continue; // don't optimize this table as there has been an error
                    }
                } // if
    
                $optimizeSql = "OPTIMIZE TABLE `{$tableName}`";
                $optimizeQuery = $db->query($optimizeSql);
                if ($optimizeQuery === false) {
                    $status = false;
                    trigger_error('Could not optimize table: ' . $tableName, E_USER_ERROR);
                } // if
            } // while
        } // if
    
        return $status;
    } // function OptimizeAllTables
    
    /**
    *   Runs sub string starting at 0 to $length
    *
    *   @param      string      $string     The string to cut off
    *   @param      int         $length     The max length of the string
    *
    *   @return     string      the cut string
    */
    public static function CutStr($string, $length) {
        return substr($string, 0, $length);
    } // function CutStr
    
    /**
    *   Escapes a string for output in HTML using htmlspecialchars()
    *   Escapes ALL quotes using htmlspecialchars('', ENT_QUOTES)
    *
    *   @param  mixed   $input  The variable to escape, if it's an array each key and value in the array will be escaped and the entire array returned
    *
    *   @return mixed   The escaped version of what was input
    */
    public static function EscapeOutputForHtml($input) {
        if (!is_array($input)) {
            return htmlspecialchars($input, ENT_QUOTES);
        } else {
            foreach ($input as $key => $value) {
                $input[htmlspecialchars($key, ENT_QUOTES)] = htmlspecialchars($value, ENT_QUOTES);
            }
            return $input;
        } // if
    } // function EscapeOutputForHtml
    
    /**
    *   Escapes a string for output in HTML using htmlspecialchars() using EscapeOutputForHtml() based on the $conditional variable (true it will, false it will not)
    *
    *   @see function EscapeOutputForHtml
    *
    *   @param  mixed   $input      The variable to escape, if it's an array each key and value in the array will be escaped and the entire array returned
    *   @param  bool    $condition  The conditional; if true the $input will be escaped; if false $input will not be escaped
    *
    *   @return mixed   The escaped version of what was input
    */
    public static function EscapeOutputForHtmlConditional($input, $condition = true) {
        return $condition ? EscapeOutputForHtml($input) : $input;
    } // function EscapeOutputForHtmlConditional
    
    /**
    *   Converts any string to a clean url compatible string (a slug), removing any non alphanumeric character and converting spaces to dashes and optionally adds a max length
    *
    *   @param  string  $urlPart    The string to modify
    *   @param  int     $maxLength  The max length, default is null and therefore no max length
    *   @param  string  $spaceChar  The character to replace the spaces with; default - (dash)
    *
    *   @return string  The url part ready for placement in a URL
    */
    public static function GetSlug($urlPart, $maxLength = null, $spaceChar = '-') {
        $url = strtolower(preg_replace(array('/[^a-z0-9\- ]/i', '/[ \-]+/'), array('', $spaceChar), trim($urlPart)));
        if ($maxLength) $url = CutStr($url, $maxLength);
        return $url;
    } // function GetSlug
    
    /**
    *   Given a file, i.e. /css/base.css, replaces it with a string containing the
    *   file's mtime, i.e. /css/base.1221534296.css.
    *   Requires that the $_SERVER['DOCUMENT_ROOT'] is available
    *
    *   To make this work without having the create multiple js and css files, the following code will be needed in the .htaccess file
    <IfModule mod_rewrite.c>
    RewriteEngine on
    RewriteRule     ^(.*)\.[\d]+\.(css|js)$     $1.$2   [L]
    </IfModule>
    *
    *   @param  string  $file  The file to be loaded.  Must be an absolute path (i.e. starting with slash)
    *
    *   @return string  The path of the <script> or <link> tag
    */
    public static function AutoVersionFile($file) {
        if(strpos($file, '/') !== 0 || !file_exists($_SERVER['DOCUMENT_ROOT'] . $file)) return $file;
    
        $mtime = filemtime($_SERVER['DOCUMENT_ROOT'] . $file);
        return preg_replace('{\\.([^./]+)$}', '.' . $mtime . ".\$1", $file);
    } // function AutoVersionFile
    
    /**
    *   Generates (returns) the HTML for the hidden field for c_user_action with the specific user action
    *
    *   @param      string      $action     The user action
    *
    *   @return     string      The HTML for the hidden field
    */
    public static function GetUserActionHidden($action) {
        return '<input type="hidden" name="c_user_action" value="' . EscapeOutputForHtml($action) . '" />';
    } // function GetUserActionHidden
    
    /**
    *   Returns the value from the array if it's set or the default when it's not
    *
    *   @param  array       $data   The array to look for the value in
    *   @param  string/int  $key    The key to look for
    *   @param  mixed       $default    The default value to return of the key is not set
    *
    *   @return mixed       The value from the array of or the default
    */
    public static function GetArrayVal($data, $key, $default = null) {
        return isset($data[$key]) ? $data[$key] : $default;
    } // function GetArrayVal
}