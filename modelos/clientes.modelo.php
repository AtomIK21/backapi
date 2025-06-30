<?php
require_once "conexion.php";

class ModeloClientes {
    static public function index($tabla, $cantidad = null, $desde = 0) {
        if ($cantidad != null) {
            $stmt = Conexion::conectar()->prepare("SELECT id, nombre, apellido, email, id_cliente, created_at, updated_at FROM $tabla LIMIT :desde, :cantidad");
            $stmt->bindParam(":desde", $desde, PDO::PARAM_INT);
            $stmt->bindParam(":cantidad", $cantidad, PDO::PARAM_INT);
        } else {
            $stmt = Conexion::conectar()->prepare("SELECT id, nombre, apellido, email, id_cliente, created_at, updated_at FROM $tabla");
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_CLASS);
    }

    static public function buscarPorEmail($tabla, $email) {
        $stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE email = :email");
        $stmt->bindParam(":email", $email, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    static public function buscarPorIdCliente($tabla, $id_cliente) {
        try {
            $stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE id_cliente = :id_cliente");
            $stmt->bindParam(":id_cliente", $id_cliente, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en buscarPorIdCliente: " . $e->getMessage());
            return false;
        }
    }

    static public function obtenerCursosDelCliente($cliente_id) {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT cursos.*
                FROM clientes_cursos
                INNER JOIN cursos ON cursos.id = clientes_cursos.id_curso
                WHERE clientes_cursos.id_cliente = :cliente_id
            ");
            $stmt->bindParam(":cliente_id", $cliente_id, PDO::PARAM_INT);
            $stmt->execute();
            
            $cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $total = count($cursos);
            
            return [
                "status" => 200,
                "total_cursos" => $total,
                "cursos" => $cursos
            ];
        } catch (PDOException $e) {
            error_log("Error en obtenerCursosDelCliente: " . $e->getMessage());
            return false;
        }
    }
}
?>