<?php
/**
 * Fix for undefined array keys "wins" and "conversion_rate" in dashboard
 * 
 * This update modifies the Lead model's getEmployeePerformanceStats method
 * to return the correct keys expected by the dashboard.
 */

// Update Lead.php - getEmployeePerformanceStats method
public function getEmployeePerformanceStats() {
    $sql = "SELECT 
                u.id, 
                u.name,
                COUNT(DISTINCT l.id) as total_leads,
                COUNT(DISTINCT cl.id) as total_calls,
                SUM(CASE WHEN l.status = 'win' THEN 1 ELSE 0 END) as wins,
                SUM(CASE WHEN l.status = 'interested' THEN 1 ELSE 0 END) as total_interested,
                ROUND(CASE WHEN COUNT(DISTINCT l.id) > 0 THEN 
                    (SUM(CASE WHEN l.status = 'win' THEN 1 ELSE 0 END) / COUNT(DISTINCT l.id)) * 100 
                ELSE 0 END, 2) as conversion_rate
            FROM users u
            LEFT JOIN leads l ON u.id = l.assigned_to AND l.deleted_at IS NULL
            LEFT JOIN call_logs cl ON l.id = cl.lead_id AND cl.deleted_at IS NULL
            WHERE u.role = 'employee' AND u.deleted_at IS NULL
            GROUP BY u.id, u.name
            ORDER BY wins DESC, total_interested DESC
            LIMIT 5";
    
    return $this->db->getRows($sql);
}

