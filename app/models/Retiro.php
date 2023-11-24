<?php

require_once './models/Cuenta.php';

class Retiro
{
    public $nroDeCuenta;
    public $nroDeRetiro;
    public $fecha;
    public $monto;

    //Base de datos
    public function crearRetiro()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO retiros (nroDeCuenta, fecha, monto) VALUES (:nroDeCuenta, :fecha, :monto)");
        $consulta->bindValue(':nroDeCuenta', $this->nroDeCuenta, PDO::PARAM_INT);
        $consulta->bindValue(':fecha', $this->fecha);
        $consulta->bindValue(':monto', $this->monto, PDO::PARAM_INT);
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }
    public static function obtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM retiros");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Retiro');
    }
    public static function obtenerRetiro($nroDeRetiro)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM retiros WHERE nroDeRetiro = :nroDeRetiro");
        $consulta->bindValue(':nroDeRetiro', $nroDeRetiro, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetchObject('Retiro');
    }

    //JSON
    public static function ObtenerRetirosJSON()
    {
        $arrayRetiros = array();
        $rutaArchivo = 'retiros.json';
        
        if(file_exists($rutaArchivo))
        {
            $data = file_get_contents($rutaArchivo); 
            $arrayAsociativo = json_decode($data,true);
            foreach($arrayAsociativo as $Retiro)
            {              
                $nuevoRetiro = new Retiro($Retiro["_nombre"], $Retiro["_apellido"], $Retiro["_tipoDocumento"], $Retiro["_numeroDocumento"], $Retiro["_email"], $Retiro["_tipoDeCuenta"], $Retiro["_moneda"], $Retiro["_saldoInicial"], $Retiro["_nroDeCuenta"], $Retiro["_nroDeRetiro"], $Retiro["_fecha"], $Retiro["_monto"]);
                $arrayRetiros[] = $nuevoRetiro;
            }
        }   
        else 
        {
            file_put_contents($rutaArchivo, "[]");
        }
        return $arrayRetiros;
    }
    public static function GuardarRetirosJSON($arrayRetiros)
    {
        $rutaArchivo = "retiros.json";
        $archivoJson = json_encode($arrayRetiros,JSON_PRETTY_PRINT);
        file_put_contents($rutaArchivo,$archivoJson);
    }

    //Otos
    public static function CompararNombre($retiro1, $retiro2)
    {
        return strcmp($retiro1->_nombre, $retiro2->_nombre);
    }
    public static function CrearArrayRetiro($arrayRetiros)
    {
        $arrayAux = [];
        if($arrayRetiros != null)
        {
            foreach ($arrayRetiros as $retiro) {
                $obj = Retiro::crearRetiroExtendido($retiro);
                if($obj != null)
                {
                    $arrayAux[] = $obj;
                }
            }
        }
        return $arrayAux;
    }
    public static function crearRetiroExtendido($retiro)
    {
        $obj = null;
        $CuentaRetiro = Cuenta::obtenerCuenta($retiro->nroDeCuenta);
        if($CuentaRetiro)
        {
            $obj = new stdClass();
            $obj->nroDeCuenta = $retiro->nroDeCuenta;
            $obj->nroDeRetiro = $retiro->nroDeRetiro;
            $obj->fecha = $retiro->fecha;
            $obj->monto = $retiro->monto;
            $obj->nombre = $CuentaRetiro->nombre;
            $obj->apellido = $CuentaRetiro->apellido;
            $obj->tipoDocumento = $CuentaRetiro->tipoDocumento;
            $obj->numeroDocumento = $CuentaRetiro->numeroDocumento;
            $obj->email = $CuentaRetiro->email;
            $obj->tipoDeCuenta = $CuentaRetiro->tipoDeCuenta;
            $obj->saldo = $CuentaRetiro->saldo;
            $obj->estaActivo = $CuentaRetiro->estaActivo;
        }
        return $obj;
    }

}