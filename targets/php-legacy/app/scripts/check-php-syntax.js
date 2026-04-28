#!/usr/bin/env node

/**
 * PHP Syntax Checker
 * 
 * Validates PHP syntax using `php -l` command.
 * Used by Git pre-commit hook to prevent committing files with syntax errors.
 */

const { execSync } = require('child_process');
const path = require('path');

// Get files from command line arguments
const files = process.argv.slice(2);

if (files.length === 0) {
  console.log('No PHP files to check.');
  process.exit(0);
}

let hasErrors = false;
let errorCount = 0;

console.log(`\n🔍 Checking PHP syntax for ${files.length} file(s)...\n`);

files.forEach(file => {
  try {
    // Run php -l (lint) on each file
    execSync(`php -l "${file}"`, {
      encoding: 'utf8',
      stdio: 'pipe'
    });
    console.log(`✓ ${file}`);
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
