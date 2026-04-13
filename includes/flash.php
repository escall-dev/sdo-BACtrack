<?php
/**
 * Flash Message Helper
 * SDO-BACtrack
 */

function setFlashMessage($type, $message) {
    $_SESSION['flash_' . $type] = $message;
}

function getFlashMessage($type) {
    $key = 'flash_' . $type;
    if (isset($_SESSION[$key])) {
        $message = $_SESSION[$key];
        unset($_SESSION[$key]);
        return $message;
    }
    return null;
}

function hasFlashMessage($type) {
    return isset($_SESSION['flash_' . $type]);
}

function displayFlashMessages() {
    $types = ['success', 'error', 'warning', 'info'];
    $output = '';
    
    foreach ($types as $type) {
        $message = getFlashMessage($type);
        if ($message) {
            $alertClass = $type === 'error' ? 'danger' : $type;
            $output .= '<div class="alert alert-' . $alertClass . '">';
            $output .= '<i class="fas fa-' . getAlertIcon($type) . '"></i>';
            $output .= '<span>' . htmlspecialchars($message) . '</span>';
            $output .= '</div>';
        }
    }
    
    return $output;
}

function getAlertIcon($type) {
    $icons = [
        'success' => 'check-circle',
        'error' => 'exclamation-circle',
        'warning' => 'exclamation-triangle',
        'info' => 'info-circle'
    ];
    return $icons[$type] ?? 'info-circle';
}
