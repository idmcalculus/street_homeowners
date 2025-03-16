# street_homeowners
A PHP application for parsing homeowner names from CSV files.

## Features

- Upload and process CSV files containing homeowner names
- Parse various name formats including:
  - Single person with initial (e.g., "Mr J. Smith")
  - Single person with full name (e.g., "Mr John Smith")
  - Single person with full name and initial (e.g., "Mr John A. Smith")
  - Single person with multiple initials (e.g., "Mr J. R. R. Smith")
  - Multiple people with shared last name (e.g., "Mr and Mrs Smith")
  - Multiple people with different last names (e.g., "Prof. John F. Smith and Dr. Jane A. B. Doe")
  - Multiple people with ampersand (e.g., "Dr & Mrs Joe Bloggs")
  - Multiple people with full names (e.g., "Mr Tom Staff and Mr John Doe")
- Clean, responsive UI using Bootstrap
- Comprehensive test coverage using PHPUnit

## Requirements

- PHP 8.0 or higher
- Composer (for PHPUnit)
- Web server (Apache/Nginx)

## Installation

1. Clone the repository
2. Install dependencies:
   ```
   composer install
   ```

## Usage

1. Start a local PHP server:
   ```
   php -S localhost:8000
   ```
2. Open your browser and navigate to `http://localhost:8000`
3. Upload a CSV file with homeowner names in the first column

## CSV Format

The CSV file should have one name per row in the first column. Example:

```csv
Name
Mr J. Smith
Mr and Mrs Smith
Dr & Mrs Joe Bloggs
```

## Running Tests

Run the tests using the provided script:
```
./run-tests.sh
```

### Enabling Code Coverage

To enable code coverage reports, you need to install Xdebug:

#### macOS (with Homebrew)

```bash
brew install php-xdebug
```

Then add the following to your php.ini file:

```ini
[xdebug]
zend_extension=xdebug.so
xdebug.mode=coverage
```

#### Ubuntu/Debian

```bash
sudo apt-get install php-xdebug
```

Then add the following to your php.ini file:

```ini
[xdebug]
zend_extension=xdebug.so
xdebug.mode=coverage
```

#### Windows

1. Download the appropriate Xdebug DLL from [https://xdebug.org/download](https://xdebug.org/download)
2. Add the following to your php.ini file:

```ini
[xdebug]
zend_extension=path\to\xdebug.dll
xdebug.mode=coverage
```

After installing Xdebug, run the tests again:

```
./run-tests.sh
```

A coverage report will be generated in the `coverage-report` directory.

## Security Considerations

- The application validates file types and only accepts CSV files
- Uploaded files are processed with unique names and cleaned up after processing
- All output is properly escaped to prevent XSS attacks
- File uploads are restricted to the uploads directory