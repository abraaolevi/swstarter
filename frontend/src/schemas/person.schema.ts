import { z } from "zod"

export const PersonSchema = z.object({
  id: z.number(),
  name: z.string(),
  birth_year: z.string(),
  gender: z.string(),
  eye_color: z.string(),
  hair_color: z.string(),
  height: z.string(),
  mass: z.string(),
});

export const PersonFilmSchema = z.object({
  id: z.number(),
  title: z.string(),
  opening_crawl: z.string(),
});

export const PersonDetailSchema = z.object({
  success: z.boolean(),
  data: z.object({
    person: PersonSchema,
    films: z.array(PersonFilmSchema),
  }),
});

export type Person = z.infer<typeof PersonSchema>;
export type PersonFilm = z.infer<typeof PersonFilmSchema>;
export type PersonDetail = z.infer<typeof PersonDetailSchema>;
