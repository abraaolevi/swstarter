import type { FilmDetail } from "../schemas/film.schema"
import { FilmDetailSchema } from "../schemas/film.schema"
import type { PersonDetail } from "../schemas/person.schema"
import { PersonDetailSchema } from "../schemas/person.schema"
import type { SearchResult } from "../schemas/search.schema"
import { SearchResultSchema } from "../schemas/search.schema"

const API_BASE_URL = "http://localhost:8000/api";

export async function searchAPI(
  type: "people" | "films",
  query: string
): Promise<SearchResult> {
  const endpoint = type === "people" ? "/people/search" : "/films/search";

  const response = await fetch(`${API_BASE_URL}${endpoint}`, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      Accept: "application/json",
    },
    body: JSON.stringify({ query }),
  });

  if (!response.ok) {
    throw new Error(`Search failed: ${response.status} ${response.statusText}`);
  }

  const data = await response.json();

  return SearchResultSchema.parse(data);
}

export async function getPersonAPI(id: string): Promise<PersonDetail> {
  const response = await fetch(`${API_BASE_URL}/people/${id}`, {
    method: "GET",
    headers: {
      Accept: "application/json",
    },
  });

  if (!response.ok) {
    throw new Error(
      `Failed to fetch person: ${response.status} ${response.statusText}`
    );
  }

  const result = await response.json();

  if (!result.success) {
    throw new Error(result.message || "Failed to fetch person");
  }

  return PersonDetailSchema.parse(result);
}

export async function getFilmAPI(id: string): Promise<FilmDetail> {
  const response = await fetch(`${API_BASE_URL}/films/${id}`, {
    method: "GET",
    headers: {
      Accept: "application/json",
    },
  });

  if (!response.ok) {
    throw new Error(
      `Failed to fetch film: ${response.status} ${response.statusText}`
    );
  }

  const result = await response.json();

  if (!result.success) {
    throw new Error(result.message || "Failed to fetch film");
  }

  return FilmDetailSchema.parse(result);
}
