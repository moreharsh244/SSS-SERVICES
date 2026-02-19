<?php
function admin_notifications_path() {
    return __DIR__ . '/admin_notifications.json';
}

function load_admin_notifications() {
    $path = admin_notifications_path();
    if (!file_exists($path)) {
        return [];
    }
    $fp = fopen($path, 'r');
    if (!$fp) {
        return [];
    }
    flock($fp, LOCK_SH);
    $raw = stream_get_contents($fp);
    flock($fp, LOCK_UN);
    fclose($fp);
    $items = json_decode($raw, true);
    return is_array($items) ? $items : [];
}

function save_admin_notifications(array $items) {
    $path = admin_notifications_path();
    $fp = fopen($path, 'c+');
    if (!$fp) {
        return false;
    }
    flock($fp, LOCK_EX);
    ftruncate($fp, 0);
    rewind($fp);
    fwrite($fp, json_encode($items, JSON_UNESCAPED_SLASHES));
    fflush($fp);
    flock($fp, LOCK_UN);
    fclose($fp);
    return true;
}

function add_admin_notification($type, $title, $message = '', $link = '') {
    $items = load_admin_notifications();
    $items[] = [
        'id' => uniqid('notif_', true),
        'type' => (string)$type,
        'title' => (string)$title,
        'message' => (string)$message,
        'link' => (string)$link,
        'is_read' => false,
        'created_at' => date('Y-m-d H:i:s')
    ];
    return save_admin_notifications($items);
}

function get_unread_notifications_count() {
    return count(load_admin_notifications());
}

function get_recent_notifications($limit = 10) {
    $items = load_admin_notifications();
    usort($items, function ($a, $b) {
        $ta = strtotime($a['created_at'] ?? '');
        $tb = strtotime($b['created_at'] ?? '');
        return $tb <=> $ta;
    });
    return array_slice($items, 0, (int)$limit);
}

function delete_admin_notification($id) {
    $items = load_admin_notifications();
    $filtered = [];
    foreach ($items as $item) {
        if (($item['id'] ?? '') !== $id) {
            $filtered[] = $item;
        }
    }
    return save_admin_notifications($filtered);
}

function clear_admin_notifications() {
    return save_admin_notifications([]);
}

function get_notification_icon($type) {
    $icons = [
        'order' => 'bi-cart-check-fill text-success',
        'build' => 'bi-cpu-fill text-primary',
        'service' => 'bi-tools text-warning',
        'low_stock' => 'bi-exclamation-triangle-fill text-danger'
    ];
    return $icons[$type] ?? 'bi-bell-fill text-info';
}
?>
