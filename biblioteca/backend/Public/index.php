<?php
declare(strict_types=1);

// Permitir peticiones desde Angular (CORS básico)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Cargar el autoloader de Composer
require_once __DIR__ . '/../vendor/autoload.php';

use App\Infrastructure\Persistence\MySqlBookRepository;
use App\Infrastructure\Persistence\MySqlLoanRepository;
use App\Infrastructure\Persistence\MySqlMemberRepository;
use App\Application\UseCase\CreateBookService;
use App\Application\UseCase\ListBooksService;
use App\Application\UseCase\RegisterLoanService;
use App\Infrastructure\Http\BookController;
use App\Infrastructure\Http\LoanController;

// 1. Configuración de la conexión a la Base de Datos (MySQL)
$dbHost = '127.0.0.1';
$dbName = 'biblioteca';
$dbUser = 'root';
$dbPass = ''; // Cambia por tu contraseña de MySQL si tienes una

try {
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión a la base de datos: ' . $e->getMessage()]);
    exit();
}

// 2. Inyección de dependencias (Wiring manual de la Arquitectura Hexagonal)

//Repositorios 
$bookRepository = new MySqlBookRepository($pdo);
$createBookService = new CreateBookService($bookRepository);
$listBooksService = new ListBooksService($bookRepository);
$bookController = new BookController($createBookService, $listBooksService);

//Casos de uso 
$loanRepository = new MySqlLoanRepository($pdo);
$memberRepository = new MySqlMemberRepository($pdo);
$registerLoanService = new RegisterLoanService($bookRepository, $loanRepository, $memberRepository);
$loanController = new LoanController($registerLoanService);

//Controladores 
$bookController = new BookController($createBookService, $listBooksService);
$loanController = new LoanController($registerLoanService);

// 3. Enrutador básico (Router)
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Limpiar prefijos si ejecutas en una subcarpeta o servidor embebido de PHP
// Ejemplo esperado: /api/v1/books
$path = str_replace('/api/v1', '', $uri);

if ($path === '/books' && $method === 'POST') {
    $bookController->store();
    exit();
}

if ($path === '/books' && $method === 'GET') {
    $bookController->index();
    exit();
}

if ($path === '/loans' && $method === 'POST') {
    $loanController->store();
    exit();
}

// Si la ruta no coincide con ninguna
http_response_code(404);
header('Content-Type: application/json');
echo json_encode([
    'title' => 'Recurso no encontrado',
    'status' => 404,
    'detail' => 'La ruta solicitada no existe en esta API.'
]);