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
    
    
        if(empty($input_central[0]) || empty($input_central[1]) || empty($input_mine[0]) || empty($input_mine[1])){
            throw new Exception("Falta algun elemento.");

        }

        $IPcentral = $input_central[0];
        $PORTcentral = $input_central[1];
        $IPpeer = $input_mine[0];
        $PORTpeer = $input_mine[1];
    
        
        echo "Conectado al central server \n";

        $parentPid = posix_getpid();
        if(pcntl_fork()==0){

            
                run_p1($IPpeer,$PORTpeer);
                
            exit(0);
            

        }elseif(pcntl_fork() == 0){
            
            while(true){

                run_uploaded($IPcentral,$PORTcentral,$IPpeer,$PORTpeer);
                sleep(30);
            }

            exit(0);

        }
        
        run_ux($IPcentral,$PORTcentral,$IPpeer,$PORTpeer);
        posix_kill(0 - $parentPid, SIGTERM);  //matamos todos lo hijos del padre


    }catch(Exception $e){
        echo "Se produjo un error: ". $e -> getMessage();
        socket_close($GLOBALS["socket"]);
    }
    

}