En este README.md se va a explicar el funcionamiento de la segunda práctica de la asignatura.

INICIALIZACIÓN: HELLO

Para ejecutar el programa se debe hacer desde main.php por lo que la terminal de ejecución debe estar en el mismo directorio que el archivo main.php. Para ejecutar las diferentes partes de la práctica es de la siguiente manera:

    - php main.php --server: Ejecuta el main en modo servidor central, aquel que procesará las peticiones de PUT y GET de los peers. Por defecto se le asigna la IP 127.0.1.1 y puerto 8085. Se pueden cambiar accediendo al php "config_local.php".

    - php main.php http://IPcentral:PUERTOcentral/miIP:miPUERTO: Ese es el modelo de línea para introducir en un terminal para conectarnos al servidor central y convertirnos en un peer. Se debe tener en la misma ruta en la que se ejecuta el terminal con esa intrucción, una carpeta que contenga los archivos que se deseen. Esos archivos son los que se subirán al servidor central al mandarle la petición PUT. El nombre de dicha carpeta debe ser formato miIP_miPUERTO. Se incluyen 3 carpetas con diferentes archivos de varias extensiones para actuar como peers.


    EJEMPLO de funcionamiento:

    - En un terminal ejecuto: php main.php --server  
    - En otro terminal ejecuto: php main.php http://127.0.1.1:8085/127.0.0.2:2222

    De este modo me conecto al servidor central con IP: 127.0.1.1 y puerto: 8085. E indico mi IP: 127.0.0.2 y puerto: 2222. Tengo una carpeta llamada 127.0.0.2_2222 en el mismo directorio donde ejecuto el terinal para conectarme como peer.

FUNCIONAMIENTO DEL CÓDIGO:

    -FUNCIONES RECICLADAS:
    En el php "common.php" hay un conjunto de funciones que emplean otros php tanto desde el lado del servidor central como en el lado del peer. Las funciones presentes:
        - get_header($socket): Obtiene las cabeceras http. Da como parámetro de salida la cebecera y de entrada el  $socket al que escuchar.

        - process_header_response($header): procesa la cabecera cuando es de respuesta, como parámetro de entrada se le introduce la cabecera obtenida con la función get_header y de salida proporciona un array con esa cabecera procesada.

        - send_GET_request($sock,$type,$path): genera una peticion tipo GET, se introduce como parámetros de entrada el $socket para hacer el socket_write(), $type es el tipo de peticion GET (search,files,peers) y $path es el archivo o trozo de archivo que se va a buscar en la base de datos (.json) del servidor central.

        - send_response($status,$body, $content_length = null,$sock): Envia una respuesta por parte del lado del servidor una vez que procesa la peticion de un peer. Como parámetros de entrada tiene el $status indicando si la peticion es correcta o no ("200 OK" o "402 Forbbiden"), $body (es el contenido que manda de respuesta el servidor al peer) y $sock que es el socket donde hacer el socket_write().

        -socket_connection($server,$port): funcion que realiza la conexión de un peer con un servidor (tanto el servidor central y servidor de un peer). Los parámetros de entrada son la IP ($server) y el puerto ($port) son la IP y puerto del sevidor al que conectarse. El socket creado lo establece como variable global en $GLOBALS["socket_client"].

        -close_socket(): Se cierra el socket guardado en la variable global generada por la funcion anterior $GLOBALS["socket_client"].





    -SERVIDOR CENTRAL: 
        - Ejecuntado php main.php --server: 
            - Se ejecuta un hilo hijo que hace la comprobación periódica (cada 100 segundos) de los archivos que tiene guardados. Si de los archivos de cada peer (en caso de haberlo) el tiempo en el que se actualizó con respecto al de la comprobación es mayor de 100 segundos, se borra. Para el trabajo se ha tenido en cuenta que por cada peer conectado al servidor central se genera un json con los nombres de los archivos de ese peer. Ese json posee un campo llamado "[time]" y otro llamado "[files]". Ese hilo accede a cada uno de los json obteniendo el valor del campo "[time]" y lo compara con el valor actual, si la resta del valor actual con el del json es es mayor de 100 segundos, elimina el json de la base de datos del servidor central.

            - El hilo padre ejecuta la funcion run_central($server_host,$server_port) que pertenece al php "central.php". Aquí es donde se ejcuta la funcion de servidor central. Los parámetros de entrada de la función corresponden a la IP del servidor central ($server_host) y el puerto ($server_port) definidos en el config_local.php. Se utilizan establecer un socket_bind() son esa IP y puerto. En caso de que haya algún error en las creaciones de los sockets se lanzará una excepción. Depués se ejecutará un while(true) donde se acepatarán a los diferentes peers que se quieran conectar al servidor central. Para cada peer se crea un hilo hijo que procesa las peticiones, primero obteniendo la cabecera de las peticiones de los peer con get_header($socket) y el proceso de esa peticion en process_header_request($header). En caso de no ser el hilo hijo, se cierra el socket.

                -Pro


     
    
