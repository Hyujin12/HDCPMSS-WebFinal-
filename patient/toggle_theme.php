<?php
session_start();

// Handle theme toggle
if (isset($_POST['toggle_theme'])) {
    $current_theme = isset($_SESSION['theme']) ? $_SESSION['theme'] : 'light';
    $_SESSION['theme'] = ($current_theme === 'light') ? 'dark' : 'light';
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'theme' => $_SESSION['theme']
    ]);
    exit;
}

// If accessed without POST, return current theme
header('Content-Type: application/json');
echo json_encode([
    'success' => false,
    'message' => 'Invalid request method'
]);
exit;
?>