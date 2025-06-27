'use client';

import React, { useState } from 'react';
import { SidebarProvider, Sidebar, SidebarTrigger, SidebarInset, SidebarHeader, SidebarContent, SidebarFooter } from '@/components/ui/sidebar';
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import PromptForm from './prompt-form';
import HistoryPanel from './history-panel';
import SettingsPanel from './settings-panel';
import PreviewWindow from './preview-window';
import { Gem, History, Settings, Wand2 } from 'lucide-react';
import type { HistoryItem } from '@/types';

export default function DiviAiDesigner() {
  const [prompt, setPrompt] = useState('');
  const [generatedCode, setGeneratedCode] = useState('');
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [history, setHistory] = useState<HistoryItem[]>([]);

  const handlePromptSubmit = async (currentPrompt: string) => {
    if (!currentPrompt || isLoading) return;
    
    setIsLoading(true);
    setError(null);
    setGeneratedCode('');

    const { processPrompt } = await import('@/app/actions');
    const result = await processPrompt(currentPrompt);
    
    setIsLoading(false);

    if (result.error) {
      setError(result.error);
    } else {
      setGeneratedCode(result.data || '');
      const newHistoryItem: HistoryItem = {
        id: new Date().toISOString(),
        prompt: currentPrompt,
        timestamp: new Date().toLocaleString(),
      };
      setHistory(prev => [newHistoryItem, ...prev]);
    }
  };

  const handleHistorySelect = (selectedPrompt: string) => {
    setPrompt(selectedPrompt);
  };
  
  return (
    <SidebarProvider>
      <Sidebar>
        <SidebarHeader>
          <div className="flex items-center gap-2">
            <div className="p-2 bg-primary/10 rounded-lg">
              <Gem className="h-6 w-6 text-primary" />
            </div>
            <h1 className="text-lg font-semibold text-foreground font-headline">Divi AI Designer</h1>
          </div>
        </SidebarHeader>
        <SidebarContent>
          <Tabs defaultValue="generate" className="w-full">
            <TabsList className="grid w-full grid-cols-3">
              <TabsTrigger value="generate"><Wand2 className="h-4 w-4 mr-1"/> Generate</TabsTrigger>
              <TabsTrigger value="history"><History className="h-4 w-4 mr-1"/> History</TabsTrigger>
              <TabsTrigger value="settings"><Settings className="h-4 w-4 mr-1"/> Settings</TabsTrigger>
            </TabsList>
            <TabsContent value="generate">
              <PromptForm 
                prompt={prompt} 
                setPrompt={setPrompt} 
                onSubmit={handlePromptSubmit} 
                isLoading={isLoading} 
              />
            </TabsContent>
            <TabsContent value="history">
              <HistoryPanel history={history} onSelect={handleHistorySelect} />
            </TabsContent>
            <TabsContent value="settings">
              <SettingsPanel />
            </TabsContent>
          </Tabs>
        </SidebarContent>
        <SidebarFooter>
        </SidebarFooter>
      </Sidebar>
      <SidebarInset>
        <header className="flex items-center justify-between p-2 border-b sticky top-0 bg-background/80 backdrop-blur-sm z-10">
          <div className="flex items-center gap-2">
            <SidebarTrigger />
            <h2 className="text-md font-semibold font-headline">Divi Page Preview</h2>
          </div>
        </header>
        <main className="p-4">
          <PreviewWindow generatedCode={generatedCode} isLoading={isLoading} error={error} />
        </main>
      </SidebarInset>
    </SidebarProvider>
  );
}
