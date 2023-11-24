<?php
require_once './models/Cuenta.php';

class Deposito
{
    public $nroDeCuenta;
    public $nroDeDeposito;
    public $fecha;
    public $monto;

    //Base de datos
    public function crearDeposito()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO depositos (nroDeCuenta, fecha, monto) VALUES (:nroDeCuenta, :fecha, :monto)");
        $consulta->bindValue(':nroDeCuenta', $this->nroDeCuenta, PDO::PARAM_INT);
        $consulta->bindValue(':fecha', $this->fecha);
        $consulta->bindValue(':monto', $this->monto, PDO::PARAM_INT);
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }
    public static function obtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM depositos");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Deposito');
    }
    public static function obtenerDeposito($nroDeDeposito)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM depositos WHERE nroDeDeposito = :nroDeDeposito");
        $consulta->bindValue(':nroDeDeposito', $nroDeDeposito, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetchObject('Deposito');
    }

    //JSON
    public static function ObtenerDepositosJSON()
    {
        $arrayDepositos = array();
        $rutaArchivo = 'depositos.json';
        
        if(file_exists($rutaArchivo))
        {
            $data = file_get_contents($rutaArchivo); 
            $arrayAsociativo = json_decode($data,true);
            foreach($arrayAsociativo as $deposito)
            {              
                $nuevoDeposito = new Deposito($deposito["_nombre"], $deposito["_apellido"], $deposito["_tipoDocumento"], $deposito["_numeroDocumento"], $deposito["_email"], $deposito["_tipoDeCuenta"], $deposito["_moneda"], $deposito["_saldoInicial"], $deposito["_nroDeCuenta"], $deposito["_nroDeDeposito"], $deposito["_fecha"], $deposito["_monto"]);
                $arrayDepositos[] = $nuevoDeposito;
            }
        }   
        else 
        {
            file_put_contents($rutaArchivo, "[]");
        }
        return $arrayDepositos;
    }
    public static function GuardarDepositosJSON($arrayDepositos)
    {
        $rutaArchivo = "depositos.json";
        $archivoJson = json_encode($arrayDepositos,JSON_PRETTY_PRINT);
        file_put_contents($rutaArchivo,$archivoJson);
    }

    //Consulta Movimientos
    public static function CompararNombre($deposito1, $deposito2)
    {
        return strcmp($deposito1->_nombre, $deposito2->_nombre);
    }

    public static function CrearArrayDeposito($arrayDeposito)
    {
        $arrayAux = [];
        if($arrayDeposito != null)
        {
            foreach ($arrayDeposito as $deposito) {
                $obj = Deposito::crearDepositoExtendido($deposito);
                if($obj != null)
                {
                    $arrayAux[] = $obj;
                }
            }
        }
        return $arrayAux;
    }

    public static function crearDepositoExtendido($deposito)
    {
        $obj = null;
        $CuentaDeposito = Cuenta::obtenerCuenta($deposito->nroDeCuenta);
        if($CuentaDeposito)
        {
            $obj = new stdClass();
            $obj->nroDeCuenta = $deposito->nroDeCuenta;
            $obj->nroDeDeposito = $deposito->nroDeDeposito;
            $obj->fecha = $deposito->fecha;
            $obj->monto = $deposito->monto;
            $obj->nombre = $CuentaDeposito->nombre;
            $obj->apellido = $CuentaDeposito->apellido;
            $obj->tipoDocumento = $CuentaDeposito->tipoDocumento;
            $obj->numeroDocumento = $CuentaDeposito->numeroDocumento;
            $obj->email = $CuentaDeposito->email;
            $obj->tipoDeCuenta = $CuentaDeposito->tipoDeCuenta;
            $obj->saldo = $CuentaDeposito->saldo;
            $obj->estaActivo = $CuentaDeposito->estaActivo;
            
        }
        return $obj;
    }

}