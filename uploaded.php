<?php

//Este php relaiza las subidas al servder central con un PUT
require_once "common.php";
function run_uploaded($sock,$IPpeer,$PORTpeer,$IPcentral,$PORTcentral){  

    $GLOBALS["IPcentral"] = $IPcentral;
    $GLOBALS["PORTcentral"] = $PORTcentral;
    //formato de carpeta desde las que se cogen archivos: IPpeer_PORTpeer
    $peerFolder = $IPpeer."_".$PORTpeer;
    $root = getcwd();
    $rootFolder = $root."/".$peerFolder;
    
    if(is_dir($rootFolder)){
        $files = scandir($rootFolder);  //devuelve un array con todos los ficheros 
        $files = array_diff($files, array('.', '..'));
    }
    
    //Vemos si el json existe o no en el servidor
    
    send_putRequest($sock,$IPpeer,$PORTpeer,$files);


}


function send_putRequest($sock,$IPpeer,$PORTpeer,$files){

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
   
    socket_write( $sock,$request,strlen($request));  //aqui el peer se resetea y no sabemos por qué
   
    $header = get_header($sock);
    $header_procced = process_header_response($header);

    
    if($header_procced["status"] !== "200"){
        echo "Error en la peticion";
        die();
    }
    

}