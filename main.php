<?php
//cd /mnt/c/UPNA/MASTER/Dis_Apps_Telem/Practicas/PRACTICA2/P2   ==>esto es la ruta de mi portatil

require_once 'central.php';   //el poner require_once se incluye solo una vez
require_once 'config_local.php';
require_once 'delete_files_central.php';
require_once 'peer.php';






if($argv[1] == '--server'){

    //Esta es la parte en la que se elimina un json si el cliente no hace PUT
    if(pcntl_fork()==0){
        
        while(true){
            $limit = 100;
            $time = time();
            delete_old_files($limit,$time);
            sleep(10);
            
        }

        exit(0);
    }


    run_central_server($server_host,$server_port);

}else{
    $data = $argv[1];
    run_peer($data);
}






