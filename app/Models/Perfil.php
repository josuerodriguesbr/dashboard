<?php
namespace App\Models;

use Config\Database;

class Perfil
{
    /**
     * Verifica se dois perfis pertencem à mesma árvore hierárquica (mesmo anfitrião raiz)
     */
    public static function pertencemMesmaArvore($perfilOrigemId, $perfilDestinoId)
    {
        $anfitriaoOrigem = self::encontrarAnfitriaoRaiz($perfilOrigemId);
        $anfitriaoDestino = self::encontrarAnfitriaoRaiz($perfilDestinoId);

        return $anfitriaoOrigem === $anfitriaoDestino;
    }

    /**
     * Encontra o anfitrião raiz (assinante principal) de um perfil
     */
    public static function encontrarAnfitriaoRaiz($perfilId)
    {
        $pdo = Database::getConnection();
        
        try {
            $atual = $perfilId;
            $visitados = []; // Para evitar loops infinitos
            
            while (true) {
                // Adiciona o perfil atual à lista de visitados
                $visitados[] = $atual;
                
                // Busca o perfil atual
                $stmt = $pdo->prepare("
                    SELECT p.id, p.hashAnfitriao, pa.nivel as papel_nivel
                    FROM integra_perfis p
                    JOIN integra_papeis pa ON p.id_papel = pa.id
                    WHERE p.id = ?
                ");
                $stmt->execute([$atual]);
                $perfil = $stmt->fetch();
                
                if (!$perfil) {
                    // Perfil não encontrado, retornamos o último válido
                    break;
                }
                
                // Se o papel for Assinante ou não tiver hashAnfitriao, encontramos o anfitrião raiz
                if ($perfil['papel_nivel'] === 'Assinante' || empty($perfil['hashAnfitriao'])) {
                    return $perfil['id'];
                }
                
                // Busca o perfil pelo hashAnfitriao
                $stmt = $pdo->prepare("
                    SELECT id, hashAnfitriao, pa.nivel as papel_nivel
                    FROM integra_perfis p
                    JOIN integra_papeis pa ON p.id_papel = pa.id
                    WHERE p.hashConvite = ?
                ");
                $stmt->execute([$perfil['hashAnfitriao']]);
                $anfitriao = $stmt->fetch();
                
                if (!$anfitriao || in_array($anfitriao['id'], $visitados)) {
                    // Não encontrou anfitrião ou encontrou um loop, retornamos o perfil atual como raiz
                    return $perfil['id'];
                }
                
                $atual = $anfitriao['id'];
            }
        } catch (\Exception $e) {
            error_log("Perfil::encontrarAnfitriaoRaiz falhou: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Busca os perfis que pertencem à mesma árvore hierárquica de um perfil específico
     */
    public static function getPerfisDaArvore($perfilId)
    {
        $pdo = Database::getConnection();
        
        try {
            $anfitriaoRaiz = self::encontrarAnfitriaoRaiz($perfilId);
            
            if (!$anfitriaoRaiz) {
                return [];
            }
            
            // Busca o hashAnfitriao do anfitrião raiz
            $stmt = $pdo->prepare("SELECT hashAnfitriao FROM integra_perfis WHERE id = ?");
            $stmt->execute([$anfitriaoRaiz]);
            $anfitriaoRaizHash = $stmt->fetchColumn();
            
            // Monta a consulta recursiva para encontrar todos os perfis na árvore
            // Isso pode precisar ser feito com múltiplas consultas devido às limitações do MySQL
            $perfis = [];
            $paraVerificar = [$anfitriaoRaiz];
            $visitados = [];
            
            while (!empty($paraVerificar)) {
                $atual = array_shift($paraVerificar);
                
                if (in_array($atual, $visitados)) {
                    continue;
                }
                
                $visitados[] = $atual;
                $perfis[] = $atual;
                
                // Busca perfis que têm este como anfitrião
                $stmt = $pdo->prepare("
                    SELECT p.id
                    FROM integra_perfis p
                    WHERE p.hashAnfitriao = (
                        SELECT hashConvite 
                        FROM integra_perfis 
                        WHERE id = ?
                    )
                ");
                $stmt->execute([$atual]);
                $filhos = $stmt->fetchAll(\PDO::FETCH_COLUMN);
                
                foreach ($filhos as $filhoId) {
                    if (!in_array($filhoId, $visitados)) {
                        $paraVerificar[] = $filhoId;
                    }
                }
            }
            
            return $perfis;
        } catch (\Exception $e) {
            error_log("Perfil::getPerfisDaArvore falhou: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Verifica se um perfil pode acessar outro perfil
     */
    public static function podeAcessarPerfil($perfilSolicitanteId, $perfilAlvoId)
    {
        $perfisDaArvore = self::getPerfisDaArvore($perfilSolicitanteId);
        return in_array($perfilAlvoId, $perfisDaArvore);
    }

    /**
     * Busca perfis por papel e anfitrião
     */
    public static function getPerfisPorAnfitriaoEPapel($anfitriaoRaizId, $papeis = [])
    {
        $pdo = Database::getConnection();
        
        try {
            $anfitriaoHash = self::getHashConvite($anfitriaoRaizId);
            
            if (!$anfitriaoHash) {
                return [];
            }
            
            // Monta a cláusula WHERE para os papeis
            $papeisWhere = '';
            $params = [$anfitriaoHash];
            
            if (!empty($papeis)) {
                $papeisStr = implode(',', array_fill(0, count($papeis), '?'));
                $papeisWhere = "AND pa.nivel IN ($papeisStr)";
                $params = array_merge($params, $papeis);
            }
            
            $sql = "
                SELECT p.*, u.nome, u.email, pa.nivel as papel_nivel
                FROM integra_perfis p
                JOIN integra_usuarios u ON p.id_usuario = u.id
                JOIN integra_papeis pa ON p.id_papel = pa.id
                WHERE p.hashAnfitriao = ?
                $papeisWhere
                ORDER BY pa.nivel, u.nome
            ";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchAll();
        } catch (\Exception $e) {
            error_log("Perfil::getPerfisPorAnfitriaoEPapel falhou: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtém o hash de convite de um perfil
     */
    public static function getHashConvite($perfilId)
    {
        $pdo = Database::getConnection();
        
        try {
            $stmt = $pdo->prepare("SELECT hashConvite FROM integra_perfis WHERE id = ?");
            $stmt->execute([$perfilId]);
            return $stmt->fetchColumn();
        } catch (\Exception $e) {
            error_log("Perfil::getHashConvite falhou: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtém informações básicas de um perfil
     */
    public static function getPerfilPorId($perfilId)
    {
        $pdo = Database::getConnection();
        
        try {
            $stmt = $pdo->prepare("
                SELECT p.*, u.nome, u.email, pa.nivel as papel_nivel
                FROM integra_perfis p
                JOIN integra_usuarios u ON p.id_usuario = u.id
                JOIN integra_papeis pa ON p.id_papel = pa.id
                WHERE p.id = ?
            ");
            $stmt->execute([$perfilId]);
            return $stmt->fetch();
        } catch (\Exception $e) {
            error_log("Perfil::getPerfilPorId falhou: " . $e->getMessage());
            return false;
        }
    }
}