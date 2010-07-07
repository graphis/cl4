<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
*   This file has the class ClaeroField which is used for displaying a field, both for editing and viewing
*
*   @author     Claero Systems <craig.nakamoto@claero.com> / XM Media Inc <dhein@xmmedia.net>
*   @copyright  Claero Systems / XM Media Inc  2004-2009
*   @version    $Id: class-claero_field.php 763 2010-06-04 20:03:19Z dhein $
*/

//$libLoc = str_replace('/class-claero_field.php', '', __FILE__);
//require_once($libLoc . '/claero_config.php');
//require_once($libLoc . '/common.php');
//require_once($libLoc . '/class-claero.php');

/**
*   Displays a field, both for editing and viewing
*
*   @author     Claero Systems <craig.nakamoto@claero.com> / XM Media Inc <dhein@xmmedia.net>
*   @copyright  Claero Systems / XM Media Inc  2004-2009
*
*   $field = new ClaeroField($type, $name, $value, $array);
*   $field->GetField();
*
*   @see    class ClaeroError
*   @see    class ClaeroDb
*   @see    class Claero
*
*   @todo   new field types: phone (with and without country code), html input
*   @todo   add search functionality (additional fields such as "on", "before", "after" for date)
*/
class Claerolib4_Field extends Claerolib4_Base {
    /**
    *   Contains the HTML for the field
    *   @var    string
    */
    protected $html = '';

    /**
    *   Type of field
    *   @var    string
    */
    private $type;

    /**
    *   Name of field
    *   @var    string
    */
    protected $name;

    /**
    *   Id of field
    *   @var    string
    */
    protected $id;

    /**
    *   Value of field
    *   @var    string/array/int/bool
    */
    protected $value;

    /**
    *   Class of field
    *   @var    string
    */
    protected $class;

    /**
    *   URL path of claero lib, set to CLAERO_URL
    *   @var    string
    */
    private $libPath;

    /**
    *   Stores the number of options found in the case of a select, etc. use NumRows() to get the value
    *   @var    string
    */
    protected $numRows;

    /**
    *   Flag to determine if the field has been prepared
    *   @var    bool
    */
    protected $fieldHtmlPrepared = false;

    /**
    *   For storing the array of the values for the current field (like select, radios or checkbox)
    *   @var    array
    */
    protected $preparedSource = array();

    /**
    *   Attributes that apply to all fields
    *   @var    array
    */
    protected $attributes = array(
        'disabled' => '',
        'readonly' => '',
        'on_change' => '',
        'on_keypress' => '',
        'on_keyup' => '',
        'on_keydown' => '',
        'on_mouseup' => '',
        'on_mousedown' => '',
        'on_click' => '',
        'on_focus' => '',
        'on_blur' => '',
        'on_select' => '',
        'style' => '',
        'tabindex' => '',
    );

    /**
    *   Prepares the field
    *   Gets any additional data needed from meta data
    *
    *   @param  string      $type       type of field (text, radio, select, etc)
    *   @param  string      $name       name of field for within HTML
    *   @param  var         $value      selected/current value of field (must be array for checkboxes or radios) (default null)
    *   @param  array       $options    array of options, values & claero db
    *       claero_db => ClaerDb object
    *       id => id of field
    *       class => class of field
    *       source => string if sql statement, array if array of values (key (value) => value (display))
    *       source_id => when a sql statement is used to create a select, this is the id field (value=id)
    *       source_value => when a sql statement is used to create a select, this is the display field
    *       select_one_flag => adds "-- Select One --" to a select, value ""
    *       select_all_flag => adds "All" to a select, value "all"
    *       select_none_flag => add "None" to a select, value "none"
    *       orientation => the way that radio buttons and checkboxes are laid out, allowed: horizontal, vertical, table, table_vertical (for radios only, puts text above the <input> separated by a <br />) (default: horizontal)
    *       columns => for radios and checkboxes, the number of columsn to display (default: 2); if using orientation table_vertical, then this value will be set to the number of values in the prepareSource (for radios)
    *       table_tag => for radios and checkboxes, if the table tag should be included in the output (default: true)
    *       attributes => array of other attributes
    *       multiple => enables/disables the select as a multiple select
    *       prefix_html => html to put before the field, but include in the html within the object
    *       checkbox_display => text to display beside the input type="checkbox" and include within a label
    *       checkbox_value => the value of the checkbox (default: 1)
    *       checkbox_hidden => sets the display of the hidden field that goes with the checkbox (default: true (on))
    *       clean_text => disables clean text performed on all output text (default: true)
    *       password_confirm => enables and is an array data for the name and id of the confirm password field (default: false)
    *       add_values => an array additional values for a select
    *       show_seconds => if false, the seconds field on a DateTime type is not displayed
    *       show_no_value_text => if false, then the "No values to choose from" won't be shown
    *       show_date_format => if true (default), then the "(YYYY-MM-DD)" will be displayed after a date field
    *       value_sql => this is a sql statement that will be run to populate $this->value (the field's value) -- most useful for checkboxes or multiple selects
    *       value_start => used for number_drop -- the starting value (default 18)
    *       value_end => used for number_drop -- the ending value (default 80)
    *       value_increment => used for number_drop -- the increment (default 1)
    *       height_start => used for height_drop -- the starting height in inches (default 59 4foot 11inces)
    *       height_end => user for height_drop -- the ending height in inches (default 84 7feet)
    *       add_nbsp => add a no breaking space between the checkbox and the label (default false)
    *       year_start => the year to start at for the year drop down (default current year)
    *       year_end => the year to end at for the year drop down (default 1930)
    *       year_order => the order in which to display the years; options: asc, desc (default desc)
    *       use_month_numbers => if set to true, the month drop down will display numbers instead of month names (default false)
    *       month_year_separator => the separator between the month and year fields in the Month Year Drop (default &nbsp;)
    *       reverse_radios => reverses the order of the radios; does not reverse the value of the radios; applied to yes/no and male/female (default false)
    *       override_class => if set to true, then any additional class added by the make function for the field type will not happen; use for something like MakeHtml() where it adds a default class
    */
    public function __construct($type, $name, $value = null, $options = array()) {

        parent::__construct($options);

        // initiate the default values for the object
        $this->libPath = CLAERO_URL;

        $this->type = $type;
        $this->name = $name;
        $this->value = $value;
        if (isset($options['id'])) $this->id = $options['id'];
        if (isset($options['class'])) $this->class = $options['class'];

        // options that need defaults
        $possibleOptions = array(
            'source' => array(),
            'source_id' => 'id',
            'source_value' => 'name',
            'select_one_flag' => false,
            'select_all_flag' => false,
            'select_none_flag' => false,
            'orientation' => 'horizontal',
            'columns' => null, // default to 2 below
            'table_tag' => true,
            'multiple' => null,
            'prefix_html' => '',
            'checkbox_display' => '',
            'checkbox_value' => '1',
            'checkbox_hidden' => true,
            'clean_text' => true,
            'password_confirm' => false,
            'year_start' => date('Y'),
            'year_end' => 1930,
            'year_order' => 'desc',
            'add_values' => false,
            'show_seconds' => true,
            'allowed_tables' => false,
            'show_no_value_text' => true,
            'show_date_format' => true,
            'value_sql' => false,
            'value_start' => 18,
            'value_end' => 80,
            'value_increment' => 1,
            'height_start' => 59, // this is also in Claero::FormatValueForDisplay()
            'height_end' => 84, // this is also in Claero::FormatValueForDisplay()
            'add_nbsp' => false,
            'use_month_numbers' => false,
            'month_year_separator' => '&nbsp;',
            'reverse_radios' => false,
            'override_class' => false,
        );
        $this->SetObjectOptions($options, $possibleOptions);

        // if the columns are null and the orientation is not table_vertical (for radios), then set the default columns to 2
        if ($this->options['columns'] === null && $this->options['orientation'] != 'table_vertical') $this->options['columns'] = 2;

        if (strlen($this->options['prefix_html']) > 0) $this->html .= $this->options['prefix_html'];
        $this->options['year_order'] = strtolower($this->options['year_order']);

        // options that don't need defaults
        if (isset($options['attributes']) && is_array($options['attributes'])) $this->attributes = $options['attributes'];

        if ($this->options['value_sql']) {
            $valueQuery = $this->claeroDb->Query($this->options['value_sql']);
            $this->value = $valueQuery->GetAllRows();
        } // if

    } // function __construct

    public function PrepareFieldHtml() {
        if (!$this->fieldHtmlPrepared) {
            $this->fieldHtmlPrepared = true;

            $functionName = 'Make' . str_replace(' ', '', ucwords(str_replace('_', ' ', $this->type)));
            $nonSearchFunctionName = str_replace('Search', '', $functionName);

            // check to see if the method exists
            if (method_exists($this, $functionName)) {
                $this->{$functionName}();
            } else if (strpos($functionName, 'Search') !== false && method_exists($this, $nonSearchFunctionName)) {
                $this->{$nonSearchFunctionName}();
            } else {
                trigger_error('Input Error: The method ' . $functionName . ' does not exist, therefore a text input will be created.', E_USER_ERROR);
                $this->MakeText();
            } // if
        } // if
    } // function PrepareFieldHtml

    /**
    *   Runs GetHtml()
    *
    *   @return     string      HTML for field
    */
    public function GetField() {
        return $this->GetHtml();
    } // function GetField

    /**
    *   Returns HTML for the field
    *
    *   @return     string  HTML for the field
    */
    public function GetHtml() {
        $this->PrepareFieldHtml();

        return $this->html;
    } // function GetHtml

    /**
    *   Returns the type of field
    *
    *   @return     string      The type of field
    */
    public function GetType() {
        return $this->type;
    } // function GetType

    /**
    *   Returns the name of the field
    *
    *   @return     string      The name of the field
    */
    public function GetName() {
        return $this->name;
    } // function GetName

    /**
    *   Returns the class of the field
    *
    *   @return     string      The class of the field
    */
    public function GetClass() {
        return $this->class;
    } // function GetClass

    /**
    *   Returns the value of the field
    *
    *   @return     string      The value of the field
    */
    public function GetValue() {
        return $this->value;
    } // function GetValue

    /**
    *   Returns the id of the field
    *
    *   @return     string      The id of the field
    */
    public function GetId() {
        return $this->id;
    } // function GetId

    /**
    *   Sets the value of the field
    *
    *   @param      var     $value      The value to set the field's value to
    */
    public function SetValue($value) {
        $this->value = $value;
    } // function SetValue

    /**
    *   Sets the name of the field
    *
    *   @param      var     $name       The name to set the field's name to
    */
    public function SetName($name) {
        $this->name = $name;
    } // function SetName

    /**
    *   Sets the name of the field
    *
    *   @param      var     $id         The id to set the field's id to
    */
    public function SetId($id) {
        $this->id = $id;
    } // function SetId

    /**
    *   Sets an attribute for field within the object
    *
    *   @param      string      $attribute      the attribute name
    *   @param      string      $value          the value of the attribute
    */
    public function SetAttribute($attribute, $value) {
        if (isset($this->attributes[$attribute])) {
            if (isset($this->options['attributes'])) $this->options['attributes'][$attribute] .= $value;
            $this->attributes[$attribute] .= $value;
        } else {
            if (isset($this->options['attributes'])) $this->options['attributes'][$attribute] = $value;
            $this->attributes[$attribute] = $value;
        }
    } // function SetAttribute

    /**
    *   Returns the html for the attributes of the field (name, id, class, and everything in options)
    *
    *   @return     string      html string of attributes
    */
    protected function DisplayAttributes($defaultOptions = array(), $showId = true) {
        // set up default options
        foreach ($defaultOptions as $key => $value) {
            if (!isset($this->attributes[$key])) {
                // missing this one, so add it
                $this->attributes[$key] = $value;
            } // if
        } // foreach

        $html = ' ';
        // add name & id
        $html .= 'name="' . $this->name . '"';
        if ($showId && $this->id) $html .= ' id="' . $this->id . '"';
        // add class if it's set
        if ($this->class) $html .= ' class="' . $this->class . '"';
        // add additional parameters within attributes if true (not an empty string)
        foreach ($this->attributes as $attribute => $value) {
            if ($value) $html .= ' ' . str_replace('_', '', $attribute) . '="' . $value . '"';
        }

        return $html;
    } // function DisplayAttributes

    /**
    *   Makes a date input field with date "pop-up"
    *   $this->value should be in the format Y-m-d (yyyy-mm-dd)
    *   Set $this->value equal to false (bool) or "empty" (string) to have an empty date field
    *   Set $this->value equal to true (bool) or empty string to get current date
    */
    protected function MakeDate() {
        $html = '';
        $dateFormat = 'Y-m-d';

        if (!$this->id) $this->id = str_replace(array('[',']'), '_', $this->name);

        // figure out what the value of the date field should be
        if ($this->value == 'empty' || $this->value === false || strtotime($this->value) === -1 || $this->value == '0000-00-00' || $this->value == '0000-00-00 00:00:00') {
            $this->value = '';
        } else if ($this->value === true || strlen($this->value) == 0) {
            $this->value = date($dateFormat);
        }

        if (isset($this->attributes['on_change'])) {
            $onChange = $this->attributes['on_change'];
            unset($this->attributes['on_change']); // remove it so it dosen't get added to the input
        } else {
            $onChange = '';
        }

        $html .= '<input type="text" ' . $this->DisplayAttributes() . ' size="10" maxlength="10" value="' . substr($this->value, 0, 10) . '" />' . EOL;
        $html .= <<<EOA
<script type="text/javascript">
$(function() {
    $("#{$this->id}").datepicker(){$onChange};
});
</script>
EOA;

        $this->html .= $html;
    } // function MakeDate

    /**
    *   Creates 3 drop downs (month day year)
    */
    protected function MakeDateDrop() {
        if (!is_array($this->value)) {
            $strToTime = strtotime($this->value);
        } else {
            $strToTime = -1;
        }
        if ($this->value == 'empty' || $this->value === false || $strToTime === -1 || $this->value == '0000-00-00' || $this->value == '0000-00-00 00:00:00') {
            if (is_array($this->value)) {
                $month = $this->value['month'];
                $day = $this->value['day'];
                $year = $this->value['year'];
            } else {
                $month = $year = $day = '';
            }
        } else if ($this->value === true || strlen($this->value) == 0) {
            $dateParts = date_parse(date('r'));
            $month = $dateParts['month'];
            $day = $dateParts['day'];
            $year = $dateParts['year'];
        } else if (!is_array($this->value)) {
            $dateParts = date_parse($this->value);
            $month = $dateParts['month'];
            $day = $dateParts['day'];
            $year = $dateParts['year'];
        }

        $defaultOptions = $this->options;
        $defaultOptions['class'] = $this->class;

        $options = $defaultOptions;
        $options['id'] = $this->id . '_month';
        $options['source'] = $this->GetMonthArray();
        $month = new ClaeroField('select', $this->name . '[month]', $month, $options);

        $dayArray = array();
        for ($i = 1; $i <= 31; $i ++) {
            $dayArray[$i] = $i;
        }
        $options = $defaultOptions;
        $options['id'] = $this->id . '_day';
        $options['source'] = $dayArray;
        $day = new ClaeroField('select', $this->name . '[day]', $day, $options);

        $options = $defaultOptions;
        $options['id'] = $this->id . '_year';
        $options['source'] = $this->GetYearArray();
        $year = new ClaeroField('select', $this->name . '[year]', $year, $options);

        $this->html .= $month->GetHtml() . '&nbsp;' . $day->GetHtml() . '&nbsp;' . $year->GetHtml();
    } // function MakeDateDrop

    /**
    *   Creates 2 drop downs (month year)
    */
    protected function MakeMonthYearDrop() {
        if (!is_array($this->value)) {
            $strToTime = strtotime($this->value);
        } else {
            $strToTime = -1;
        }
        if ($this->value == 'empty' || $this->value === false || $strToTime === -1 || $this->value == '0000-00-00' || $this->value == '0000-00-00 00:00:00') {
            if (is_array($this->value)) {
                $month = $this->value['month'];
                $year = $this->value['year'];
            } else {
                $month = $year = '';
            }
        } else if ($this->value === true || strlen($this->value) == 0) {
            $dateParts = date_parse(date('r'));
            $month = $dateParts['month'];
            $year = $dateParts['year'];
        } else if (!is_array($this->value)) {
            $dateParts = date_parse($this->value);
            $month = $dateParts['month'];
            $year = $dateParts['year'];
        }

        $defaultOptions = $this->options;
        $defaultOptions['class'] = $this->class;

        $options = $defaultOptions;
        $options['id'] = $this->id . '_month';
        $options['source'] = $this->GetMonthArray();
        $month = new ClaeroField('select', $this->name . '[month]', $month, $options);

        $options = $defaultOptions;
        $options['id'] = $this->id . '_year';
        $options['source'] = $this->GetYearArray();
        $year = new ClaeroField('select', $this->name . '[year]', $year, $options);

        $this->html .= $month->GetHtml() . $this->options['month_year_separator'] . $year->GetHtml();
    } // function MakeDateDrop

    /**
    *   Creates 3 text fields (mm dd yyyy)
    */
    protected function MakeDateThreeField() {
        if (!is_array($this->value)) {
            $strToTime = strtotime($this->value);
        } else {
            $strToTime = -1;
        }
        if ($this->value == 'empty' || $this->value === false || $strToTime === -1 || $this->value == '0000-00-00' || $this->value == '0000-00-00 00:00:00') {
            if (is_array($this->value)) {
                $month = $this->value['month'];
                $day = $this->value['day'];
                $year = $this->value['year'];
            } else {
                $month = $year = $day = '';
            }
        } else if ($this->value === true || strlen($this->value) == 0) {
            $dateParts = date_parse(date('r'));
            $month = $dateParts['month'];
            $day = $dateParts['day'];
            $year = $dateParts['year'];
        } else if (!is_array($this->value)) {
            $dateParts = date_parse($this->value);
            $month = $dateParts['month'];
            $day = $dateParts['day'];
            $year = $dateParts['year'];
        }

        $defaultOptions = $this->options;
        $defaultOptions['class'] = $this->class;
        $defaultOptions['attributes']['size'] = 2;
        $defaultOptions['attributes']['maxlength'] = 2;

        $options = $defaultOptions;
        $options['id'] = $this->id . '_month';
        $month = new ClaeroField('text', $this->name . '[month]', $month, $options);

        $options = $defaultOptions;
        $options['id'] = $this->id . '_day';
        $day = new ClaeroField('text', $this->name . '[day]', $day, $options);

        $options = $defaultOptions;
        $options['attributes']['size'] = 4;
        $options['attributes']['maxlength'] = 4;
        $options['id'] = $this->id . '_year';
        $year = new ClaeroField('text', $this->name . '[year]', $year, $options);

        $this->html .= $month->GetHtml() . '&nbsp;' . $day->GetHtml() . '&nbsp;' . $year->GetHtml();
    } // function MakeDate3Field

    /**
    *   Creates 2 drop downs (year month)
    */
    protected function MakeYearMonth() {
        if (!is_array($this->value)) {
            $strToTime = strtotime($this->value);
        } else {
            $strToTime = -1;
        }
        if ($this->value == 'empty' || $this->value === false || $strToTime === -1 || $this->value == '0000-00-00' || $this->value == '0000-00-00 00:00:00') {
            $month = $year = '';
            if (is_array($this->value)) {
                $month = $this->value['month'];
                $year = $this->value['year'];
            } else {
                $month = $year = '';
            }
        } else if ($this->value === true || strlen($this->value) == 0) {
            $dateParts = date_parse(date('r'));
            $month = $dateParts['month'];
            $year = $dateParts['year'];
        } else if (!is_array($this->value)) {
            $dateParts = date_parse($this->value);
            $month = $dateParts['month'];
            $year = $dateParts['year'];
        }

        $defaultOptions = $this->options;
        $defaultOptions['class'] = $this->class;

        $yearArray = $this->GetYearArray();
        $options = $defaultOptions;
        $options['id'] = $this->id . '_year';
        $options['source'] = $yearArray;
        $year = new ClaeroField('select', $this->name . '[year]', $year, $options);

        $options = $defaultOptions;
        $options['id'] = $this->id . '_month';
        $options['source'] = $this->GetMonthArray();
        $month = new ClaeroField('select', $this->name . '[month]', $month, $options);

        $this->html .= $year->GetHtml() . '&nbsp;' . $month->GetHtml() . '<input type="hidden" name="' . $this->name . '[day]" value="1" />';
    } // function MakeDateDrop

    /**
    *   Makes a select with grouped options based on `parent`
    *   The field `parent` is required in the SQL
    */
    protected function MakeSelectGrouped() {
        $status = true;

        if (!is_string($this->options['source'])) {
            trigger_error('Input Error: Did not receive a SQL statement for grouped select field (' . $this->name . ')', E_USER_ERROR);
            $html .= $this->value;

        } else {
            // check to see if we have an id field
            if ($this->options['source_id']) {
                $idField = $this->options['source_id'];
            } else {
                $status = false;
                trigger_error('No id field set for select', E_USER_ERROR);
            }

            // check to see if we have a value field
            if ($this->options['source_value']) {
                $valueField = $this->options['source_value'];
            } else {
                $status = false;
                trigger_error('No value field set for select', E_USER_ERROR);
            }

            // get the query
            $selectSource = $this->options['source'];
            $queryResult = $this->GetSource($selectSource, $idField, $valueField);
            if ($queryResult === false) {
                trigger_error('Query Error: SQL for MakeSelectGroup for field named "' . $this->name . '" failed ' . $this->claeroDb->GetLastQuery(), E_USER_ERROR);
                return $this->value;

            } else if ($queryResult->NumRows() !== 0) {
                // get the first row of the result
                $queryResult->FetchInto($row);
                // check to see if the parent_name field isset within the row
                if (!isset($row['parent'])) {
                    $status = false;
                    trigger_error('The parent field "parent" could not be found within the SQL results', E_USER_ERROR);
                }

                if ($status) {
                    // reset position back to the start
                    $queryResult->DataSeek();

                    // now prepare the 2 dimensional array, replacing the existing source in the object
                    $this->options['source'] = array();
                    while ($queryResult->FetchInto($row)) {
                        if (!isset($this->options['source'][$row['parent']])) {
                            $this->options['source'][$row['parent']] = array();
                        }
                        $this->options['source'][$row['parent']][$row[$idField]] = $row[$valueField];
                    }

                    // now create the select
                    $this->MakeSelect();
                }
            }
        }
    } // function MakeSelectGrouped

    /**
    *   Makes a select drop down from SQL
    */
    protected function MakeSelect() {
        $html = EOL; // not sure if these should be an EOL here... might cause weird formatting issues

        if (!($this->options['source'] && (is_string($this->options['source']) || is_array($this->options['source'])))) {
            trigger_error('Input Error: Did not receive a SQL statement or array of values for select field (' . $this->name . ')', E_USER_ERROR);
            $html .= $this->value;

        } else {
            $selectSource = $this->options['source'];

            if ($this->options['multiple']) {
                $attributes = array('multiple' => 'multiple', 'size' => 4);
            } else {
                $attributes = array('size' => 1);
            }

            if (is_string($selectSource) && stripos($selectSource, 'select') === false) {
                // we have array in the source in the format of "m|Male||f|Female" that we need to explode
                $selectSource = GetSourceArray($selectSource);
            }

            // set up the select and add all or none options if selected
            $html .= '<select' . $this->DisplayAttributes($attributes) . '>' . EOL;
            if ($this->options['select_one_flag']) {
                $html .= $this->GetSelectOption($this->value, '', '-- Select One --');
            } // if
            if ($this->options['select_all_flag']) {
                $html .= $this->GetSelectOption($this->value, 'all', 'All');
            } // if
            if ($this->options['select_none_flag']) {
                $html .= $this->GetSelectOption($this->value, 'none', 'None');
            } // if
            if (is_array($this->options['add_values'])) {
                foreach ($this->options['add_values'] as $value => $name) {
                    if (is_array($name)) {
                        $html .= '<optgroup label="' . $this->CleanText($value) . '">';
                        foreach ($name as $subValue => $subName) {
                            $html .= $this->GetSelectOption($this->value, $subValue, $subName);
                        }
                        $html .= '</optgroup>';
                    } else {
                        $html .= $this->GetSelectOption($this->value, $value, $name);
                    }
                }
            } // if

            // now we need to loop throught the values to display in the select
            // if $this->options['select_value'] is a string, then use it as a sql statement
            // if $this->options['select_value'] is an array, then use it as an array
            if (is_array($selectSource)) {
                // loop through array creating option tags
                // if an array value is an array, look for a 'name' key within the sub array and make an optgroup

                foreach ($selectSource as $value => $name) {
                    if (is_array($name)) {
                        $html .= '<optgroup label="' . $this->CleanText($value) . '">';
                        foreach ($name as $subValue => $subName) {
                            if (is_array($this->value)) {
                                if (in_array($subValue, $this->value)) {
                                    $optionValue = $subValue;
                                } else {
                                    $optionValue = '';
                                }
                            } else{
                                $optionValue = $this->value;
                            }

                            if ($subValue || $subName) $html .= $this->GetSelectOption($optionValue, $subValue, $subName);
                        }
                        $html .= '</optgroup>';
                    } else {
                        if (is_array($this->value)) {
                            if (in_array($value, $this->value)) {
                                $optionValue = $value;
                            } else {
                                $optionValue = '';
                            }
                        } else{
                            $optionValue = $this->value;
                        }
                        $html .= $this->GetSelectOption($optionValue, $value, $name);
                    }
                }

            } else {
                $status = true;
                if ($this->options['source_id']) {
                    $idField = $this->options['source_id'];
                } else {
                    $status = false;
                    trigger_error('No id field set for select');
                }

                // check to see if we have a value field
                if ($this->options['source_value']) {
                    $valueField = $this->options['source_value'];
                } else {
                    $status = false;
                    trigger_error('No value field set for select');
                }

                if ($status) {
                    $queryResult = $this->GetSource($selectSource, $idField, $valueField);
                    if ($queryResult) {
                        while ($queryResult->FetchInto($row)) {
                            if (is_array($this->value)) {
                                if (in_array($row[$idField], $this->value)) {
                                    $optionValue = $row[$idField];
                                } else {
                                    $optionValue = '';
                                }
                            } else{
                                $optionValue = $this->value;
                            }
                            $html .= $this->GetSelectOption($optionValue, $row[$idField], $row[$valueField]);
                        } // while
                    } else {
                        // query failed...
                        //$html .= '<!-- query failed -->' . EOL;
                    } // if
                }
            }
            $html .= '</select>';
        } // if

        $this->html .= $html;
    } // function MakeSelect

    /**
    *   Makes a select of the tables in the database
    *   Uses MakeSelect to create the drop down
    */
    protected function MakeTableSelect() {
        $html = '';

        $queryResult = $this->claeroDb->Query("SHOW TABLES");
        if ($queryResult == false) {
            $html .= 'Unable to retrieve values.';
        } else if ($queryResult->NumRows() === 0) {
            // no rows found in query
            if ($this->options['show_no_value_text']) $html .= 'No values to choose from.';
        } else {
            $this->options['source'] = array();

            while ($queryResult->FetchInto($table, DB_FETCH_MODE_NUMBERED)) {
                if (!is_array($this->options['allowed_tables']) || in_array($table[0], $this->options['allowed_tables'])) {
                    $this->options['source'][$table[0]] = $table[0];
                }
            }

            $metaSql = "SELECT table_name, label FROM `" . CLAERO_META_TABLE . "` WHERE column_name = ''";
            $metaQuery = $this->claeroDb->Query($metaSql);
            if ($metaQuery !== false) {
                while ($metaQuery->FetchInto($meta)) {
                    if (isset($this->options['source'][$meta['table_name']])) $this->options['source'][$meta['table_name']] = $meta['label'];
                }
            } else {
                trigger_error('Query Error: Failed to get proper names from meta table for tables.', E_USER_ERROR);
            }

            asort($this->options['source']);

            if ($this->options['select_none_flag']) {
                $this->options['select_none_flag'] = false;
                $this->options['add_values'] = array(
                    '' => 'None',
                );
            }

            $this->MakeSelect();
        }

        if (strlen($html) > 0) $this->html .= $html;
    } // function MakeTableSelect

    /**
    *   Returns a option tag
    *
    *   @param      string      $selected   selected value within drop down
    *   @param      string      $value      value of option
    *   @param      string      $name       display value of option
    *
    *   @return     string      html string of option tag
    */
    protected function GetSelectOption($selected, $value, $name) {
        return '    <option value="' . $this->CleanText($value) . '"' . (strval($selected) == strval($value)  || (is_array($selected) && in_array($value, $selected)) ? ' selected="selected"' : '') . '>' . $this->CleanText($name) . '</option>' . EOL;
    } // function GetSelectOption

    /**
    *   Makes a checkbox
    */
    protected function MakeCheckbox() {
        $html = '';

        // output a hidden field with the same name as the checkbox with value of 0,
        // so that when posting, a zero is posted in place of an unchecked checkbox (which won't be posted')
        if ($this->options['checkbox_hidden']) {
            $hiddenField = new ClaeroField('hidden', $this->name, 0);

            $html .= $hiddenField->GetHtml();
        }

        if ($this->options['checkbox_display']) {
            $html .= '<label>';
        }

        if ($this->value) {
            $checked = ' checked="checked" ';
        } else {
            $checked = '';
        }

        $html .= '<input type="checkbox"' . $this->DisplayAttributes() . ' value="' . $this->options['checkbox_value'] . '"' . $checked . ' />';

        if ($this->options['checkbox_display']) {
            $html .= '&nbsp;' . $this->options['checkbox_display'] . '</label>';
        }

        $this->html .= $html;
    } // function MakeCheckbox

    /**
    *   Creates 3 radios for checkbox searching: Either, Checked, Unchecked
    */
    protected function MakeCheckboxSearch() {
        $html = '';

        $this->options['source'] = array(
            '' => 'Either',
            '1' => 'Checked',
            '2' => 'Unchecked',
        );

        $this->MakeRadios();
    } // function MakeCheckboxSearch

    /**
    *   Makes a checkboxes
    */
    protected function MakeCheckboxes() {
        $html = '';

        $this->value = (array) $this->value;
        $checkboxSource = $this->options['source'];

        if (is_string($checkboxSource) && stripos($checkboxSource, 'select') === false) {
            // we have array in the source that we need to explode in the format of "m|Male||f|Female"
            $checkboxSource = GetSourceArray($checkboxSource);
        }

        if ($this->options['checkbox_hidden']) {
            $hiddenField = new ClaeroField('hidden', $this->name, 0);

            $html .= $hiddenField->GetHtml();
        }

        if ($this->options['orientation'] == 'table' && $this->options['table_tag']) {
            $html .= '<table border="0" cellpadding="1" cellspacing="1">';
        }

        $idName = str_replace('[]', '', $this->name);

        if (is_array($checkboxSource)) {
            $col = 1;
            foreach ($checkboxSource as $id => $value) {
                if ($this->options['orientation'] == 'table') {
                    if ($col == 1) $html .= EOL . '<tr>';
                    $html .= '<td>';
                }

                if (in_array($id, $this->value)) {
                    $optionValue = true;
                } else {
                    $optionValue = false;
                }

                $checkboxOptions = array('id' => $idName . '_' . $id, 'checkbox_value' => $id, 'checkbox_hidden' => false);
                $checkbox = new ClaeroField('checkbox', $this->name, $optionValue, $checkboxOptions);

                $html .= '<label>' . $checkbox->GetHtml() . (!$this->options['add_nbsp'] ? '' : '&nbsp;') . $this->CleanText($value) . '</label>';

                ++ $col;

                switch($this->options['orientation']) {
                    case 'horizontal' :
                        $html .= '&nbsp;&nbsp;&nbsp;';
                        break;
                    case 'table' :
                        $html .= '</td>';
                        if ($col == $this->options['columns'] + 1) {
                            $html .= '</tr>';
                        }
                        break;
                    default :
                        $html .= HEOL;
                        break;
                } // switch orientation
            } // foreach source

        } else {
            $status = true;
            if ($this->options['source_id']) {
                $idField = $this->options['source_id'];
            } else {
                $status = false;
                trigger_error('No id field set for select.');
            }

            // check to see if we have a value field
            if ($this->options['source_value']) {
                $valueField = $this->options['source_value'];
            } else {
                $status = false;
                trigger_error('No value field set for select.');
            }

            if ($status) {
                $queryResult = $this->GetSource($checkboxSource, $idField, $valueField);

                $col = 1;
                while ($queryResult->FetchInto($row)) {
                    if ($this->options['orientation'] == 'table') {
                        if ($col == 1) $html .= EOL . '<tr>';
                        $html .= '<td>';
                    }

                    if (in_array($row[$idField], $this->value)) {
                        $optionValue = true;
                    } else {
                        $optionValue = false;
                    }

                    $checkboxOptions = array('id' => $idName . '_' . $row[$idField], 'checkbox_value' => $row[$idField], 'checkbox_hidden' => false);
                    $checkbox = new ClaeroField('checkbox', $this->name, $optionValue, $checkboxOptions);

                    $html .= '<label>' . $checkbox->GetHtml() . (!$this->options['add_nbsp'] ? '' : '&nbsp;') . $this->CleanText($row[$valueField]) . '</label>';

                    ++ $col;

                    switch($this->options['orientation']) {
                        case 'horizontal' :
                            $html .= '&nbsp;&nbsp;&nbsp;';
                            break;
                        case 'table' :
                            $html .= '</td>';
                            if ($col == $this->options['columns'] + 1) {
                                $html .= '</tr>';
                                $col = 1;
                            }
                            break;
                        default :
                            $html .= HEOL;
                            break;
                    } // switch orientation
                } // while
            } // if status (valid data)
        } // if array or string

        // remove the last line break for extra spaces
        switch($this->options['orientation']) {
            case 'horizontal' :
                $html = substr($html, 0, -1);
                break;
            case 'table' :
                if ($this->options['table_tag']) {
                    $html .= '</table>';
                }
                break;
            default :
                $html = substr($html, 0, -6);
                break;
        }

        $this->html .= $html;
    } // function MakeCheckboxes

    /**
    *   Makes a multiple select
    */
    protected function MakeMultipleSelect() {
        $html = EOL; // not sure if this should be here... may cause weird formatting issues

        $this->options['multiple'] = 'multiple';
        if (!isset($this->options['attributes']['size'])) $this->options['attributes']['size'] = 4;

        if (substr($this->name, -2) != '[]') $this->name .= '[]';

        $this->MakeSelect();

        $this->html .= $html;
    } // function MakeMultipleSelect

    /**
    *   Makes a text input
    */
    protected function MakeText() {
        $html = '';

        $html .= '<input type="text"' . $this->DisplayAttributes(array('size' => 30, 'maxlength' => 30)) . ' value="' . $this->CleanText() . '" />';

        $this->html .= $html;
    } // function MakeText

    /**
    *   Make a numeric text input
    *   This is just a standard text input, with a numeric class added to it
    */
    protected function MakeNumeric() {
        $this->SetAttribute('class', 'numeric');
        $this->MakeText();
    } // function MakeNumeric

    /**
    *   Makes a password input with a confirmation
    */
    protected function MakePasswordConfirm() {
        $html = '';

        $html .= '<input type="password"' . $this->DisplayAttributes(array('size' => 30, 'maxlength' => 30)) . ' value="' . $this->CleanText() . '" />';

        if ($this->options['password_confirm']) {
            if (isset($this->options['password_confirm']['name']) && $this->options['password_confirm']['name'] != '') {
                $this->name = $this->options['password_confirm']['name'];
            } else {
                $this->name .= '_confirm';
            }
            if (isset($this->options['password_confirm']['id']) && $this->options['password_confirm']['id'] != '') {
                $this->id = $this->options['password_confirm']['id'];
            } else {
                $this->id .= '_confirm';
            }
            $html .= HEOL . '<input type="password"' . $this->DisplayAttributes(array('size' => 30, 'maxlength' => 30)) . ' value="' . $this->CleanText() . '" />';
        } else {
            $html .= EOL;
        }

        $this->html .= $html;
    } // function MakePasswordConfirm

    /**
    *   Makes a password input
    */
    protected function MakePassword() {
        $html = '';

        $html .= '<input type="password"' . $this->DisplayAttributes(array('size' => 30, 'maxlength' => 30)) . ' value="' . $this->CleanText() . '" />';

        $this->html .= $html;
    } // function MakePassword

    /**
    *   Makes a text area
    */
    protected function MakeTextArea() {
        $html = '';

        $html .= '<textarea' . $this->DisplayAttributes(array('rows' => 5, 'cols' => 60)) . '>';
        $html .= $this->CleanText($this->value);
        $html .= '</textarea>';

        $this->html .= $html;
    } // function MakeTextArea

    /**
    *   Makes radio buttons
    */
    protected function MakeRadios() {
        $html = '';

        $radioSource = $this->options['source'];

        if (is_string($radioSource) && stripos($radioSource, 'select') === false) {
            // we have array in the source that we need to explode in the format of "m|Male||f|Female"
            $radioSource = GetSourceArray($radioSource);
        }

        if (!is_array($radioSource)) {
            $status = true;
            if ($this->options['source_id']) {
                $idField = $this->options['source_id'];
            } else {
                $status = false;
                trigger_error('Input Error: No id field set for select.');
            }

                // check to see if we have a value field
            if ($this->options['source_value']) {
                $valueField = $this->options['source_value'];
            } else {
                $status = false;
                trigger_error('Input Error: No value field set for select.');
            }

            if ($status) {
                $queryResult = $this->GetSource($radioSource, $idField, $valueField);

                while ($queryResult->FetchInto($row)) {
                    $this->preparedSource[$row[$idField]] = $row[$valueField];
                } // while
            } // if status (valid input)
        } else {
            $this->preparedSource = $radioSource;
        } // if array or string

        if ($this->options['orientation'] == 'table_vertical' && $this->options['columns'] === null) {
            $this->options['columns'] = count($this->preparedSource);
        }

        if (($this->options['orientation'] == 'table' || $this->options['orientation'] == 'table_vertical') && $this->options['table_tag']) {
            $html .= '<table border="0" cellpadding="1" cellspacing="1" class="cRadioTable">';
        }

        $col = 1;
        foreach($this->preparedSource as $radioKey => $radioValue) {
            switch($this->options['orientation']) {
                case 'horizontal' :
                    if ($col != 1) $html .= '&nbsp;&nbsp;&nbsp;';
                    break;
                case 'table' :
                case 'table_vertical' :
                    if ($col == 1) $html .= '<tr>';
                    $html .= '<td>';
                    break;
                default :
                    if ($col != 1) $html .= HEOL;
                    break;
            } // switch orientation

            if ($this->value == $radioKey) {
                $checked = ' checked="checked"';
            } else {
                $checked = '';
            }

            $id = $this->id . '_' . $radioKey;
            if ($this->options['orientation'] != 'table_vertical') {
                $html .= '<label><input type="radio"' . $this->DisplayAttributes(array(), false) . ' id="' . $id . '" value="' . $radioKey . '"' . $checked . ' />&nbsp;' . $this->CleanText($radioValue) . '</label>';
            } else {
                $html .= '<label for="' . $id . '">' . $this->CleanText($radioValue) . '<br /><input type="radio"' . $this->DisplayAttributes(array(), false) . ' id="' . $id . '" value="' . $radioKey . '"' . $checked . ' /></label>';
            }

            if ($this->options['orientation'] == 'table' || $this->options['orientation'] == 'table_vertical') {
                $html .= '</td>';
                if ($col == $this->options['columns']) {
                    $col = 1;
                    $html .= '</tr>';
                } else {
                    ++ $col;
                }
            } else {
                ++ $col;
            }
        } // foreach

        if (($this->options['orientation'] == 'table' || $this->options['orientation'] == 'table_vertical') && $this->options['table_tag']) {
            $html .= '</table>';
        }

        $this->html .= $html;
    } // function MakeRadios

    /**
    *   Returns the HTML for an individual radio
    *
    *   @param      bool/anything   $radioKey       if bool true, then it will get the next key in the source and use it, if anything else it will use it as the key
    *
    *   @return     string          HTML for display
    */
    public function GetRadio($radioKey = true) {
        // if the field has not been prepared, prepare it first so $this->preparedSource is populated
        if (!$this->fieldHtmlPrepared) {
            $this->PrepareFieldHtml();
            reset($this->preparedSource);
        }

        // set to true, so use the next key in the array
        if ($radioKey === true) {
            $radioKey = key($this->preparedSource);
            next($this->preparedSource);
        }

        if ($this->value == $radioKey) {
            $checked = ' checked="checked" ';
        } else {
            $checked = '';
        }

        return '<input type="radio"' . $this->DisplayAttributes(array(), false) . ' id="' . $this->name . '_' . $radioKey . '" value="' . $radioKey . '"' . $checked . ' />';
    } // function GetRadio

    /**
    *   Makes button
    */
    protected function MakeButton() {
        $html = '';

        $html .= '<input type="button"' . $this->DisplayAttributes(array('onclick' => '')) . ' value="' . $this->CleanText() . '" />';

        $this->html .= $html;
    } // function MakeButton

    /**
    *   Makes submit
    */
    protected function MakeSubmit() {
        $html = '';

        $html .= '<input type="submit"' . $this->DisplayAttributes() . ' value="' . $this->CleanText() . '" />';

        $this->html .= $html;
    } // function MakeSubmit

    /**
    *   Makes reset
    */
    protected function MakeReset() {
        $html = '';
        $html .= '<input type="reset"' . $this->DisplayAttributes() . ' value="' . $this->CleanText() . '" />';

        $this->html .= $html;
    } // function MakeReset

    /**
    *   Makes a file input
    */
    protected function MakeFile() {
        $html = '';

        $html .= '<input type="file"' . $this->DisplayAttributes() . ' />';

        $this->html .= $html;
    } // function MakeFile

    /**
    *   Makes a hidden input
    */
    protected function MakeHidden() {
        $html = '';

        $html .= '<input type="hidden"' . $this->DisplayAttributes() . ' value="' . $this->CleanText() . '" />';

        $this->html .= $html;
    } // function MakeHidden

    /**
    *   Makes a textarea to support a TinyMCE editor
    */
    protected function MakeHtml() {
        $html = '';

        if (!$this->options['override_class']) {
            if (strlen($this->class) > 0) $this->class .= ' mceEditor';
            else $this->class = 'mceEditor';
        }

        $html .= '<textarea ' . $this->DisplayAttributes(array('rows' => 5, 'cols' => 90)) . '>';
        $html .= $this->CleanText($this->value);
        $html .= '</textarea>';

        $this->html .= $html;
    } // function MakeHtml

    /**
    *   Makes a text field (in display is creates a link)
    */
    protected function MakeLink() {
        $this->MakeText();
    } // function MakeLink

    /**
    *   Makes a drop down of the field types available within the current object
    *   Based on the method name like "Make[field type]"
    */
    protected function MakeFormType() {
        $this->options['attributes'] = $this->attributes;
        $this->options['source'] = array('' => 'None');

        $methods = get_class_methods($this);
        sort($methods);

        foreach ($methods as $method) {
            if (strpos($method, 'Make') === 0) {
                $displayName = trim(preg_replace('/([A-Z])/', ' \\1', str_replace('Make', '', $method)));
                $value = str_replace(' ', '_', strtolower($displayName));
                $this->options['source'][$value] = $displayName;
            }
        }

        $select = new ClaeroField('select', $this->name, $this->value, $this->options);
        $this->html .= $select->GetHtml();
    } // function MakeFieldType

    /**
    *   Makes a text field with a calendar icon and popup calendar beside and optional date format legend
    */
    protected function MakeDatetime() {
        $html = '';
        $dateFormat = 'Y-m-d';
        $origName = $this->name;

        // figure out what the value of the date field should be
        $strToTime = strtotime($this->value);
        if ($this->value == 'empty' || $this->value === false || $strToTime === -1 || $this->value == '0000-00-00' || $this->value == '0000-00-00 00:00:00') {
            $this->value = '';
        } else if ($this->value === true || strlen($this->value) == 0) {
            $this->value = date($dateFormat);
        }

        if ($this->value == '') {
            $date = $hour = $min = $sec = $modulation = '';
        } else {
            $unix = strtotime($this->value);
            $date = date($dateFormat, $unix);
            $hour = date('g', $unix);
            $min = date('i', $unix);
            $sec = date('s', $unix);
            $modulation = date('a', $unix);
        }

        if (isset($this->attributes['on_change'])) {
            $onChange = $this->attributes['on_change'];
            unset($this->attributes['on_change']); // remove it so it dosen't get added to the input
        } else {
            $onChange = '';
        }

        $this->name = $origName . '[date]';
        $html .= '<input type="text" ' . $this->DisplayAttributes() . ' size="10" maxlength="10" value="' . substr($this->value, 0, 10) . '" />' . EOL;
        $html .= <<<EOA
<script type="text/javascript">
$(function() {
    $("#{$this->id}").datepicker(){$onChange};
});
</script>
EOA;

        $this->name = $origName . '[hour]';
        $html .= '<input type="text"' . $this->DisplayAttributes(array(), false) . ' size="2" maxlength="2" value="' . $hour . '" style="text-align:right;" /> : ';
        $this->name = $origName . '[min]';
        $html .= '<input type="text"' . $this->DisplayAttributes(array(), false) . ' size="2" maxlength="2" value="' . $min . '" style="text-align:right;" /> ';
        if ($this->options['show_seconds']) {
            $this->name = $origName . '[sec]';
            $html .= ' : <input type="text"' . $this->DisplayAttributes(array(), false) . ' size="2" maxlength="2" value="' . $sec . '" style="text-align:right;" /> ';
        }

        $modulationSelect = new ClaeroField('radios', $origName . '[modulation]', $modulation, array('source' => array('am' => 'AM', 'pm' => 'PM')));
        $html .= $modulationSelect->GetHtml();

        $this->name = $origName;

        $this->html .= $html;
    } // function MakeDatetime

    /**
    *   Creates a 2 radio Yes/No
    */
    public function MakeYesNoRadio() {
        if ($this->options['reverse_radios']) {
            $this->options['source'] = array(
                2 => 'No',
                1 => 'Yes',
            );
        } else {
            $this->options['source'] = array(
                1 => 'Yes',
                2 => 'No',
            );
        }

        $this->MakeRadios();
    } // function MakeYesNoRadio

    /**
    *   Creates a 2 radio Male/Female
    */
    public function MakeGenderRadio() {
        if ($this->options['reverse_radios']) {
            $this->options['source'] = array(
                2 => 'Female',
                1 => 'Male',
            );
        } else {
            $this->options['source'] = array(
                1 => 'Male',
                2 => 'Female',
            );
        }

        $this->MakeRadios();
    } // function MakeGenderRadio

    /**
    *   Creates a select field with a range of numbers based on value_start, value_end, and value_increment
    */
    public function MakeNumberDrop() {
        $array = range($this->options['value_start'], $this->options['value_end'], $this->options['value_increment']);

        // since range() creates an array of the keys always starting at 0, we need to loop through and set them to the same as the value
        $sourceArray = array();
        foreach ($array as $num) {
            $sourceArray[$num] = $num;
        }

        $this->options['source'] = $sourceArray;

        $this->MakeSelect();
    } // function MakeNumberDrop

    /**
    *   Makes a drop of height measurements, using a start end value in inches in source table (for example 48|90 will do 4' to 7'6")
    *   Will also include the metric measurement in cm
    *   The first value will have "or under" and the last value will have "or over"
    *
    */
    public function MakeHeightDrop() {
        for ($i = $this->options['height_start']; $i <= $this->options['height_end']; $i += $this->options['value_increment']) {
            $value = floor($i / 12). '\'' . ($i % 12 > 0 ? $i % 12 . '"' : '') . ' or ' . round($i * 2.54, 0) . 'cm';
            if ($i == $this->options['height_start']) $value .= ' or under';
            else if ($i == $this->options['height_end']) $value .= ' or over';
            $sourceArray[$i] = $value;
        }

        $this->options['source'] = $sourceArray;

        $this->MakeSelect();
    } // function MakeHeightDrop

    /**
    *   Cleans the value for display purposes
    *
    *   @param    string    $var    specified value that's not the value of field (default: false)
    *
    *   @return     void    just sets the value to a clean version of value
    */
    protected function CleanText($var = false) {
        if ($this->options['clean_text']) {
            if ($var !== false) {
                return claero::EscapeOutputForHtml($var);
            } else {
                return claero::EscapeOutputForHtml($this->value);
            }
        } else {
            if ($var !== false) {
                return $var;
            } else {
                return $this->value;
            }
        }
    } // function CleanText

    /**
    *   Get the value of $this->numRows which should contain the number of options found for a select type field
    *
    *   @return     int    number of rows / fields
    */
    public function NumRows() {
        return $this->numRows;
    } // function NumRows

    /**
    *   Gets query result for select, radio, and checkboxes
    *
    *     @param    string    $sql    sql statement to run to retrieve results
    *
    *   @return     sql result    the query result from FetchInto
    */
    protected function GetSource($sql, $idField, $valueField) {
        // run SQL query and create option tags from results
        // $html is neccessary here for displaying messages
        $html = '';

        $queryResult = $this->claeroDb->Query($sql);
        $this->numRows = $queryResult ? $queryResult->NumRows() : 0;
        if ($queryResult === false) {
            $html .= 'Unable to retrieve values.';

        } else if ($this->numRows === 0) {
            // no rows found in query
            if ($this->options['show_no_value_text'] && !$this->options['select_one_flag'] && !$this->options['select_none_flag'] && !$this->options['select_all_flag'] && (!is_array($this->options['add_values']) || count($this->options['add_values']) == 0)) $html .= 'No values to choose from.';

        } else {
            $status = true;

            // get the first row of the result
            $queryResult->FetchInto($row);
            // check to see if the id field isset within the row
            if (!isset($row[$idField])) {
                $status = false;
                trigger_error('The id field "' . $idField . '" could not be found within the SQL results', E_USER_ERROR);
            }
            // check to see if the value field isset within the row
            if (!isset($row[$valueField])) {
                $status = false;
                trigger_error('The value field "' . $valueField . '" could not be found within the SQL results', E_USER_ERROR);
            }

            if ($status) {
                // return the data pointer in the query result back to row 0 (the default of DataSeek())
                $queryResult->DataSeek();
            } else {
                $html .= 'Unable to retrieve values.';
            } // if status
        } // if rows in query

        $this->html .= $html;

        return $queryResult;
    } // function GetSource

    /**
    *   Returns an array of years based on the options year_start, year_end, and year_order
    *   The value and the key will be the same value
    *
    *   @return     array   The array of years to generate a select
    */
    protected function GetYearArray() {
        $yearArray = array();

        if ($this->options['year_order'] == 'asc') {
            for ($i = $this->options['year_start']; $i <= $this->options['year_end']; ++$i) {
                $yearArray[$i] = $i;
            }
        } else {
            for ($i = $this->options['year_start']; $i >= $this->options['year_end']; --$i) {
                $yearArray[$i] = $i;
            }
        }

        return $yearArray;
    } // function GetYearArray

    /**
    *   Returns an array of months depending on the option use_month_numbers
    *
    *   @return     array   The array of months, which the key being the month number and the value being the month name or number
    */
    protected function GetMonthArray() {
        if ($this->options['use_month_numbers']) {
            return array(1 => '01', 2 => '02', 3 => '03', 4 => '04', 5 => '05', 6 => '06', 7 => '07', 8 => '08', 9 => '09', 10 => '10', 11 => '11', 12 => '12');
        } else {
            return array(1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April', 5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August', 9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December');
        }
    } // function GetMonthArray

    /**
    *   Returns the value of $this->prepareSource (if not populated yet, it will be null)
    *
    *   @return     array       The prepare source or null is not prepared yet
    */
    public function GetPreparedSource() {
        return $this->preparedSource;
    } // function GetPreparedSource

    /******************************
    * The following functions can be used to get the value of hard coded fields within ClaeroField
    * They should be able to be called statically
    *******************************/

    /**
    *   Can be called statically
    *   Returns Yes/No depending on the $value (empty string if not 1 or 2)
    *
    *   @param      int     $value      The value to process
    *
    *   @return     string      Yes/No or empty string if not 1 or 2
    */
    public static function GetYesNoValue($value) {
        if ($value == 1) return 'Yes';
        else if ($value == 2) return 'No';
        return '';
    } // function GetYesNoValue
} // class ClaeroField