import { useState } from "react"
import { useSearch } from "../../../hooks/useSearch"
import Button from "../button/Button"
import styles from "./SearchForm.module.css"

function SearchForm() {
  const { type, setType, query, setQuery, submitSearch, isLoading } =
    useSearch();
  const [isSubmitting, setIsSubmitting] = useState(false);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (isSubmitting || query.trim().length === 0) {
      return;
    }
    try {
      setIsSubmitting(true);
      await submitSearch();
    } finally {
      setIsSubmitting(false);
    }
  };

  return (
    <form
      className={styles.search}
      onSubmit={handleSubmit}
      aria-label="search form"
    >
      <p>What are you searching for?</p>
      <fieldset className={styles.options}>
        <legend className="sr-only">Search type</legend>
        <div>
          <input
            id="people"
            type="radio"
            name="type"
            checked={type === "people"}
            onChange={() => setType("people")}
          />
          <label htmlFor="people">People</label>
        </div>
        <div>
          <input
            id="movies"
            type="radio"
            name="type"
            checked={type === "films"}
            onChange={() => setType("films")}
          />
          <label htmlFor="movies">Movies</label>
        </div>
      </fieldset>

      <input
        className={styles.input}
        type="text"
        placeholder={
          type === "people"
            ? "e.g. Chewbacca, Yoda, Boba Fett"
            : "e.g. A New Hope, The Empire Strikes Back"
        }
        value={query}
        onChange={(e) => setQuery(e.target.value)}
        aria-label="search input"
      />

      <Button
        type="submit"
        disabled={query.trim().length === 0 || isLoading || isSubmitting}
        aria-disabled={query.trim().length === 0 || isLoading || isSubmitting}
      >
        {isLoading || isSubmitting ? "Searching..." : "Search"}
      </Button>
    </form>
  );
}

export default SearchForm;
