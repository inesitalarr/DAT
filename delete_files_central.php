<?php


function delete_old_files($limit,$time){

    $dir = getcwd();
    if(!is_dir($dir)){
        echo "Error, no exite el directorio";
        die();
    }

    $files = scandir($dir);

    foreach($files as $file){
        if(pathinfo($file, PATHINFO_EXTENSION) == 'json'){

            $rootFile = $dir."/".$file;
            $fileContent = file_get_contents($rootFile);
            $contentDecode = json_decode($fileContent,true);
            $time_file = $contentDecode["time"];
            if(($time-$time_file) >= $limit){
                echo "Tiempo excedido \n";
                unlink($rootFile);
            }

        }
    }

}
