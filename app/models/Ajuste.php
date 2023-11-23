<?php

class Ajuste
{
    public $importe;
    public $motivo;
    public $numeroDeDepositoORetiro;
    public $numeroDeAjuste;


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

    public function crearAjusteJSON()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO ajustes (importe, motivo, numeroDeDepositoORetiro, numeroDeAjuste) VALUES (:importe, :motivo, :numeroDeDepositoORetiro, :numeroDeAjuste)");
        $consulta->bindValue(':importe', $this->importe, PDO::PARAM_INT);
        $consulta->bindValue(':motivo', $this->motivo, PDO::PARAM_STR);
        $consulta->bindValue(':numeroDeDepositoORetiro', $this->numeroDeDepositoORetiro, PDO::PARAM_INT);
        $consulta->bindValue(':numeroDeAjuste', $this->numeroDeAjuste, PDO::PARAM_INT);
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }
    public static function obtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT importe, motivo, numeroDeDepositoORetiro, numeroDeAjuste FROM ajustes");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Ajuste');
    }

    public static function obtenerAjuste($numeroDeAjuste)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT importe, motivo, numeroDeDepositoORetiro, numeroDeAjuste FROM ajustes WHERE numeroDeAjuste = :numeroDeAjuste");
        $consulta->bindValue(':numeroDeAjuste', $numeroDeAjuste, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetchObject('Mesa');
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

    public static function borrarAjustes()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("TRUNCATE ajustes");
        $consulta->execute();
    }

    public static function ObtenerAjustes()
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

    public static function GuardarAjustes($arrayAjustes)
    {
        $rutaArchivo = "ajustes.json";
        $archivoJson = json_encode($arrayAjustes,JSON_PRETTY_PRINT);
        file_put_contents($rutaArchivo,$archivoJson);
    }

    
}