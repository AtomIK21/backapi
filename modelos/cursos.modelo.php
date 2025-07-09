<?php

require_once "conexion.php";

class ModeloCursos {

    /**
     * Devuelve el total de cursos en la tabla
     * @return int
     */
    static public function totalCursos() {
        $db   = Conexion::conectar();
        $sql  = "SELECT COUNT(*) AS total FROM cursos";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) $row['total'];
    }

    /**
     * Listado paginado de cursos con join al instructor
     */
    static public function index($tabla1, $tabla2, $cantidad, $desde) {
        if ($cantidad !== null) {
            $sql = "SELECT 
                        $tabla1.id,
                        $tabla1.titulo,
                        $tabla1.descripcion,
                        $tabla1.instructor,
                        $tabla1.imagen,
                        $tabla1.precio,
                        $tabla1.id_creador,
                        $tabla2.nombre,
                        $tabla2.apellido
                    FROM $tabla1
                    INNER JOIN $tabla2 ON $tabla1.id_creador = $tabla2.id
                    LIMIT :desde, :cantidad";
            $stmt = Conexion::conectar()->prepare($sql);
            $stmt->bindValue(':desde',    $desde,    PDO::PARAM_INT);
            $stmt->bindValue(':cantidad', $cantidad, PDO::PARAM_INT);
        } else {
            $sql = "SELECT 
                        $tabla1.id,
                        $tabla1.titulo,
                        $tabla1.descripcion,
                        $tabla1.instructor,
                        $tabla1.imagen,
                        $tabla1.precio,
                        $tabla1.id_creador,
                        $tabla2.nombre,
                        $tabla2.apellido
                    FROM $tabla1
                    INNER JOIN $tabla2 ON $tabla1.id_creador = $tabla2.id";
            $stmt = Conexion::conectar()->prepare($sql);
        }
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_CLASS);
        $stmt = null;
        return $result;
    }

    static public function create($tabla, $datos) {
        $stmt = Conexion::conectar()->prepare(
            "INSERT INTO $tabla
                (titulo, descripcion, instructor, imagen, precio, id_creador, created_at, updated_at)
             VALUES
                (:titulo, :descripcion, :instructor, :imagen, :precio, :id_creador, :created_at, :updated_at)"
        );
        $stmt->bindParam(":titulo",      $datos["titulo"],     PDO::PARAM_STR);
        $stmt->bindParam(":descripcion", $datos["descripcion"],PDO::PARAM_STR);
        $stmt->bindParam(":instructor",  $datos["instructor"], PDO::PARAM_STR);
        $stmt->bindParam(":imagen",      $datos["imagen"],     PDO::PARAM_STR);
        $stmt->bindParam(":precio",      $datos["precio"],     PDO::PARAM_STR);
        $stmt->bindParam(":id_creador",  $datos["id_creador"], PDO::PARAM_INT);
        $stmt->bindParam(":created_at",  $datos["created_at"], PDO::PARAM_STR);
        $stmt->bindParam(":updated_at",  $datos["updated_at"], PDO::PARAM_STR);

        if ($stmt->execute()) {
            return "ok";
        } else {
            // En caso de error, opcionalmente devuelve errorInfo()
            return print_r(Conexion::conectar()->errorInfo(), true);
        }
    }

    static public function show($tabla1, $tabla2, $id) {
        $sql = "SELECT 
                    $tabla1.id,
                    $tabla1.titulo,
                    $tabla1.descripcion,
                    $tabla1.instructor,
                    $tabla1.imagen,
                    $tabla1.precio,
                    $tabla1.id_creador,
                    $tabla2.nombre,
                    $tabla2.apellido
                FROM $tabla1
                INNER JOIN $tabla2 ON $tabla1.id_creador = $tabla2.id
                WHERE $tabla1.id = :id";
        $stmt = Conexion::conectar()->prepare($sql);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_CLASS);
        $stmt = null;
        return $result;
    }

    static public function update($tabla, $datos) {
        $sql = "UPDATE $tabla SET
                    titulo      = :titulo,
                    descripcion = :descripcion,
                    instructor  = :instructor,
                    imagen      = :imagen,
                    precio      = :precio,
                    updated_at  = :updated_at
                WHERE id = :id";
        $stmt = Conexion::conectar()->prepare($sql);
        $stmt->bindParam(":id",          $datos["id"],          PDO::PARAM_INT);
        $stmt->bindParam(":titulo",      $datos["titulo"],      PDO::PARAM_STR);
        $stmt->bindParam(":descripcion", $datos["descripcion"], PDO::PARAM_STR);
        $stmt->bindParam(":instructor",  $datos["instructor"],  PDO::PARAM_STR);
        $stmt->bindParam(":imagen",      $datos["imagen"],      PDO::PARAM_STR);
        $stmt->bindParam(":precio",      $datos["precio"],      PDO::PARAM_STR);
        $stmt->bindParam(":updated_at",  $datos["updated_at"],  PDO::PARAM_STR);

        if ($stmt->execute()) {
            return "ok";
        } else {
            return print_r(Conexion::conectar()->errorInfo(), true);
        }
    }

    static public function delete($tabla, $id) {
        $stmt = Conexion::conectar()->prepare("DELETE FROM $tabla WHERE id = :id");
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        if ($stmt->execute()) {
            return "ok";
        } else {
            return print_r(Conexion::conectar()->errorInfo(), true);
        }
    }
}
