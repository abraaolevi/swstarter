import { createContext } from "react"
import type { SearchResult } from "../../schemas/search.schema"

type SearchType = "people" | "films";

export type SearchContextValue = {
  type: SearchType;
  setType: (type: SearchType) => void;
  query: string;
  setQuery: (q: string) => void;
  submitSearch: () => Promise<void>;
  isLoading: boolean;
  data?: SearchResult;
};

export const SearchContext = createContext<SearchContextValue | undefined>(
  undefined
);
