<?php
//Este php hace la funcion de servidor de un peer

function run_p1($IPpeer,$PORTpeer){

    $GLOBALS['IPpeer'] = $IPpeer;
    $GLOBALS['PORTpeer'] = $PORTpeer;

 

        //Se crea un socket y se verifica que funciones correctamente
        $sock = socket_create(AF_INET, SOCK_STREAM, getprotobyname('tcp'));  //se crea el socket
       /*if (!is_resource($sock)) {
            $error_code = socket_last_error();
            $error_msg = socket_strerror($error_code);
            throw new Exception("Error al crear el socket: [$error_code] $error_msg");
        }*/

        //Dicho socket se pone modo escucha
        $bound = socket_bind($sock,$IPpeer,$PORTpeer);
        
        if($bound == false){
            $error_code = socket_last_error();
            $error_msg = socket_strerror($error_code);
            throw new Exception("Error al crear el socket: [$error_code] $error_msg");
        }

        //Nos ponemos a la escucha del socket
        $listen = socket_listen($sock,1);  //el 5 es para el max de conexiones
        if($listen == false){
            $error_code = socket_last_error();
            $error_msg = socket_strerror($error_code);
            throw new Exception("Error al crear el socket: [$error_code] $error_msg");
        }

        //Ahora el lciente se conecta al socket

        while(true){
            $peer_socket = socket_accept($sock);
            
            if(pcntl_fork()==0){

                if($peer_socket == false){
                    $error_code = socket_last_error();
                    $error_msg = socket_strerror($error_code);
                    throw new Exception("Error al crear el socket cliente: [$error_code] $error_msg");
                }else{
    
                    //Si no hay errores indico que el cliente se ha conectado
                    $GLOBALS["peer_socket"] = $peer_socket;    
                    process_peer();    
                }
                exit(0);
            }
            else{
                socket_close($peer_socket); 
            } 
        }
    
}

function process_peer(){
    //Se obtiene la peticion del cliente
    $header = get_header($GLOBALS["peer_socket"]);
        //Se procesa la petición del cliente
    process_request($header);
}


//Ahora analizamos la solicitud del cliente
function process_request($header) {  

    if (strpos($header, "\r\n") !== false){
        $header_string = str_replace("\r\n"," ",$header);
    }else{
        $header_string = str_replace("\n"," ",$header);
    }
    
    $valores_header = explode(" ",$header_string);

    $protocol = $valores_header[2];
    
    $peerFolder = $GLOBALS['IPpeer']."_".$GLOBALS['PORTpeer'];
    $root = getcwd();
    $rootFolder = $root."/".$peerFolder;
    $archivo = $rootFolder ."/".$valores_header[1]; // Ruta completa al archivo

    if (file_exists($archivo) === false){
        $body = "";
        $status = "403 Forbidden";
        send_response($status,$body, strlen($body));
    }else{
        $body = file_get_contents($archivo);
        $status = "200 OK";
        //Le mando al respuesta al cliente
        send_response($status,$body, strlen($body),$GLOBALS["peer_socket"]);
    }
}
    


