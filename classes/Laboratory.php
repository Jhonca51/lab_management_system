<?php
/**
 * Clase Laboratory
 * Manejo de laboratorios y documentos
 */

class Laboratory {
    private $db;
    
    public function __construct($database = null) {
        $this->db = $database ?: Database::getInstance();
    }
    
    /**
     * Obtener estadísticas generales
     */
    public function getStats() {
        try {
            $stats = [
                'total' => 0,
                'completados' => 0,
                'pendientes' => 0,
                'fallidos' => 0,
                'laboratories' => []
            ];
            
            // Total de documentos
            $stats['total'] = $this->db->count("SELECT COUNT(*) FROM buscador_lab");
            
            // Por estado
            $statusCounts = $this->db->fetchAll(
                "SELECT estado_descarga, COUNT(*) as count FROM buscador_lab GROUP BY estado_descarga"
            );
            
            foreach ($statusCounts as $row) {
                $status = strtolower($row['estado_descarga'] ?? 'pendientes');
                $stats[$status] = $row['count'];
            }
            
            // Por laboratorio
            $stats['laboratories'] = $this->db->fetchAll(
                "SELECT Nombre_Lab, COUNT(*) as count FROM buscador_lab 
                 GROUP BY Nombre_Lab ORDER BY count DESC"
            );
            
            return $stats;
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
    
    /**
     * Buscar documentos con filtros
     */
    public function searchDocuments($filters = []) {
        try {
            $where = [];
            $params = [];
            
            // Filtro por laboratorio
            if (!empty($filters['lab'])) {
                $where[] = "Nombre_Lab = ?";
                $params[] = $filters['lab'];
            }
            
            // Filtro por número de documento
            if (!empty($filters['doc'])) {
                $where[] = "Numero_ID LIKE ?";
                $params[] = '%' . $filters['doc'] . '%';
            }
            
            // Filtro por estado
            if (!empty($filters['status'])) {
                if ($filters['status'] === 'SIN_ESTADO') {
                    $where[] = "estado_descarga IS NULL";
                } else {
                    $where[] = "estado_descarga = ?";
                    $params[] = $filters['status'];
                }
            }
            
            // Filtro por fecha
            if (!empty($filters['fecha_inicio'])) {
                $where[] = "Fecha_ID >= ?";
                $params[] = $filters['fecha_inicio'];
            }
            
            if (!empty($filters['fecha_fin'])) {
                $where[] = "Fecha_ID <= ?";
                $params[] = $filters['fecha_fin'];
            }
            
            $whereClause = empty($where) ? '' : 'WHERE ' . implode(' AND ', $where);
            $limit = isset($filters['limit']) ? (int)$filters['limit'] : 50;
            
            $sql = "SELECT * FROM buscador_lab $whereClause 
                    ORDER BY fecha_actualizacion DESC LIMIT $limit";
            
            return $this->db->fetchAll($sql, $params);
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
    
    /**
     * Obtener documento por ID
     */
    public function getDocumentById($id) {
        $sql = "SELECT * FROM buscador_lab WHERE id = ?";
        return $this->db->fetch($sql, [$id]);
    }
    
    /**
     * Obtener todos los laboratorios
     */
    public function getAllLaboratories() {
        $sql = "SELECT DISTINCT Nombre_Lab FROM buscador_lab ORDER BY Nombre_Lab";
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Actualizar estado de documento
     */
    public function updateDocumentStatus($id, $status) {
        try {
            $sql = "UPDATE buscador_lab SET estado_descarga = ?, fecha_actualizacion = NOW() WHERE id = ?";
            $this->db->query($sql, [$status, $id]);
            
            return [
                'success' => true,
                'message' => 'Estado actualizado correctamente'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Obtener estadísticas por laboratorio
     */
    public function getLabStats($labName) {
        try {
            $stats = [];
            
            // Total documentos del laboratorio
            $stats['total'] = $this->db->count(
                "SELECT COUNT(*) FROM buscador_lab WHERE Nombre_Lab = ?", 
                [$labName]
            );
            
            // Por estado
            $statusCounts = $this->db->fetchAll(
                "SELECT estado_descarga, COUNT(*) as count 
                 FROM buscador_lab WHERE Nombre_Lab = ? 
                 GROUP BY estado_descarga",
                [$labName]
            );
            
            foreach ($statusCounts as $row) {
                $status = strtolower($row['estado_descarga'] ?? 'pendientes');
                $stats[$status] = $row['count'];
            }
            
            return $stats;
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
    
    /**
     * Obtener documentos recientes
     */
    public function getRecentDocuments($limit = 10) {
        $sql = "SELECT * FROM buscador_lab 
                ORDER BY fecha_actualizacion DESC 
                LIMIT ?";
        return $this->db->fetchAll($sql, [$limit]);
    }
}
?>