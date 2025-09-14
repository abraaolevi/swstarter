import styles from './Search.module.css'

function Search() {
  return (
    <form className={styles.search}>
      <p>What are you searching for?</p>
      <div className={styles.options}>
        <div>
          <input id="people" type="radio" name="type" />
          <label htmlFor="people">People</label>
        </div>
        <div>
          <input id="movies" type="radio" name="type" />
          <label htmlFor="movies">Movies</label>
        </div>
      </div>

      <input className={styles.input} type="text" placeholder="e.g. Chewbacca, Yoda, Boba Fett" />

      <button className={styles.button} type="submit" disabled>Search</button>
    </form>
  );
}

export default Search;
