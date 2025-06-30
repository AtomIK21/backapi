<?php
class Conexion {
    static public function conectar() {
        $link = new PDO("mysql:host=localhost;dbname=api-rest", "root", "");
        $link->exec("set names utf8"); // configuración de caracteres para PHP
        return $link;
    }
}
?>
