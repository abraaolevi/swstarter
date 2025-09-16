import { useQuery } from "@tanstack/react-query"
import type { PersonDetail } from "../schemas/person.schema"
import { getPersonAPI } from "../services/api"

export function usePerson(id: string) {
  return useQuery<PersonDetail>({
    queryKey: ["person", id],
    queryFn: () => getPersonAPI(id),
    enabled: !!id,
  });
}
