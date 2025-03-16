<?php
require_once __DIR__ . '/vendor/autoload.php';
use App\Person;

/**
 * Renders the output
 * 
 * @param array $processed_people Array of processed people data
 * @return string HTML output
 */
function html_output(array $processed_people): string {
	$output = '';
	
	$output .= '<div class="results-container" id="rawDataView">';
	$output .= '<h3 class="mb-4">Raw Data</h3>';
	$output .= '<div class="raw-output">';
	$output .= '<pre>' . print_r($processed_people, true) . '</pre>';
	$output .= '</div>';
	$output .= '</div>';
	
	return $output;
}

session_start();
$processed_people = $_SESSION['processed_people'] ?? [];
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<title>Homeowners CSV Name Parser</title>
		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
		<style>
			body {
				background-color: #f8f9fa;
			}
			.container {
				max-width: 800px;
				margin-top: 50px;
			}
			.form-container, .results-container {
				background: white;
				padding: 30px;
				border-radius: 10px;
				box-shadow: 0 0 15px rgba(0,0,0,0.1);
				margin-top: 30px;
			}
			.alert {
				margin-top: 20px;
				position: relative;
				padding-right: 40px;
			}
			.alert .btn-close {
				position: absolute;
				right: 10px;
				top: 50%;
				transform: translateY(-50%);
			}
			.form-check-label {
				cursor: pointer;
			}
			.raw-output {
				max-height: 600px;
				overflow-y: scroll;
				font-family: monospace;
				background-color: #f8f9fa;
				padding: 15px;
				border-radius: 5px;
				border: 1px solid #dee2e6;
			}
		</style>
	</head>
	<body>
		<div class="container">
			<div class="form-container">
				<h2 class="mb-4">Homeowners CSV Name Parser</h2>
				<form action="process.php" method="post" enctype="multipart/form-data">
					<div class="mb-3">
						<label for="csvFileInput" class="form-label">Upload CSV File</label>
						<input type="file" class="form-control" id="csvFileInput" name="csvFileInput" accept=".csv" required>
						<div class="form-text">Please upload a CSV file containing homeowner names.</div>
					</div>
					<button type="submit" class="btn btn-primary">Process File</button>
				</form>
			</div>
	
			<?php if (isset($_GET['error'])): ?>
				<div class="alert alert-danger alert-dismissible fade show" role="alert">
					<?= htmlspecialchars($_GET['error']); ?>
					<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
				</div>
			<?php endif; ?>
	
			<?php if (isset($_GET['success'])): ?>
				<div class="alert alert-success alert-dismissible fade show" role="alert">
					File processed successfully!
					<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
				</div>
			<?php endif; ?>
	
			<?php if (!empty($processed_people)): ?>
	
				<div id="output-container">
					<?php 
					
					
					// Output the HTML
					echo html_output($processed_people);
					?>
				</div>
			<?php endif; ?>
		</div>
	
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
		<script>
			document.addEventListener('DOMContentLoaded', () => {
				// Auto-dismiss alerts after 10 seconds
				setTimeout(() => {
					document.querySelectorAll('.alert .btn-close').forEach(btn => btn.click());
				}, 10000);
			});
		</script>
	</body>
</html>