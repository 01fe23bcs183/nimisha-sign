const axios = require('axios');

const SERVER_URL = process.env.SERVER_URL || 'http://localhost:3000';

async function testLanguagesEndpoint() {
  console.log('=== Testing GET /languages endpoint ===\n');

  try {
    const response = await axios.get(`${SERVER_URL}/languages`);
    const languages = response.data;

    console.log(`Found ${languages.length} languages`);
    console.log('\nFirst 10 languages:');
    languages.slice(0, 10).forEach(lang => {
      console.log(`  - ${lang.id}: ${lang.name}`);
    });

    if (languages.length >= 60) {
      console.log(`\nLanguages test PASSED: Found ${languages.length} languages (60+ expected)`);
      return true;
    } else {
      console.log(`\nLanguages test FAILED: Only found ${languages.length} languages (60+ expected)`);
      return false;
    }
  } catch (error) {
    console.error('Error fetching languages:', error.message);
    if (error.response) {
      console.error('Response data:', error.response.data);
    }
    return false;
  }
}

async function testPythonExecution() {
  console.log('\n=== Testing Python execution (language_id: 71) ===\n');

  try {
    const response = await axios.post(`${SERVER_URL}/execute`, {
      language_id: 71,
      source_code: 'print("Hello World")',
      stdin: ''
    });

    console.log('Response:', JSON.stringify(response.data, null, 2));
    
    if (response.data.stdout && response.data.stdout.trim() === 'Hello World') {
      console.log('\nPython test PASSED: Output matches expected "Hello World"');
      return true;
    } else {
      console.log('\nPython test FAILED: Output does not match expected "Hello World"');
      return false;
    }
  } catch (error) {
    console.error('Error:', error.message);
    if (error.response) {
      console.error('Response data:', error.response.data);
    }
    return false;
  }
}

async function testBashExecution() {
  console.log('\n=== Testing Bash execution (language_id: 46) ===\n');

  try {
    const response = await axios.post(`${SERVER_URL}/execute`, {
      language_id: 46,
      source_code: 'echo "Hello from Bash"',
      stdin: ''
    });

    console.log('Response:', JSON.stringify(response.data, null, 2));
    
    if (response.data.stdout && response.data.stdout.trim() === 'Hello from Bash') {
      console.log('\nBash test PASSED: Output matches expected "Hello from Bash"');
      return true;
    } else {
      console.log('\nBash test FAILED: Output does not match expected "Hello from Bash"');
      return false;
    }
  } catch (error) {
    console.error('Error:', error.message);
    if (error.response) {
      console.error('Response data:', error.response.data);
    }
    return false;
  }
}

async function testGoExecution() {
  console.log('\n=== Testing Go execution (language_id: 60) ===\n');

  const goCode = `package main

import "fmt"

func main() {
    fmt.Println("Hello from Go")
}`;

  try {
    const response = await axios.post(`${SERVER_URL}/execute`, {
      language_id: 60,
      source_code: goCode,
      stdin: ''
    });

    console.log('Response:', JSON.stringify(response.data, null, 2));
    
    if (response.data.stdout && response.data.stdout.trim() === 'Hello from Go') {
      console.log('\nGo test PASSED: Output matches expected "Hello from Go"');
      return true;
    } else {
      console.log('\nGo test FAILED: Output does not match expected "Hello from Go"');
      return false;
    }
  } catch (error) {
    console.error('Error:', error.message);
    if (error.response) {
      console.error('Response data:', error.response.data);
    }
    return false;
  }
}

async function runAllTests() {
  console.log('========================================');
  console.log('  Judge0 Full Version API Tests');
  console.log('========================================\n');

  const results = {
    languages: await testLanguagesEndpoint(),
    python: await testPythonExecution(),
    bash: await testBashExecution(),
    go: await testGoExecution()
  };

  console.log('\n========================================');
  console.log('  Test Summary');
  console.log('========================================');
  console.log(`Languages endpoint: ${results.languages ? 'PASSED' : 'FAILED'}`);
  console.log(`Python execution:   ${results.python ? 'PASSED' : 'FAILED'}`);
  console.log(`Bash execution:     ${results.bash ? 'PASSED' : 'FAILED'}`);
  console.log(`Go execution:       ${results.go ? 'PASSED' : 'FAILED'}`);

  const allPassed = Object.values(results).every(r => r);
  if (!allPassed) {
    console.log('\nNote: Execution tests may fail due to cgroup v2 incompatibility.');
    console.log('The languages endpoint should still work correctly.');
  }
}

runAllTests();
