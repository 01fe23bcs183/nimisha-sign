const axios = require('axios');

const SERVER_URL = process.env.SERVER_URL || 'http://localhost:3000';

async function testHelloWorld() {
  console.log('Testing Hello World Python execution...\n');

  try {
    const response = await axios.post(`${SERVER_URL}/execute`, {
      language_id: 71,
      source_code: 'print("Hello World")',
      stdin: ''
    });

    console.log('Response:', JSON.stringify(response.data, null, 2));
    
    if (response.data.stdout) {
      console.log('\nOutput:', response.data.stdout.trim());
    }

    if (response.data.stdout && response.data.stdout.trim() === 'Hello World') {
      console.log('\nTest PASSED: Output matches expected "Hello World"');
    } else {
      console.log('\nTest FAILED: Output does not match expected "Hello World"');
      process.exit(1);
    }

  } catch (error) {
    console.error('Error:', error.message);
    if (error.response) {
      console.error('Response data:', error.response.data);
    }
    process.exit(1);
  }
}

testHelloWorld();
