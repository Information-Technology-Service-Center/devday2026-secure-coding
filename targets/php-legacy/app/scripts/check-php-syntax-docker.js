#!/usr/bin/env node

/**
 * PHP Syntax Checker (Docker Version)
 * 
 * Validates PHP syntax using `php -l` command inside Docker container.
 * Used by Git pre-commit hook to prevent committing files with syntax errors.
 * 
 * This script is designed for projects where PHP is only available in Docker.
 */

const { execSync } = require('child_process');
const path = require('path');

// Get files from command line arguments
const files = process.argv.slice(2);

if (files.length === 0) {
  console.log('No PHP files to check.');
  process.exit(0);
}

// Check if Docker is running
try {
  execSync('docker --version', { stdio: 'pipe' });
} catch (error) {
  console.error('\n❌ Docker is not available or not running.');
  console.error('Please start Docker to run PHP syntax checks.\n');
  process.exit(1);
}

let hasErrors = false;
let errorCount = 0;

console.log(`\n🔍 Checking PHP syntax for ${files.length} file(s) using Docker...\n`);

files.forEach(file => {
  try {
    // Get relative path from current working directory
    const absolutePath = path.resolve(file);
    const cwd = process.cwd();
    let relativePath = path.relative(cwd, absolutePath);
    
    // Convert Windows path to Unix-style path for Docker
    relativePath = relativePath.replace(/\\/g, '/');
    
    // Run php -l inside the Docker container
    // We mount the project directory and run syntax check on the relative file
    const dockerCommand = `docker run --rm -v "${cwd}:/app" -w /app php:7.4-cli php -l "${relativePath}"`;
    
    execSync(dockerCommand, {
      encoding: 'utf8',
      stdio: 'pipe'
    });
    
    console.log(`✓ ${relativePath}`);
  } catch (error) {
    hasErrors = true;
    errorCount++;
    console.error(`✗ ${file}`);
    
    // Display the error output from PHP
    if (error.stdout) {
      console.error(error.stdout.trim());
    }
    if (error.stderr) {
      console.error(error.stderr.trim());
    }
    console.error('');
  }
});

if (hasErrors) {
  console.error(`\n❌ Found syntax errors in ${errorCount} file(s).`);
  console.error('Please fix the errors above before committing.\n');
  process.exit(1);
} else {
  console.log(`\n✅ All PHP files have valid syntax.\n`);
  process.exit(0);
}
