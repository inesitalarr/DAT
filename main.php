<?php
//cd /mnt/c/UPNA/MASTER/Dis_Apps_Telem/Practicas/PRACTICA2/P2   ==>esto es la ruta de mi portatil

require_once 'central.php';   //el poner require_once se incluye solo una vez
require_once 'config_local.php';
require_once 'delete_files_central.php';
require_once 'peer.php';

/*
require_once 'uploaded.php';
require_once 'p1.php';
require_once 'ux.php';*/



if($argv[1] == '--server'){

    //Esta es la parte en la que se elimina un json si el cliente no hace PUT
    if(pcntl_fork()==0){
        
        while(true){
            $limit = 60;
            $time = time();
            delete_old_files($limit,$time);
            sleep(5);
            
        }

        exit(0);
    }


    run_central_server($server_host,$server_port);

}else{
    $data = $argv[1];
    run_peer($data);
}



/*
//lo de abajo se pone asi para ir depurando todos los php
if($argv[1] == '--server'){

     //Esta es la parte en la que se elimina un json si el cliente no hace PUT
    if(pcntl_fork()==0){
        
        while(true){
            $limit = 30;
            $time = time();
            delete_old_files($limit,$time);
            sleep(5);
            
        }

        exit(0);
    }


    run_central_server($server_host,$server_port);

}elseif($argv[1] == '--uploaded'){

    //PROBLEMA => AL HACER EL PEER EL socket_write() se resetea socket

    $sock = socket_create(AF_INET, SOCK_STREAM, getprotobyname('tcp'));  //se crea el socket
    $socket_conn = socket_connect($sock,$server_host,$server_port);
    $IPpeer = "127.0.0.1";
    $PORTpeer = 8082;


    while(true){
        run_uploaded($sock,$IPpeer,$PORTpeer);
        sleep(10);
    }

    
}elseif($argv[1] == '--p1'){
    $IPpeer = "127.0.0.1";
    $PORTpeer = 8082;
    
    run_p1($IPpeer,$PORTpeer);

}elseif($argv[1] == '--ux'){

    $sock = socket_create(AF_INET, SOCK_STREAM, getprotobyname('tcp'));  //se crea el socket
    $socket_conn = socket_connect($sock,$server_host,$server_port);
    $IPpeer = "127.0.0.1";
    $PORTpeer = 8085;

    run_ux($sock);
}
*/
