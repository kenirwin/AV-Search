<?php

function ConnectPDO($db="lib") { 
    $db = new PDO(DSN, USER, PASS);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
return $db;
}
?>