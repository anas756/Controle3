<?php
// Start session
session_start();

// Include configuration and utility files
require_once 'config/database.php';
require_once 'utils/Functions.php';
require_once 'utils/Session.php';

// Include models
require_once 'models/User.php';
require_once 'models/Post.php';
require_once 'models/Comment.php';

// Include controllers
require_once 'controllers/AuthController.php';
require_once 'controllers/ProfileController.php';
require_once 'controllers/PostController.php';
require_once 'controllers/CommentController.php';

// Initialize Session utility
$session = new Session();

// Database connection
$database = new Database();
$db = $database->getConnection();

// Check for remember me cookie
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_token'])) {
    $auth = new AuthController($db, $session);
    $auth->loginWithRememberToken($_COOKIE['remember_token']);
}

// Simple routing
$page = isset($_GET['page']) ? $_GET['page'] : 'home';
$action = isset($_GET['action']) ? $_GET['action'] : 'index';

// Route to appropriate controller/action
switch ($page) {
    case 'auth':
        $controller = new AuthController($db, $session);
        if ($action === 'login') {
            $controller->login();
        } elseif ($action === 'register') {
            $controller->register();
        } elseif ($action === 'logout') {
            $controller->logout();
        } else {
            // Default to login page
            $controller->showLoginForm();
        }
        break;
        
    case 'profile':
        // Check if user is logged in
        if (!$session->isLoggedIn()) {
            header("Location: index.php?page=auth&action=login");
            exit;
        }
        
        $controller = new ProfileController($db, $session);
        if ($action === 'edit') {
            $controller->edit();
        } elseif ($action === 'update') {
            $controller->update();
        } else {
            // Default to view profile
            $controller->view();
        }
        break;
        
    case 'post':
        // Check if user is logged in
        if (!$session->isLoggedIn()) {
            header("Location: index.php?page=auth&action=login");
            exit;
        }
        
        $controller = new PostController($db, $session);
        if ($action === 'create') {
            $controller->create();
        } elseif ($action === 'delete') {
            $controller->delete();
        } else {
            // Default to view post
            $controller->view();
        }
        break;
        
    case 'comment':
        // Check if user is logged in
        if (!$session->isLoggedIn()) {
            header("Location: index.php?page=auth&action=login");
            exit;
        }
        
        $controller = new CommentController($db, $session);
        if ($action === 'add') {
            $controller->add();
        } elseif ($action === 'delete') {
            $controller->delete();
        }
        break;
        
    case 'home':
    default:
        // If not logged in, redirect to login page
        if (!$session->isLoggedIn()) {
            header("Location: index.php?page=auth&action=login");
            exit;
        }
        
        // Show home page with posts
        $controller = new PostController($db, $session);
        $controller->index();
        break;
}
?>
