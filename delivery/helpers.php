<?php
if (!function_exists('ensure_delivery_tables')) {
    function ensure_delivery_tables($con){
        // Create base table if missing
        $base = "CREATE TABLE IF NOT EXISTS del_login (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(100) NOT NULL,
            password VARCHAR(255) DEFAULT NULL,
            full_name VARCHAR(100) DEFAULT NULL,
            email VARCHAR(150) DEFAULT NULL,
            phone VARCHAR(30) DEFAULT NULL,
            role VARCHAR(30) DEFAULT 'delivery',
            is_active TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        @mysqli_query($con, $base);

        // Ensure del_login columns for profile/role data
        $cols = [
            'full_name' => "ALTER TABLE del_login ADD COLUMN full_name VARCHAR(100) DEFAULT NULL",
            'email' => "ALTER TABLE del_login ADD COLUMN email VARCHAR(150) DEFAULT NULL",
            'phone' => "ALTER TABLE del_login ADD COLUMN phone VARCHAR(30) DEFAULT NULL",
            'role' => "ALTER TABLE del_login ADD COLUMN role VARCHAR(30) DEFAULT 'delivery'",
            'is_active' => "ALTER TABLE del_login ADD COLUMN is_active TINYINT(1) DEFAULT 1",
            'created_at' => "ALTER TABLE del_login ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP"
        ];
        foreach($cols as $col => $alter){
            $cq = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='del_login' AND COLUMN_NAME='{$col}'";
            $cr = mysqli_query($con, $cq);
            if(!$cr || mysqli_num_rows($cr)===0){
                @mysqli_query($con, $alter);
            }
        }

        // Ensure password column length for hashes
        $colInfoQ = "SELECT CHARACTER_MAXIMUM_LENGTH, DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='del_login' AND COLUMN_NAME='password' LIMIT 1";
        $colRes = mysqli_query($con, $colInfoQ);
        if($colRes && mysqli_num_rows($colRes)>0){
            $col = mysqli_fetch_assoc($colRes);
            $len = intval($col['CHARACTER_MAXIMUM_LENGTH'] ?? 0);
            $dt = strtolower($col['DATA_TYPE'] ?? '');
            if(($dt === 'varchar' && $len < 100) || ($dt === 'char' && $len < 100)){
                @mysqli_query($con, "ALTER TABLE del_login MODIFY password VARCHAR(255) NOT NULL");
            }
        } else {
            @mysqli_query($con, "ALTER TABLE del_login ADD COLUMN password VARCHAR(255) DEFAULT NULL");
        }

        // Delivery agent address table
        $addr = "CREATE TABLE IF NOT EXISTS delivery_addresses (
            id INT AUTO_INCREMENT PRIMARY KEY,
            agent_username VARCHAR(100) NOT NULL,
            address_line1 VARCHAR(150) NOT NULL,
            address_line2 VARCHAR(150) DEFAULT NULL,
            city VARCHAR(100) NOT NULL,
            state VARCHAR(100) NOT NULL,
            postal_code VARCHAR(20) NOT NULL,
            country VARCHAR(80) NOT NULL,
            is_default TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        @mysqli_query($con, $addr);

        // Delivery audit logs
        $audit = "CREATE TABLE IF NOT EXISTS delivery_audit_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            agent_username VARCHAR(100) NOT NULL,
            action VARCHAR(100) NOT NULL,
            details TEXT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        @mysqli_query($con, $audit);
    }
}

if (!function_exists('ensure_purchase_table')) {
    function ensure_purchase_table($con){
        $create = "CREATE TABLE IF NOT EXISTS `purchase` (
            `pid` INT AUTO_INCREMENT PRIMARY KEY,
            `pname` VARCHAR(255) NOT NULL,
            `user` VARCHAR(255) NOT NULL,
            `pprice` DECIMAL(10,2) NOT NULL,
            `qty` INT NOT NULL DEFAULT 1,
            `prod_id` INT DEFAULT NULL,
            `status` VARCHAR(50) DEFAULT 'pending',
            `delivery_status` VARCHAR(50) DEFAULT 'pending',
            `assigned_agent` VARCHAR(100) DEFAULT NULL,
            `pdate` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        @mysqli_query($con, $create);

        $col_check = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='purchase' AND COLUMN_NAME='assigned_agent'";
        $col_res = mysqli_query($con, $col_check);
        if(!$col_res || mysqli_num_rows($col_res)===0){
            @mysqli_query($con, "ALTER TABLE purchase ADD COLUMN assigned_agent VARCHAR(100) DEFAULT NULL");
        }
    }
}

if (!function_exists('ensure_service_requests_table')) {
    function ensure_service_requests_table($con){
        $create = "CREATE TABLE IF NOT EXISTS `service_requests` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `user` VARCHAR(255) NOT NULL,
            `item` VARCHAR(255),
            `service_type` VARCHAR(100),
            `details` TEXT,
            `status` VARCHAR(50) DEFAULT 'pending',
            `assigned_agent` VARCHAR(100) DEFAULT NULL,
            `assigned_at` TIMESTAMP NULL DEFAULT NULL,
            `agent_note` TEXT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        @mysqli_query($con, $create);

        $cols = [
            'assigned_agent' => "ALTER TABLE service_requests ADD COLUMN assigned_agent VARCHAR(100) DEFAULT NULL",
            'assigned_at' => "ALTER TABLE service_requests ADD COLUMN assigned_at TIMESTAMP NULL DEFAULT NULL",
            'agent_note' => "ALTER TABLE service_requests ADD COLUMN agent_note TEXT NULL",
            'updated_at' => "ALTER TABLE service_requests ADD COLUMN updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP"
        ];
        foreach($cols as $col => $alter){
            $cq = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='service_requests' AND COLUMN_NAME='{$col}'";
            $cr = mysqli_query($con, $cq);
            if(!$cr || mysqli_num_rows($cr)===0){
                @mysqli_query($con, $alter);
            }
        }
    }
}

if (!function_exists('log_delivery_action')) {
    function log_delivery_action($con, $username, $action, $details){
        $u = mysqli_real_escape_string($con, $username ?? '');
        $a = mysqli_real_escape_string($con, $action ?? '');
        $d = mysqli_real_escape_string($con, $details ?? '');
        $sql = "INSERT INTO delivery_audit_logs (agent_username, action, details) VALUES ('$u','$a','$d')";
        @mysqli_query($con, $sql);
    }
}
?>