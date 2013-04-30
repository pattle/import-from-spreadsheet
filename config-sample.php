<?php

/*
|--------------------------------------------------------------------------
| Error reporting
|--------------------------------------------------------------------------
|
| Set error reporting to all
|
|
*/

error_reporting(E_ALL);

/*
|--------------------------------------------------------------------------
| Time limit
|--------------------------------------------------------------------------
|
| The maximum execution time for the script to run.  If its set to 0 then 
| there won't be a time limit set.
|
|
*/

set_time_limit(0);


/*
|--------------------------------------------------------------------------
| Memory limit
|--------------------------------------------------------------------------
|
| If the script is going to be resizing images then we are going to need to 
| increase the memory limit espcially if the images are big
|
| If we are just importing data from a spreadsheet then we probably won't
| need to increase it
|
*/

ini_set("memory_limit","500M");

/*
|--------------------------------------------------------------------------
| Default timezone
|--------------------------------------------------------------------------
|
| 
| 
|
|
 */

date_default_timezone_set('Europe/London');

/*
|--------------------------------------------------------------------------
| Workbooks
|--------------------------------------------------------------------------
|
| Set an array of workbooks to load along with their types
| 
| workbook_type (This must be set correctly otherwise the spreadsheet won't be loaded!)
|   Excel5 - File formats used by Excel between 1995 and 2003 (e.g .xls)
|   Excel2007 - File formats used by Excel 2007 and higher (e.g .xlsx)
|   OOCalc - An open document format (e.g .odf, .ods)
|   CSV - Files that end in .csv
|
| workbook_file
|   The path to the workbook
|
| primary_key
|   The primary key array in $aPrimaryKeys for this workbook
|
| map
|   The field map array in $aFieldMaps for this workbook
|
| start_row
|   The row to start reading the workbook from.  You may want to set this
|   to avoid column headings to be read
|
| end_row
|   The row to end the reading at.  If this is set to 0 PHPExcel will
|   stop reading where it gets to the end of the data
|
| columns
|   An array of columns to read.  You may want to set this if you only want
|   To read specific columns.  Set this to an empty array to make sure all
|   columns are read
|
|
 */

$aWorkbooks = array(
    '0' => array(
        'workbook_type' => 'Excel2007',
        'workbook_file' => '../spreadsheets/spreadsheet1.xlsx',
        'primary_key' => '0',
        'map' => '0',
        'start_row' => '2',
        'end_row' => '0',
        'columns' => array(),
    ),
    '1' => array(
        'workbook_type' => 'Excel5',
        'workbook_file' => '../spreadsheets/spreadsheet2.xls',
        'primary_key' => '0',
        'map' => '0',
        'start_row' => '2',
        'end_row' => '0',
        'columns' => array(),
    )
);


/*
|--------------------------------------------------------------------------
| Primary key select
|--------------------------------------------------------------------------
|
| Create an array to get the primary key we are going to be using for
| the update
| 
|
 */

$aPrimaryKeys = array(
    '0' => array(
        'field' => 'table1.table1_id',
        'table' => 'table1',
        'identifiers' => array(
            array(
                'type' => 'AND',
                'field' => 'table1.cell1',
                'value' => 'B',
                'like' => '%value'
            ),
            array(
                'type' => 'OR',
                'field' => 'table1.cell2',
                'value' => 'C',
                'like' => '%value'
            )
        )
    )
);

    
/*
|--------------------------------------------------------------------------
| Map data
|--------------------------------------------------------------------------
|
| Create an array to map spreadsheet columns to table fields.  You then
| link the spreadsheet to field map using the 'map' key in the $aWorkbooks
| array.  
| 
| You can create multiple fields maps if you want to use a different field
| map for a certain workbook or you the same one of multiple workbooks
| 
| The example below will create the update sql
|
| UPDATE table1, table2
| SET table1.cell1="VALUE IN COLUMN G" 
| table2.cell1="VALUE IN COLUMN I" 
| table2.cell2="VALUE IN COLUMN J" 
| table2.cell3="VALUE IN COLUMN N" 
| table2.cell4="VALUE IN COLUMN O" 
| table2.cell5="VALUE IN COLUMN P" 
| table2.cell6="VALUES IN COLUMNS L, M & K" 
| WHERE table1.index table2.index 
| AND table1.table1_id = "PRIMARY KEY VALUE";
| 
|
 */

$aFieldMaps = array(
    '0' => array(
        'tables' => 'table1, table2',
        'joins' => array(
            'table1.index' => 'table2.index'
        ),
        'fields' => array(
            'table1.cell1' => 'G',
            'table2.cell1' => 'I',
            'table2.cell2' => 'J',
            'table2.cell3' => 'N',
            'table2.cell4' => 'O',
            'table2.cell5' => 'P',
            'table2.cell6' => array('L', 'M', 'K')
        )
    )
);

?>
