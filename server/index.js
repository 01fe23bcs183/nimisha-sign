const express = require('express');
const axios = require('axios');
const cors = require('cors');

const app = express();
const PORT = process.env.PORT || 3000;
const JUDGE0_URL = process.env.JUDGE0_URL || 'http://localhost:2358';

app.use(cors());
app.use(express.json());

app.post('/execute', async (req, res) => {
  try {
    const { language_id, source_code, stdin } = req.body;

    if (!language_id || !source_code) {
      return res.status(400).json({
        error: 'Missing required fields: language_id and source_code are required'
      });
    }

    const submissionResponse = await axios.post(
      `${JUDGE0_URL}/submissions?base64_encoded=false&wait=true`,
      {
        language_id,
        source_code,
        stdin: stdin || ''
      },
      {
        headers: {
          'Content-Type': 'application/json'
        }
      }
    );

    const result = submissionResponse.data;

    if (result.status && result.status.id <= 2) {
      return res.status(202).json({
        message: 'Submission is still processing',
        status: result.status
      });
    }

    if (result.stderr || result.compile_output) {
      return res.json({
        stdout: result.stdout || null,
        stderr: result.stderr || null,
        compile_output: result.compile_output || null,
        status: result.status,
        time: result.time,
        memory: result.memory
      });
    }

    return res.json({
      stdout: result.stdout || '',
      stderr: result.stderr || null,
      status: result.status,
      time: result.time,
      memory: result.memory
    });

  } catch (error) {
    console.error('Error executing code:', error.message);
    
    if (error.response) {
      return res.status(error.response.status).json({
        error: 'Judge0 API error',
        details: error.response.data
      });
    }
    
    return res.status(500).json({
      error: 'Internal server error',
      message: error.message
    });
  }
});

app.get('/health', (req, res) => {
  res.json({ status: 'ok' });
});

app.listen(PORT, () => {
  console.log(`Server running on port ${PORT}`);
  console.log(`Judge0 URL: ${JUDGE0_URL}`);
});
