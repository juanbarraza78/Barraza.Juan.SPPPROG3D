<?php
require_once './models/Cuenta.php';

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
    $cuenta->estaActivo = false;

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
      $carpeta_archivos = 'img/2023/';
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

  // public function TraerTodosEstado($request, $response, $args) // GET 
  // {
  //   $parametrosParam = $request->getQueryParams();
  //   $estado = $parametrosParam['estado'];

  //   $lista = Mesa::obtenerTodosEstado($estado);
  //   $payload = json_encode(array("listaMesa" => $lista));

  //   $response->getBody()->write($payload);
  //   return $response
  //     ->withHeader('Content-Type', 'application/json');
  // }
  // public function ModificarUno($request, $response, $args) // POST 
  // {
  //   $parametros = $request->getParsedBody();

  //   $idMesa = $parametros['idMesa'];
  //   $estado = $parametros['estado'];

  //   Mesa::modificarMesa($idMesa, $estado);

  //   $payload = json_encode(array("mensaje" => "Mesa modificada con exito"));

  //   $response->getBody()->write($payload);
  //   return $response
  //     ->withHeader('Content-Type', 'application/json');
  // }
  // public function BorrarUno($request, $response, $args) // POST 
  // {

  //   $idMesa = $args['idMesa'];
  //   Mesa::borrarMesa($idMesa);

  //   $payload = json_encode(array("mensaje" => "Mesa borrado con exito"));

  //   $response->getBody()->write($payload);
  //   return $response
  //     ->withHeader('Content-Type', 'application/json');
  // }
  // public function GuardarCSV($request, $response, $args) // GET
  // {

  //   if($archivo = fopen("csv/mesas.csv", "w"))
  //   {
  //     $lista = Mesa::obtenerTodos();
  //     foreach( $lista as $mesa )
  //     {
  //         fputcsv($archivo, [$mesa->idMesa, $mesa->estado]);
  //     }
  //     fclose($archivo);
  //     $payload =  json_encode(array("mensaje" => "La lista de mesas se guardo correctamente"));
  //   }
  //   else
  //   {
  //     $payload =  json_encode(array("mensaje" => "No se pudo abrir el archivo de mesas"));
  //   }

  //   $response->getBody()->write($payload);
  //   return $response
  //     ->withHeader('Content-Type', 'application/json');
  // }
  // public function CargarCSV($request, $response, $args) // GET
  // {
  //   if(($archivo = fopen("csv/mesas.csv", "r")) !== false)
  //   {
  //     Mesa::borrarMesas();
  //     while (($filaMesa = fgetcsv($archivo, 0, ',')) !== false)
  //     {
  //       $nuevaMesa = new Mesa();
  //       $nuevaMesa->idMesa = $filaMesa[0];
  //       $nuevaMesa->estado = $filaMesa[1];
  //       $nuevaMesa->crearMesaCSV();
  //     }
  //     fclose($archivo);
  //     $payload =  json_encode(array("mensaje" => "Las mesas se cargaron correctamente"));
  //   }
  //   else
  //   {
  //     $payload =  json_encode(array("mensaje" => "No se pudo leer el archivo de mesas"));
  //   }

  //   $response->getBody()->write($payload);
  //   return $response
  //     ->withHeader('Content-Type', 'application/json');
  // }

}