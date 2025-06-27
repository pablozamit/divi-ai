// src/ai/flows/create-bulk-page-layout.ts
'use server';
/**
 * @fileOverview This file defines a Genkit flow for generating a complete Divi page layout from a natural language prompt.
 *
 * - generateFullPageLayoutFromPrompt - A function that generates a Divi page layout from a prompt.
 * - GenerateFullPageLayoutFromPromptInput - The input type for the generateFullPageLayoutFromPrompt function.
 * - GenerateFullPageLayoutFromPromptOutput - The return type for the generateFullPageLayoutFromPrompt function.
 */

import {ai} from '@/ai/genkit';
import {z} from 'genkit';

const GenerateFullPageLayoutFromPromptInputSchema = z.object({
  prompt: z.string().describe('A natural language description of the desired Divi page layout.'),
});
export type GenerateFullPageLayoutFromPromptInput = z.infer<typeof GenerateFullPageLayoutFromPromptInputSchema>;

const GenerateFullPageLayoutFromPromptOutputSchema = z.object({
  diviLayout: z.string().describe('The generated Divi Builder-compatible layout as a string.'),
});
export type GenerateFullPageLayoutFromPromptOutput = z.infer<typeof GenerateFullPageLayoutFromPromptOutputSchema>;

export async function generateFullPageLayoutFromPrompt(input: GenerateFullPageLayoutFromPromptInput): Promise<GenerateFullPageLayoutFromPromptOutput> {
  return generateFullPageLayoutFromPromptFlow(input);
}

const generateFullPageLayoutPrompt = ai.definePrompt({
  name: 'generateFullPageLayoutPrompt',
  input: {schema: GenerateFullPageLayoutFromPromptInputSchema},
  output: {schema: GenerateFullPageLayoutFromPromptOutputSchema},
  prompt: `You are an expert Divi page builder. You will generate a complete Divi page layout based on the user's description.

  User Description: {{{prompt}}}

  Generate a Divi Builder-compatible layout as a string. The layout should be complete and ready to be imported into the Divi Builder.
  The response must only contain the Divi Builder-compatible layout.
  Ensure that the generated layout is functional and well-structured, using appropriate Divi modules for each section (e.g., header, hero section, menu showcase, testimonials, contact form).
  Make sure to include all necessary shortcodes and module settings for a fully functional Divi page.
  Do not include any introductory or explanatory text.
`,
});

const generateFullPageLayoutFromPromptFlow = ai.defineFlow(
  {
    name: 'generateFullPageLayoutFromPromptFlow',
    inputSchema: GenerateFullPageLayoutFromPromptInputSchema,
    outputSchema: GenerateFullPageLayoutFromPromptOutputSchema,
  },
  async input => {
    const {output} = await generateFullPageLayoutPrompt(input);
    return output!;
  }
);
