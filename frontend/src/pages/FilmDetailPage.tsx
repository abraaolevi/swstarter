import { Link, useNavigate, useParams } from "react-router-dom"
import Box from "../components/ui/box/Box"
import Button from "../components/ui/button/Button"
import PageContainer from "../components/ui/page-container/PageContainer"
import { useFilm } from "../hooks/useFilm"
import styles from "./FilmDetailPage.module.css"

function LoadingState() {
  return (
    <div className={styles.loadingContainer}>
      <p>Loading film details...</p>
    </div>
  );
}

function ErrorState({ message }: { message: string }) {
  return (
    <div className={styles.errorContainer}>
      <p>Error loading film details:</p>
      <p>{message}</p>
    </div>
  );
}

function FilmDetailPage() {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();
  const { data, isLoading, error } = useFilm(id!);

  const handleBackToSearch = () => {
    navigate("/");
  };

  if (isLoading) {
    return (
      <PageContainer>
        <main className={styles.main}>
          <Box>
            <LoadingState />
          </Box>
        </main>
      </PageContainer>
    );
  }

  if (error) {
    return (
      <PageContainer>
        <main className={styles.main}>
          <Box>
            <ErrorState message={(error as Error).message} />
            <Button onClick={handleBackToSearch}>Back to search</Button>
          </Box>
        </main>
      </PageContainer>
    );
  }

  const film = data?.data.film;
  const characters = data?.data.characters;

  return (
    <PageContainer>
      <main className={styles.main}>
        <Box>
          <h2>{film?.title}</h2>
          <div className={styles.detailsGrid}>
            <section>
              <h3>Opening Crawl</h3>
              <div className={styles.openingCrawl}>
                {film?.opening_crawl?.split("\n").map((paragraph, index) => {
                  const endsWithPeriod = paragraph.trim().endsWith(".");
                  const className = endsWithPeriod
                    ? styles.paragraphWithPeriod
                    : styles.paragraph;
                  return (
                    <p key={index} className={className}>
                      {paragraph}
                    </p>
                  );
                })}
              </div>
            </section>
            <section>
              <h3>Characters</h3>
              <p>
                {characters && characters.length > 0
                  ? characters.map((character, index) => (
                      <span key={character.id}>
                        <Link to={`/people/${character.id}`}>
                          {character.name}
                        </Link>
                        {index < characters.length - 1 && ", "}
                      </span>
                    ))
                  : "No characters found."}
              </p>
            </section>
          </div>
          <Button onClick={handleBackToSearch}>Back to search</Button>
        </Box>
      </main>
    </PageContainer>
  );
}

export default FilmDetailPage;
