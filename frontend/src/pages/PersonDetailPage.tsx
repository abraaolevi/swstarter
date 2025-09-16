import { Link, useNavigate, useParams } from "react-router-dom"
import Box from "../components/ui/box/Box"
import Button from "../components/ui/button/Button"
import PageContainer from "../components/ui/page-container/PageContainer"
import { usePerson } from "../hooks/usePerson"
import styles from "./PersonDetailPage.module.css"

function LoadingState() {
  return (
    <div className={styles.loadingContainer}>
      <p>Loading person details...</p>
    </div>
  );
}

function ErrorState({ message }: { message: string }) {
  return (
    <div className={styles.errorContainer}>
      <p>Error loading person details:</p>
      <p>{message}</p>
    </div>
  );
}

function PersonDetailPage() {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();
  const { data, isLoading, error } = usePerson(id!);

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

  const person = data?.data.person;
  const films = data?.data.films;

  return (
    <PageContainer>
      <main className={styles.main}>
        <Box>
          <h2>{person?.name}</h2>
          <div className={styles.detailsGrid}>
            <section>
              <h3>Details</h3>
              <p>Birth Year: {person?.birth_year}</p>
              <p>Gender: {person?.gender}</p>
              <p>Height: {person?.height}</p>
              <p>Mass: {person?.mass}</p>
              <p>Eye Color: {person?.eye_color}</p>
              <p>Hair Color: {person?.hair_color}</p>
            </section>
            <section>
              <h3>Movies</h3>
              <p>
                {films && films.length > 0
                  ? films.map((film, index) => (
                      <span key={film.id}>
                        <Link to={`/films/${film.id}`}>{film.title}</Link>
                        {index < films.length - 1 && ", "}
                      </span>
                    ))
                  : "No films found."}
              </p>
            </section>
          </div>
          <Button onClick={handleBackToSearch}>Back to search</Button>
        </Box>
      </main>
    </PageContainer>
  );
}

export default PersonDetailPage;
