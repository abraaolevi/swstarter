import Box from "../components/ui/box/Box"
import PageContainer from "../components/ui/page-container/PageContainer"
import SearchForm from "../components/ui/search-form/SearchForm"
import SearchResult from "../components/ui/search-result/SearchResult"
import { SearchProvider } from "../contexts/search/SearchProvider"
import styles from "./HomePage.module.css"

function HomePage() {
  return (
    <SearchProvider>
      <PageContainer>
        <main className={styles.main}>
          <Box>
            <SearchForm />
          </Box>
          <Box>
            <SearchResult />
          </Box>
        </main>
      </PageContainer>
    </SearchProvider>
  );
}

export default HomePage;
