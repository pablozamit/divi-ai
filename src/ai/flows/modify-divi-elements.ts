// src/ai/flows/modify-divi-elements.ts
'use server';
/**
 * @fileOverview Modifies existing Divi elements based on natural language prompts.
 *
 * - modifyDiviElement - A function that modifies a Divi element based on a natural language prompt.
 * - ModifyDiviElementInput - The input type for the modifyDiviElement function.
 * - ModifyDiviElementOutput - The return type for the modifyDiviElement function.
 */

import {ai} from '@/ai/genkit';
import {z} from 'genkit';

const ModifyDiviElementInputSchema = z.object({
  diviElement: z.string().describe('The Divi element to modify, represented as a Divi shortcode.'),
  modificationPrompt: z.string().describe('A natural language prompt describing the desired modifications.'),
});
export type ModifyDiviElementInput = z.infer<typeof ModifyDiviElementInputSchema>;

const ModifyDiviElementOutputSchema = z.object({
  modifiedDiviElement: z.string().describe('The modified Divi element, represented as a Divi shortcode.'),
});
export type ModifyDiviElementOutput = z.infer<typeof ModifyDiviElementOutputSchema>;

export async function modifyDiviElement(input: ModifyDiviElementInput): Promise<ModifyDiviElementOutput> {
  return modifyDiviElementFlow(input);
}

const prompt = ai.definePrompt({
  name: 'modifyDiviElementPrompt',
  input: {schema: ModifyDiviElementInputSchema},
  output: {schema: ModifyDiviElementOutputSchema},
  prompt: `You are an expert Divi designer. You will modify a Divi element based on a natural language prompt.

Here is the Divi element:

{{{diviElement}}}

Here is the modification prompt:

{{{modificationPrompt}}}

Return the modified Divi element as a Divi shortcode.

The modified Divi element should be functionally equivalent to the original element, but with the modifications applied.

Ensure the output is valid Divi shortcode.
`,}
);

const modifyDiviElementFlow = ai.defineFlow(
  {
    name: 'modifyDiviElementFlow',
    inputSchema: ModifyDiviElementInputSchema,
    outputSchema: ModifyDiviElementOutputSchema,
  },
  async input => {
    const {output} = await prompt(input);
    return output!;
  }
);
