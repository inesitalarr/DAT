
<?php



function get_header($socket){    //parametro entrada: socket,  salida: header

    $header = '';
    while(true){
        $data = socket_read($socket,1);

        if($data === false ){
            $error_code = socket_last_error();
            $error_msg = socket_strerror($error_code);
            throw new Exception("Error en socket cliente: [$error_code] $error_msg");
        }

        $header .= $data;
       
        if (strpos($header, "\r\n\r\n") !== false || strpos($header,"\n\n")  !== false) {
            break;
        }
    }

    return $header;
}




function process_header_response($header){
    
    $header_string = str_replace("\r","",$header);
    $valores_header = explode("\n",$header_string);

    $status = explode(" ",$valores_header[0])[1];  //despues del explode se puede acceder a solo un valor []

    $header_processed = array();

    foreach($valores_header as $header_line ){
        $params = explode(":",$header_line);        
        $key = strtolower(trim($params[0]));

        if(isset($params[1])){
            $value = trim($params[1]);
            $header_processed[$key] = $value;
        }

    }
        $header_processed["status"] = $status;

        return $header_processed;
}

function send_GET_request($sock,$type,$path){
    $method = "GET";

    $request = $method." /".$type."/".$path." HTTP/1.0 \r\n";
    $request .= "Host: localhost \r\n";
    $request .= "\r\n";

    socket_write($sock,$request, strlen($request));
}




function send_response($status,$body, $content_length = null,$sock) {
    
   $protocol = "HTTP/1.0"; 
    if($status != "200 OK"){
        $response = $protocol." ".$status."\r\n";
        $response .= "Content-Length: $content_length \r\n";
        $response .= "Content-Type: text/plain\r\n"; // Usar text/plain para texto plano
        $response .= "\r\n";
        $response .= $body;
        $response .= "\r\n";
        
        
    }else{
        $response = $protocol." ".$status."\r\n";
        $response .= "Content-Type: text/plain\r\n"; // Usar text/plain para texto plano
        $response .= "Content-Length: $content_length \r\n";
        $response .= "\r\n";
        $response .= $body;
        $response .= "\r\n";
        

    }
   
    
    socket_write($sock, $response, strlen($response));

}

function socket_connection($server,$port){
    $sock = socket_create(AF_INET, SOCK_STREAM, getprotobyname('tcp'));  //se crea el socket
    $socket_conn = socket_connect($sock,$server,$port);
    if ($socket_conn === false){
        throw new Exception("Hay un error en la creacion del socket.");

    }
     
    $GLOBALS["socket_client"] = $sock;

}

function close_socket(){
    socket_close($GLOBALS["socket_client"]);
}



