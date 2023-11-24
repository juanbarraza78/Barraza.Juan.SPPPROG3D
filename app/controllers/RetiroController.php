<?php
require_once './models/Retiro.php';
require_once './models/Cuenta.php';

class RetiroController extends Retiro
{
  public function CargarUno($request, $response, $args) // POST : nroDeCuenta tipoDeCuenta monto
  {
    $parametros = $request->getParsedBody();

    $nroDeCuenta = $parametros['nroDeCuenta'];
    $fecha = date("Y-m-d");
    $monto = $parametros['monto'];
    $tipoDeCuenta = $parametros['tipoDeCuenta'];

    $retiro = new Retiro();
    $retiro->nroDeCuenta = $nroDeCuenta;
    $retiro->fecha = $fecha;
    $retiro->monto = $monto;
    
    $cuenta = Cuenta::obtenerCuentaTipoYNumero($nroDeCuenta, $tipoDeCuenta);

    if($cuenta)
    {
      if($cuenta->saldo >= $retiro->monto)
      {
        $retiro->crearRetiro();
        $cuenta->saldo -= $retiro->monto;
        $cuenta->modificarMontoCuenta();
        $payload = json_encode(array("mensaje" => "Retiro creado con exito"));
      }
      else
      {
        $payload = json_encode(array("mensaje" => "Saldo insuficiente"));
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
  public function TraerUno($request, $response, $args) // GET :  nroDeRetiro
  {
    $nroDeRetiro = $args['nroDeRetiro'];

    $retiro = Retiro::obtenerRetiro($nroDeRetiro);
    $payload = json_encode(Retiro::crearRetiroExtendido($retiro));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }
  public function TraerTodos($request, $response, $args) // GET 
  {
    
    $lista = Retiro::CrearArrayRetiro(Retiro::obtenerTodos());
    $payload = json_encode(array("listaRetiro" => $lista));

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
    $arrayRetiros = Retiro::obtenerTodos();
    foreach ($arrayRetiros as $retiro) 
    {
      $CuentaRetiro = Cuenta::obtenerCuenta($retiro->nroDeCuenta);
      if($CuentaRetiro && $CuentaRetiro->tipoDeCuenta == $tipoDeCuenta && $retiro->fecha == $fecha)
      {
        $montoTotal += $retiro->monto;
      }
    }
    $payload = json_encode(array("Total Retirado" => $montoTotal));

    $response->getBody()->write($payload);
    return $response
    ->withHeader('Content-Type', 'application/json');
  }
  public function ConsultarMovimientoB($request, $response, $args) // GET tipoDeCuenta nroDeCuenta
  {
    $parametrosParam = $request->getQueryParams();

    $tipoDeCuenta = $parametrosParam['tipoDeCuenta'];
    $nroDeCuenta = $parametrosParam['nroDeCuenta'];

    $arrayRetiros = Retiro::obtenerTodos();
    $arrayAux = [];
    foreach ($arrayRetiros as $retiro) 
    {
      $CuentaRetiro = Cuenta::obtenerCuenta($retiro->nroDeCuenta);
      if($CuentaRetiro && $retiro->nroDeCuenta == $nroDeCuenta && $CuentaRetiro->tipoDeCuenta == $tipoDeCuenta)
      {
          $arrayAux[] = $retiro;
      }
    }
    
    $payload = json_encode(array("listaRetiro" => Retiro::CrearArrayRetiro($arrayAux)));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }
  public function ConsultarMovimientoC($request, $response, $args) // GET fechaInicio fechaFin
  {
    $parametrosParam = $request->getQueryParams();
    $fechaInicio = $parametrosParam['fechaInicio'];
    $fechaFin = $parametrosParam['fechaFin'];

    $arrayRetiros = Retiro::obtenerTodos();
    $arrayAux = [];
    foreach ($arrayRetiros as $retiro) 
    {
      $CuentaRetiro = Cuenta::obtenerCuenta($retiro->nroDeCuenta);
      if($CuentaRetiro && $retiro->fecha >= $fechaInicio && $retiro->fecha <= $fechaFin)
      {
          $arrayAux[] = $retiro;
      }
    }
    //usort($arrayAux, "DepositoController::CompararNombre"); //Por ahora nada
    $payload = json_encode(array("listaRetiro" => Retiro::CrearArrayRetiro($arrayAux)));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }
  public function ConsultarMovimientoD($request, $response, $args) // GET tipoDeCuenta
  {
    $parametrosParam = $request->getQueryParams();
    $tipoDeCuenta = $parametrosParam['tipoDeCuenta'];

    $arrayRetiros = Retiro::obtenerTodos();
    $arrayAux = [];
    foreach ($arrayRetiros as $retiro) 
    {
      $CuentaRetiro = Cuenta::obtenerCuenta($retiro->nroDeCuenta);
      if($CuentaRetiro && $CuentaRetiro->tipoDeCuenta == $tipoDeCuenta)
      {
          $arrayAux[] = $retiro;
      }
    }
    $payload = json_encode(array("listaRetiro" => Retiro::CrearArrayRetiro($arrayAux)));

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

    $arrayRetiros = Retiro::obtenerTodos();
    $arrayAux = [];
    foreach ($arrayRetiros as $retiro) 
    {
      $CuentaRetiro = Cuenta::obtenerCuenta($retiro->nroDeCuenta);
      if($CuentaRetiro)
      {
        $terceraLetra = substr($CuentaRetiro->tipoDeCuenta, 2, 1);
        if($terceraLetra === $moneda)
        {
            $arrayAux[] = $retiro;
        }
      }
    }
    $payload = json_encode(array("listaRetiro" => Retiro::CrearArrayRetiro($arrayAux)));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

}