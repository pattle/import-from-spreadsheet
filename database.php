<?php

//Define the database settings
//These are the database settings for the database we are importing too
$host = 'localhost';
$username = 'username';
$password = 'password';
$database = 'database';

//Connect to mysql using the details about
$conn = mysql_connect($host,$username,$password);

//Set the character encoding to utf8 to prevent strange characters when
//insert or updating to the database
mysql_query("set names 'utf8'");

//Check to see if we were able to connect
if(!$conn)
{
    //If we weren't give an error
    die('Could not connect: ' . mysql_error());
}

//Select the database
$db = mysql_select_db($database, $conn);

//Check to see if we were able to select the database
if(!$db)
{
    //If we weren't give an error
    die('Could not select database: ' . mysql_error());
}
?>
