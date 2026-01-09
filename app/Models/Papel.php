<?php
namespace App\Models;

use Config\Database;

class Papel
{
    public static function listar()
    {
        $pdo = Database::getConnection();
        try {
            $stmt = $pdo->query("SELECT * FROM papeis ORDER BY id ASC");
            return $stmt->fetchAll();
        } catch (\Exception $e) {
            error_log("Papel::listar falhou: " . $e->getMessage());
            return [];
        }
    }

    public static function buscarPorId($id)
    {
        $pdo = Database::getConnection();
        try {
            $stmt = $pdo->prepare("SELECT * FROM papeis WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (\Exception $e) {
            error_log("Papel::buscarPorId falhou: " . $e->getMessage());
            return false;
        }
    }

    public static function buscarPorNivel($nivel)
    {
        $pdo = Database::getConnection();
        try {
            $stmt = $pdo->prepare("SELECT * FROM papeis WHERE nivel = ?");
            $stmt->execute([$nivel]);
            return $stmt->fetch();
        } catch (\Exception $e) {
            error_log("Papel::buscarPorNivel falhou: " . $e->getMessage());
            return false;
        }
    }
}
