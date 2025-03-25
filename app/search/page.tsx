"use client"

import type React from "react"

import { useState } from "react"
import Navbar from "@/components/navbar"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table"
import { Search, FileText, AlertCircle, Info } from "lucide-react"
import Link from "next/link"
import { callPhpApi } from "@/lib/php-integration"
import { USE_REAL_API } from "@/lib/config"
import { mockCases } from "@/lib/mock-data"

// Define the search result type
interface SearchResult {
  id: number
  officerName: string
  rank: string
  caseNumber: string
  caseTitle: string
  status: string
}

export default function SearchPage() {
  const [searchQuery, setSearchQuery] = useState("")
  const [searchResults, setSearchResults] = useState<SearchResult[]>([])
  const [isSearching, setIsSearching] = useState(false)
  const [hasSearched, setHasSearched] = useState(false)
  const [error, setError] = useState<string | null>(null)
  const [usingMockData, setUsingMockData] = useState(!USE_REAL_API)

  const handleSearch = async (e: React.FormEvent) => {
    e.preventDefault()
    setIsSearching(true)
    setError(null)

    try {
      if (!USE_REAL_API) {
        // Use mock data for search
        await new Promise((resolve) => setTimeout(resolve, 500)) // Simulate API delay

        if (searchQuery.trim() !== "") {
          const query = searchQuery.toLowerCase()
          const results = mockCases
            .filter(
              (caseItem) =>
                caseItem.case_number.toLowerCase().includes(query) ||
                caseItem.case_title.toLowerCase().includes(query) ||
                (caseItem.officer_name && caseItem.officer_name.toLowerCase().includes(query)),
            )
            .map((caseItem) => ({
              id: caseItem.id,
              officerName: caseItem.officer_name || "Unknown",
              rank: caseItem.rank || "N/A",
              caseNumber: caseItem.case_number,
              caseTitle: caseItem.case_title,
              status: caseItem.status,
            }))

          setSearchResults(results)
        } else {
          setSearchResults([])
        }

        setUsingMockData(true)
      } else {
        // In a real app, you would make an API call to your PHP backend
        const data = await callPhpApi(`officers/search.php?q=${encodeURIComponent(searchQuery)}`)

        if (data && data.results) {
          setSearchResults(data.results)
          setUsingMockData(false)
        } else {
          setSearchResults([])
        }
      }

      setHasSearched(true)
    } catch (error) {
      console.error("Search error:", error)
      setError("Failed to perform search. Using demo data instead.")

      // Fallback to mock data on error
      if (searchQuery.trim() !== "") {
        const query = searchQuery.toLowerCase()
        const results = mockCases
          .filter(
            (caseItem) =>
              caseItem.case_number.toLowerCase().includes(query) ||
              caseItem.case_title.toLowerCase().includes(query) ||
              (caseItem.officer_name && caseItem.officer_name.toLowerCase().includes(query)),
          )
          .map((caseItem) => ({
            id: caseItem.id,
            officerName: caseItem.officer_name || "Unknown",
            rank: caseItem.rank || "N/A",
            caseNumber: caseItem.case_number,
            caseTitle: caseItem.case_title,
            status: caseItem.status,
          }))

        setSearchResults(results)
      }

      setUsingMockData(true)
    } finally {
      setIsSearching(false)
    }
  }

  return (
    <div className="min-h-screen flex flex-col bg-secondary">
      <Navbar />

      <div className="container mx-auto px-4 py-6">
        <Link href="/dashboard" className="text-primary hover:underline mb-4 inline-block">
          Back to Home
        </Link>

        {error && (
          <div className="bg-destructive/10 text-destructive p-4 rounded-lg flex items-center gap-3 mb-4">
            <AlertCircle className="h-5 w-5" />
            <p>{error}</p>
          </div>
        )}

        {usingMockData && !error && (
          <div className="bg-blue-50 text-blue-800 p-4 rounded-lg flex items-center gap-3 mb-4">
            <Info className="h-5 w-5" />
            <p>Using demo data. PHP API connection is disabled or unavailable.</p>
          </div>
        )}

        <Card className="mb-6">
          <CardHeader>
            <CardTitle>Officer Search</CardTitle>
          </CardHeader>
          <CardContent>
            <form onSubmit={handleSearch} className="flex items-center space-x-2">
              <div className="relative flex-1">
                <Search className="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                <Input
                  type="text"
                  placeholder="Search by officer name, case number, or title..."
                  value={searchQuery}
                  onChange={(e) => setSearchQuery(e.target.value)}
                  className="pl-8"
                />
              </div>
              <Button type="submit" disabled={isSearching}>
                {isSearching ? "Searching..." : "Search"}
              </Button>
            </form>
          </CardContent>
        </Card>

        {hasSearched && (
          <Card>
            <CardHeader>
              <CardTitle>Search Results</CardTitle>
            </CardHeader>
            <CardContent>
              {searchResults.length > 0 ? (
                <Table>
                  <TableHeader>
                    <TableRow>
                      <TableHead>Officer Name</TableHead>
                      <TableHead>Rank</TableHead>
                      <TableHead>Case Number</TableHead>
                      <TableHead>Case Title</TableHead>
                      <TableHead>Status</TableHead>
                      <TableHead>Action</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    {searchResults.map((result) => (
                      <TableRow key={`${result.id}-${result.caseNumber}`}>
                        <TableCell>{result.officerName}</TableCell>
                        <TableCell>{result.rank}</TableCell>
                        <TableCell>{result.caseNumber}</TableCell>
                        <TableCell>{result.caseTitle}</TableCell>
                        <TableCell>
                          <span className={`status-${result.status.toLowerCase()}`}>{result.status}</span>
                        </TableCell>
                        <TableCell>
                          <Button variant="outline" size="sm" asChild>
                            <Link href={`/case/${result.caseNumber}`}>
                              <FileText className="h-4 w-4 mr-1" />
                              View
                            </Link>
                          </Button>
                        </TableCell>
                      </TableRow>
                    ))}
                  </TableBody>
                </Table>
              ) : (
                <p className="text-center py-4">No results found. Try a different search term.</p>
              )}
            </CardContent>
          </Card>
        )}
      </div>
    </div>
  )
}

