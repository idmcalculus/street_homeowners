<?php
session_start();
require_once __DIR__ . '/vendor/autoload.php';
use App\Person;

$uploadDirectory = __DIR__ . '/uploads/';
if (!file_exists($uploadDirectory)) {
	mkdir($uploadDirectory, 0777);
}

$file = $_FILES['csvFileInput'] ?? null;
if (!$file) {
	header('Location: index.php?error=' . urlencode('No file was uploaded.'));
	exit;
}

$fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if ($fileExtension !== 'csv') {
	header('Location: index.php?error=' . urlencode('Invalid file type. Please upload a CSV file.'));
	exit;
}

$uploadedFileName = uniqid('upload_', true) . '.csv';
$uploadedFilePath = __DIR__ . '/uploads/' . $uploadedFileName;

if (!move_uploaded_file($file['tmp_name'], $uploadedFilePath)) {
	header('Location: index.php?error=' . urlencode('Failed to upload file.'));
	exit;
}

try {
	$parsedPeople = [];

	if (($csvHandle = fopen($uploadedFilePath, "r")) !== false) {
		// Skip header row with all parameters to avoid deprecation warning
		fgetcsv($csvHandle, 0, ',', '"', '\\');

		while (($rowData = fgetcsv($csvHandle, 0, ',', '"', '\\')) !== FALSE) {
			$nameString = trim($rowData[0]);
			if (empty($nameString)) continue;

			$peopleFromRow = Person::parseNameString($nameString);
			foreach ($peopleFromRow as $person) {
				$parsedPeople[] = $person;
			}
		}

		fclose($csvHandle);
	}

	$_SESSION['processed_people'] = $parsedPeople;

	unlink($uploadedFilePath);

	header('Location: index.php?success=1');
	exit;
} catch (Exception $e) {
	unlink($uploadedFilePath);
	header('Location: index.php?error=' . urlencode($e->getMessage()));
	exit;
}