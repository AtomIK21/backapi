<?php
require_once "conexion.php";

class ModeloOrdenes{
    static public function indexByCliente($tablaOrdenes, $id_cliente) {
        $stmt = Conexion::conectar()->prepare("
            SELECT oc.id_orden, cc.id_cliente, cc.id_curso, oc.id_metodo_pago, oc.total, oc.fecha_orden
            FROM $tablaOrdenes oc
            INNER JOIN clientes_cursos cc ON oc.id_clientes_cursos = cc.id_clientes_cursos
            WHERE cc.id_cliente = :id_cliente
        ");
        $stmt->bindParam(":id_cliente", $id_cliente, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    static public function registrarVenta($tablaCursosClientes, $tablaOrdenes, $datos) {
        $conexion = Conexion::conectar();
        try {
            $conexion->beginTransaction();

            // Obtener precio del curso
            $stmtPrecio = $conexion->prepare("SELECT precio FROM cursos WHERE id = :id_curso");
            $stmtPrecio->bindParam(":id_curso", $datos["id_curso"], PDO::PARAM_INT);
            $stmtPrecio->execute();
            $curso = $stmtPrecio->fetch(PDO::FETCH_ASSOC);

            if (!$curso) {
                throw new Exception("Curso no encontrado");
            }

            $total = $curso["precio"];

            // Insertar en clientes_cursos
            $stmt1 = $conexion->prepare("INSERT INTO $tablaCursosClientes(id_cliente, id_curso, created_at, updated_at) 
                                        VALUES (:id_cliente, :id_curso, :created_at, :updated_at)");

            $stmt1->bindParam(":id_cliente", $datos["id_cliente"], PDO::PARAM_INT);
            $stmt1->bindParam(":id_curso", $datos["id_curso"], PDO::PARAM_INT);
            $stmt1->bindParam(":created_at", $datos["created_at"]);
            $stmt1->bindParam(":updated_at", $datos["updated_at"]);
            $stmt1->execute();

            $id_clientes_cursos = $conexion->lastInsertId();

            // Insertar en ordenes_clientes
            $stmt2 = $conexion->prepare("INSERT INTO $tablaOrdenes(id_clientes_cursos, id_metodo_pago, total, created_at, updated_at) 
                                        VALUES (:id_clientes_cursos, :id_metodo_pago, :total, :created_at, :updated_at)");

            $stmt2->bindParam(":id_clientes_cursos", $id_clientes_cursos, PDO::PARAM_INT);
            $stmt2->bindParam(":id_metodo_pago", $datos["id_metodo_pago"], PDO::PARAM_INT);
            $stmt2->bindParam(":total", $total);
            $stmt2->bindParam(":created_at", $datos["created_at"]);
            $stmt2->bindParam(":updated_at", $datos["updated_at"]);
            $stmt2->execute();

            $conexion->commit();
            return "ok";

        } catch (Exception $e) {
            $conexion->rollBack();
            return $e->getMessage();
        }
    }

    static public function showByCliente($tablaOrdenes, $id, $id_cliente) {
        $stmt = Conexion::conectar()->prepare("
            SELECT oc.id_orden, cc.id_cliente, cc.id_curso, oc.id_metodo_pago, oc.total, oc.fecha_orden
            FROM $tablaOrdenes oc
            INNER JOIN clientes_cursos cc ON oc.id_clientes_cursos = cc.id_clientes_cursos
            WHERE oc.id_orden = :id AND cc.id_cliente = :id_cliente
        ");
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->bindParam(":id_cliente", $id_cliente, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    static public function update($tablaOrdenes, $id, $datos) {
        $stmt = Conexion::conectar()->prepare("
            UPDATE $tablaOrdenes
            SET id_metodo_pago = :id_metodo_pago, updated_at = :updated_at
            WHERE id_orden = :id
        ");
        $stmt->bindParam(":id_metodo_pago", $datos["id_metodo_pago"], PDO::PARAM_INT);
        $stmt->bindParam(":updated_at", $datos["updated_at"]);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        return $stmt->execute() ? "ok" : "error";
    }

    static public function delete($tablaOrdenes, $id) {
        $stmt = Conexion::conectar()->prepare("DELETE FROM $tablaOrdenes WHERE id_orden = :id");
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        return $stmt->execute() ? "ok" : "error";
    }
}
?>