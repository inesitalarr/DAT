<?php


require_once 'common.php';

function run_central_server($server_host,$server_port){

    try{

        //Se crea un socket y se verifica que funciones correctamente
        $sock = socket_create( AF_INET,  SOCK_STREAM,  getprotobyname( 'tcp'));  //se crea el socket
        
       /* if (is_resource($sock) === false) {
            echo "AQUI";
            $error_code = socket_last_error();
            $error_msg = socket_strerror($error_code);
            throw new Exception("Error al crear el socket: [$error_code] $error_msg");
        }*/


        //Dicho socket se pone modo escucha
        $bound = socket_bind($sock,$server_host,$server_port);
        if($bound == false){
            $error_code = socket_last_error();
            $error_msg = socket_strerror($error_code);
            throw new Exception("Error al crear el socket: [$error_code] $error_msg");
        }else{
            echo "Servidor vinculado a $server_host:$server_port\n";
        }

        //Nos ponemos a la escucha del socket
        $listen = socket_listen($sock,5);  //el 10 es para el max de conexiones
        if($listen == false){
            $error_code = socket_last_error();
            $error_msg = socket_strerror($error_code);
            throw new Exception("Error al crear el socket: [$error_code] $error_msg");
        }

        //Genero la base de datos para guardar archivos con clientes
        
        while(true){

            $client_socket = socket_accept($sock);

            if(pcntl_fork() == 0){

                $GLOBALS["client_socket"] = $client_socket;

                if($client_socket === false){
                    $error_code = socket_last_error();
                    $error_msg = socket_strerror($error_code);
                    throw new Exception("Error al crear el socket cliente: [$error_code] $error_msg");
                }else{
                    echo "El cliente se ha conectado. \n";
                }

                
                
                $header = get_header($GLOBALS["client_socket"]);  // Supongo que esto lee una petición del cliente
                process_header_request($header);
                

                exit(0);
            }else{
                socket_close($client_socket);
            }

           
        }

    }catch(Exception $e){
        echo "Se produjo un error: ". $e -> getMessage();
    }


}
    function process_header_request($header){

       
        trim($header);
        $header_sliced = explode(" ",$header);
    

        if($header_sliced[0] == "PUT"){

            putRequest($header);
            $status = "200 OK";
            $body = "";    
            send_response($status,$body,$content_length = strlen($body),$GLOBALS["client_socket"]);
            
       
        }elseif($header_sliced[0] == "GET"){
            
            
            $header_sliced_bar = explode("/",$header_sliced[1]);
            $type = $header_sliced_bar[1];  //ver si es search, peers, files
            $path = $header_sliced_bar[2]; //aqui nombre del archivo o trozo archivo
    
            if ($type == "search"){
             
                $matchFiles = searchRequest($path);

                if(empty($matchFiles)){
                    $status = "403 Forbidden";
                    $body = "No hay coincidencias.";
                }else{
                    $status = "200 OK";
                    $body = implode("\n",$matchFiles);
                }
                
                send_response($status,$body,$content_length = strlen($body),$GLOBALS["client_socket"]);
    
            }elseif($type == "peers"){

                
                $matchingPeers = peersRequest($path);
                if(empty($matchingPeers)){
                    $status = "403 Forbidden";
                    $body = "El archivo que desea descargar no existe.";
                }else{
                    $status = "200 OK";
                    $body = implode("\n",$matchingPeers);
                }
                send_response($status,$body,$content_length = strlen($body),$GLOBALS["client_socket"]);
    
    
            }elseif($type == "files"){

                $filesPeer = filesRequest($path);
                if(empty($filesPeer)){
                    $status = "403 Forbidden";
                    $body = "No existe este peer en el servidor central.";
                }else{
                    $status = "200 OK";
                    $body = implode("\n",$filesPeer);
                }
                send_response($status,$body,$content_length = strlen($body),$GLOBALS["client_socket"]);
    
    
            }else{
                throw new Exception("Tipo de peticion GET no disponible.");
            }
    

    
        }else{
            //tendremos un exit o quit
            exitRequest($header);

        }
    
    }
    
        
function putRequest($header){

    $header_string = str_replace("\r","",$header);
    $valores_header = explode("\n",$header_string);
    $header_sliced_space = explode(" ",$valores_header[0]);
    $header_sliced_bar = explode("/",$header_sliced_space[1]);
    $header_sliced_dots = explode(":",$header_sliced_bar[2]);

    $IPpeer = $header_sliced_dots[0];
    $PORTpeer = $header_sliced_dots[1];


    $header_processed = array();

    foreach($valores_header as $header_line ){
        $params = explode(":",$header_line);        
        $key = strtolower(trim($params[0]));

        if(isset($params[1])){
            $value = trim($params[1]);
            $header_processed[$key] = $value;
        }

    }

    $header_processed["IPpeer"] = $IPpeer;
    $header_processed["PORTpeer"] = $PORTpeer;

    $num_bytes = $header_processed["content-length"];
    $body = socket_read($GLOBALS["client_socket"],intval($num_bytes));
    $time = time();

    $file_json = strval($header_processed["IPpeer"])."_".strval($header_processed["PORTpeer"].".json");
    
    if(file_exists($file_json) === true){
        $data_json = file_get_contents($file_json);
        $array_json = json_decode($data_json, true);
        $array_json["time"] = $time;
        $array_json["files"] = $body;
    }else{

        $array_json = array(
            "time" => $time,
            "files" => $body
        );

    }


    $dataUploaded = json_encode($array_json, JSON_PRETTY_PRINT);
    file_put_contents($file_json,$dataUploaded);
}
        
function searchRequest($path){

    
    $dir = getcwd();
    if(!is_dir($dir)){
        throw new Exception("Directorio no existente.");
    }
    

    $files = scandir($dir);
    $matchingFiles = [];

    foreach($files as $file){
        if(pathinfo($file, PATHINFO_EXTENSION) == 'json'){

            $rootFile = $dir."/".$file;
            $fileContent = file_get_contents($rootFile);
            $contentDecode = json_decode($fileContent,true);
            
            if(is_array($contentDecode["files"]) === false){
                
                $arrayFiles =  $contentDecode["files"];
                $arrayFiles = str_replace("[","",$arrayFiles);
                $arrayFiles = str_replace("]","",$arrayFiles);
                $arrayFiles = explode(",",$arrayFiles);

            }else{
                $arrayFiles = $contentDecode["files"];
            }

            

            foreach ($arrayFiles as $fileName) {
                if (strpos(strtolower($fileName), strtolower($path)) !== false) {
                    $fileName = str_replace('"','',$fileName);
                    $matchingFiles[] = $fileName;
                }
            }

            

        }
    }

    
    return $matchingFiles;

}


function peersRequest($path){

    $dir = getcwd();
    if(!is_dir($dir)){
        throw new Exception("Directorio no existente. \n");
    }
    

    $files = scandir($dir);
    $matchingPeers = [];

    foreach($files as $file){

        if(pathinfo($file, PATHINFO_EXTENSION) == 'json'){

            $rootFile = $dir."/".$file;
            $fileContent = file_get_contents($rootFile);
            $contentDecode = json_decode($fileContent,true);
            
            if(is_array($contentDecode["files"]) === false){
                
                $arrayFiles =  $contentDecode["files"];
                $arrayFiles = str_replace("[","",$arrayFiles);
                $arrayFiles = str_replace("]","",$arrayFiles);
                $arrayFiles = explode(",",$arrayFiles);

            }else{
                $arrayFiles = $contentDecode["files"];
            }

            
            
            foreach ($arrayFiles as $fileName) {
                $fileName = str_replace('"','',$fileName);
                trim($fileName);
                if ($fileName === $path) {
                    $matchingPeers[] = $file;
                }
            }

        }

    }

    $matchingPeers = str_replace(".json","",$matchingPeers);
    $matchingPeers = str_replace("_",":",$matchingPeers);
    return $matchingPeers;
}

    
function filesRequest($input){

    
    $dir = getcwd();
    if(!is_dir($dir)){
        throw new Exception("Directorio no existente. \n");
    }
    
    $files = scandir($dir);
    $filesPeers = [];
    $path = str_replace(":","_",$input);
    $rootPeer = $dir."/".$path.".json";

    
    if(file_exists($rootPeer)){


        $fileContent = file_get_contents($rootPeer);
        $contentDecode = json_decode($fileContent,true);

        if(is_array($contentDecode["files"]) === false){
                
            $arrayFiles =  $contentDecode["files"];
            $arrayFiles = str_replace("[","",$arrayFiles);
            $arrayFiles = str_replace("]","",$arrayFiles);
            $arrayFiles = explode(",",$arrayFiles);

        }else{
            $arrayFiles = $contentDecode["files"];
        }

        foreach ($arrayFiles as $fileName) {
            $fileName = str_replace('"','',$fileName);
            trim($fileName);
            $filesPeer[] = $fileName;
            
        }

    }

    
    return $filesPeer;

}

    
function exitRequest($header){

    trim($header);
    $exit_sliced = explode(" ",$header);
    $peerName = $exit_sliced[1];

    $dir = getcwd();
    if(!is_dir($dir)){
        throw new Exception("Directorio no existente. \n");
    }
    
    $files = scandir($dir);
    $rootPeer = $dir."/".$peerName.".json";

    
    if(file_exists($rootPeer)){

        unlink($rootPeer);

    }

    
    



}