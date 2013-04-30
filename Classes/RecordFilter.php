<?php
/*
 *  Class to filter out data in a worksheet
 */
class RecordFilter implements PHPExcel_Reader_IReadFilter
{
    //Define class variables
    private $_startRow = 0; 
    private $_endRow   = 0; 
    private $_columns  = array(); 
    private $_exclusions  = array(); 

    /*
     *  __construct($startRow, $endRow, $columns).  Constructor function to set variables
     *  @author  Chris Pattle
     *  @param   $startRow INT The row to start the read at
     *  @param   $endRow   INT The row to end the read at
     *  @param   $columns  ARRAY An array of columns to include in the read
     */
    public function __construct($startRow, $endRow, $columns, $exclusions)
    { 
            $this->_startRow = $startRow; 
            $this->_endRow   = $endRow; 
            $this->_columns  = $columns; 
            $this->_exclusions = $exclusions;
    } 

    /*
     *  readCell($column, $row, $worksheetName = '').  Function to check a cell is in a certain range before reading it
     *  @author  Chris Pattle
     *  @param   $column STRING The column letter (e.g A, B, C)
     *  @param   $row    INT The row number (e.g 1, 2, 3)
     *  @param   $worksheetName STRING The name of the worksheet
     *  @return  BOOL Returns TRUE is the cell is in the range and is okay to read.  Returns FALSE is the cell isn't it the range and can't be read
     */
    public function readCell($column, $row, $worksheetName = '')
    {
            //Check to see if the row number is greater than the start row
            //And that the row number is greater than the end row OR that the end row is set to 0
            if($row >= $this->_startRow && ($row <= $this->_endRow || $this->_endRow == 0 ))
            {
                    //Check to see if we have a column range
                    //If we do then see if the column is in the range otherwise assume its okay to read this cell
                    if(!empty($this->_columns))
                    {
                            //Check to see that the column in the allowed range of columns
                            if(in_array($column,$this->_columns))
                            {
                                    if(!empty($this->_exclusions) && isset($this->_exclusions[$worksheetName]))
                                    {
                                            if(in_array($row, $this->_exclusions[$worksheetName]['rows']))
                                            {
                                                    return false;
                                            }
                                            else
                                            {
                                                    return true;
                                            }
                                    }
                                    else
                                    {
                                            //Return true to show that its okay to read this cell
                                            return true; 
                                    }
                            }
                            else
                            {
                                    return false;
                            }
                    }
                    else
                    {

                            if(!empty($this->_exclusions) && isset($this->_exclusions[$worksheetName]))
                            {
                                    if(in_array($row, $this->_exclusions[$worksheetName]['rows']))
                                    {
                                            return false;
                                    }
                                    else
                                    {
                                            return true;
                                    }
                            }
                            else
                            {
                                    //Return true to show that its okay to read this cell
                                    return true; 
                            }
                    }
            }
            
            //If the cell is not in the allowed range then return false to the data isn't read
            return false; 
    } 

}

?>
