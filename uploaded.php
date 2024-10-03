<?php

//Este php relaiza las subidas al servder central con un PUT
require_once "common.php";
function run_uploaded($IPcentral,$PORTcentral,$IPpeer,$PORTpeer){  

    
    //formato de carpeta desde las que se cogen archivos: IPpeer_PORTpeer
    $peerFolder = $IPpeer."_".$PORTpeer;
    $root = getcwd();
    $rootFolder = $root."/".$peerFolder;
    
    if(is_dir($rootFolder)){
        $files = scandir($rootFolder);  //devuelve un array con todos los ficheros 
        $files = array_diff($files, array('.', '..'));
    }
    

    socket_connection($IPcentral,$PORTcentral);
    send_putRequest($IPpeer,$PORTpeer,$files);
    close_socket();

}


function send_putRequest($IPpeer,$PORTpeer,$files){

    $method = "PUT";
    $protocol = "HTTP/1.0";
    $body = json_encode(array_values($files));

    $request = $method." /host/".$IPpeer.":".$PORTpeer." ".$protocol."\r\n";
    $request .= "Host: localhost\r\n";
    $request .= "Content-Type: application/json\r\n";
    $request .= "Connection: keep-alive\r\n";
    $request .= "Content-Length: ".strlen($body)."\r\n";
    $request .= "\r\n";
    $request .= $body;
    $request .= "\r\n";
   
    socket_write( $GLOBALS["socket_client"],$request,strlen($request));  //aqui el peer se resetea y no sabemos por qué
   
    
    $header = get_header($GLOBALS["socket_client"]);
    $header_procced = process_header_response($header);

    
    if($header_procced["status"] !== "200"){
        echo "Error en la peticion";
        die();
    }
    

}