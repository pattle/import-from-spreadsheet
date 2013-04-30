<?php

//Include the database connection
//This will contain the connection and selected the database we are importing the data too
include_once 'database.php';

//Include the config file
//This contains all of the configuration for this import
//E.g Workbooks, workbook types, field maps
include_once 'config.php';

//Load any classes needed to read the spreadshhet
include_once 'Classes/PHPExcel/IOFactory.php';
include_once 'Classes/RecordFilter.php';

$totalRecordsCount = 0;
$totalUpdatedCount = 0;

//Check to see if there are any workbooks defined in the config file
if(isset($aWorkbooks) && !empty($aWorkbooks))
{
        //Loop through all of the workbooks we need to read
        foreach($aWorkbooks as $aWorkbook)
        {
                //Check too see if the worbooks have been set up correctly in
                //the config by checking all the required array elements are there
                if(isset($aWorkbook['workbook_type']) && isset($aWorkbook['workbook_file']) && isset($aWorkbook['primary_key']) && isset($aWorkbook['map']))
                {
                        $workbookCount = 0;

                        //Define an array to store all of the data we get from the workbooks in
                        $aWorkBookData = array();

                        $filter = new RecordFilter($aWorkbook['start_row'], $aWorkbook['end_row'], $aWorkbook['columns'], $aWorkbook['exclusions']);

                        //Create the read using the workbook type
                        $oReader = PHPExcel_IOFactory::createReader($aWorkbook['workbook_type']);

                        //Set the barcode filter
                        $oReader->setReadFilter($filter);

                        $oReader->setReadDataOnly(true);

                        //Load the workbook
                        $oWorkbook = $oReader->load($aWorkbook['workbook_file']);

                        //For this workbook get the array to find the primary key will be doing the updates with
                        $aPrimaryKey = $aPrimaryKeys[$aWorkbook['primary_key']];

                        //For this workbook get the array to map columns to fields
                        $aQuery = $aFieldMaps[$aWorkbook['map']];

                        //Loop through all of the worksheets in the workbook
                        foreach ($oWorkbook->getWorksheetIterator() as $oWorksheet)
                        {
                                //Convert the worksheet data into an array
                                $aSheetData = $oWorksheet->toArray(null,true,true,true);

                                //Loop through the rows of worksheet data
                                foreach($aSheetData as $aRowData)
                                {
                                        //Filter out any null values and add it to our workbook array
                                        $aRowData = array_filter($aRowData);
                                        $aWorkBookData[] = $aRowData;
                                }
                        }

                        //Filter out any empty arrays
                        $aWorkBookData = array_filter($aWorkBookData);

                        //Loop through the rows
                        foreach($aWorkBookData as $aRow)
                        {
                                //Before we update this record in the database we need to check if it is in the database otherwise there is no point
                                $primaryKeySelect = 'SELECT ' . $aPrimaryKey['field'] . ' as primary_key FROM ' . $aPrimaryKey['table'] . ' WHERE ';
                                $primaryKeyWhere = '';

                                //Check to see if there are any identifiers defined
                                //Identifiers will be when we are matching a field with a value
                                if(isset($aPrimaryKey['identifiers']) && !empty($aPrimaryKey['identifiers']))
                                {
                                        //If there are loop through the identifiers and add them to the query
                                        foreach($aPrimaryKey['identifiers'] as $aIdentifier)
                                        {
                                                //Check to see if there has already been things added to the where clause
                                                //If there are then it means we need to prefix with AND, OR etc
                                                if($primaryKeyWhere != '')
                                                        $primaryKeyWhere .= ' ' . $aIdentifier['type'] . ' ';


                                                if(isset($aIdentifier['like']))
                                                {
                                                        $likeValue = str_replace('value', $aRow[$aIdentifier['value']], $aIdentifier['like']);

                                                        //Add to the where clause
                                                        $primaryKeyWhere .= mysql_real_escape_string($aIdentifier['field']) . ' LIKE "' . mysql_real_escape_string($likeValue) . '" ';
                                                }
                                                else
                                                {
                                                        //Add to the where clause
                                                        $primaryKeyWhere .= mysql_real_escape_string($aIdentifier['field']) . '="' . mysql_real_escape_string($aRow[$aIdentifier['value']]) . '" ';
                                                }
                                        }
                                }

                                $primaryKeySelect .= $primaryKeyWhere . ';';
                                $primaryKeyQuery = mysql_query($primaryKeySelect);

                                //Check to see if the query returned any rows
                                if(mysql_num_rows($primaryKeyQuery) > 0)
                                {
                                        $aPrimaryKeyRow = mysql_fetch_assoc($primaryKeyQuery);
                                        $primaryKey = $aPrimaryKeyRow['primary_key'];

                                        //Start building the query
                                        $sql = 'UPDATE ' . $aQuery['tables'] . ' SET ';

                                        $updates = '';

                                        //Check to see if there are any fields defined
                                        //There will be the field we are updating along with the column indexs from the spreadsheet
                                        if(isset($aQuery['fields']) && !empty($aQuery['fields']))
                                        {
                                                //Loop through the fields
                                                foreach($aQuery['fields'] as $field => $value)
                                                {
                                                        //Check to see if the value is an array
                                                        //If its an array it means we are adding more than one column into this field
                                                        if(is_array($value))
                                                        {
                                                                $values = '';

                                                                //Loop through the values and concatenate them
                                                                foreach($value as $partValue)
                                                                {
                                                                        if(is_array($partValue))
                                                                        {
                                                                                //Check to see if this column exists in the row
                                                                                if(isset($aRow[$partValue['field']]))
                                                                                {
                                                                                        $values .= $partValue['before'] . $aRow[$partValue['field']] . $partValue['after'] . ' ';
                                                                                }
                                                                        }
                                                                        else
                                                                        {
                                                                                //Check to see if this column exists in the row
                                                                                if(isset($aRow[$partValue]))
                                                                                {
                                                                                        $values .= $aRow[$partValue] . ' ';
                                                                                }
                                                                        }
                                                                }

                                                                //Check to see if there has already been things fields added to the update
                                                                //If there are then it means we need to prefix with comma to seperate them
                                                                if($updates != '')
                                                                        $updates .= ', ';

                                                                //Add the field with the value we are setting it to to the update
                                                                $updates .= $field . '="' . $string = str_replace('™','', html_entity_decode(mysql_real_escape_string(trim($values)), ENT_QUOTES, "UTF-8")) . '"';
                                                        }
                                                        else
                                                        {
                                                                //Check to see if this column exists in the row
                                                                if(isset($aRow[$value]))
                                                                {
                                                                        //Check to see if there has already been things fields added to the update
                                                                        //If there are then it means we need to prefix with comma to seperate them
                                                                        if($updates != '')
                                                                                $updates .= ', ';

                                                                        //Add the field with the value we are setting it to to the update
                                                                        $updates .= $field . '="' . str_replace('™','', html_entity_decode(mysql_real_escape_string(trim($aRow[$value])), ENT_QUOTES, "UTF-8")) . '"';
                                                                }
                                                        }
                                                }
                                        }

                                        $sql .= $updates;
                                        $sql .= ' WHERE ';
                                        $where = '';

                                        //Check to see if there are any joins defined
                                        //Joins will be when we are joining 2 table via a common field
                                        if(isset($aQuery['joins']) && !empty($aQuery['joins']))
                                        {
                                                //If there are loop through the joins and add them to the query
                                                foreach($aQuery['joins'] as $field1 => $field2)
                                                {
                                                        //Check to see if there has already been things added to the where clause
                                                        //If there are then it means we need to prefix with AND, OR etc
                                                        if($where != '')
                                                                $where .= ' AND ';

                                                        //Add the join to the where clauses
                                                        $where .= mysql_real_escape_string($field1) . '=' . mysql_real_escape_string($field2);
                                                }
                                        }


                                        //Check to see if there has already been things added to the where clause
                                        //If there are then it means we need to prefix with AND, OR etc
                                        if($where != '')
                                                $where .= ' AND ';

                                        $where .= $aPrimaryKey['field'] . '="' . $primaryKey . '"';


                                        $sql .= $where;
                                        $sql .= ';';

                                        $query = mysql_query($sql);

                                        if($query === TRUE)
                                        {
                                            echo 'Query <span style="color: green;">successful</span><br /></br />';
                                        }
                                        else
                                        {
                                            echo $sql . '<br /><br />';
                                            echo 'Query <span style="color: red;">failed</span><br /></br />';
                                        }

                                        //Now that we have updated the product information in the database we now need to create the images

                                        //First of all check to see if we need to find an image for this product by checking if the $imageColumn variable is set in the config
                                        if($imageColumn != '')
                                        {
                                                create_images($aRow, $imageColumn, $aImageExtensions, $aImageSizes, $aImageUpdate);
                                        }

                                        $totalUpdatedCount++;
                                        $workbookCount++;
                                }
                                else
                                {
                                        echo 'Couldn\'t find ' . $aRow['A'];
                                }

                                $totalRecordsCount++;
                        }
                        
                        echo '<b>Finished importing all of the data from ' . $aWorkbook['workbook_file'] . '</b><br/>';
                        echo 'Total number of records updated from this workbook: <b>' . $workbookCount . '</b><br/><br/><hr><br />';
                }
                else
                {
                        echo 'You have not set up your workbooks correctly in the config file<br />';
                }
        }
        
        echo 'Total number of records: <b>' . $totalRecordsCount . '</b><br />';
        echo 'Total number of records updated: <b>' . $totalUpdatedCount . '</b><br />';
        echo 'Failed to update <b>' . ($totalRecordsCount - $totalUpdatedCount) . '</b> because they couldn\'t be found in the database<br />';
}
else
{
        echo 'You need to set up any workbooks you want to read from in the config file';
}
?>