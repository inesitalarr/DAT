#!/bin/bash
curl -X POST -H "Content-Type: application/json" -d '{

    "IDsonda": 1,
    "Parametro": "Temperatura",
    "Valor": 30.5,
    "Unidades": "°C"
    
}' http://localhost:5050/rx_data.php


#/mnt/c/UPNA/MASTER/Dis_Apps_Telem/Practicas/PRACTICA3/P3/SONDAS
