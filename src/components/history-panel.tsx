'use client';
import React from "react";
import { ScrollArea } from "@/components/ui/scroll-area";
import { Button } from "@/components/ui/button";
import { Separator } from "@/components/ui/separator";
import type { HistoryItem } from "@/types";
import { RotateCw } from "lucide-react";

interface HistoryPanelProps {
  history: HistoryItem[];
  onSelect: (prompt: string) => void;
}

export default function HistoryPanel({ history, onSelect }: HistoryPanelProps) {
  if (history.length === 0) {
    return (
      <div className="text-center text-sm text-muted-foreground p-8">
        Your prompt history will appear here.
      </div>
    );
  }
  
  return (
    <ScrollArea className="h-[30rem]">
      <div className="p-1 space-y-2">
        {history.map((item, index) => (
          <React.Fragment key={item.id}>
            <div className="p-2 space-y-2 rounded-md hover:bg-accent/50">
              <p className="text-sm font-medium leading-none truncate">{item.prompt}</p>
              <p className="text-xs text-muted-foreground">{item.timestamp}</p>
              <Button 
                variant="outline" 
                size="sm" 
                className="mt-2"
                onClick={() => onSelect(item.prompt)}
              >
                <RotateCw className="mr-2 h-3 w-3" />
                Apply Again
              </Button>
            </div>
            {index < history.length - 1 && <Separator />}
          </React.Fragment>
        ))}
      </div>
    </ScrollArea>
  );
}
