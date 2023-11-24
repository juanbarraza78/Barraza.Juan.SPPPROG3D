<?php

class Ajuste
{
    public $importe;
    public $motivo;
    public $numeroDeDepositoORetiro;
    public $numeroDeAjuste;

    //Base de datos
    public function crearAjuste()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO ajustes (importe, motivo, numeroDeDepositoORetiro) VALUES (:importe, :motivo, :numeroDeDepositoORetiro)");
        $consulta->bindValue(':importe', $this->importe, PDO::PARAM_INT);
        $consulta->bindValue(':motivo', $this->motivo, PDO::PARAM_STR);
        $consulta->bindValue(':numeroDeDepositoORetiro', $this->numeroDeDepositoORetiro, PDO::PARAM_INT);
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }
    public static function obtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM ajustes");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Ajuste');
    }
    public static function obtenerAjuste($numeroDeAjuste)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM ajustes WHERE numeroDeAjuste = :numeroDeAjuste");
        $consulta->bindValue(':numeroDeAjuste', $numeroDeAjuste, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetchObject('Ajuste');
    }
    public static function modificarAjuste($numeroDeAjuste, $numeroDeDepositoORetiro, $importe, $motivo)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE ajustes SET importe = :importe, motivo = :motivo,  numeroDeDepositoORetiro = :numeroDeDepositoORetiro WHERE numeroDeAjuste = :numeroDeAjuste");
        $consulta->bindValue(':importe', $importe, PDO::PARAM_INT);
        $consulta->bindValue(':motivo', $motivo, PDO::PARAM_STR);
        $consulta->bindValue(':numeroDeDepositoORetiro', $numeroDeDepositoORetiro, PDO::PARAM_INT);
        $consulta->bindValue(':numeroDeAjuste', $numeroDeAjuste, PDO::PARAM_INT);
        $consulta->execute();
    }
    public static function borrarAjuste($numeroDeAjuste)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("DELETE FROM ajustes WHERE numeroDeAjuste = :numeroDeAjuste");
        $consulta->bindValue(':numeroDeAjuste', $numeroDeAjuste, PDO::PARAM_INT);
        $consulta->execute();
    }

    //JSON
    public static function ObtenerAjustesJSON()
    {
        $arrayAjustes = array();
        $rutaArchivo = 'ajustes.json';
        
        if(file_exists($rutaArchivo))
        {
            $data = file_get_contents($rutaArchivo); 
            $arrayAsociativo = json_decode($data,true);
            foreach($arrayAsociativo as $ajuste)
            {              
                $nuevoAjuste = new Ajuste();
                $nuevoAjuste->importe =  $ajuste["importe"];
                $nuevoAjuste->motivo =  $ajuste["motivo"];
                $nuevoAjuste->numeroDeDepositoORetiro = $ajuste["numeroDeDepositoORetiro"];
                $nuevoAjuste->numeroDeAjuste = $ajuste["numeroDeAjuste"];
                $arrayAjustes[] = $nuevoAjuste;
            }
        }   
        else 
        {
            file_put_contents($rutaArchivo, "[]");
        }
        return $arrayAjustes;
    }
    public static function GuardarAjustesJSON($arrayAjustes)
    {
        $rutaArchivo = "ajustes.json";
        $archivoJson = json_encode($arrayAjustes,JSON_PRETTY_PRINT);
        file_put_contents($rutaArchivo,$archivoJson);
    }










    
}