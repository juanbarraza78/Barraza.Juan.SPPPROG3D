<?php
require_once './models/Ajuste.php';
require_once './models/Deposito.php';
require_once './models/Retiro.php';
require_once './models/Cuenta.php';

class AjusteController extends Ajuste
{
  public function CargarUno($request, $response, $args) // POST : importe motivo numeroDeDepositoORetiro
  {
    $parametros = $request->getParsedBody();

    $importe = $parametros['importe'];
    $motivo = $parametros['motivo'];
    $numeroDeDepositoORetiro = $parametros['numeroDeDepositoORetiro'];

    $ajuste = new Ajuste();
    $ajuste->importe = $importe;
    $ajuste->motivo = $motivo;
    $ajuste->numeroDeDepositoORetiro = $numeroDeDepositoORetiro;

    $existe = false;
    if($motivo == "Deposito")
    {
      $deposito = Deposito::obtenerDeposito($numeroDeDepositoORetiro);
      if($deposito)
      {
        $existe = true;
        $cuenta = Cuenta::obtenerCuenta($deposito->nroDeCuenta);
      }
    }
    else
    {
      $retiro = Retiro::obtenerRetiro($numeroDeDepositoORetiro);
      if($retiro)
      {
        $existe = true;
        $cuenta = Cuenta::obtenerCuenta($retiro->nroDeCuenta);
      }
    }
    if($existe && $cuenta)
    {
      $ajuste->crearAjuste();
      if($motivo == "Deposito")
      {
        $cuenta->saldo += $importe;
        $payload = json_encode(array("mensaje" => "Deposito ajustado con exito"));
      }
      else
      {
        $cuenta->saldo -= $importe;
        $payload = json_encode(array("mensaje" => "Retiro ajustado con exito"));
      }
      $cuenta->modificarMontoCuenta();
      
    }
    else
    {
      $payload = json_encode(array("mensaje" => "No existe el retiro o deposito"));
    }
    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }
  public function TraerUno($request, $response, $args) // GET :  numeroDeAjuste
  {
    $numeroDeAjuste = $args['numeroDeAjuste'];

    $ajuste = Ajuste::obtenerAjuste($numeroDeAjuste);
    $payload = json_encode($ajuste);

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }
  public function TraerTodos($request, $response, $args) // GET 
  {
    $lista = Ajuste::obtenerTodos();
    $payload = json_encode(array("listaAjustes" => $lista));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

}