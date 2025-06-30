<?php 
class ControladorClientes {
    public function index() {
        $clientes = ModeloClientes::index("clientes");
        $json = [
            "status" => 200,
            "total_registros" => count($clientes),
            "detalle" => $clientes
        ];
        echo json_encode($json, true);
    }

    public function login($email, $llave_secreta) {
        $cliente = ModeloClientes::buscarPorEmail("clientes", $email);

        if ($cliente && password_verify($llave_secreta, $cliente['llave_secreta'])) {
            // Iniciar sesión
            session_start();
            $_SESSION['cliente_id'] = $cliente['id'];
            $_SESSION['cliente_email'] = $cliente['email'];
            
            echo json_encode([
                "status" => 200,
                "detalle" => "Login exitoso",
                "token" => bin2hex(random_bytes(16)), 
                "cliente" => [
                    "id" => $cliente['id'],
                    "nombre" => $cliente['nombre'],
                    "email" => $cliente['email'],
                    "id_cliente" => $cliente['id_cliente']
                ]
            ]);
        } else {
            echo json_encode([
                "status" => 401,
                "detalle" => "Credenciales inválidas"
            ]);
        }
    }


    public function create($datos) {
        if (!isset($datos["nombre"]) || 
            !isset($datos["apellido"]) || 
            !isset($datos["email"]) || 
            !isset($datos["llave_secreta"])) {
            echo json_encode([
                "status" => 400,
                "detalle" => "Todos los campos son requeridos"
            ]);
            return;
        }

        $clienteExistente = ModeloClientes::buscarPorEmail("clientes", $datos["email"]);
        if ($clienteExistente) {
            echo json_encode([
                "status" => 400,
                "detalle" => "El email ya está registrado"
            ]);
            return;
        }

        $hashedPassword = password_hash($datos["llave_secreta"], PASSWORD_DEFAULT);
        $id_cliente = md5(uniqid(rand(), true));

        $stmt = Conexion::conectar()->prepare("INSERT INTO clientes (nombre, apellido, email, llave_secreta, id_cliente, created_at, updated_at) 
                                              VALUES (:nombre, :apellido, :email, :llave_secreta, :id_cliente, NOW(), NOW())");

        $stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
        $stmt->bindParam(":apellido", $datos["apellido"], PDO::PARAM_STR);
        $stmt->bindParam(":email", $datos["email"], PDO::PARAM_STR);
        $stmt->bindParam(":llave_secreta", $hashedPassword, PDO::PARAM_STR);
        $stmt->bindParam(":id_cliente", $id_cliente, PDO::PARAM_STR);

        if ($stmt->execute()) {
            echo json_encode([
                "status" => 201,
                "detalle" => "Cliente registrado exitosamente",
                "cliente" => [
                    "id" => Conexion::conectar()->lastInsertId(),
                    "nombre" => $datos["nombre"],
                    "apellido" => $datos["apellido"],
                    "email" => $datos["email"],
                    "id_cliente" => $id_cliente
                ]
            ]);
        } else {
            echo json_encode([
                "status" => 500,
                "detalle" => "Error al registrar el cliente"
            ]);
        }

    }

    public function cursosDelCliente($cliente_id) {
        $resultado = ModeloClientes::obtenerCursosDelCliente($cliente_id);
        
        if ($resultado === false) {
            http_response_code(500);
            echo json_encode([
                "status" => 500,
                "message" => "Error en el servidor"
            ]);
            return;
        }
                
        header('Content-Type: application/json');
        echo json_encode($resultado);
        exit;
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
            
            // Devuelve la estructura esperada por el frontend
            return [
                "status" => 200,
                "total_cursos" => $total,
                "cursos" => $cursos
            ];
            
        } catch (PDOException $e) {
            error_log("Error en obtenerCursosDelCliente: " . $e->getMessage());
            return [
                "status" => 500,
                "message" => "Error en el servidor"
            ];
        }
    }

    }

?>