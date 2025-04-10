"use client"

import { useState } from "react"
import { Button } from "@/components/ui/button"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Switch } from "@/components/ui/switch"
import { PHP_API_URL, USE_REAL_API } from "@/lib/config"
import Navbar from "@/components/navbar"

export default function ApiTestPage() {
  const [testUrl, setTestUrl] = useState<string>(`${PHP_API_URL}/test.php`)
  const [testResult, setTestResult] = useState<string>("")
  const [loading, setLoading] = useState<boolean>(false)
  const [error, setError] = useState<string | null>(null)
  const [showFullResponse, setShowFullResponse] = useState<boolean>(false)

  const testConnection = async () => {
    setLoading(true)
    setError(null)
    setTestResult("")

    try {
      const response = await fetch(testUrl)
      const text = await response.text()

      setTestResult(text)

      // Try to parse as JSON to check if it's valid
      try {
        JSON.parse(text)
        setError(null)
      } catch (e) {
        setError("Response is not valid JSON. This may indicate a PHP configuration issue.")
      }
    } catch (err) {
      setError(err instanceof Error ? err.message : String(err))
    } finally {
      setLoading(false)
    }
  }

  return (
    <div className="min-h-screen flex flex-col bg-secondary">
      <Navbar />

      <div className="container mx-auto p-4">
        <h1 className="text-2xl font-bold mb-4">API Connection Test</h1>

        <Card className="mb-4">
          <CardHeader>
            <CardTitle>PHP API Configuration</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="space-y-4">
              <div>
                <Label htmlFor="api-url">Current API URL</Label>
                <Input id="api-url" value={PHP_API_URL} readOnly className="font-mono text-sm" />
                <p className="text-sm text-muted-foreground mt-1">
                  This is set via the NEXT_PUBLIC_PHP_API_URL environment variable
                </p>
              </div>

              <div>
                <div className="flex items-center space-x-2">
                  <Switch id="use-real-api" checked={USE_REAL_API} disabled />
                  <Label htmlFor="use-real-api">
                    Use Real API
                    {USE_REAL_API ? " (Enabled)" : " (Disabled)"}
                  </Label>
                </div>
                <p className="text-sm text-muted-foreground mt-1">
                  This is controlled by the USE_REAL_API setting in lib/config.ts
                </p>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card className="mb-4">
          <CardHeader>
            <CardTitle>Test API Connection</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="space-y-4">
              <div>
                <Label htmlFor="test-url">Test URL</Label>
                <Input
                  id="test-url"
                  value={testUrl}
                  onChange={(e) => setTestUrl(e.target.value)}
                  className="font-mono text-sm"
                />
              </div>

              <Button onClick={testConnection} disabled={loading}>
                {loading ? "Testing..." : "Test Connection"}
              </Button>

              <div className="flex items-center space-x-2">
                <Switch id="show-full" checked={showFullResponse} onCheckedChange={setShowFullResponse} />
                <Label htmlFor="show-full">Show Full Response</Label>
              </div>
            </div>
          </CardContent>
        </Card>

        {error && (
          <Card className="mb-4 bg-red-50">
            <CardHeader>
              <CardTitle className="text-red-600">Error</CardTitle>
            </CardHeader>
            <CardContent>
              <pre className="bg-red-100 p-4 rounded overflow-auto">{error}</pre>
            </CardContent>
          </Card>
        )}

        {testResult && (
          <Card>
            <CardHeader>
              <CardTitle>Response</CardTitle>
            </CardHeader>
            <CardContent>
              <pre className="bg-gray-100 p-4 rounded overflow-auto">
                {showFullResponse
                  ? testResult
                  : testResult.length > 500
                    ? testResult.substring(0, 500) + "... (truncated, toggle 'Show Full Response' to see all)"
                    : testResult}
              </pre>
            </CardContent>
          </Card>
        )}
      </div>
    </div>
  )
}

