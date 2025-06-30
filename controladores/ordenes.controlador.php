<?php
class ControladorOrdenes{
    public function index($id_cliente) {
        $ordenes = ModeloOrdenes::indexByCliente("ordenes_clientes", $id_cliente);
        echo json_encode([
            "status" => 200,
            "ordenes" => $ordenes
        ]);
    }

    public function create($datos, $cliente) {
        // Verificar que id_curso sea un array y tenga elementos
        if (!isset($datos['id_curso']) || !is_array($datos['id_curso']) || count($datos['id_curso']) === 0 || !isset($datos['id_metodo_pago'])) {
            echo json_encode([
                "status" => 400,
                "detalle" => "Datos incompletos o inválidos"
            ]);
            return;
        }

        $respuestas = [];
        $errores = [];
        
        foreach ($datos['id_curso'] as $id_curso) {
            $datosVenta = [
                "id_cliente" => $cliente['id'],
                "id_curso" => $id_curso,
                "id_metodo_pago" => $datos["id_metodo_pago"],
                "created_at" => date("Y-m-d H:i:s"),
                "updated_at" => date("Y-m-d H:i:s")
            ];

            $respuesta = ModeloOrdenes::registrarVenta("clientes_cursos", "ordenes_clientes", $datosVenta);
            
            if ($respuesta === "ok") {
                $respuestas[] = "Curso $id_curso registrado";
            } else {
                $errores[] = "Error con curso $id_curso: " . $respuesta;
            }
        }

        if (count($errores)) {
            echo json_encode([
                "status" => 207, // Multi-status
                "detalle" => "Algunos cursos no se registraron",
                "exitosos" => $respuestas,
                "errores" => $errores
            ]);
        } else {
            echo json_encode([
                "status" => 201,
                "detalle" => "Todos los cursos registrados exitosamente",
                "detalle_cursos" => $respuestas
            ]);
        }
    }

    public function show($id, $id_cliente) {
        $orden = ModeloOrdenes::showByCliente("ordenes_clientes", $id, $id_cliente);
        if ($orden) {
            echo json_encode([
                "status" => 200,
                "orden" => $orden
            ]);
        } else {
            echo json_encode([
                "status" => 404,
                "detalle" => "Orden no encontrada"
            ]);
        }
    }

    public function update($id, $datos, $id_cliente) {
        if (!isset($datos["id_metodo_pago"])) {
            echo json_encode([
                "status" => 400,
                "detalle" => "Campo 'id_metodo_pago' es requerido"
            ]);
            return;
        }

        $respuesta = ModeloOrdenes::update("ordenes_clientes", $id, [
            "id_metodo_pago" => $datos["id_metodo_pago"],
            "updated_at" => date("Y-m-d H:i:s")
        ]);

        if ($respuesta === "ok") {
            echo json_encode([
                "status" => 200,
                "detalle" => "Orden actualizada correctamente"
            ]);
        } else {
            echo json_encode([
                "status" => 500,
                "detalle" => "Error al actualizar"
            ]);
        }
    }

    public function delete($id, $id_cliente) {
        $respuesta = ModeloOrdenes::delete("ordenes_clientes", $id);

        if ($respuesta === "ok") {
            echo json_encode([
                "status" => 200,
                "detalle" => "Orden eliminada correctamente"
            ]);
        } else {
            echo json_encode([
                "status" => 500,
                "detalle" => "Error al eliminar"
            ]);
        }
    }
}
?>