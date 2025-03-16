<?php
require_once __DIR__ . '/vendor/autoload.php';
use App\Person;

/**
 * Renders the output in either raw or table format
 * 
 * @param array $processed_people Array of processed people data
 * @param string $format Output format ('raw' or 'table')
 * @return string HTML output
 */
function html_output(array $processed_people,  string $format = 'raw'): string {
	$output = '';
	
	if ($format === 'raw') {
		$output .= '<div class="results-container" id="rawDataView">';
		$output .= '<h3 class="mb-4">Raw Data</h3>';
		$output .= '<div class="raw-output">';
		$output .= '<pre>' . print_r($processed_people, true) . '</pre>';
		$output .= '</div>';
		$output .= '</div>';
	} else { // table format
		$output .= '<div class="results-container" id="tableDataView">';
		$output .= '<h3 class="mb-4">Processed Results</h3>';
		$output .= '<table class="table table-striped">';
		$output .= '<thead>';
		$output .= '<tr>';
		$output .= '<th>Title</th>';
		$output .= '<th>First Name</th>';
		$output .= '<th>Initials</th>';
		$output .= '<th>Last Name</th>';
		$output .= '</tr>';
		$output .= '</thead>';
		$output .= '<tbody>';
		
		foreach ($processed_people as $person) {
			$output .= '<tr>';
			$output .= '<td>' . htmlspecialchars($person['title']) . '</td>';
			$output .= '<td>' . htmlspecialchars($person['firstName'] ?? '') . '</td>';
			$output .= '<td>' . htmlspecialchars($person['initials'] ?? '') . '</td>';
			$output .= '<td>' . htmlspecialchars($person['lastName']) . '</td>';
			$output .= '</tr>';
		}
		
		$output .= '</tbody>';
		$output .= '</table>';
		$output .= '</div>';
	}
	
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
			.table th {
				background-color: #f8f9fa;
			}
			.display-format {
				margin: 20px 0;
				display: flex;
				align-items: center;
				gap: 10px;
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
				<div class="display-format">
					<div class="form-check form-check-inline">
						<input class="form-check-input" type="radio" name="outputFormat" id="rawOutputFormat" value="raw" checked>
						<label class="form-check-label" for="rawOutputFormat">Raw Data</label>
					</div>
					<div class="form-check form-check-inline">
						<input class="form-check-input" type="radio" name="outputFormat" id="tableOutputFormat" value="table">
						<label class="form-check-label" for="tableOutputFormat">Table View</label>
					</div>
				</div>
				<div id="output-container">
					<?php
					// Default to raw format
					$format = 'raw';
					
					// Check if JavaScript is disabled and form was submitted with a format
					if (isset($_GET['format']) && in_array($_GET['format'], ['raw', 'table'])) {
						$format = $_GET['format'];
					}
					
					// Output the HTML based on the selected format
					echo html_output($processed_people, $format);
					?>
				</div>
			<?php endif; ?>
		</div>
	
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
		<script>
			document.addEventListener('DOMContentLoaded', () => {
				const rawOutputFormat = document.getElementById('rawOutputFormat');
				const tableOutputFormat = document.getElementById('tableOutputFormat');
				const outputContainer = document.getElementById('output-container');
				
				// Add event listeners to the radio buttons
				rawOutputFormat.addEventListener('change', () => {
					if (rawOutputFormat.checked) {
						fetchAndUpdateOutput('raw');
					}
				});
				
				tableOutputFormat.addEventListener('change', () => {
					if (tableOutputFormat.checked) {
						fetchAndUpdateOutput('table');
					}
				});
				
				// Function to fetch and update the output based on format
				function fetchAndUpdateOutput(format) {
					// Create a URL with the format parameter
					const url = new URL(window.location.href);
					url.searchParams.set('format', format);
					
					// Use fetch to get the new content
					fetch(url)
						.then(response => response.text())
						.then(html => {
							// Create a temporary element to parse the HTML
							const tempElement = document.createElement('div');
							tempElement.innerHTML = html;
							
							// Extract the output container from the response
							const newOutputContainer = tempElement.querySelector('#output-container');
							
							// Replace the current output container with the new one
							if (newOutputContainer) {
								outputContainer.innerHTML = newOutputContainer.innerHTML;
							}
						})
						.catch(error => {
							console.error('Error fetching output:', error);
						});
				}
				
				// Auto-dismiss alerts after 10 seconds
				setTimeout(() => {
					document.querySelectorAll('.alert .btn-close').forEach(btn => btn.click());
				}, 10000);
			});
		</script>
	</body>
</html>