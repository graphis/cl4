<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
*   This class is used to build an HTML table with data and options.
*   (20100623 CSN this class was taken from claerolib_3 and modified for claerolib_4 aka Koaha module 'claero-table', almost not change needed)
*
*   @author     Claero Systems <craig.nakamoto@claero.com> / XM Media Inc <dhein@xmmedia.net>
*   @copyright  Claero Systems / XM Media Inc  2004-2009
*   @version    $Id: class-claero_table.php 715 2010-01-15 17:19:50Z cnakamoto $
*/

class Claerolib4_Table {

    /**
    *   this is the array of rows in the current table, add using AddRow and/or AddCell
    *   @var    string
    */
    protected $tableData = array();

    /**
    *   this is the array of attributes for the table
    *   @var    string
    */
    protected $tableOptions = array();

    /**
    *   this is the row number of the last row added
    *   @var    string
    */
    protected $lastRowNumber = 0;

    /**
    *   array of td attributes if set in form $this->tdAttribute[$rowNumber][$columnNumber] = $attributeString, set using $this->SetAttribute()
    *   @var    string
    */
    protected $tdAttribute = array();

    /**
    *   array of tr attributes if set in form $this->trAttribute[$rowNumber] = $attributeString, set using $this->SetAttribute()
    *   @var    string
    */
    protected $trAttribute = array();

    /**
    *   array of column spans in form $this->colSpans[$rowNumber][$colNumber] = $count, set using $this->SetColSpan(), which also adds the attribute to the row
    *   @var    string
    */
    protected $colSpan = array();

    /**
    *   Prepares the table
    *
    *   @param  array   $options    array of options for the table (row, column, and cell attributes are set with SetAttributes (these are dealt within ResetOptions())
    *           cell_spacing => sets the cellspacing for the table (default 0)
    *           cell_padding = > set the cellpadding for the table (default 0)
    *           border => sets the border for the table (default 0)
    *           transpose => if true, then the rows will be columns (default false)
    *           min_width => an array of minimum widths of columns (default none set)
    *           width => an array of widths of columns (uses the width of the td tag) (default none set)
    *           spacer => the image used as a space for min widths
    *           heading => an array of headings (default none set)
    *           odd_even => if true, then the odd or even class will be applied to each row (default true)
    *           debug => if true, additional information will be put in HTML comments regarding the table (default false)
    *           header => if false, then the table tag will be displayed (default true)
    *           footer => if false, then the closing table tag will not be displayed (default true)
    *           sort_by => the column by which the table is sorted by, will add a class "sort..." for every cell in that column with the suffix of order_by (default 0)
    *           order_by => the suffix for the sort_by column class (see sort_by) (default 0)
    *           table_id => the table id (default claero-table-rand string)
    *           table_class => the table class
    *           table_style => the style to put on the table
    *           populate_all_cols => enables/disables the populating of all the columns even if all the columns are not sent for each row
    *           tr_class => array of classes for each tr (if none specified then no class other than defaults)
    *           td_class => array of classes for each td specified by row then column (if none specified then only column class will be added)
    *           tr_attributes => additional attributes for each tr (such as on_click) specified by the full string
    *           start_row_num => the row number to start with, used for controlling the odd/even shading
    *           num_columns => this is the number of columns that will be added to the table, use this when your first row does not contain the correct number of rows
    *           col_span => this populates $this->colSpan with column spans; array($rowNumber => array($columnNumber => $cols))
    *           cell_align => the alignment for the cells, keyed on column number; will be applied to all table rows, including the header row
    *           rows_only => return on the contents of the tbody.
    */
    public function __construct($options = array()) {

        $this->ResetOptions($options);

    } // function __construct

    /**
    *   add options
    *
    *   @return     int     row number of added row
    */
    public function ResetOptions($options) {

        // set up default options (and clear any existing options)
        $this->tableOptions = array(
            'cell_spacing' => 0,
            'cell_padding' => 0,
            'border' => 0,
            'transpose' => false,
            'min_width' => array(),
            'width' => NULL,
            'spacer' => "/images/spacer.gif",
            'heading' => NULL,
            'odd_even' => true,
            'debug' => false,
            'header' => true,
            'footer' => true,
            'sort_by' => 0,
            'order_by' => 0,
            'table_id'=> 'claero-table-' . substr(md5(time()), 0, 8),
            'table_class' => '',
            'table_style' => '',
            'table_width' => null,
            'populate_all_cols' => true,
            'tr_class' => array(),
            'td_class' => array(),
            'tr_attributes' => array(),
            'start_row_num' => 0,
            'num_columns' => null,
            'col_span' => null,
            'cell_align' => array(),
            'rows_only' => false,
        );

        // set any passed options
        foreach ($options as $key => $value) {
            $this->tableOptions[$key] = $value;
        } // foreach

        foreach ($this->tableOptions['tr_attributes'] as $rowNumber => $attribute) {
            $this->SetAttribute($rowNumber, false, $attribute);
        }

        $this->lastRowNumber = $this->tableOptions['start_row_num'];

        // loop through the colspans using the SetColSpan() function
        if (is_array($this->tableOptions['col_span'])) {
            foreach ($this->tableOptions['col_span'] as $rowNumber => $cols) {
                foreach ($cols as $columnNumber => $span) {
                    $this->SetColSpan($rowNumber, $columnNumber, $span);
                }
            }
        }

    } // function ResetOptions

    /**
    *   Add html tr or td tag attributes.  Can set row or cell.
    *
    *   @param      int     $rowNumber      the row number to be set - required
    *   @param      int     $columnNumber   the column number to be set - required for cell only, set to false otherwise
    */
    public function SetAttribute($rowNumber, $columnNumber, $attributeString) {

        if ($columnNumber !== false) {
            // must be cell attribute
            if (!isset($this->tdAttribute[$rowNumber][$columnNumber])) $this->tdAttribute[$rowNumber][$columnNumber] = '';
            $this->tdAttribute[$rowNumber][$columnNumber] .= (strlen($this->tdAttribute[$rowNumber][$columnNumber]) > 0 ? ' ' : '') . $attributeString;
        } else {
            // must be a row attribute
            if (!isset($this->trAttribute[$rowNumber])) $this->trAttribute[$rowNumber] = '';
            $this->trAttribute[$rowNumber] .= (strlen($this->trAttribute[$rowNumber]) > 0 ? ' ' : '') . $attributeString;
        } // if

    } // function SetOptions

    public function SetCellClass($rowNumber, $columnNumber, $class) {
        if (!isset($this->tableOptions['td_class'][$rowNumber][$columnNumber])) $this->tableOptions['td_class'][$rowNumber][$columnNumber] = $class;
        else $this->tableOptions['td_class'][$rowNumber][$columnNumber] .= ' ' . $class;
    } // function SetCellClass

    /**
    *   Sets the row class in the tableOptions
    *
    *   @param      int     $rowNumber      The row number to apply the class to
    *   @param      string  $class          The class to apply (adds to existing classes)
    */
    public function SetRowClass($rowNumber, $class) {
        if (!isset($this->tableOptions['tr_class'][$rowNumber])) $this->tableOptions['tr_class'][$rowNumber] = $class;
        else $this->tableOptions['tr_class'][$rowNumber] .= ' ' . $class;
    } // function SetRowClass

    /**
    *   Sets the row id using SetAttribute
    *
    *   @param      int     $rowNumber      The row number to apply the id to
    *   @param      string  $id             The id to apply
    */
    public function SetRowId($rowNumber, $id) {
        $this->SetAttribute($rowNumber, false, 'id="' . $id . '"');
    } // function SetRowId

    /**
    *   Sets the column span for a specific column using SetAttribute
    *
    *   @param      int     $rowNumber      The row number of the column (starting at 0)
    *   @param      int     $columnNumber   The column number (starting at 0)
    *   @param      int     $count          The number of columns to span (defualt 2)
    */
    public function SetColSpan($rowNumber, $columnNumber, $count = 2) {
        $this->SetAttribute($rowNumber, $columnNumber, 'colspan="' . $count . '"');
        $this->colSpan[$rowNumber]['column_number'] = $columnNumber;
        $this->colSpan[$rowNumber]['column_count'] = $count;
    } // function SetColSpan

    /**
    *   Add a row of data to the table (populate the next row)
    *
    *   @param      array       $rowData        An array of the data to display
    *
    *   @return     int     row number of added row
    */
    public function AddRow($rowData = array(), $escapeOutputForHtml = false) {

        // set last row number
        $currentRow = $this->lastRowNumber;
        ++$this->lastRowNumber;

        if ($escapeOutputForHtml) {
            foreach ($rowData as $key => $value) {
                $rowData[$key] = EscapeOutputForHtml($value);
            }
        }

        // add data
        $this->tableData[$currentRow] = $rowData;

        return $currentRow;

    } // function AddRow

    /**
    *   Adds a cell to the row specified, other at the end of the existing rows or a specific column
    *   (the row *does not* need to already exist within the tableData array)
    *
    *   @param      int     $rowNumber      The row to add the cell to
    *   @param      string  $cellData       The string to put inside the cell (put in tableData)
    *   @param      int     $columnNumber   The column number to put the data in (default: null therefore the next column in the row)
    *
    *   @return     int     The column number that was added
    */
    public function AddCell($rowNumber, $cellData, $columnNumber = null) {
        if (!isset($this->tableData[$rowNumber])) {
            $this->tableData[$rowNumber] = array();
            ++$this->lastRowNumber;
        }

        if ($columnNumber === null) {
            $columnNumber = count($this->tableData[$rowNumber]);
        }

        $this->tableData[$rowNumber][$columnNumber] = $cellData;

        return $columnNumber;
    } // function AddCell

    /**
    *   Adds a heading to the list of headings
    *
    *   @param      string      $text       The string to put in the table cell
    *
    *   @return     int         The column that was added
    */
    public function AddHeading($text) {
        $this->tableOptions['heading'][] = $text;

        return count($this->tableOptions['heading']);
    } // function AddHeading

    /**
    *   Generates and returns the html of the table
    *
    *   @return     string      HTML of the table
    */
    public function GetHtml() {

        $resultHtml = '';

        if ($this->tableOptions['num_columns'] !== null) {
            $numColumns = intval($this->tableOptions['num_columns']);
        } else {
            // reset the array so we count the number of columns in the first row
            // (csn - but if the first row has a column span, this doesn't work, so if heading is set,
            // use this instead to at least solve the problem when there is a heading)
            if (isset($this->tableOptions['heading']) && sizeof($this->tableOptions['heading']) > 0) {
                $numColumns = sizeof($this->tableOptions['heading']);
            } else {
                reset($this->tableData);
                $numColumns = isset($this->tableData[key($this->tableData)]) ? count($this->tableData[key($this->tableData)]) : 0;
            } // if
        }

        if ($this->tableOptions['debug']) {
            $resultHtml .= "DisplayTable -> NumRows: ". $numRows . "<br/>\n";
            $resultHtml .= "DisplayTable -> NumColumns: ". $numColumns . "<br/>\n";
        } // if

        if (!$this->tableOptions['rows_only']){
            if ($this->tableOptions['header']) {
               // start the table
               $resultHtml .= EOL . '<table';
               $resultHtml .= ' id="' . $this->tableOptions['table_id'] . '"';
               $resultHtml .= (strlen($this->tableOptions['border']) > 0) ? ' border="' . $this->tableOptions['border'] . '"' : '';
               $resultHtml .= (strlen($this->tableOptions['cell_spacing']) > 0) ? ' cellspacing="' . $this->tableOptions['cell_spacing'] . '"' : '';
               $resultHtml .= (strlen($this->tableOptions['cell_padding']) > 0) ? ' cellpadding="' . $this->tableOptions['cell_padding'] . '"' : '';
               $resultHtml .= (strlen($this->tableOptions['table_class']) > 0) ? ' class="' . $this->tableOptions['table_class'] . '"' : '';
               $resultHtml .= (strlen($this->tableOptions['table_style']) > 0) ? ' style="' . $this->tableOptions['table_style'] . '"' : '';
               $resultHtml .= (strlen($this->tableOptions['table_width']) > 0) ? ' width="' . $this->tableOptions['table_width'] . '"' : '';
               $resultHtml .= '>' . EOL;
            }

            // create the header row if applicable
            if ( !$this->tableOptions['transpose'] && !empty($this->tableOptions['heading']) ) {
                $resultHtml .= "<thead>\n";
                $resultHtml .= "    <tr>\n";
                // display the headings for each column
                for ($j=0; $j<$numColumns ; $j++) {
                    $resultHtml .= '        <th class="column' . $j . ($j == $this->tableOptions['sort_by'] ? ' sort' . $this->tableOptions['order_by']: '') .  '"';
                    // add column width if passed in options
                    if (is_array($this->tableOptions['width']) && $this->tableOptions['width'][$j] > 0) {
                        $resultHtml .= ' width="' . $this->tableOptions['width'][$j] . '"';
                    }
                    if (isset($this->tableOptions['cell_align'][$j]) && $this->tableOptions['cell_align'][$j]) $resultHtml .= ' align="' . $this->tableOptions['cell_align'][$j] . '"';
                    $resultHtml .= '>';
                    // set column min width if passed in options
                    // not sure if we need to check if the min_width is > 0
                    if (isset($this->tableOptions['min_width'][$j]) && $this->tableOptions['min_width'][$j] > 0) {
                        $resultHtml .= '<img src="' . $this->tableOptions['spacer'] . '" width="' . $this->tableOptions['min_width'][$j] . '" height="0" border="0" align="absbottom" /><br />';
                    }
                    $resultHtml .= (!empty($this->tableOptions['heading'][$j]) ? $this->tableOptions['heading'][$j] : '') . '</th>' . EOL;
                } // for
                $resultHtml .= '    </tr>' . EOL;
                $resultHtml .= '</thead>' . EOL;
            } // if

            $resultHtml .= '<tbody'  . (! $this->tableOptions['header'] && ! empty($this->tableOptions['table_id']) ? ' id="' . $this->tableOptions['table_id']. '"' : '') . '>' . EOL;
        }

        // display a spacer row if no heading row is displayed and minwidth is used
        if (is_array($this->tableOptions['min_width']) && !$this->tableOptions['transpose'] && empty($this->tableOptions['heading'])) {
            $resultHtml .= '    <!-- SPACER ROW: ******** -->' . EOL . '    <tr>' . EOL;
            // display a row with spacer images
            for ($j=0; $j<$numColumns ; $j++) {
                $resultHtml .= '        <td class="spacer"';
                // add column width if passed in options
                if (is_array($this->tableOptions['width']) && $this->tableOptions['width'][$j] > 0)
                    $resultHtml .= ' width="' . $this->tableOptions['width'][$j] . '"';
                $resultHtml .= '>';
                // set column min width if passed in options
                if (isset($this->tableOptions['min_width'][$j]) && $this->tableOptions['min_width'][$j] > 0)
                    $resultHtml .= '<img src="/images/spacer.gif" width="' . $this->tableOptions['min_width'][$j] . '" height="1" border="0" />';
                $resultHtml .= '</td>' . EOL;
            } // for
            $resultHtml .= '    </tr>' . EOL;
        } // if

        // display each row of data
        foreach ($this->tableData as $rowNum => $rows) {

            $resultHtml .= '    <!-- DATA ROW: ' . $rowNum . ' ******** -->' . EOL;

            // set up the row tag
            $resultHtml .= '    <tr class="row' . $rowNum;
            if ($this->tableOptions['odd_even']) $resultHtml .= (fmod($rowNum, 2) > 0) ? ' odd' : ' even';
            if (isset($this->tableOptions['tr_class'][$rowNum])) $resultHtml .= ' ' . $this->tableOptions['tr_class'][$rowNum];
            $resultHtml .= '"';
            if (isset($this->trAttribute[$rowNum])) $resultHtml .= ' ' . $this->trAttribute[$rowNum];
            $resultHtml .= '>' . EOL;

            // make headings the first column of the table if there are headings and we are transposing
            if ( $this->tableOptions['transpose'] && !empty($this->tableOptions['heading']) ) {
                $resultHtml .= '        <td>';
                $resultHtml .= (!empty($this->tableOptions['heading'][$rowNum]) ? $this->tableOptions['heading'][$rowNum]: '&nbsp;');
                $resultHtml .= '</td>' . EOL;
            } // if

            // add the data rows
            $cols = 0;
            foreach ($rows as $colNum => $rowValue) {
                // check for column span and don't add column if we are in a column span
                if ($this->InColSpan($colNum, $rowNum)) {
                    // don't do anything
                } else {
                    // add column
                    $resultHtml .= '        ';
                    if ($this->tableOptions['transpose']) {
                        $resultHtml .= '<td>' . $rowValue . '</td>' . EOL;
                    } else {
                        $resultHtml .= '<td class="column' . $colNum;
                        if ($rowNum == $this->tableOptions['sort_by']) $resultHtml .= ' sort' . $this->tableOptions['order_by'];
                        if (isset($this->tableOptions['td_class'][$rowNum][$colNum])) $resultHtml .= ' ' . $this->tableOptions['td_class'][$rowNum][$colNum];
                        $resultHtml .= '"';
                        if (isset($this->tableOptions['cell_align'][$colNum]) && $this->tableOptions['cell_align'][$colNum]) $resultHtml .= ' align="' . $this->tableOptions['cell_align'][$colNum] . '"';
                        if (isset($this->tdAttribute[$rowNum][$colNum])) $resultHtml .= ' ' . $this->tdAttribute[$rowNum][$colNum];
                        $resultHtml .= '>' . $rowValue . '</td>' . EOL;
                    } // if
                } // if
                ++$cols;
                if ($cols > 300) break;
            } // foreach

            if ($this->tableOptions['populate_all_cols'] && $cols < $numColumns) {
                // create the columns for the rest (this breaks the use of colspan right now, so you have to set it to false in the options)
                for ($colNum = $cols; $colNum < $numColumns; $colNum ++) {
                    $resultHtml .= '<td class="column' . $colNum . ($rowNum == $this->tableOptions['sort_by'] ? ' sort' . $this->tableOptions['order_by']: '') . '">&nbsp;</td>' . EOL;
                }
            }

            $resultHtml .= '    </tr>' . EOL;

        } // for

        if (!$this->tableOptions['rows_only']){
            $resultHtml .= '</tbody>' . EOL;

            if ($this->tableOptions['footer']) {
                $resultHtml .= '</table>' . EOL;
            }
        }

        return $resultHtml;

    } // function GetHtml

    /**
    *   Check to see if the given column number is within a column span for this row in the table
    *
    *   @return     boolean     true if it is, false otherwise
    */
    function InColSpan($colNum, $rowNum) {

        $columnsInSpan = array();

        // see if there are any colspans in this row first
        if (isset($this->colSpan[$rowNum])) {
            // now find which columns are in colspans
            foreach ($this->colSpan[$rowNum] AS $column => $span) {
                // add all the columns in the this span to the array
                for($i=$column+1; $i < $column + $span; $i++) $columnsInSpan[] = $i;
            } // foreach
        } // if

        return in_array($colNum, $columnsInSpan); // see if the column is in a span

    } // inColSpan

}