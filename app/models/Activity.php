<?php

require_once __DIR__ . '/../core/Model.php';

class Activity extends Model
{
    public function recent($limit = 5)
    {
        $sql = "SELECT a.*, l.name AS lead_name, l.lead_code, u.name AS user_name 
                FROM lead_activities a
                JOIN leads l ON a.lead_id = l.id
                LEFT JOIN users u ON a.user_id = u.id
                ORDER BY a.id DESC LIMIT " . (int)$limit;
        return $this->db->query($sql)->fetchAll();
    }

    public function log($leadId, $userId, $action, $details = null)
    {
        $stmt = $this->db->prepare("INSERT INTO lead_activities (lead_id, user_id, action, details) VALUES (?, ?, ?, ?)");
        return $stmt->execute([(int)$leadId, (int)$userId, $action, $details]);
    }
}

