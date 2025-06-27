'use server';

import { generateFullPageLayoutFromPrompt } from "@/ai/flows/create-bulk-page-layout";
const WP_URL = process.env.WORDPRESS_URL || 'http://localhost:8080';
const KEY_ENDPOINT = `${WP_URL}/wp-json/gwd/v1/gemini-key`;

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
    const res = await fetch(KEY_ENDPOINT, { cache: 'no-store' });
    if (!res.ok) throw new Error('Request failed');
    const json = await res.json();
    return { data: json.key || '' };
  } catch (error) {
    console.error('Error loading API key:', error);
    return { error: 'Failed to load API key.' };
  }
}

export async function saveApiKey(apiKey: string) {
  try {
    const res = await fetch(KEY_ENDPOINT, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ key: apiKey }),
    });
    if (!res.ok) throw new Error('Request failed');
    return { success: true };
  } catch (error) {
    console.error('Error saving API key:', error);
    return { error: 'Failed to save API key.' };
  }
}
