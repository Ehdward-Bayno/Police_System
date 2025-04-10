"use client"

import { useState, useEffect } from "react"
import Navbar from "@/components/navbar"
import { Button } from "@/components/ui/button"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table"
import { Input } from "@/components/ui/input"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select"
import { FileText, Search, Filter, Clock, AlertCircle, Info } from "lucide-react"
import Link from "next/link"
import { callPhpApi } from "@/lib/php-integration"
import { USE_REAL_API } from "@/lib/config"
import type { Case } from "@/lib/mock-data"

export default function CasesPage() {
  const [cases, setCases] = useState<Case[]>([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)
  const [usingMockData, setUsingMockData] = useState(false)
  const [searchQuery, setSearchQuery] = useState("")
  const [statusFilter, setStatusFilter] = useState<string>("all")
  const [sortBy, setSortBy] = useState<string>("created_at")
  const [sortOrder, setSortOrder] = useState<"asc" | "desc">("desc")

  useEffect(() => {
    const fetchCases = async () => {
      setLoading(true)
      try {
        // Fetch cases from PHP backend
        const data = await callPhpApi("cases/list.php")

        if (data && Array.isArray(data)) {
          setCases(data)
        } else {
          console.warn("Received non-array data from API")
        }

        // Check if we're using mock data
        setUsingMockData(!USE_REAL_API)
      } catch (error) {
        console.error("Error fetching cases:", error)
        setError(
          typeof error === "object" && error !== null && "message" in error
            ? String(error.message)
            : "Failed to load cases from the server. Using demo data instead.",
        )
        setUsingMockData(true)
      } finally {
        setLoading(false)
      }
    }

    fetchCases()
  }, [])

  // Filter and sort cases
  const filteredCases = cases
    .filter((caseItem) => {
      // Apply search filter
      if (searchQuery) {
        const query = searchQuery.toLowerCase()
        return (
          caseItem.case_number.toLowerCase().includes(query) ||
          caseItem.case_title.toLowerCase().includes(query) ||
          (caseItem.officer_name && caseItem.officer_name.toLowerCase().includes(query))
        )
      }
      return true
    })
    .filter((caseItem) => {
      // Apply status filter
      if (statusFilter === "all") return true
      return caseItem.status.toLowerCase() === statusFilter.toLowerCase()
    })
    .sort((a, b) => {
      // Apply sorting
      if (sortBy === "created_at") {
        return sortOrder === "asc"
          ? new Date(a.created_at).getTime() - new Date(b.created_at).getTime()
          : new Date(b.created_at).getTime() - new Date(a.created_at).getTime()
      } else if (sortBy === "last_accessed_at") {
        // Handle cases where last_accessed_at might be null
        if (!a.last_accessed_at) return sortOrder === "asc" ? -1 : 1
        if (!b.last_accessed_at) return sortOrder === "asc" ? 1 : -1

        return sortOrder === "asc"
          ? new Date(a.last_accessed_at).getTime() - new Date(b.last_accessed_at).getTime()
          : new Date(b.last_accessed_at).getTime() - new Date(a.last_accessed_at).getTime()
      } else if (sortBy === "case_number") {
        return sortOrder === "asc"
          ? a.case_number.localeCompare(b.case_number)
          : b.case_number.localeCompare(a.case_number)
      } else {
        // Default sort by case title
        return sortOrder === "asc" ? a.case_title.localeCompare(b.case_title) : b.case_title.localeCompare(a.case_title)
      }
    })

  const toggleSortOrder = () => {
    setSortOrder(sortOrder === "asc" ? "desc" : "asc")
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
          <CardHeader className="pb-3">
            <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
              <CardTitle>Recent Cases</CardTitle>
              <div className="flex flex-col sm:flex-row gap-2">
                <div className="relative">
                  <Search className="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                  <Input
                    type="search"
                    placeholder="Search cases..."
                    className="pl-8 w-full sm:w-[250px]"
                    value={searchQuery}
                    onChange={(e) => setSearchQuery(e.target.value)}
                  />
                </div>
                <Select value={statusFilter} onValueChange={setStatusFilter}>
                  <SelectTrigger className="w-full sm:w-[150px]">
                    <Filter className="h-4 w-4 mr-2" />
                    <SelectValue placeholder="Filter by status" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="all">All Statuses</SelectItem>
                    <SelectItem value="open">Open</SelectItem>
                    <SelectItem value="closed">Closed</SelectItem>
                    <SelectItem value="pending">Pending</SelectItem>
                    <SelectItem value="under review">Under Review</SelectItem>
                  </SelectContent>
                </Select>
              </div>
            </div>
          </CardHeader>
          <CardContent>
            {loading ? (
              <div className="text-center py-8">
                <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-primary mx-auto mb-4"></div>
                <p className="text-muted-foreground">Loading cases...</p>
              </div>
            ) : filteredCases.length > 0 ? (
              <div className="overflow-x-auto">
                <Table>
                  <TableHeader>
                    <TableRow>
                      <TableHead
                        className="cursor-pointer hover:text-primary"
                        onClick={() => {
                          setSortBy("case_number")
                          if (sortBy === "case_number") toggleSortOrder()
                        }}
                      >
                        Case Number {sortBy === "case_number" && (sortOrder === "asc" ? "↑" : "↓")}
                      </TableHead>
                      <TableHead
                        className="cursor-pointer hover:text-primary"
                        onClick={() => {
                          setSortBy("case_title")
                          if (sortBy === "case_title") toggleSortOrder()
                        }}
                      >
                        Case Title {sortBy === "case_title" && (sortOrder === "asc" ? "↑" : "↓")}
                      </TableHead>
                      <TableHead>Officer</TableHead>
                      <TableHead>Status</TableHead>
                      <TableHead
                        className="cursor-pointer hover:text-primary"
                        onClick={() => {
                          setSortBy("created_at")
                          if (sortBy === "created_at") toggleSortOrder()
                        }}
                      >
                        Created {sortBy === "created_at" && (sortOrder === "asc" ? "↑" : "↓")}
                      </TableHead>
                      <TableHead
                        className="cursor-pointer hover:text-primary"
                        onClick={() => {
                          setSortBy("last_accessed_at")
                          if (sortBy === "last_accessed_at") toggleSortOrder()
                        }}
                      >
                        Last Accessed {sortBy === "last_accessed_at" && (sortOrder === "asc" ? "↑" : "↓")}
                      </TableHead>
                      <TableHead className="text-right">Action</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    {filteredCases.map((caseItem) => (
                      <TableRow key={caseItem.id}>
                        <TableCell className="font-medium">{caseItem.case_number}</TableCell>
                        <TableCell>{caseItem.case_title}</TableCell>
                        <TableCell>
                          {caseItem.officer_name ? (
                            <>
                              {caseItem.officer_name}
                              {caseItem.rank && (
                                <span className="text-xs text-muted-foreground block">{caseItem.rank}</span>
                              )}
                            </>
                          ) : (
                            <span className="text-muted-foreground">Not assigned</span>
                          )}
                        </TableCell>
                        <TableCell>
                          <span className={`status-${caseItem.status.toLowerCase()}`}>{caseItem.status}</span>
                        </TableCell>
                        <TableCell>
                          {new Date(caseItem.created_at).toLocaleDateString()}
                          <span className="text-xs text-muted-foreground block">
                            {new Date(caseItem.created_at).toLocaleTimeString()}
                          </span>
                        </TableCell>
                        <TableCell>
                          {caseItem.last_accessed_by ? (
                            <>
                              <span className="font-medium">{caseItem.last_accessed_by}</span>
                              <span className="text-xs text-muted-foreground block">
                                <Clock className="inline-block h-3 w-3 mr-1" />
                                {new Date(caseItem.last_accessed_at || "").toLocaleDateString()}
                              </span>
                            </>
                          ) : (
                            <span className="text-muted-foreground">Not accessed yet</span>
                          )}
                        </TableCell>
                        <TableCell className="text-right">
                          <Button variant="outline" size="sm" asChild>
                            <Link href={`/case/${caseItem.case_number}`}>
                              <FileText className="h-4 w-4 mr-1" />
                              View
                            </Link>
                          </Button>
                        </TableCell>
                      </TableRow>
                    ))}
                  </TableBody>
                </Table>
              </div>
            ) : (
              <div className="text-center py-8">
                <div className="rounded-full bg-muted h-12 w-12 flex items-center justify-center mx-auto mb-4">
                  <FileText className="h-6 w-6 text-muted-foreground" />
                </div>
                <h3 className="text-lg font-medium mb-2">No cases found</h3>
                <p className="text-muted-foreground mb-4">
                  {searchQuery || statusFilter !== "all"
                    ? "Try adjusting your search or filter criteria."
                    : "Start by uploading case files or creating a new case."}
                </p>
                <Button asChild>
                  <Link href="/upload">Upload Files</Link>
                </Button>
              </div>
            )}
          </CardContent>
        </Card>
      </div>
    </div>
  )
}

