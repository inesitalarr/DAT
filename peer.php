<?php

//Este php hace la funcion de crear un cliente, le pasamos el argv[1] que sera tipo:
//php main.php http://IPcentralserver:PORTcentralserver/IPmia:PORTmio

require_once 'ux.php';
require_once 'p1.php';
require_once 'uploaded.php';
function run_peer($input){

    try{

        if(empty(($input))){
            
            throw new Exception("Error en la conexion.");
        }
    
        $input_sliced = explode("//",$input);
        $input_bar = explode("/",$input_sliced[1]);
        $input_central = explode(":",$input_bar[0]);
        $input_mine = explode(":",$input_bar[1]);
    
        $IPcentral = $input_central[0];
        $PORTcentral = $input_central[1];
        $IPpeer = $input_mine[0];
        $PORTpeer = $input_mine[1];
    
        if(empty($IPcentral) || empty($PORTpeer) || empty($IPpeer) || empty($PORTpeer)){
            echo "Falta algun parametro";
            die();
        }
    
        //se crea el socket del peer
        $sock = socket_create(AF_INET, SOCK_STREAM, getprotobyname('tcp'));  //se crea el socket
        $GLOBALS["socket"] = $sock;

        $timeout = ['sec' => 500, 'usec' => 0]; // 5 segundos
        socket_set_option($sock, SOL_SOCKET, SO_SNDTIMEO, $timeout);
        socket_set_option($sock, SOL_SOCKET, SO_RCVTIMEO, $timeout);
        //Nos conectamos al socket donde el servidor está esucchando
        $socket_conn = socket_connect($sock,$IPcentral,$PORTcentral);

        if ($socket_conn === false){
            $error_code = socket_last_error();
            $error_msg = socket_strerror($error_code);
            throw new Exception("Error al crear el socket cliente: [$error_code] $error_msg");
        }else{
            echo "Conectado al central server \n";
    
            if(pcntl_fork()==0){
    
                while(true){
                    run_p1($IPpeer,$PORTpeer);
                    
                }

                exit(0);
                
    
            }elseif(pcntl_fork() == 0){
                
                while(true){
                    run_uploaded($GLOBALS["socket"],$IPpeer,$PORTpeer,$IPcentral,$PORTcentral);
                    sleep(10);
                }

                exit(0);
            }
            run_ux($GLOBALS["socket"]);
        }

    }catch(Exception $e){
        echo "Se produjo un error: ". $e -> getMessage();
    }
    

}