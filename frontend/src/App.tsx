import styles from './App.module.css'
import Box from './components/ui/box/Box'
import Header from './components/ui/header/Header'
import Search from './components/ui/search/Search'

function App() {
  return (
    <div className={styles.app}>
      <Header />
      <main className={styles.main}>
        <Box>
          <Search />
        </Box>
      </main>
    </div>
  )
}

export default App
