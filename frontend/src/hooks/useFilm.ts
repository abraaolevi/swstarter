import { useQuery } from "@tanstack/react-query"
import type { FilmDetail } from "../schemas/film.schema"
import { getFilmAPI } from "../services/api"

export function useFilm(id: string) {
  return useQuery<FilmDetail>({
    queryKey: ["film", id],
    queryFn: () => getFilmAPI(id),
    enabled: !!id,
  });
}
