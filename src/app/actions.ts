'use server';

import { generateFullPageLayoutFromPrompt } from "@/ai/flows/create-bulk-page-layout";
import { promises as fs } from 'fs';
import path from 'path';

const API_KEY_FILE = path.resolve(process.cwd(), 'api-key.txt');

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

export async function loadApiKey() {
  try {
    const key = await fs.readFile(API_KEY_FILE, 'utf8');
    return { data: key.trim() };
  } catch (error) {
    // File may not exist or reading failed
    return { data: '' };
  }
}

export async function saveApiKey(apiKey: string) {
  try {
    await fs.writeFile(API_KEY_FILE, apiKey, 'utf8');
    return { success: true };
  } catch (error) {
    console.error('Error saving API key:', error);
    return { error: 'Failed to save API key.' };
  }
}
