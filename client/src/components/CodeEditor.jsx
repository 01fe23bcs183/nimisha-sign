import { useState } from 'react';
import Editor from '@monaco-editor/react';
import axios from 'axios';

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:3000';

function CodeEditor() {
  const [code, setCode] = useState('print("Hello World")');
  const [output, setOutput] = useState('');
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState(null);

  const handleEditorChange = (value) => {
    setCode(value);
  };

  const runCode = async () => {
    setIsLoading(true);
    setError(null);
    setOutput('');

    try {
      const response = await axios.post(`${API_URL}/execute`, {
        language_id: 71,
        source_code: code,
        stdin: ''
      });

      if (response.data.stdout) {
        setOutput(response.data.stdout);
      } else if (response.data.stderr) {
        setError(response.data.stderr);
      } else if (response.data.compile_output) {
        setError(response.data.compile_output);
      } else if (response.data.status && response.data.status.description !== 'Accepted') {
        setError(`Error: ${response.data.status.description}`);
      } else {
        setOutput('(No output)');
      }
    } catch (err) {
      setError(err.response?.data?.error || err.message || 'An error occurred');
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <div className="code-editor-container">
      <h1>Online Code Editor</h1>
      
      <div className="editor-wrapper">
        <Editor
          height="400px"
          defaultLanguage="python"
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

      <div className="controls">
        <button 
          onClick={runCode} 
          disabled={isLoading}
          className="run-button"
        >
          {isLoading ? 'Running...' : 'Run Code'}
        </button>
      </div>

      <div className="output-section">
        <h3>Output:</h3>
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

export default CodeEditor;
