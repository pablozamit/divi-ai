'use client';

import { Accordion, AccordionContent, AccordionItem, AccordionTrigger } from "@/components/ui/accordion";
import { Lightbulb } from "lucide-react";
import { Button } from "./ui/button";

const examples = [
  "Add a contact form with name, email, and message fields",
  "Create a three-column services section with icons",
  "Change all buttons to red with rounded corners",
  "Add a testimonials carousel with customer photos",
  "Create a landing page for a restaurant with header, hero section, menu showcase, testimonials, and contact form",
];

interface PromptExamplesProps {
    onExampleSelect: (prompt: string) => void;
}

export default function PromptExamples({ onExampleSelect }: PromptExamplesProps) {
  return (
    <Accordion type="single" collapsible className="w-full">
      <AccordionItem value="item-1">
        <AccordionTrigger>
            <div className="flex items-center gap-2 text-sm">
                <Lightbulb className="h-4 w-4" />
                Prompt Examples
            </div>
        </AccordionTrigger>
        <AccordionContent>
          <div className="space-y-2 pt-2">
            {examples.map((example, index) => (
              <Button 
                key={index} 
                variant="ghost" 
                size="sm"
                className="w-full h-auto text-left justify-start text-muted-foreground hover:text-foreground"
                onClick={() => onExampleSelect(example)}
              >
                <p className="whitespace-normal">{example}</p>
              </Button>
            ))}
          </div>
        </AccordionContent>
      </AccordionItem>
    </Accordion>
  );
}
