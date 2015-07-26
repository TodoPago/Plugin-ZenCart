<?php
    require('includes/application_top.php');

    global $db;

    $sql = "delete  from todo_pago_atributos where product_id = ".$_POST["product_id"];
    
    $db->Execute($sql);
    
    $sql = "";
    
    foreach($_POST as $key=>$value){
    
      $sql .=" ".$key. "='".$value."',";   
    }
    
    $sql = trim($sql,",");
    
    $sql = "insert into todo_pago_atributos set ".$sql;
    
    echo $db->Execute($sql);