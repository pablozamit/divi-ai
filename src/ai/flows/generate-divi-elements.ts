'use server';

/**
 * @fileOverview A flow that generates Divi elements from a natural language prompt.
 *
 * - generateDiviElements - A function that generates Divi elements based on a prompt.
 * - GenerateDiviElementsInput - The input type for the generateDiviElements function.
 * - GenerateDiviElementsOutput - The return type for the generateDiviElements function.
 */

import {ai} from '@/ai/genkit';
import {z} from 'genkit';

const GenerateDiviElementsInputSchema = z.object({
  prompt: z.string().describe('A natural language prompt describing the desired Divi elements.'),
});
export type GenerateDiviElementsInput = z.infer<typeof GenerateDiviElementsInputSchema>;

const GenerateDiviElementsOutputSchema = z.object({
  diviElements: z.string().describe('The Divi Builder-compatible instructions to generate page elements and modify existing content.'),
});
export type GenerateDiviElementsOutput = z.infer<typeof GenerateDiviElementsOutputSchema>;

export async function generateDiviElements(input: GenerateDiviElementsInput): Promise<GenerateDiviElementsOutput> {
  return generateDiviElementsFlow(input);
}

const generateDiviElementsPrompt = ai.definePrompt({
  name: 'generateDiviElementsPrompt',
  input: {schema: GenerateDiviElementsInputSchema},
  output: {schema: GenerateDiviElementsOutputSchema},
  prompt: `You are a WordPress plugin that helps users generate Divi elements from natural language prompts.

  The user will provide a prompt describing the desired Divi elements.
  You will translate the prompt into Divi Builder-compatible instructions.

  Example Prompt: Add a hero section with a blue background, white heading saying 'Welcome to Our Company', and a call-to-action button

  Output:
  `,
});

const generateDiviElementsFlow = ai.defineFlow(
  {
    name: 'generateDiviElementsFlow',
    inputSchema: GenerateDiviElementsInputSchema,
    outputSchema: GenerateDiviElementsOutputSchema,
  },
  async input => {
    const {output} = await generateDiviElementsPrompt(input);
    return output!;
  }
);
