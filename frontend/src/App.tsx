import { Route, Routes } from 'react-router-dom'
import styles from './App.module.css'
import FilmDetailPage from './pages/FilmDetailPage'
import HomePage from './pages/HomePage'
import PersonDetailPage from './pages/PersonDetailPage'

function App() {
  return (
    <div className={styles.app}>
      <Routes>
        <Route path="/" element={<HomePage />} />
        <Route path="/people/:id" element={<PersonDetailPage />} />
        <Route path="/films/:id" element={<FilmDetailPage />} />
      </Routes>
    </div>
  );
}

export default App;
