import { BrowserRouter, Routes, Route } from 'react-router-dom'
import './App.css'
import CodeEditor from './components/CodeEditor'
import EmbedEditor from './components/EmbedEditor'

function App() {
  return (
    <BrowserRouter>
      <Routes>
        <Route path="/" element={
          <div className="app">
            <CodeEditor />
          </div>
        } />
        <Route path="/embed" element={
          <div className="embed-app">
            <EmbedEditor />
          </div>
        } />
      </Routes>
    </BrowserRouter>
  )
}

export default App
