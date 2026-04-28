#!/usr/bin/env node

const { execSync } = require('child_process');
const path = require('path');

const files = process.argv.slice(2);

if (files.length === 0) {
  console.log('No PHP files to check.');
  process.exit(0);
}

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
    const absolutePath = path.resolve(file);
    const cwd = process.cwd();
    let relativePath = path.relative(cwd, absolutePath);
    
    relativePath = relativePath.replace(/\\/g, '/');
    
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
