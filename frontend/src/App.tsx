import styles from './App.module.css'
import Header from './components/ui/Header'

function App() {
  return (
    <div className={styles.app}>
      <Header />
      <main className={styles.main}>
        <h2>Content</h2>
      </main>
    </div>
  )
}

export default App
