<?php
require_once './models/Mesa.php';
require_once './models/Pedido.php';

class MesaController extends Mesa
{
  public function CargarUno($request, $response, $args) // POST : estado
  {
    $parametros = $request->getParsedBody();

    $estado = $parametros['estado'];

    $mesa = new Mesa();
    $mesa->estado = $estado;

    $mesa->crearMesa();

    $payload = json_encode(array("mensaje" => "Mesa creado con exito"));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function TraerUno($request, $response, $args) // GET :  idMesa
  {
    $id = $args['idMesa'];

    $mesa = Mesa::obtenerMesa($id);
    $payload = json_encode($mesa);

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function TraerTodos($request, $response, $args) // GET 
  {
    $lista = Ajuste::obtenerTodos();
    $payload = json_encode(array("listaMesa" => $lista));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function TraerTodosEstado($request, $response, $args) // GET estado
  {
    $parametrosParam = $request->getQueryParams();
    $estado = $parametrosParam['estado'];

    $lista = Mesa::obtenerTodosEstado($estado);
    $payload = json_encode(array("listaMesa" => $lista));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function ModificarUno($request, $response, $args) // POST  idMesa estado
  {
    $parametros = $request->getParsedBody();

    $idMesa = $parametros['idMesa'];
    $estado = $parametros['estado'];

    Mesa::modificarMesa($idMesa, $estado);

    $payload = json_encode(array("mensaje" => "Mesa modificada con exito"));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function BorrarUno($request, $response, $args) // DELETE idMesa
  {

    $idMesa = $args['idMesa'];
    Mesa::borrarMesa($idMesa);

    $payload = json_encode(array("mensaje" => "Mesa borrado con exito"));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function MesaMasUsada($request, $response, $args) // GET
  {

    $listaMesas = Mesa::obtenerTodos();
    $primeraIteracion = true;
    $mesaMasUsada = null;

    foreach ($listaMesas as $mesa) 
    {
      $listaPedidos = Pedido::obtenerTodosSegunIdMesa($mesa->idMesa);
      $cantidad = count($listaPedidos);
      if($primeraIteracion || $maximoPedidos < $cantidad)
      {
        $primeraIteracion = false;
        $maximoPedidos = $cantidad;
        $mesaMasUsada = $mesa;
      }
    }
    

    $payload = json_encode(array("Mesa mas usada" => $mesaMasUsada, "Cantida de usos" => $maximoPedidos));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function MesaMejoresComentarios($request, $response, $args) // GET
  {

    $listaMesas = Mesa::obtenerTodos();
    $primeraIteracion = true;
    $mejorComentario = null;
    $mejorPuntuacion = 0;
    $mejorMesa = null;
     
    foreach ($listaMesas as $mesa) 
    {
      $listaPedidos = Pedido::obtenerTodosSegunIdMesa($mesa->idMesa);
      foreach ($listaPedidos as $pedido) 
      {
        if($pedido->estado == "finalizado" && ($primeraIteracion || $pedido->puntuacion > $mejorPuntuacion))
        {
          $primeraIteracion = false;
          $mejorComentario = $pedido->comentarios;
          $mejorPuntuacion = $pedido->puntuacion;
          $mejorMesa = $pedido->idMesa;
        }
      }
    }

    $payload = json_encode(array("Mejor comentario" => $mejorComentario, "Mesa:" => $mejorMesa));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function GuardarCSV($request, $response, $args) // GET
  {
          
    if($archivo = fopen("csv/mesas.csv", "w"))
    {
      $lista = Mesa::obtenerTodos();
      foreach( $lista as $mesa )
      {
          fputcsv($archivo, [$mesa->idMesa, $mesa->estado]);
      }
      fclose($archivo);
      $payload =  json_encode(array("mensaje" => "La lista de mesas se guardo correctamente"));
    }
    else
    {
      $payload =  json_encode(array("mensaje" => "No se pudo abrir el archivo de mesas"));
    }

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function CargarCSV($request, $response, $args) // GET
  {
    if(($archivo = fopen("csv/mesas.csv", "r")) !== false)
    {
      Mesa::borrarMesas();
      while (($filaMesa = fgetcsv($archivo, 0, ',')) !== false)
      {
        $nuevaMesa = new Mesa();
        $nuevaMesa->idMesa = $filaMesa[0];
        $nuevaMesa->estado = $filaMesa[1];
        $nuevaMesa->crearMesaCSV();
      }
      fclose($archivo);
      $payload =  json_encode(array("mensaje" => "Las mesas se cargaron correctamente"));
    }
    else
    {
      $payload =  json_encode(array("mensaje" => "No se pudo leer el archivo de mesas"));
    }
              
    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

}