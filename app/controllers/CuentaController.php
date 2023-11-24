<?php
require_once './models/Cuenta.php';
require_once './models/Retiro.php';
require_once './models/Deposito.php';

class CuentaController extends Cuenta
{
  public function CargarUno($request, $response, $args) // POST : nombre apellido tipoDocumento numeroDocumento email tipoDeCuenta saldo archivo
  {
    $parametros = $request->getParsedBody();

    $nombre = $parametros['nombre'];
    $apellido = $parametros['apellido'];
    $tipoDocumento = $parametros['tipoDocumento'];
    $numeroDocumento = $parametros['numeroDocumento'];
    $email = $parametros['email'];
    $tipoDeCuenta = $parametros['tipoDeCuenta'];
    $saldo = $parametros['saldo'];

    $cuenta = new Cuenta();
    $cuenta->nombre = $nombre;
    $cuenta->apellido = $apellido;
    $cuenta->tipoDocumento = $tipoDocumento;
    $cuenta->numeroDocumento = $numeroDocumento;
    $cuenta->email = $email;
    $cuenta->tipoDeCuenta = $tipoDeCuenta;
    if (isset($saldo)) {
      $cuenta->saldo = $saldo;
    } else {
      $cuenta->saldo = 0;
    }
    $cuenta->estaActivo = true;

    $arrayCuentas = Cuenta::obtenerTodos();
    $existe = false;
    foreach ($arrayCuentas as $cuentaAux) {
      if(Cuenta::IsMismoCliente($cuentaAux,$cuenta))
      {
        $existe = true;
      }
    }

    if (!$existe) {
      $id = $cuenta->crearCuenta();
      $carpeta_archivos = 'img/cuentas/2023/';
      $nombre_archivo = $id . $cuenta->tipoDeCuenta;
      $ruta_destino = $carpeta_archivos . $nombre_archivo . ".jpg";
      if (move_uploaded_file($_FILES['archivo']['tmp_name'], $ruta_destino)) {
        
        $payload = json_encode(array("mensaje" => "Cuenta creada con exito"));
      } else {
        $payload = json_encode(array("mensaje" => "Error Foto"));
      }
    }
    else
    {
      $payload = json_encode(array("mensaje" => "La cuenta ya existe"));
    }


    

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }
  public function TraerUno($request, $response, $args) // GET :  nroDeCuenta
  {
    $nroDeCuenta = $args['nroDeCuenta'];

    $cuenta = Cuenta::obtenerCuenta($nroDeCuenta);
    $payload = json_encode($cuenta);

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }
  public function TraerTodos($request, $response, $args) // GET 
  {
    $lista = Cuenta::obtenerTodos();
    $payload = json_encode(array("listaCuenta" => $lista));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }
  public function TraerUnoTipoYCuenta($request, $response, $args) // POST :  nroDeCuenta tipoDeCuenta
  {
    $parametros = $request->getParsedBody();

    $nroDeCuenta = $parametros['nroDeCuenta'];
    $tipoDeCuenta = $parametros['tipoDeCuenta'];

    $cuenta = Cuenta::obtenerCuentaTipoYNumero($nroDeCuenta,$tipoDeCuenta);
    if($cuenta)
    {
      $payload = json_encode($cuenta);
      $payload = json_encode(array("TipoDeCuenta" => $cuenta->tipoDeCuenta,"Saldo" => $cuenta->saldo));
    }
    else if(Cuenta::obtenerCuenta($nroDeCuenta))
    {
      $payload = json_encode(array("mensaje" => "Tipo de cuenta incorrecto"));
    }
    else
    {
      $payload = json_encode(array("mensaje" => "No existe cuenta"));
    }

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }
  public function ModificarUno($request, $response, $args) // POST : nombre apellido tipoDocumento numeroDocumento email tipoDeCuenta nroDeCuenta
  {
    $parametros = $request->getParsedBody();

    $nombre = $parametros['nombre'];
    $apellido = $parametros['apellido'];
    $tipoDocumento = $parametros['tipoDocumento'];
    $numeroDocumento = $parametros['numeroDocumento'];
    $email = $parametros['email'];
    $tipoDeCuenta = $parametros['tipoDeCuenta'];
    $nroDeCuenta = $parametros['nroDeCuenta'];

    $cuenta = new Cuenta();

    $cuenta->nombre = $nombre;
    $cuenta->apellido = $apellido;
    $cuenta->tipoDocumento = $tipoDocumento;
    $cuenta->numeroDocumento = $numeroDocumento;
    $cuenta->email = $email;
    $cuenta->tipoDeCuenta = $tipoDeCuenta;
    $cuenta->nroDeCuenta = $nroDeCuenta;

    

    if (Cuenta::obtenerCuentaTipoYNumero($nroDeCuenta,$tipoDeCuenta)) {
      $payload = json_encode(array("mensaje" => "Se Modifico correctamente"));
      $cuenta->ModificarCuenta();
    }
    else
    {
      $payload = json_encode(array("mensaje" => "No existe"));
    }

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }
  public function EliminarUno($request, $response, $args) // DELETE : tipoDeCuenta nroDeCuenta
  {
    $parametros = $request->getQueryParams();
    $tipoDeCuenta = $parametros['tipoDeCuenta'];
    $nroDeCuenta = $parametros['nroDeCuenta'];

    $cuenta = new Cuenta();

    if (Cuenta::obtenerCuentaTipoYNumero($nroDeCuenta,$tipoDeCuenta)) {
      $cuenta->estaActivo = false;
      $cuenta->tipoDeCuenta = $tipoDeCuenta;
      $cuenta->nroDeCuenta = $nroDeCuenta;
      $cuenta->EliminarCuenta();

      $nombre_archivo = $nroDeCuenta . $tipoDeCuenta;
      $ruta_origen = 'img/cuentas/2023/' . $nombre_archivo . ".jpg";
      $ruta_destino = 'img/BackupCuentas/2023/'.$nombre_archivo.".jpg";
      if (rename($ruta_origen , $ruta_destino)) {
        $payload = json_encode(array("mensaje" => "Cuenta Eliminada"));
      }
      else
      {
        $payload = json_encode(array("mensaje" => "Error foto"));
      }
      
    }
    else
    {
      $payload = json_encode(array("mensaje" => "No existe esa Cuenta"));
    }

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }
  public function ConsultarMovimientoF($request, $response, $args) // GET tipoDeCuenta nroDeCuenta
  {
    $parametrosParam = $request->getQueryParams();

    $tipoDeCuenta = $parametrosParam['tipoDeCuenta'];
    $nroDeCuenta = $parametrosParam['nroDeCuenta'];

    $arrayDepositos = Deposito::obtenerTodos();
    $arrayRetiros = Retiro::obtenerTodos();
    $arrayAux = [];
    foreach ($arrayDepositos as $deposito) 
    {
      $CuentaDeposito = Cuenta::obtenerCuenta($deposito->nroDeCuenta);
      if($CuentaDeposito && $deposito->nroDeCuenta == $nroDeCuenta && $CuentaDeposito->tipoDeCuenta == $tipoDeCuenta)
      {
          $arrayAux[] = $deposito;
      }
    }
    foreach ($arrayRetiros as $retiro) 
    {
      $CuentaRetiro = Cuenta::obtenerCuenta($retiro->nroDeCuenta);
      if($CuentaRetiro && $retiro->nroDeCuenta == $nroDeCuenta && $CuentaRetiro->tipoDeCuenta == $tipoDeCuenta)
      {
          $arrayAux[] = $retiro;
      }
    }

    $payload = json_encode(array("lista" => $arrayAux));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

}