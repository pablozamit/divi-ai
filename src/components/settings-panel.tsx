'use client';

import { useEffect, useState } from "react";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { useToast } from "@/hooks/use-toast";

export default function SettingsPanel() {
  const [apiKey, setApiKey] = useState('');
  const [isSaving, setIsSaving] = useState(false);
  const { toast } = useToast();

  useEffect(() => {
    (async () => {
      try {
        const { loadApiKey } = await import('@/app/actions');
        const result = await loadApiKey();
        if (result.data) {
          setApiKey(result.data);
          localStorage.setItem('geminiApiKey', result.data);
          return;
        }
      } catch (e) {
        console.error('Failed to load API key from server', e);
      }
      const stored = localStorage.getItem('geminiApiKey');
      if (stored) setApiKey(stored);
    })();
  }, []);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setIsSaving(true);
    try {
      const { saveApiKey } = await import('@/app/actions');
      const result = await saveApiKey(apiKey);
      setIsSaving(false);

      if (result.error) {
        toast({
          title: 'Error saving API key',
          description: result.error,
          variant: 'destructive',
        });
        return;
      }

      localStorage.setItem('geminiApiKey', apiKey);
      toast({
        title: 'Settings saved',
        description: 'Your API key has been stored.',
      });
    } catch (error) {
      setIsSaving(false);
      toast({
        title: 'Unexpected error',
        description: 'Something went wrong while saving.',
        variant: 'destructive',
      });
    }
  };

  return (
    <div className="p-1">
      <Card>
        <CardHeader>
          <CardTitle>API Configuration</CardTitle>
          <CardDescription>
            Enter your Google Gemini API key to connect your account.
          </CardDescription>
        </CardHeader>
        <form onSubmit={handleSubmit}>
          <CardContent className="space-y-4">
            <div className="space-y-2">
              <Label htmlFor="api-key">Gemini API Key</Label>
              <Input
                id="api-key"
                type="password"
                value={apiKey}
                onChange={(e) => setApiKey(e.target.value)}
                placeholder="*******************"
              />
            </div>
            <Button type="submit" disabled={isSaving || !apiKey}>
              {isSaving ? 'Saving...' : 'Save Settings'}
            </Button>
          </CardContent>
        </form>
      </Card>
    </div>
  );
}
