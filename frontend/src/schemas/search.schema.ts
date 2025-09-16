import { z } from "zod"

export const SearchResultSchema = z.object({
  success: z.boolean(),
  data: z.array(
    z.object({
      id: z.number(),
      name: z.string(),
    })
  ),
});

export type SearchResult = z.infer<typeof SearchResultSchema>;
