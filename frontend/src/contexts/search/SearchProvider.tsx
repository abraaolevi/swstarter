import { useQuery } from "@tanstack/react-query"
import { useState } from "react"
import { searchAPI } from "../../services/api"
import { SearchContext } from "./SearchContext"

export function SearchProvider({ children }: { children: React.ReactNode }) {
  const [type, setType] = useState<"people" | "films">("people");
  const [query, setQuery] = useState("");
  const [submittedQuery, setSubmittedQuery] = useState("");

  const searchQuery = useQuery({
    queryKey: [type, "search", submittedQuery],
    queryFn: () => searchAPI(type, submittedQuery),
    enabled: submittedQuery.length > 0,
  });

  const submitSearch = async () => {
    const trimmed = query.trim();
    if (trimmed.length === 0) {
      return;
    }

    if (trimmed === submittedQuery) {
      await searchQuery.refetch();
      return;
    }

    setSubmittedQuery(trimmed);
  };

  return (
    <SearchContext.Provider
      value={{
        type,
        setType,
        query,
        setQuery,
        submitSearch,
        isLoading: searchQuery.isLoading,
        data: searchQuery.data,
      }}
    >
      {children}
    </SearchContext.Provider>
  );
}
