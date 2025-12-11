import { useState, useEffect, useCallback } from 'react';
import { useSearchParams } from 'react-router-dom';
import Editor from '@monaco-editor/react';
import axios from 'axios';

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:3000';

const LANGUAGE_MAP = {
  python: 71,
  javascript: 63,
  cpp: 54,
  java: 62,
  c: 50
};

function EmbedEditor() {
  const [searchParams] = useSearchParams();
  const [code, setCode] = useState('');
  const [output, setOutput] = useState('');
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState(null);
  const [language, setLanguage] = useState('python');
  const [languageId, setLanguageId] = useState(71);

  useEffect(() => {
    const lang = searchParams.get('lang') || 'python';
    const base64Code = searchParams.get('base64_code');
    
    setLanguage(lang);
    setLanguageId(LANGUAGE_MAP[lang] || 71);
    
    if (base64Code) {
      try {
        const decodedCode = atob(base64Code);
        setCode(decodedCode);
      } catch (e) {
        console.error('Failed to decode base64 code:', e);
        setCode('# Invalid base64 code provided');
      }
    } else {
      setCode(lang === 'python' ? 'print("Hello World")' : '// Enter your code here');
    }
  }, [searchParams]);

  const runCode = useCallback(async () => {
    setIsLoading(true);
    setError(null);
    setOutput('');

    try {
      const response = await axios.post(`${API_URL}/execute`, {
        language_id: languageId,
        source_code: code,
        stdin: ''
      });

      let result = '';
      if (response.data.stdout) {
        result = response.data.stdout;
        setOutput(result);
      } else if (response.data.stderr) {
        result = response.data.stderr;
        setError(result);
      } else if (response.data.compile_output) {
        result = response.data.compile_output;
        setError(result);
      } else if (response.data.status && response.data.status.description !== 'Accepted') {
        result = `Error: ${response.data.status.description}`;
        setError(result);
      } else {
        result = '(No output)';
        setOutput(result);
      }
      
      return { stdout: response.data.stdout, stderr: response.data.stderr, error: null };
    } catch (err) {
      const errorMsg = err.response?.data?.error || err.message || 'An error occurred';
      setError(errorMsg);
      return { stdout: null, stderr: null, error: errorMsg };
    } finally {
      setIsLoading(false);
    }
  }, [code, languageId]);

  useEffect(() => {
    const handleMessage = async (event) => {
      if (event.data && event.data.type === 'RUN_TEST') {
        const expectedOutput = event.data.expected_output;
        
        const result = await runCode();
        
        let passed = false;
        if (result.stdout) {
          const actualOutput = result.stdout.trim();
          const expected = expectedOutput.trim();
          passed = actualOutput === expected;
        }
        
        if (event.source) {
          event.source.postMessage({
            type: 'TEST_RESULT',
            passed: passed,
            actual_output: result.stdout ? result.stdout.trim() : null,
            expected_output: expectedOutput,
            error: result.error || result.stderr
          }, '*');
        }
      }
    };

    window.addEventListener('message', handleMessage);
    return () => window.removeEventListener('message', handleMessage);
  }, [runCode]);

  const handleEditorChange = (value) => {
    setCode(value);
  };

  return (
    <div className="embed-container">
      <div className="embed-editor-wrapper">
        <Editor
          height="300px"
          language={language}
          value={code}
          onChange={handleEditorChange}
          theme="vs-dark"
          options={{
            minimap: { enabled: false },
            fontSize: 14,
            lineNumbers: 'on',
            automaticLayout: true,
            scrollBeyondLastLine: false,
            padding: { top: 10 }
          }}
        />
      </div>

      <div className="embed-controls">
        <button 
          onClick={runCode} 
          disabled={isLoading}
          className="run-button"
        >
          {isLoading ? 'Running...' : 'Run Code'}
        </button>
      </div>

      <div className="embed-output-section">
        <h4>Output:</h4>
        {isLoading && <div className="loading">Executing code...</div>}
        {error && <pre className="output error">{error}</pre>}
        {output && !error && <pre className="output success">{output}</pre>}
        {!isLoading && !error && !output && (
          <pre className="output placeholder">Click "Run Code" to see output</pre>
        )}
      </div>
    </div>
  );
}

export default EmbedEditor;
