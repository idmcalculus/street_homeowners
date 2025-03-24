<?php
ob_start();
session_start();
require_once __DIR__ . '/vendor/autoload.php';

use App\Services\NameParserFactory;
use League\Csv\Reader;

$uploadDirectory = __DIR__ . '/uploads/';
if (!file_exists($uploadDirectory)) {
    mkdir($uploadDirectory, 0777);
}

try {
    $file = $_FILES['csvFileInput'] ?? null;
    if (!$file) {
        throw new RuntimeException('No file was uploaded.');
    }

    // Validate file type
    if (strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)) !== 'csv') {
        throw new RuntimeException('Invalid file type. Please upload a CSV file.');
    }

    // Create CSV reader instance
    $csv = Reader::createFromPath($file['tmp_name'], 'r');
    $csv->setHeaderOffset(0); // Skip header row
    
    // Get the header row to know the column name
    $header = $csv->getHeader();
    $nameColumnIndex = 0; // Default to first column
    
    $parsedPeople = [];
    
    // Create parser instance
    $parser = NameParserFactory::create();
    
    // Process each record
    foreach ($csv->getRecords() as $record) {
        // Safely get the name string using array_values to ensure numeric indexing
        $record = array_values($record);
        $nameString = isset($record[$nameColumnIndex]) ? trim($record[$nameColumnIndex]) : '';
        
        if (empty($nameString)) continue;

        $peopleFromRow = $parser->parse($nameString);
        $parsedPeople = array_merge($parsedPeople, $peopleFromRow);
    }

    $_SESSION['processed_people'] = $parsedPeople;

    $uploadedFileName = uniqid('upload_', true) . '.csv';
    $uploadedFilePath = __DIR__ . '/uploads/' . $uploadedFileName;

    if (!move_uploaded_file($file['tmp_name'], $uploadedFilePath)) {
        throw new RuntimeException('Failed to upload file.');
    }

    unlink($uploadedFilePath);

    header('Location: index.php?success=1');
    exit;

} catch (Exception $e) {
    if (isset($uploadedFilePath) && file_exists($uploadedFilePath)) {
        unlink($uploadedFilePath);
    }
    header('Location: index.php?error=' . urlencode($e->getMessage()));
    exit;
}