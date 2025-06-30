<?php 
class ControladorCursos{
    public function index($pagina) {
        $cantidad = 15;
        $desde = ($pagina - 1) * $cantidad;
        $cursos = ModeloCursos::index("cursos", "clientes", $cantidad, $desde);

        $json = [
            "status" => 200,
            "total_registros" => count($cursos),
            "detalle" => $cursos
        ];

        echo json_encode($json, true);
    }

    public function create($datos, $cliente) {
        $camposRequeridos = ["titulo", "descripcion", "instructor", "imagen", "precio"];
        
        foreach ($camposRequeridos as $campo) {
            if(!isset($datos[$campo])) {
                $json = [
                    "status" => 400,
                    "detalle" => "Campo '$campo' es requerido"
                ];
                echo json_encode($json, true);
                return;
            }
        }

        $datosCurso = [
            "titulo" => $datos["titulo"],
            "descripcion" => $datos["descripcion"],
            "instructor" => $datos["instructor"],
            "imagen" => $datos["imagen"],
            "precio" => $datos["precio"],
            "id_creador" => $cliente['id'],
            "created_at" => date('Y-m-d H:i:s'),
            "updated_at" => date('Y-m-d H:i:s')
        ];

        $create = ModeloCursos::create("cursos", $datosCurso);  

        if ($create == 'ok') {
            $json = [
                "status" => 201,
                "detalle" => "Curso creado exitosamente"
            ];
        } else {
            $json = [
                "status" => 500,
                "detalle" => "Error al guardar el curso"
            ];
        }
        echo json_encode($json, true);
    }

    public function show($id) {
        $curso = ModeloCursos::show("cursos", "clientes", $id);

        if (!empty($curso)) {
            $json = [
                "status" => 200,
                "detalle" => $curso
            ];
        } else {
            $json = [
                "status" => 200,
                "total_registros" => 0,
                "detalles" => "No hay ningún curso registrado"
            ];
        }
        echo json_encode($json, true);	
    }

    public function update($id, $datos, $cliente) {
        $camposRequeridos = ["titulo", "descripcion", "instructor", "imagen", "precio"];
        
        foreach ($camposRequeridos as $campo) {
            if (!isset($datos[$campo])) {
                $json = [
                    "status" => 400,
                    "detalle" => "El campo '$campo' es requerido"
                ];
                echo json_encode($json, true);
                return;
            }
        }

        $datosActualizar = [
            "id" => $id,
            "titulo" => $datos["titulo"],
            "descripcion" => $datos["descripcion"],
            "instructor" => $datos["instructor"],
            "imagen" => $datos["imagen"],
            "precio" => $datos["precio"],
            "updated_at" => date('Y-m-d H:i:s')
        ];

        $update = ModeloCursos::update("cursos", $datosActualizar);

        if ($update == "ok") {
            $json = [
                "status" => 200,
                "detalle" => "Curso actualizado exitosamente"
            ];
        } else {
            $json = [
                "status" => 500,
                "detalle" => "Error al actualizar el curso"
            ];
        }
        echo json_encode($json, true);
    }

    public function delete($id, $cliente) {
        $delete = ModeloCursos::delete("cursos", $id);

        if ($delete == "ok") {
            $json = [
                "status" => 200,
                "detalle" => "Curso eliminado exitosamente"
            ];
        } else {
            $json = [
                "status" => 500,
                "detalle" => "Error al eliminar el curso"
            ];
        }
        echo json_encode($json, true);
    }
}
?>