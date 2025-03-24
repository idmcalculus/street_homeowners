<?php
require_once __DIR__ . '/vendor/autoload.php';
use App\Person;

// Initialize Twig
$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/templates');
$twig = new \Twig\Environment($loader, [
    'cache' => __DIR__ . '/cache',
    'debug' => true,
]);
$twig->addExtension(new \Twig\Extension\DebugExtension());

session_start();
$processed_people = $_SESSION['processed_people'] ?? [];

// Prepare template variables
$context = [
    'error' => $_GET['error'] ?? null,
    'success' => isset($_GET['success']),
    'processed_people' => $processed_people,
    'format' => $_GET['format'] ?? 'raw'
];

// Render template
echo $twig->render('index.html.twig', $context);