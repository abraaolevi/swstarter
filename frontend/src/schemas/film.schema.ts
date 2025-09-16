import { z } from "zod"

export const FilmSchema = z.object({
  id: z.number(),
  title: z.string(),
  opening_crawl: z.string(),
});

export const FilmCharacterSchema = z.object({
  id: z.number(),
  name: z.string(),
  birth_year: z.string(),
  gender: z.string(),
  eye_color: z.string(),
  hair_color: z.string(),
  height: z.string(),
  mass: z.string(),
});

export const FilmDetailSchema = z.object({
  success: z.boolean(),
  data: z.object({
    film: FilmSchema,
    characters: z.array(FilmCharacterSchema),
  }),
});

export type Film = z.infer<typeof FilmSchema>;
export type FilmCharacter = z.infer<typeof FilmCharacterSchema>;
export type FilmDetail = z.infer<typeof FilmDetailSchema>;
