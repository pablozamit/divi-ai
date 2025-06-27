'use client';
import { Textarea } from "@/components/ui/textarea";
import { Button } from "@/components/ui/button";
import { Loader2, Wand2 } from "lucide-react";
import PromptExamples from "./prompt-examples";

interface PromptFormProps {
  prompt: string;
  setPrompt: (prompt: string) => void;
  onSubmit: (prompt: string) => void;
  isLoading: boolean;
}

export default function PromptForm({ prompt, setPrompt, onSubmit, isLoading }: PromptFormProps) {
  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    onSubmit(prompt);
  };
  
  return (
    <div className="p-1 space-y-4">
      <form onSubmit={handleSubmit} className="space-y-4">
        <Textarea
          placeholder="e.g., Create a landing page for a restaurant with a hero, menu, and contact form..."
          value={prompt}
          onChange={(e) => setPrompt(e.target.value)}
          rows={8}
          className="bg-card"
          disabled={isLoading}
        />
        <Button type="submit" className="w-full" disabled={!prompt || isLoading}>
          {isLoading ? (
            <Loader2 className="mr-2 h-4 w-4 animate-spin" />
          ) : (
            <Wand2 className="mr-2 h-4 w-4" />
          )}
          {isLoading ? 'Weaving with AI...' : 'Execute'}
        </Button>
      </form>
      <PromptExamples onExampleSelect={setPrompt} />
    </div>
  );
}
