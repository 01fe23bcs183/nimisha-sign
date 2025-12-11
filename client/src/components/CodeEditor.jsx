import { useState, useEffect } from 'react';
import Editor from '@monaco-editor/react';
import axios from 'axios';
import { mapLanguageToMonaco, getDefaultCode } from '../utils/languageMapper';

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:3000';

function CodeEditor() {
  const [code, setCode] = useState('print("Hello World")');
  const [output, setOutput] = useState('');
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState(null);
  const [languages, setLanguages] = useState([]);
  const [selectedLanguage, setSelectedLanguage] = useState(null);
  const [monacoLanguage, setMonacoLanguage] = useState('python');
  const [isLoadingLanguages, setIsLoadingLanguages] = useState(true);

  useEffect(() => {
    const fetchLanguages = async () => {
      try {
        const response = await axios.get(`${API_URL}/languages`);
        const sortedLanguages = response.data.sort((a, b) => 
          a.name.localeCompare(b.name)
        );
        setLanguages(sortedLanguages);
        
        const pythonLang = sortedLanguages.find(l => l.name.toLowerCase().includes('python 3'));
        if (pythonLang) {
          setSelectedLanguage(pythonLang);
          setMonacoLanguage(mapLanguageToMonaco(pythonLang.name));
        } else if (sortedLanguages.length > 0) {
          setSelectedLanguage(sortedLanguages[0]);
          setMonacoLanguage(mapLanguageToMonaco(sortedLanguages[0].name));
        }
      } catch (err) {
        console.error('Failed to fetch languages:', err);
        setLanguages([{ id: 71, name: 'Python (3.8.1)' }]);
        setSelectedLanguage({ id: 71, name: 'Python (3.8.1)' });
      } finally {
        setIsLoadingLanguages(false);
      }
    };

    fetchLanguages();
  }, []);

  const handleLanguageChange = (e) => {
    const langId = parseInt(e.target.value, 10);
    const lang = languages.find(l => l.id === langId);
    if (lang) {
      setSelectedLanguage(lang);
      const newMonacoLang = mapLanguageToMonaco(lang.name);
      setMonacoLanguage(newMonacoLang);
      setCode(getDefaultCode(newMonacoLang));
    }
  };

  const handleEditorChange = (value) => {
    setCode(value);
  };

  const runCode = async () => {
    if (!selectedLanguage) return;
    
    setIsLoading(true);
    setError(null);
    setOutput('');

    try {
      const response = await axios.post(`${API_URL}/execute`, {
        language_id: selectedLanguage.id,
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
      
      <div className="language-selector">
        <label htmlFor="language-select">Language: </label>
        <select 
          id="language-select"
          value={selectedLanguage?.id || ''}
          onChange={handleLanguageChange}
          disabled={isLoadingLanguages}
          className="language-dropdown"
        >
          {isLoadingLanguages ? (
            <option>Loading languages...</option>
          ) : (
            languages.map(lang => (
              <option key={lang.id} value={lang.id}>
                {lang.name}
              </option>
            ))
          )}
        </select>
        <span className="language-count">
          {!isLoadingLanguages && `(${languages.length} languages available)`}
        </span>
      </div>
      
      <div className="editor-wrapper">
        <Editor
          height="400px"
          language={monacoLanguage}
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
          disabled={isLoading || !selectedLanguage}
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
