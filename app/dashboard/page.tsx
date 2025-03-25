"use client"

import { useState, useEffect } from "react"
import Navbar from "@/components/navbar"
import { Button } from "@/components/ui/button"
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card"
import { Search, Upload, FileText, Clock, User, AlertCircle, Info } from "lucide-react"
import Link from "next/link"
import { callPhpApi } from "@/lib/php-integration"
import { USE_REAL_API } from "@/lib/config"
import type { AccessLog, CaseStats } from "@/lib/mock-data"

export default function Dashboard() {
  const [recentActivity, setRecentActivity] = useState<AccessLog[]>([])
  const [caseStats, setCaseStats] = useState<CaseStats>({
    total: 0,
    open: 0,
    closed: 0,
    pending: 0,
  })
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)
  const [usingMockData, setUsingMockData] = useState(false)

  useEffect(() => {
    const fetchDashboardData = async () => {
      setLoading(true)
      setError(null)

      try {
        // Fetch recent activity from PHP backend
        const activityData = await callPhpApi("dashboard/recent-activity.php")

        if (activityData && Array.isArray(activityData)) {
          setRecentActivity(activityData)
        } else {
          console.warn("Received non-array activity data")
        }

        // Fetch case statistics from PHP backend
        const statsData = await callPhpApi("dashboard/case-stats.php")

        if (statsData && typeof statsData === "object") {
          setCaseStats(statsData)
        } else {
          console.warn("Received invalid stats data")
        }

        // Check if we're using mock data
        setUsingMockData(!USE_REAL_API)
      } catch (error) {
        console.error("Error fetching dashboard data:", error)
        setError("Failed to load dashboard data. Using demo data instead.")
        setUsingMockData(true)
      } finally {
        setLoading(false)
      }
    }

    fetchDashboardData()
  }, [])

  return (
    <div className="min-h-screen flex flex-col bg-secondary">
      <Navbar />

      <main className="flex-1 container mx-auto px-4 py-8">
        <h1 className="text-3xl font-bold mb-8">Dashboard</h1>

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

        {/* Stats Cards */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
          <Card>
            <CardHeader className="pb-2">
              <CardTitle className="text-2xl flex items-center">
                {loading ? <div className="w-8 h-8 rounded-full bg-primary/10 animate-pulse"></div> : caseStats.total}
              </CardTitle>
              <CardDescription className="flex items-center">
                <FileText className="h-4 w-4 mr-1 text-muted-foreground" />
                Total Cases
              </CardDescription>
            </CardHeader>
          </Card>

          <Card>
            <CardHeader className="pb-2">
              <CardTitle className="text-2xl flex items-center text-green-600">
                {loading ? <div className="w-8 h-8 rounded-full bg-primary/10 animate-pulse"></div> : caseStats.open}
              </CardTitle>
              <CardDescription className="flex items-center">
                <div className="h-2 w-2 rounded-full bg-green-500 mr-1"></div>
                Open Cases
              </CardDescription>
            </CardHeader>
          </Card>

          <Card>
            <CardHeader className="pb-2">
              <CardTitle className="text-2xl flex items-center text-red-600">
                {loading ? <div className="w-8 h-8 rounded-full bg-primary/10 animate-pulse"></div> : caseStats.closed}
              </CardTitle>
              <CardDescription className="flex items-center">
                <div className="h-2 w-2 rounded-full bg-red-500 mr-1"></div>
                Closed Cases
              </CardDescription>
            </CardHeader>
          </Card>

          <Card>
            <CardHeader className="pb-2">
              <CardTitle className="text-2xl flex items-center text-yellow-600">
                {loading ? <div className="w-8 h-8 rounded-full bg-primary/10 animate-pulse"></div> : caseStats.pending}
              </CardTitle>
              <CardDescription className="flex items-center">
                <div className="h-2 w-2 rounded-full bg-yellow-500 mr-1"></div>
                Pending Cases
              </CardDescription>
            </CardHeader>
          </Card>
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
          {/* Recent Activity */}
          <Card className="lg:col-span-1">
            <CardHeader>
              <CardTitle className="flex items-center">
                <Clock className="h-5 w-5 mr-2 text-primary" />
                Recent Activity
              </CardTitle>
              <CardDescription>Latest case access events</CardDescription>
            </CardHeader>
            <CardContent className="p-0">
              {loading ? (
                <div className="p-6 space-y-3">
                  {[...Array(5)].map((_, i) => (
                    <div key={i} className="flex gap-3 items-center">
                      <div className="w-8 h-8 rounded-full bg-primary/10 animate-pulse"></div>
                      <div className="space-y-2 flex-1">
                        <div className="h-4 bg-primary/10 rounded animate-pulse w-3/4"></div>
                        <div className="h-3 bg-primary/10 rounded animate-pulse w-1/2"></div>
                      </div>
                    </div>
                  ))}
                </div>
              ) : recentActivity.length > 0 ? (
                <ul className="divide-y">
                  {recentActivity.map((activity, index) => (
                    <li key={index} className="p-4 hover:bg-muted/50">
                      <div className="flex items-start gap-3">
                        <div className="h-8 w-8 rounded-full bg-primary/10 flex items-center justify-center text-primary">
                          <User className="h-4 w-4" />
                        </div>
                        <div>
                          <p className="text-sm font-medium">
                            <span className="text-primary">{activity.user_name}</span> accessed{" "}
                            <Link href={`/case/${activity.case_number}`} className="text-primary hover:underline">
                              {activity.case_title}
                            </Link>
                          </p>
                          <p className="text-xs text-muted-foreground mt-1">
                            <Clock className="inline-block h-3 w-3 mr-1" />
                            {new Date(activity.accessed_at).toLocaleString()}
                          </p>
                        </div>
                      </div>
                    </li>
                  ))}
                </ul>
              ) : (
                <div className="p-6 text-center">
                  <p className="text-muted-foreground">No recent activity</p>
                </div>
              )}
            </CardContent>
          </Card>

          {/* Quick Actions */}
          <div className="lg:col-span-2 grid grid-cols-1 md:grid-cols-3 gap-6">
            <Card>
              <CardHeader className="pb-2">
                <CardTitle className="text-xl flex items-center">
                  <Search className="h-5 w-5 mr-2 text-primary" />
                  Officer Search
                </CardTitle>
                <CardDescription>Search for officers and view their profiles</CardDescription>
              </CardHeader>
              <CardContent>
                <p className="mb-4 text-sm">Search for officers by name, badge number, or case details.</p>
                <Link href="/search">
                  <Button className="w-full">Go to Search</Button>
                </Link>
              </CardContent>
            </Card>

            <Card>
              <CardHeader className="pb-2">
                <CardTitle className="text-xl flex items-center">
                  <Upload className="h-5 w-5 mr-2 text-primary" />
                  Upload Files
                </CardTitle>
                <CardDescription>Upload case files and documents</CardDescription>
              </CardHeader>
              <CardContent>
                <p className="mb-4 text-sm">Upload Excel files and other documents to the system.</p>
                <Link href="/upload">
                  <Button className="w-full">Go to Upload</Button>
                </Link>
              </CardContent>
            </Card>

            <Card>
              <CardHeader className="pb-2">
                <CardTitle className="text-xl flex items-center">
                  <FileText className="h-5 w-5 mr-2 text-primary" />
                  Recent Cases
                </CardTitle>
                <CardDescription>View recently added cases</CardDescription>
              </CardHeader>
              <CardContent>
                <p className="mb-4 text-sm">Access the most recent case files and documents.</p>
                <Link href="/cases">
                  <Button className="w-full">View Cases</Button>
                </Link>
              </CardContent>
            </Card>
          </div>
        </div>
      </main>
    </div>
  )
}

