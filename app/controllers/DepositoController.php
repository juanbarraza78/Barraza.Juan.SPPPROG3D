<?php
require_once './models/Deposito.php';
require_once './models/Cuenta.php';

class DepositoController extends Deposito
{
  public function CargarUno($request, $response, $args) // POST : nroDeCuenta tipoDeCuenta monto archivo
  {
    $parametros = $request->getParsedBody();

    $nroDeCuenta = $parametros['nroDeCuenta'];
    $fecha = date("Y-m-d");
    $monto = $parametros['monto'];
    $tipoDeCuenta = $parametros['tipoDeCuenta'];

    $deposito = new Deposito();
    $deposito->nroDeCuenta = $nroDeCuenta;
    $deposito->fecha = $fecha;
    $deposito->monto = $monto;
    
    $cuenta = Cuenta::obtenerCuentaTipoYNumero($nroDeCuenta, $tipoDeCuenta);

    if($cuenta)
    {

      $id = $deposito->crearDeposito();

      $carpeta_archivos = 'img/depositos/2023/';
      $nombre_archivo = $cuenta->tipoDeCuenta . $deposito->nroDeCuenta . $id;
      $ruta_destino = $carpeta_archivos . $nombre_archivo . ".jpg";
      if (move_uploaded_file($_FILES['archivo']['tmp_name'], $ruta_destino)) {
        $payload = json_encode(array("mensaje" => "Deposito creado con exito"));
        $cuenta->saldo += $deposito->monto;
        $cuenta->modificarMontoCuenta();
      } else {
        $payload = json_encode(array("mensaje" => "Error Foto"));
      }
    }
    else
    {
      $payload = json_encode(array("mensaje" => "No existe esa cuenta"));
    }
    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }
  public function TraerUno($request, $response, $args) // GET :  nroDeDeposito
  {
    $nroDeDeposito = $args['nroDeDeposito'];

    $deposito = Deposito::obtenerDeposito($nroDeDeposito);
    $payload = json_encode(Deposito::crearDepositoExtendido($deposito));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }
  public function TraerTodos($request, $response, $args) // GET 
  {
    $lista = Deposito::obtenerTodos();
    $payload = json_encode(array("listaDeposito" => Deposito::CrearArrayDeposito($lista)));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }
  public static function CompararNombre($deposito1, $deposito2)
  {
      return strcmp($deposito1->nombre, $deposito2->nombre);
  }

  public function ConsultarMovimientoA($request, $response, $args) // GET tipoDeCuenta fecha
  {
    $parametrosParam = $request->getQueryParams();
    $tipoDeCuenta = $parametrosParam['tipoDeCuenta'];
    $fecha = $parametrosParam['fecha'];
    if (!isset($fecha)) {
      $fecha = New Datetime();
      $fecha->sub(new DateInterval('P1D'));
      $fecha->format('d-m-Y');
    } 
    $montoTotal = 0;
    $arrayDepositos = Deposito::obtenerTodos();
    foreach ($arrayDepositos as $deposito) 
    {
      $CuentaDeposito = Cuenta::obtenerCuenta($deposito->nroDeCuenta);
      if($CuentaDeposito && $CuentaDeposito->tipoDeCuenta == $tipoDeCuenta && $deposito->fecha == $fecha)
      {
        $montoTotal += $deposito->monto;
      }
    }
    $payload = json_encode(array("Total depositado" => $montoTotal));

    $response->getBody()->write($payload);
    return $response
    ->withHeader('Content-Type', 'application/json');
  }
  public function ConsultarMovimientoB($request, $response, $args) // GET tipoDeCuenta nroDeCuenta
  {
    $parametrosParam = $request->getQueryParams();

    $tipoDeCuenta = $parametrosParam['tipoDeCuenta'];
    $nroDeCuenta = $parametrosParam['nroDeCuenta'];

    $arrayDepositos = Deposito::obtenerTodos();
    $arrayAux = [];
    foreach ($arrayDepositos as $deposito) 
    {
      $CuentaDeposito = Cuenta::obtenerCuenta($deposito->nroDeCuenta);
      if($CuentaDeposito && $deposito->nroDeCuenta == $nroDeCuenta && $CuentaDeposito->tipoDeCuenta == $tipoDeCuenta)
      {
          $arrayAux[] = $deposito;
      }
    }
    $payload = json_encode(array("listaDeposito" => Deposito::CrearArrayDeposito($arrayAux)));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }
  public function ConsultarMovimientoC($request, $response, $args) // GET fechaInicio fechaFin
  {
    $parametrosParam = $request->getQueryParams();
    $fechaInicio = $parametrosParam['fechaInicio'];
    $fechaFin = $parametrosParam['fechaFin'];

    $arrayDepositos = Deposito::obtenerTodos();
    $arrayAux = [];
    foreach ($arrayDepositos as $deposito) 
    {
      $CuentaDeposito = Cuenta::obtenerCuenta($deposito->nroDeCuenta);
      if($CuentaDeposito && $deposito->fecha >= $fechaInicio && $deposito->fecha <= $fechaFin)
      {
          $arrayAux[] = $deposito;
      }
    }
    //usort($arrayAux, "DepositoController::CompararNombre"); //Por ahora nada
    $payload = json_encode(array("listaDeposito" => Deposito::CrearArrayDeposito($arrayAux)));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }
  public function ConsultarMovimientoD($request, $response, $args) // GET tipoDeCuenta
  {
    $parametrosParam = $request->getQueryParams();
    $tipoDeCuenta = $parametrosParam['tipoDeCuenta'];

    $arrayDepositos = Deposito::obtenerTodos();
    $arrayAux = [];
    foreach ($arrayDepositos as $deposito) 
    {
      $CuentaDeposito = Cuenta::obtenerCuenta($deposito->nroDeCuenta);
      if($CuentaDeposito && $CuentaDeposito->tipoDeCuenta == $tipoDeCuenta)
      {
          $arrayAux[] = $deposito;
      }
    }
    $payload = json_encode(array("listaDeposito" => Deposito::CrearArrayDeposito($arrayAux)));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }
  public function ConsultarMovimientoE($request, $response, $args) // GET moneda
  {
    $parametrosParam = $request->getQueryParams();
    $moneda = $parametrosParam['moneda'];
    if($moneda !== "$")
    {
      $moneda = "U";
    }

    $arrayDepositos = Deposito::obtenerTodos();
    $arrayAux = [];
    foreach ($arrayDepositos as $deposito) 
    {
      $CuentaDeposito = Cuenta::obtenerCuenta($deposito->nroDeCuenta);
      if($CuentaDeposito)
      {
        $terceraLetra = substr($CuentaDeposito->tipoDeCuenta, 2, 1);
        if($terceraLetra === $moneda)
        {
            $arrayAux[] = $deposito;
        }
      }

    }
    $payload = json_encode(array("listaDeposito" => Deposito::CrearArrayDeposito($arrayAux)));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

}