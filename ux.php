<?php
//Este php hace la funcion de interfaz que vera el peer cuando se conecte al central server


function run_ux($sock){

    echo "Introduce la peticion que desee: \n  search (muestra los archivos que contengan esa cadena  \ndownload (descarga el archivo desde un peer que lo tenga) \nfiles: (muestra los archivos de un peer conocido) \n";

    while (true) {

        echo ">";
        $input = fgets(STDIN);

        
        
        if (trim($input) !== "") {
            $input = trim($input); // Eliminar espacios en blanco al inicio y final
            
            $slice = explode(" ",$input);
            $type_request = strtolower($slice[0]);
            if(isset($slice[1])){
                $name = $slice[1];

                if (trim($name) !== ""){

                    if($type_request == "search"){
                        send_GET_request($sock,$type_request,$name);
                        $header = get_header($sock);
                        $header_processed = process_header_response($header);
    
                        if($header_processed["status"] == "200"){
                            $num_bytes = $header_processed["content-length"];
                            $body = socket_read($sock,intval($num_bytes));
                            echo $body ."\n";
                        }else{
                            $num_bytes = $header_processed["content-length"];
                            $body = socket_read($sock,intval($num_bytes));
                            echo $body ."\n";
                        }
                        
                    }elseif($type_request == "files"){
                        send_GET_request($sock,$type_request,$name);
                        $header = get_header($sock);
                        $header_processed = process_header_response($header);

    
                        if($header_processed["status"] == "200"){
                            $num_bytes = $header_processed["content-length"];
                            $body = socket_read($sock,intval($num_bytes));
                            echo $body ."\n";
                        }else{
                            $num_bytes = $header_processed["content-length"];
                            $body = socket_read($sock,intval($num_bytes));
                            echo $body ."\n";
                        }
    
                    }elseif($type_request == "download"){
                        
                        //Primero obtengo la lista de peers
                        $type = "peers";
                        send_GET_request($sock,$type,$name);
                        $header = get_header($sock);
        
                        $header_processed = process_header_response($header);

                        if($header_processed["status"] == "200"){
                            $num_bytes = $header_processed["content-length"];
                            $body = socket_read($sock,intval($num_bytes));

                        }else{
                            $num_bytes = $header_processed["content-length"];
                            $body = socket_read($sock,intval($num_bytes));
                            echo $body ."\n";

                        }


                        //De esa lista de peers se coge uno aleatorio y se establece un nuevo socket
                        $arrayPeers = explode("\n",$body);         
                        $position = array_rand($arrayPeers);
                        $peer = $arrayPeers[$position];
                        $peerSliced = explode(":",$peer);
                        
                        $peerID = $peer[0];
                        $peerPORT = $peer[1];

                        requestDownload($name,$peerID,$peerPORT);

    
                    }else {
                        echo "Peticion no valida. Por favor, introduce 'search', 'download' o 'files'.\n";
                    }
                }

            }else{

                if($type_request == "exit" || $type_request == "quit"){
                    echo "Conexion cerrada. \n";
                    break;
                }else{
                    echo "Peticion no valida. Indique que quiere ver o descargar.\n";
                }
            }


        }else{
            echo "Entrada vacia. Por favor, introduce un comando.\n";
        }

        

    
    }

    socket_close($sock);


}

function requestDownload($path,$peerID,$peerPORT){

    $peerPORT = 8082;
    $sock_peer = socket_create(AF_INET, SOCK_STREAM, getprotobyname('tcp'));  //se crea el socket
    $socket_conn = socket_connect($sock_peer,$peerID,$peerPORT);

    
    if ($socket_conn === false){
        /*$error_code = socket_last_error();
        $error_msg = socket_strerror($error_code);
        throw new Exception("Error al conectarse al socket: [$error_code] $error_msg");*/
        echo "ERROR";
        
    }else{
        echo "Conectado al servidor \n";
        
        // Enviar peticion GET al servidor
        
        $request = "GET /".$path." HTTP/1.0 \r\n";
        $request .= "Host: localhost \r\n";
        $request .= "\r\n";

        socket_write($sock_peer,$request, strlen($request));
        
        //Recibo respuesta
        $header = get_header($sock_peer);
        $header_processed = process_header_response($header);

        if($header_processed["status"] == "200"){
            $num_bytes = $header_processed["content-length"];
            $body = socket_read($GLOBALS["socket"],intval($num_bytes));
            echo "Descarga realizada \n";
            
            file_put_contents("descarga.txt",$body);

        }else{
            echo "Error en la descarga\n";
        }
    }
}