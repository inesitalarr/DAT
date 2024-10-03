<?php
//Este php hace la funcion de interfaz que vera el peer cuando se conecte al central server


function run_ux($server_host,$server_port,$IPpeer,$PORTpeer){

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

                        socket_connection($server_host,$server_port);

                        send_GET_request($GLOBALS["socket_client"],$type_request,$name);
                        $header = get_header($GLOBALS["socket_client"]);
                        $header_processed = process_header_response($header);
    
                        if($header_processed["status"] == "200"){
                            $num_bytes = $header_processed["content-length"];
                            $body = socket_read($GLOBALS["socket_client"],intval($num_bytes));
                            echo $body ."\n";
                        }else{
                            $num_bytes = $header_processed["content-length"];
                            $body = socket_read($GLOBALS["socket_client"],intval($num_bytes));
                            echo $body ."\n";
                        }

                        close_socket();
                        
                    }elseif($type_request == "files"){

                        socket_connection($server_host,$server_port);

                        send_GET_request($GLOBALS["socket_client"],$type_request,$name);
                        $header = get_header($GLOBALS["socket_client"]);
                        $header_processed = process_header_response($header);

    
                        if($header_processed["status"] == "200"){
                            $num_bytes = $header_processed["content-length"];
                            $body = socket_read($GLOBALS["socket_client"],intval($num_bytes));
                            echo $body ."\n";
                        }else{
                            $num_bytes = $header_processed["content-length"];
                            $body = socket_read($GLOBALS["socket_client"],intval($num_bytes));
                            echo $body ."\n";
                        }

                        close_socket();

                    }elseif($type_request == "download"){
                        
                        socket_connection($server_host,$server_port);

                        //Primero obtengo la lista de peers
                        $type = "peers";
                        send_GET_request($GLOBALS["socket_client"],$type,$name);
                        $header = get_header($GLOBALS["socket_client"]);
        
                        $header_processed = process_header_response($header);

                        if($header_processed["status"] == "200"){
                            $num_bytes = $header_processed["content-length"];
                            $body = socket_read($GLOBALS["socket_client"],intval($num_bytes));
                            echo "Los peers que poseen el archivo que desea son: \n";
                            echo $body."\n";
                            close_socket();

                            //De esa lista de peers se coge uno aleatorio y se establece un nuevo socket
                            $arrayPeers = explode("\n",$body);         
                            $position = array_rand($arrayPeers);
                            $peer = $arrayPeers[$position];
                            $peerSliced = explode(":",$peer);
                            
                            $peerIP = $peerSliced[0];
                            $peerPORT = $peerSliced[1];

                            requestDownload($name,$peerIP,$peerPORT);


                        }else{
                            $num_bytes = $header_processed["content-length"];
                            $body = socket_read($GLOBALS["socket_client"],intval($num_bytes));
                            echo $body ."\n";
                            close_socket();

                        }

                
    
                    }else {
                        echo "Peticion no valida. Por favor, introduce 'search', 'download' o 'files'.\n";
                    }
                }

            }else{

                if($type_request == "exit" || $type_request == "quit"){
                    socket_connection($server_host,$server_port);
                    $request = $type_request." ".$IPpeer."_".$PORTpeer." HTTP/1.0 \r\n";
                    $request .= "Host: localhost \r\n";
                    $request .= "\r\n";
                    socket_write($GLOBALS["socket_client"],$request, strlen($request));
                    close_socket();

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

   // die();


}

function requestDownload($path,$peerIP,$peerPORT){


    socket_connection($peerIP,$peerPORT);
    
  
        
    // Enviar peticion GET al servidor
    $request = "GET /".$path." HTTP/1.0 \r\n";
    $request .= "Host: localhost \r\n";
    $request .= "\r\n";
    socket_write($GLOBALS["socket_client"],$request, strlen($request));
    
    //Recibo respuesta
    $header = get_header($GLOBALS["socket_client"]);
    $header_processed = process_header_response($header);

    if($header_processed["status"] == "200"){
        $num_bytes = $header_processed["content-length"];
        $body = socket_read($GLOBALS["socket_client"],intval($num_bytes));
        echo "Descarga realizada \n";
        
        $fileInfo = pathinfo($path);
        $extension = $fileInfo["extension"];
        $filename = $fileInfo["filename"];
        
        $fileDL = $filename."_DL.".$extension;
        file_put_contents($fileDL,$body);

    }else{
        echo "Error en la descarga\n";
    }

    close_socket();
    
}