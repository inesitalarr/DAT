<?php

//Recibe los json (datos) que le manda el AGREGADOR de las SONDAS
require_once 'common.php';
$db = conexion_mysql();

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    
    try{


        $json = file_get_contents('php://input');

        if(!empty($json)){  
            $data_json = json_decode($json,true);
            
            //Si la lectura del json ha sido correcta nos conectamos a la base de datos
            
           
            //creamos la tabla si no existe
            $nombre_tabla = 'dbDataSondas';
            $coger_datos = $crear_tabla_sql = "CREATE TABLE IF NOT EXISTS DataSondas (
                IDSonda INT,
                FechaRegistro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                DatosSonda JSON
            )";
            $r = $db -> prepare($coger_datos);
            $r -> execute();
            


            
            $IDsonda = (int)$data_json['IDsonda'];

           
            if($aux != null){


                $coger_datos = 'INSERT INTO DataSondas (IDSonda, DatosSonda) VALUES (:idsonda,:filejson)';
                $auxo[':idsonda'] = $IDsonda;
                $auxo['filejson'] = $data_json;

                $r = $db -> prepare($coger_datos);
                $r -> execute($auxo);
                $aux = $r -> fetch();

                if($aux != null){
                    echo "Datos recibidos y guardados correctamente en el controlador. \n";
                }else{
                    throw new Exception("Error en la subida de los datos al controlador. \n");
                }

                

            }else{

                throw new Exception( "Error al insertar los nuevos datos en la base de datos: ".mysqli_error($c)."\n");

            }

            mysqli_close($c);

       
        }else{
            throw new Exception( "Error al obtener los datos del AGREGADOR. \n");
        }




    }catch(Exception $e){
        echo "Error: ". $e->getMessage();
    }

    

}