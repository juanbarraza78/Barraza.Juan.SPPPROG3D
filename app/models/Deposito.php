<?php


class Deposito
{
    public $nroDeCuenta;
    public $nroDeDeposito;
    public $fecha;
    public $monto;

    public function crearCuenta()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO depositos (nroDeCuenta, fecha, monto) VALUES (:nroDeCuenta, :fecha, :monto)");
        $consulta->bindValue(':nroDeCuenta', $this->nroDeCuenta, PDO::PARAM_STR);
        $consulta->bindValue(':fecha', $this->fecha, PDO::PARAM_STR);
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
    public static function obtenerCuenta($nroDeDeposito)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM depositos WHERE nroDeDeposito = :nroDeDeposito");
        $consulta->bindValue(':nroDeDeposito', $nroDeDeposito, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetchObject('Deposito');
    }

    //JSON
    public static function CompararNombre($deposito1, $deposito2)
    {
        return strcmp($deposito1->_nombre, $deposito2->_nombre);
    }

    public static function GenerarIdAutoIncrementalDeposito()
    {
        $nroDeDeposito = 2000;

        if(file_exists("nroDeDeposito.txt"))
        {
            $nroDeDeposito = file_get_contents("nroDeDeposito.txt");           
        }

        $nroDeDeposito++;

        file_put_contents("nroDeDeposito.txt", $nroDeDeposito);

        return $nroDeDeposito;
    }

    public static function ObtenerDepositos()
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

    public static function GuardarDepositos($arrayDepositos)
    {
        $rutaArchivo = "depositos.json";
        $archivoJson = json_encode($arrayDepositos,JSON_PRETTY_PRINT);
        file_put_contents($rutaArchivo,$archivoJson);
    }

    public static function AgregarDeposito($nuevoDeposito)
    {
        $arrayDepositos = Deposito::ObtenerDepositos();
        $arrayDepositos[] = $nuevoDeposito;
        Deposito::GuardarDepositos($arrayDepositos);
    }
    //a
    public static function TotalDepositado($TipoDeCuenta, $moneda, $fecha = null)
    {
        $montoTotal = 0;
        if($fecha == null)
        {
            $fecha = New Datetime();
            $fecha->sub(new DateInterval('P1D'));
            $fecha->format('d-m-Y');
        }
        $arrayDepositos = Deposito::ObtenerDepositos();
        foreach ($arrayDepositos as $deposito) 
        {
            if($deposito->_tipoDeCuenta == $TipoDeCuenta && $deposito->_moneda == $moneda && $deposito->_fecha == $fecha)
            {
                $montoTotal += $deposito->_monto;
            }
        }
        return $montoTotal;
    }
    //b
    public static function BuscarDepositoParticular($numeroDeCuenta)
    {
        $arrayDepositos = Deposito::ObtenerDepositos();
        $arrayAux = [];
        foreach ($arrayDepositos as $deposito) 
        {
            if($deposito->_nroDeCuenta == $numeroDeCuenta)
            {
                $arrayAux[] = $deposito;
            }
        }
        return $arrayAux;
    }
    //c
    public static function BuscarEntreFechas($fechaInicial, $fechaFinal)
    {
        $arrayDepositos = Deposito::ObtenerDepositos();
        $arrayAux = [];
        foreach ($arrayDepositos as $deposito) 
        {
            if($deposito->_fecha >= $fechaInicial && $deposito->_fecha <= $fechaFinal)
            {
                $arrayAux[] = $deposito;
            }
        }
        usort($arrayAux, "Deposito::CompararNombre");
        return $arrayAux;
    }
    //d
    public static function BuscarPorTipoDeCuenta($TipoDeCuenta)
    {
        $arrayDepositos = Deposito::ObtenerDepositos();
        $arrayAux = [];
        foreach ($arrayDepositos as $deposito) 
        {
            if($deposito->_tipoDeCuenta == $TipoDeCuenta)
            {
                $arrayAux[] = $deposito;
            }
        }
        return $arrayAux;
    }
    //e
    public static function BuscarPorMoneda($moneda)
    {
        $arrayDepositos = Deposito::ObtenerDepositos();
        $arrayAux = [];
        foreach ($arrayDepositos as $deposito) 
        {
            if($deposito->_moneda == $moneda)
            {
                $arrayAux[] = $deposito;
            }
        }
        return $arrayAux;
    }

    private function MostrarDeposito()
    {
        echo $this->_nombre."-";
        echo $this->_apellido."-";
        echo $this->_tipoDocumento."-";
        echo $this->_numeroDocumento."-";
        echo $this->_email."-";
        echo $this->_tipoDeCuenta."-";
        echo $this->_moneda."-";
        echo $this->_saldoInicial."-";
        echo $this->_nroDeCuenta."-";
        echo $this->_nroDeDeposito."-";
        echo $this->_fecha."-";
        echo $this->_monto;
        echo "</br>";
    }

    public static function MostrarArrayDepositos($arrayDepositos)
    {
        if($arrayDepositos != null)
        {
            echo "Nombre - Apellido - Tipo de documento - Numero de documento - Email - Tipo de cuenta - Tipo de moneda - Saldo inicial - Numero de cuenta - Numero de deposito - Fecha - Monto";
            echo "</br>";
            echo "</br>";
            foreach ($arrayDepositos as $deposito) 
            {
                $deposito->MostrarDeposito();
            }
        }
        else{
            echo "No hay lista de Depositos </br>";
        }

    }


}