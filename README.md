En este README.md se va a explicar el funcionamiento de la segunda práctica de la asignatura.

INICIALIZACIÓN: 

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
            - Se ejecuta un hilo hijo que hace la comprobación periódica (cada 100 segundos) de los archivos que tiene guardados (a través de la funcion delete_old_files($limit,$time) en delete_files_central.php). Si de los archivos de cada peer (en caso de haberlo) el tiempo en el que se actualizó con respecto al de la comprobación es mayor de 100 segundos, se borra. Para el trabajo se ha tenido en cuenta que por cada peer conectado al servidor central se genera un json con los nombres de los archivos de ese peer. Ese json posee un campo llamado "[time]" y otro llamado "[files]". Ese hilo accede a cada uno de los json obteniendo el valor del campo "[time]" y lo compara con el valor actual, si la resta del valor actual con el del json es es mayor de 100 segundos, elimina el json de la base de datos del servidor central.

            - El hilo padre ejecuta la funcion run_central($server_host,$server_port) que pertenece al php "central.php". Aquí es donde se ejcuta la funcion de servidor central. Los parámetros de entrada de la función corresponden a la IP del servidor central ($server_host) y el puerto ($server_port) definidos en el config_local.php. Se utilizan establecer un socket_bind() son esa IP y puerto. En caso de que haya algún error en las creaciones de los sockets se lanzará una excepción. Depués se ejecutará un while(true) donde se acepatarán a los diferentes peers que se quieran conectar al servidor central. Para cada peer se crea un hilo hijo que procesa las peticiones, primero obteniendo la cabecera de las peticiones de los peer con get_header($socket) y el proceso de esa peticion en process_header_request($header). En caso de no ser el hilo hijo, se cierra el socket.


    -CONEXION COMO PEER:

        -Ejecutando php main.php http://IPcentral:PUERTOcentral/miIP:miPUERTO se accede a la función run_peer($data) en peer.php. El parámetro de entrada es http://IPcentral:PUERTOcentral/miIP:miPUERTO, por lo que luego dentro de peer.php se desglosa. Depués se generarñan 2 hilos hijos donde se ejecutaran las funciones run_p1() y uploaded() en p1.php y uploaded.php respectivamente. Por otro lado, el ID del hilo padre se guarda en una variable y en ese hilo padre se ejecuta la función run_ux() en ux.php. En caso de que se salga de esa funcion es porque el usuario habrá decidido desconectarse del servidor central y por ello el resto de hilos hijo se terminan.

        - run_p1($IPpeer, $PORTpeer) es donde se levanta la parte de servidor de un peer al conectarse al servidor central. Este tiene como parametros de entrada la IP y puerto del peer que se conecta al servidor central, ya que los peer tienen además la funcionalidad de servdiro. Sin embargo, este servidor solo procesa un tipo de petición, la de descarga. Con esa IP y puerto del peer levanta un socket que se queda a la escucha de conexiones (otros peers) que solicitarán descargarse un archivo que posee ese peer. 

        - run_uploaded($IPcentral,$PORTcentral,$IPpeer,$PORTpeer): es la funcion que lleva a cabo de manera periodica las solicitudes PUT al servidor central. Lo que permite actualizar la lista de archivos que posee ese peer en el lado del servidor central. Esta funcion se ejecuta de manera periodica en el peer.php, teniendo en cuenta que el tiempo en el que sube esa lista de archivos al servidor central es menor que el tiempo que tarda el servidor central en revisar el tiempo límite de los .json.

            Dentro de esta funcion nos conectamos al servidor central, mandamos la peticion y cerramos la conexión. Así por cada peticion PUT que se haga de manera periodica. Se usan los datos $IPpeer y $PORTpeer para mandarlos en la propia peticion PUT, de esa manera el servidor central genera un .json con la IP y puerto de ese peer y ahí almacena los nombres de los archivos que posee ese peer (además de la última actualización o el tiempo en el que realizó el ultimo PUT).

        - run_ux($server_host,$server_port,$IPpeer,$PORTpeer): es la funcion que muestra la interfaz al usuario. En cuanto el peer se conecte al servidor central se le mostrará en el terminal un mensaje indicando que puede escirbir la peticion que desee: search, download o files. Por cada peticion que escriba el usuario por la terminal se creará una peticion tipo GET que será enviada al servidor central. Por ello, por cada peticion GET se realizará un socket_connect y cuando se reciba la respuesta del servidor central un socket_close.

            -search trozoArchivo: Se indicara este tipo de peticion y la cadena de texto que se desee. El servidor devolvera una respuesta con los archivos que contengan esa cadena en el nombre.

            -download nombreArchivo: se generará una petición GET /peers. En el lado del servidor central se recibirá y procesará de manera que la respuesta enviada al usuario será una lista de peers que contienen ese archivo. Automaticamente se elegira uno de esos peers al hazar ($peerIP y $peerPORT) y se ejecutara la funcion de requestDownload($path,$peerIP,$peerPORT) (todo esto dentro de ux). Esta funcion hará que se conecte el peer que desea el archivo al otro peer aleatoriamente elegido que posee el archivo. Ese archivo que desea descargar se guarda en la variable $path. De nuevo al tratarse de una peticion enviada a un servidor (en este caso al de un peer) se hace un socket_connection() y al recibirse la respuesta dle servidor un socket_close(). El archivo se descargará en el directrio en el que se encuentra el peer que hace la peticion de download y el nombre del archivo decargado sera: nombreArchivoOriginal_DL.extensiondelArchivoOriginal.

            -files IPpeer:PORTpeer  : El usuarios también puede ver los archivos que posee un peer en concreto introduciendo la IP:PUERTO del peer. El servidor central devolverá una lista con los archivos que posee ese peer.

            -exit o quit: Si el usuario en la interfaz escribe alguna de estas dos peticiones, se generara una peticion que se enviara al servidor central y este eliminará el archivo .json asociado a este peer. Del mismo modo que el peer se desconectará, saliendo de la función run_ux() y matando todos los hilos hijos.

            -CONTROL DE ERRORES EN LA INTERFAZ DE USUARIO:

                - En caso de introducir una peticion que no sea search, download o files se mostrara por la interfaz un mensaje indicando que la peticion no es correcta.

                - Si solo se introduce un search, download o files: se comprueba que no es un exit o quit y si no lo es se muestra un mensaje indicando que se especifique la cadena a buscar o archivo a descargar.
                
                -En caso de que no haya ningun archivo con la cadena de texto que se desea buscar, el nombre del archivo a descargar sea incorrecto o no haya coincidencias del peer del que se desean ver sus archivos, de nuevo por la interfaz se mostrará un mensaje indicando que incidencia ha habido.





     
    
