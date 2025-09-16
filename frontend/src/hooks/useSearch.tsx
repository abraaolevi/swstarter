import { useContext } from "react"
import { SearchContext } from "../contexts/search/SearchContext"

export function useSearch() {
  const ctx = useContext(SearchContext);
  if (!ctx) {
    throw new Error("useSearch must be used within a SearchProvider");
  }
  return ctx;
}
