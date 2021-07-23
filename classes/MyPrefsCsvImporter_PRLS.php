<?php 
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class MyPrefsCsvImporter_PRLS 
{ 
    private $fp; 
    private $parse_header; 
    private $header; 
    private $enclosure;
    private $delimiter;
    private $escape; 
    private $length; 
    //-------------------------------------------------------------------- 

    function __construct(
        $file_name, $parse_header=false, $enclosure =" ", $delimiter=",", $escape="\\", $length=10000) 
    { 
        $this->fp = fopen($file_name, "r"); 
        $this->parse_header = $parse_header;
        $this->enclosure = $enclosure;
        $this->delimiter = $delimiter; 
        $this->escape = $escape;
        $this->length = $length; 
       // $this->lines = $lines; 

        if ($this->parse_header) 
        { 
           $this->header = fgetcsv(
                $this->fp, $this->length, $this->delimiter, $this->enclosure, $this->escape); 
        } 

    } 
    //-------------------------------------------------------------------- 
    function __destruct() 
    { 
        if ($this->fp) 
        { 
            fclose($this->fp); 
        } 
    } 
    //-------------------------------------------------------------------- 
    function get($max_lines=0) 
    { 
        //if $max_lines is set to 0, then get all the data 

        $data = array(); 

        if ($max_lines > 0) 
            $line_count = 0; 
        else 
            $line_count = -1; // so loop limit is ignored 

        while ($line_count < $max_lines 
                && ($row = fgetcsv($this->fp, $this->length, 
                    $this->delimiter, $this->enclosure, $this->escape)) !== FALSE) 
        { 
            if ($this->parse_header) 
            { 
                foreach ($this->header as $i => $heading_i) 
                { 
                    $row_new[$heading_i] = $row[$i]; 
                } 
                $data[] = $row_new; 
            } 
            else 
            { 
                $data[] = $row; 
            } 

            if ($max_lines > 0) 
                $line_count++; 
        } 
        return $data; 
    } 
    //-------------------------------------------------------------------- 

}