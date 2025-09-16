import { useNavigate } from "react-router-dom"
import { useSearch } from "../../../hooks/useSearch"
import Button from "../button/Button"
import styles from "./SearchResult.module.css"

function EmptyState() {
  return (
    <div className={styles.emptyContainer}>
      <p>There are zero matches.</p>
      <p>Use the form to search for People or Movies.</p>
    </div>
  );
}

function LoadingState() {
  return (
    <div className={styles.emptyContainer}>
      <p>Searching...</p>
    </div>
  );
}

function SearchResult() {
  const { data, isLoading, type } = useSearch();
  const navigate = useNavigate();
  const isEmptyResults = !isLoading && (!data || data.data.length === 0);

  const handleMoreDetails = (item: { id: number; name: string }) => {
    const path = type === "people" ? `/people/${item.id}` : `/films/${item.id}`;
    navigate(path);
  };

  return (
    <div>
      <h2 className={styles.title}>Results</h2>
      {isEmptyResults && <EmptyState />}
      {isLoading && <LoadingState />}

      {data?.data.map((item) => (
        <div key={item.id} className={styles.resultItem}>
          <span>{item.name}</span>
          <Button onClick={() => handleMoreDetails(item)}>See details</Button>
        </div>
      ))}
    </div>
  );
}

export default SearchResult;
