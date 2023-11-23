<?php


class Retiro
{
    public $nroDeCuenta;
    public $nroDeRetiro;
    public $fecha;
    public $monto;

    public function __construct($nombre, $apellido, $tipoDocumento, $numeroDocumento, $email, $tipoDeCuenta, $moneda, $saldoInicial, $nroDeCuenta, $nroRetiro = null, $fecha = null, $monto)
    {
        $this->_nombre= $nombre;
        $this->_apellido= $apellido;
        $this->_tipoDocumento= $tipoDocumento;
        $this->_numeroDocumento= $numeroDocumento;
        $this->_email= $email;
        $this->_tipoDeCuenta= $tipoDeCuenta;
        $this->_moneda= $moneda;
        $this->_saldoInicial= $saldoInicial;
        $this->_nroDeCuenta= $nroDeCuenta;
        if($nroRetiro == null)
        {
            $this->_nroDeRetiro = Retiro::GenerarIdAutoIncrementalRetiro();
        }
        else
        {
            $this->_nroDeRetiro = $nroRetiro;
        }
        if($fecha == null)
        {
            $this->_fecha = date("d-m-Y");
        }
        else
        {
            $this->_fecha = $fecha;
        }
        $this->_monto = $monto;
    }

    public static function CompararNombre($retiro1, $retiro2)
    {
        return strcmp($retiro1->_nombre, $retiro2->_nombre);
    }

    public static function GenerarIdAutoIncrementalRetiro()
    {
        $nroDeRetiro = 3000;

        if(file_exists("nroDeRetiro.txt"))
        {
            $nroDeRetiro = file_get_contents("nroDeRetiro.txt");           
        }

        $nroDeRetiro++;

        file_put_contents("nroDeRetiro.txt", $nroDeRetiro);

        return $nroDeRetiro;
    }

    public static function ObtenerRetiros()
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

    public static function GuardarRetiros($arrayRetiros)
    {
        $rutaArchivo = "retiros.json";
        $archivoJson = json_encode($arrayRetiros,JSON_PRETTY_PRINT);
        file_put_contents($rutaArchivo,$archivoJson);
    }

    public static function AltaRetirar($nuevoRetiro)
    {
        $arrayRetiros = Retiro::ObtenerRetiros();
        $arrayRetiros[] = $nuevoRetiro;
        Retiro::GuardarRetiros($arrayRetiros);
    }

    public static function BuscarTotalRetirado($TipoDeCuenta, $moneda, $fecha = null)
    {
        $montoTotal = 0;
        if($fecha == null)
        {
            $fecha = New Datetime();
            $fecha->sub(new DateInterval('P1D'));
            $fecha->format('d-m-Y');
        }
        $arrayRetiros = Retiro::ObtenerRetiros();
        foreach ($arrayRetiros as $retiro) 
        {
            if($retiro->_tipoDeCuenta == $TipoDeCuenta && $retiro->_moneda == $moneda && $retiro->_fecha == $fecha)
            {
                $montoTotal += $retiro->_monto;
            }
        }
        return $montoTotal;
    }
    // public static function BuscarTotalRetiradoActivo($TipoDeCuenta, $moneda, $fecha = null)
    // {
    //     $arrayAux = [];
    //     if($fecha == null)
    //     {
    //         $fecha = New Datetime();
    //         $fecha->sub(new DateInterval('P1D'));
    //         $fecha->format('d-m-Y');
    //     }
    //     $arrayRetiros = Retiro::ObtenerRetiros();
    //     foreach ($arrayRetiros as $retiro) 
    //     {
    //         if($retiro->_tipoDeCuenta == $TipoDeCuenta && $retiro->_moneda == $moneda && $retiro->_fecha == $fecha)
    //         {
    //             $arrayAux[] = $retiro;
    //         }
    //     }
    //     return $arrayAux;
    // }

    public static function BuscarRetiroParticular($numeroDeCuenta)
    {
        $arrayRetiros = Retiro::ObtenerRetiros();
        $arrayAux = [];
        foreach ($arrayRetiros as $retiro) 
        {
            if($retiro->_nroDeCuenta == $numeroDeCuenta)
            {
                $arrayAux[] = $retiro;
            }
        }
        return $arrayAux;
    }

    public static function BuscarRetiroEntreFechas($fechaInicial, $fechaFinal)
    {
        $arrayRetiros = Retiro::ObtenerRetiros();
        $arrayAux = [];
        foreach ($arrayRetiros as $retiro) 
        {
            if($retiro->_fecha >= $fechaInicial && $retiro->_fecha <= $fechaFinal)
            {
                $arrayAux[] = $retiro;
            }
        }
        usort($arrayAux, "Retiro::CompararNombre");
        return $arrayAux;
    }

    public static function BuscarPorTipoDeCuentaRetiro($TipoDeCuenta)
    {
        $arrayRetiros = Retiro::ObtenerRetiros();
        $arrayAux = [];
        foreach ($arrayRetiros as $retiro) 
        {
            if($retiro->_tipoDeCuenta == $TipoDeCuenta)
            {
                $arrayAux[] = $retiro;
            }
        }
        return $arrayAux;
    }

    public static function BuscarPorMonedaRetiro($moneda)
    {
        $arrayRetiros = Retiro::ObtenerRetiros();
        $arrayAux = [];
        foreach ($arrayRetiros as $retiro) 
        {
            if($retiro->_moneda == $moneda)
            {
                $arrayAux[] = $retiro;
            }
        }
        return $arrayAux;
    }

    private function MostrarRetiro()
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
        echo $this->_nroDeRetiro."-";
        echo $this->_fecha."-";
        echo $this->_monto;
        echo "</br>";
    }

    public static function MostrarArrayRetiro($arrayRetiros)
    {
        if($arrayRetiros != null)
        {
            echo "Nombre - Apellido - Tipo de documento - Numero de documento - Email - Tipo de cuenta - Tipo de moneda - Saldo inicial - Numero de cuenta - Numero de Retiro - Fecha - Monto";
            echo "</br>";
            echo "</br>";
            foreach ($arrayRetiros as $retiro) 
            {
                $retiro->MostrarRetiro();
            }
        }
        else{
            echo "No hay lista de Retiros </br>";
        }

    }


}