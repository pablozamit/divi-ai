'use server';

import { generateFullPageLayoutFromPrompt } from "@/ai/flows/create-bulk-page-layout";

export async function processPrompt(prompt: string) {
  try {
    if (!prompt) {
        return { error: 'Prompt cannot be empty.' };
    }
    const result = await generateFullPageLayoutFromPrompt({ prompt });
    return { data: result.diviLayout };

  } catch (error) {
    console.error('Error processing prompt:', error);
    return { error: 'An unexpected error occurred. Please try again later.' };
  }
}
