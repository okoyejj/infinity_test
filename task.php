<?php
//display errors encountered to aid in debugging code
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

//check if the uploaded directory exists, if not create uploaded directory
$directoryName = 'uploaded';

if(checkFolder($directoryName)){
    
    echo $directoryName.' folder exists <br/>';
    
    
    //create an array of all csv files
    $files = glob($directoryName."/*.csv");
    
    //check if the array holds any files
    if(count($files) != 0){
        
        tableManage();
        
        //looping through the csv files found   
       foreach($files as $file){
        
            //print_r(readCSV($file)).'<br/>';
           //loadCSVData($file);
           parseCSVData($file);
           
           $directoryName = 'processed';

           if(checkFolder($directoryName)){
    
            moveFile($file);
           
           }
           else
               createFolder($directoryName);
               
           
        }
        
    }
    else
        echo 'No csv files found<br/>';
    

}
else{
    echo 'false<br/>';
    createFolder($directoryName);
    //do nothing folder is empty 
}
function checkFolder($name){
    
    return file_exists($name);
}
function createFolder($name){
    
     mkdir($name, 0700);
}
function readCSV($csvFile){
    
    $file_handle = fopen($csvFile, 'r');
    while (!feof($file_handle)) {
        $line_of_text[] = fgetcsv($file_handle, 1024);
    }
    fclose($file_handle);
    return $line_of_text;
}
function tableManage(){
    
    //If array holds files check if table in db exists 
        $servername = "localhost";
        $username = "root";
        $password = "root";
        $dbname = "PHP_Daemon";
        $table = "Daemon";

        // Create connection
        $conn = mysqli_connect($servername, $username, $password, $dbname);
        // Check connection
        if (!$conn) {
            die("Connection failed: " . mysqli_connect_error());
        }
        
        else{
            
            // sql to create table if not exist 
            $sql = "CREATE TABLE ".$table." ( `event_id` INT(8) UNSIGNED NOT NULL AUTO_INCREMENT ,`eventDatetime` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP , `eventAction` VARCHAR(20) NOT NULL , `callRef` INT NULL , `eventValue` DECIMAL NULL , `eventCurrencyCode` VARCHAR(3) NULL ,PRIMARY KEY (`event_id`))";
            
        }

        if (mysqli_query($conn, $sql)) {
            echo "Table ".$table." created successfully";
        } 
        else {

            echo "Error creating table: " . mysqli_error($conn);
            if ($result = mysqli_query($conn,"SHOW TABLES LIKE '".$table."'")) {
                //error is existence of table
                if($result->num_rows == 1) {
                    echo "Table exists";
                    //insert data here 
                }
            }
            else {
                echo "Table does not exist something went wrong";
            }
        }

        mysqli_close($conn); 
}
function loadCSVData($fileName, $servername = "localhost",
        $username = "root",
        $password = "root",
        $dbname = "PHP_Daemon",
        $table = "Daemon"){

    //don't use and insert since table order can change
    $query ="
        LOAD DATA INFILE '$fileName'
        INTO TABLE $table
        FIELDS TERMINATED BY ','
        ESCAPED BY '\'
        LINES TERMINATED BY '\n'
        IGNORE 1 LINES
        (eventDatetime,eventAction,callRef,eventValue,eventCurrencyCode)
        SET Date = STR_TO_DATE(eventDatetime, '%Y-%m-%d %H:%M:%S')";
    
    echo($query);

    // Create connection fix this
    $conn = mysqli_connect($servername, $username, $password, $dbname);

    mysqli_query($conn, $query);
}
function parseCSVData($fileName, $servername = "localhost",
        $username = "root",
        $password = "root",
        $dbname = "PHP_Daemon",
        $table = "Daemon"){
    
   $csvFile = fopen($fileName, 'r');
    //skip 1st line
    $head = fgetcsv($csvFile);
    
    while(($line = fgetcsv($csvFile)) != false){
        print_r($line);
        /*for($x=0; $x<5; x++){
            
           if(isset($line[x])){
             
            }
            else
                $line[x] = ''; 
            }*/
        
        $query = "INSERT INTO ".$table." ($head[0],$head[1],$head[2],$head[3],$head[4]) VALUES ('".$line[0]."','".$line[1]."','".$line[2]."','".$line[3]."','".$line[4]."')";
        
        // Create connection fix this
        $conn = mysqli_connect($servername, $username, $password, $dbname);

        mysqli_query($conn, $query);
    }
}
function moveFile($fileName){
    
    $old = $fileName;
    $parentFolder = basename(__DIR__);
    echo $new = $parentFolder."/processed/".basename($fileName);
    copy($old, $new) or die("Unable to copy $old to $new.");
    if (copy($old,$new)) {
      unlink($old);
        echo $fileName."deleted";    
    }
}
?>