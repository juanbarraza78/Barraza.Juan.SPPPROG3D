<?php
class Cuenta
{
    public $nombre;
    public $apellido;
    public $tipoDocumento;
    public $numeroDocumento;
    public $email;
    public $tipoDeCuenta;
    public $saldo;
    public $nroDeCuenta;
    public $estaActivo;  

    //Base de datos
    public function crearCuenta()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO cuentas (nombre, apellido, tipoDocumento, numeroDocumento, email, tipoDeCuenta, saldo, estaActivo) VALUES (:nombre, :apellido, :tipoDocumento, :numeroDocumento, :email, :tipoDeCuenta, :saldo, :estaActivo)");
        $consulta->bindValue(':nombre', $this->nombre, PDO::PARAM_STR);
        $consulta->bindValue(':apellido', $this->apellido, PDO::PARAM_STR);
        $consulta->bindValue(':tipoDocumento', $this->tipoDocumento, PDO::PARAM_STR);
        $consulta->bindValue(':numeroDocumento', $this->numeroDocumento, PDO::PARAM_INT);
        $consulta->bindValue(':email', $this->email, PDO::PARAM_STR);
        $consulta->bindValue(':tipoDeCuenta', $this->tipoDeCuenta, PDO::PARAM_STR);
        $consulta->bindValue(':saldo', $this->saldo, PDO::PARAM_INT);
        $consulta->bindValue(':estaActivo', $this->estaActivo, PDO::PARAM_BOOL);
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }
    public static function obtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM cuentas");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Cuenta');
    }
    public static function obtenerCuenta($nroDeCuenta)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM cuentas WHERE nroDeCuenta = :nroDeCuenta");
        $consulta->bindValue(':nroDeCuenta', $nroDeCuenta, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetchObject('Cuenta');
    }
    //Json
    public static function ObtenerClientesJson()
    {
        $arrayClientes = array();
        $rutaArchivo = 'banco.json';
        
        if(file_exists($rutaArchivo))
        {
            $data = file_get_contents($rutaArchivo); 
            $arrayAsociativo = json_decode($data,true);
            foreach($arrayAsociativo as $cliente)
            {              
                $nuevoCliente = new Cliente($cliente["_nombre"], $cliente["_apellido"], $cliente["_tipoDocumento"], $cliente["_numeroDocumento"], $cliente["_email"], $cliente["_tipoDeCuenta"], $cliente["_saldoInicial"], $cliente["_nroDeCuenta"],$cliente["_estaActivo"]);
                $arrayClientes[] = $nuevoCliente;
            }
        }   
        else 
        {
            file_put_contents($rutaArchivo, "[]");
        }
        return $arrayClientes;
    }
    public static function GuardarClientesJson($arrayClientes)
    {
        $rutaArchivo = "banco.json";
        $archivoJson = json_encode($arrayClientes,JSON_PRETTY_PRINT);
        file_put_contents($rutaArchivo,$archivoJson);
    }


    public static function IsMismoCliente($cliente1 , $cliente2)
    {
        $exito = false;
        if($cliente1->nombre == $cliente2->nombre && $cliente1->apellido == $cliente2->apellido && $cliente1->tipoDeCuenta == $cliente2->tipoDeCuenta)
        {
            $exito = true;
        }
        return $exito;
    }

    public static function obtenerCuentaTipoYNumero($nroDeCuenta, $tipoDeCuenta)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM cuentas WHERE nroDeCuenta = :nroDeCuenta AND tipoDeCuenta = :tipoDeCuenta");
        $consulta->bindValue(':nroDeCuenta', $nroDeCuenta, PDO::PARAM_INT);
        $consulta->bindValue(':tipoDeCuenta', $tipoDeCuenta, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchObject('Cuenta');
    }
    
    public static function ConsultarCliente($numeroDeCuenta, $tipoDeCuenta)
    {
        $arrayClientes = Cuenta::obtenerTodos();
        $retorno = Cuenta::ValidarClienteCuenta($arrayClientes, $numeroDeCuenta, $tipoDeCuenta);

        if(is_string($retorno))
        {
            echo $retorno;
        }
        else
        {
            echo $arrayClientes[$retorno]->_moneda . $arrayClientes[$retorno]->_saldoInicial;
        }
    }

    public static function ValidarClienteCuenta($arrayClientes, $numeroDeCuenta, $tipoDeCuenta)
    {
        $retorno = "no existe la combinación de nro y tipo de cuenta";
        $contador = 0;
        foreach ($arrayClientes as $cliente) 
        {
            if($cliente->_nroDeCuenta == $numeroDeCuenta)
            {
                if($cliente->_tipoDeCuenta == $tipoDeCuenta)
                {
                    $retorno = $contador;
                }
                else
                {
                    $retorno = "tipo de cuenta incorrecto";
                }
                break;
            }
            $contador++;
        }
        return $retorno;
    }

    public static function DepositarMonto($numeroDeCuenta, $tipoDeCuenta, $moneda, $importe)
    {
        $retorno = null; 

        $arrayClientes = Cliente::ObtenerClientes();
        
        $validacion = Cliente::ValidarClienteCuenta($arrayClientes, $numeroDeCuenta, $tipoDeCuenta);
        
        if(is_string($validacion))
        {
            echo $validacion;
        }
        else if($arrayClientes[$validacion]->_moneda == $moneda)
        {
            $cliente = $arrayClientes[$validacion];
            $cliente->_saldoInicial += $importe;
            Cliente::GuardarClientes($arrayClientes);
            
            $deposito = new Deposito($cliente->_nombre, $cliente->_apellido, $cliente->_tipoDocumento, $cliente->_numeroDocumento, $cliente->_email, $cliente->_tipoDeCuenta, $cliente->_moneda, $cliente->_saldoInicial, $cliente->_nroDeCuenta, null, null, $importe);
            Deposito::AgregarDeposito($deposito);
            $retorno = $deposito;
        }
        else
        {
            echo "Error en el tipo de moneda";
        }

        return $retorno;
    }

    public static function ModificarCuenta($cliente)
    {
        $arrayClientes = Cliente::ObtenerClientes();

        $validacion = Cliente::ValidarClienteCuenta($arrayClientes, $cliente->_nroDeCuenta, $cliente->_tipoDeCuenta);

        if(is_string($validacion))
        {
            echo "No existe la cuenta";
        }
        elseif($arrayClientes[$validacion]->_moneda == $cliente->_moneda)
        {
            $arrayClientes[$validacion]->_nombre = $cliente->_nombre;
            $arrayClientes[$validacion]->_apellido = $cliente->_apellido;
            $arrayClientes[$validacion]->_tipoDocumento = $cliente->_tipoDocumento;
            $arrayClientes[$validacion]->_numeroDocumento = $cliente->_numeroDocumento;
            $arrayClientes[$validacion]->_email = $cliente->_email;
            // $arrayClientes[$validacion]->_tipoDeCuenta = $cliente->_tipoDeCuenta;
            //$arrayClientes[$validacion]->_moneda = $cliente->_moneda;
            // $arrayClientes[$validacion]->_nroDeCuenta = $cliente->_nroDeCuenta;
            Cliente::GuardarClientes($arrayClientes);
            echo "Cuenta modificada correctamente";
        }
        else
        {
            echo "Tipo de moneda incorrecto";
        }
    }

    public static function RetirarMonto($numeroDeCuenta, $tipoDeCuenta, $moneda, $importe)
    {
        $arrayClientes = Cliente::ObtenerClientes();
        $validacion = Cliente::ValidarClienteCuenta($arrayClientes, $numeroDeCuenta, $tipoDeCuenta);
        if(is_string($validacion))
        {
            echo $validacion;
        }
        else if($arrayClientes[$validacion]->_moneda == $moneda)
        {

            $cliente = $arrayClientes[$validacion];  
            if($cliente->_saldoInicial >= $importe)
            {
               // BancoJson
                $cliente->_saldoInicial -= $importe;
                Cliente::GuardarClientes($arrayClientes);
                // RetiroJson   
                $retiro = new Retiro($cliente->_nombre, $cliente->_apellido, $cliente->_tipoDocumento, $cliente->_numeroDocumento, $cliente->_email, $cliente->_tipoDeCuenta, $cliente->_moneda, $cliente->_saldoInicial, $cliente->_nroDeCuenta, null, null, $importe);
                Retiro::AltaRetirar($retiro);
                echo "Retiro correctamente";
            }
            else
            {
                echo "Saldo inferior al monto a retirar";
            }
             
        }
        else
        {
            echo "Error en el tipo de moneda";
        }
    }

    public static function AjusteCuenta($importe,$motivo,$numeroDeDepositoORetiro)
    {
        $existe = Cliente::ValidarNroDepositoORetiro($numeroDeDepositoORetiro,$motivo);
        if($existe != null && $importe >= 0)
        {
            $montoAModificar = $importe - $existe->_monto;
            $arrayClientes = Cliente::ObtenerClientes();
            foreach ($arrayClientes as $clientes) 
            {
                if($clientes->_nroDeCuenta == $existe->_nroDeCuenta)
                {
                    if($motivo== "Deposito")
                    {
                        $clientes->_saldoInicial += $montoAModificar;
                        echo "Deposito ajustado";
                    }
                    else
                    {
                        $clientes->_saldoInicial -= $montoAModificar;
                        echo "Retiro ajustado";
                    }
                    break;
                }
            }
            Cliente::GuardarClientes($arrayClientes);
            $ajuste = new Ajuste($importe,$motivo,$numeroDeDepositoORetiro);
            Ajuste::AltaAjuste($ajuste); 
        }
        else
        {
            echo "No existe el id o el motivo es incorrecto";
        }

    }

    public static function ValidarNroDepositoORetiro($numeroDeDepositoORetiro,$motivo)
    {
        $arrayAux = [];
        $existe = null;
        if($motivo == "Deposito")
        {
            $arrayAux = Deposito::ObtenerDepositos();
            foreach($arrayAux as $deposito)
            {
                if($deposito->_nroDeDeposito == $numeroDeDepositoORetiro)
                {
                    $existe = $deposito;
                    break;
                }   
            }

        }
        else if($motivo == "Retiro")
        {
            $arrayAux =Retiro::ObtenerRetiros();
            foreach($arrayAux as $retiro)
            {
                if($retiro->_nroDeRetiro == $numeroDeDepositoORetiro)
                {
                    $existe = $retiro;
                    break;
                }   
            }
        }
        return $existe;
    }

    public static function BorrarCliente($numeroDeCuenta, $tipoDeCuenta)
    {
        $arrayClientes = Cliente::ObtenerClientes();
        foreach ($arrayClientes as $clientes) 
        {
            if($clientes->_nroDeCuenta == $numeroDeCuenta && $clientes->_tipoDeCuenta == $tipoDeCuenta)
            {
                $clientes->_estaActivo = false;


                $nombre_archivo = $clientes->_nroDeCuenta . $clientes->_tipoDeCuenta;

                $ruta_origen = 'ImagenesDeCuentas/2023/' . $nombre_archivo . ".jpg";

                $ruta_destino = 'ImagenesBackupCuentas/2023/'.$nombre_archivo.".jpg";

                if (rename($ruta_origen , $ruta_destino)) {
                    echo "Cuenta borrada correctamente";
                }
                break;
            }
        }
        Cliente::GuardarClientes($arrayClientes);
    }

    public static function MostrarActivas($arrayRetiros)
    {
        $arrayClientes = Cliente::ObtenerClientes();
        $arrayAux = [];
        foreach ($arrayRetiros as $retiro) 
        {
            foreach($arrayClientes as $cliente)
            {   
                if($retiro->_nroDeCuenta == $cliente->_nroDeCuenta && $cliente->_estaActivo == true)
                {
                    $arrayAux[] = $retiro;
                }
            }

        }
        return $arrayAux;
    }

}
?>