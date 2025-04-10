"use client"

import { useState, useEffect } from "react"
import { useParams, useRouter } from "next/navigation"
import Navbar from "@/components/navbar"
import { Button } from "@/components/ui/button"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Textarea } from "@/components/ui/textarea"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select"
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table"
import { RadioGroup, RadioGroupItem } from "@/components/ui/radio-group"
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs"
import { Printer, Save, UserCheck, Clock, AlertCircle, Info } from "lucide-react"
import Link from "next/link"
import { callPhpApi, saveCaseDetails } from "@/lib/php-integration"
import { USE_REAL_API } from "@/lib/config"
import type { Case, Respondent } from "@/lib/mock-data"

export default function CaseDetailPage() {
  const params = useParams()
  const router = useRouter()
  const caseId = params.id as string

  const [caseData, setCaseData] = useState<Case | null>(null)
  const [loading, setLoading] = useState(true)
  const [saving, setSaving] = useState(false)
  const [error, setError] = useState<string | null>(null)
  const [success, setSuccess] = useState<string | null>(null)
  const [usingMockData, setUsingMockData] = useState(false)
  const [approvalStatus, setApprovalStatus] = useState<string | null>(null)
  const [activeTab, setActiveTab] = useState("details")

  useEffect(() => {
    const fetchCaseData = async () => {
      setLoading(true)
      try {
        // Fetch case data from PHP backend
        const data = await callPhpApi(`cases/details.php?id=${encodeURIComponent(caseId)}`)

        if (data) {
          setCaseData(data)
        } else {
          setError("Case not found")
          setTimeout(() => {
            router.push("/search")
          }, 2000)
        }

        // Check if we're using mock data
        setUsingMockData(!USE_REAL_API)
      } catch (error) {
        console.error("Error fetching case data:", error)
        setError("Failed to load case data. Using demo data instead.")
        setUsingMockData(true)
      } finally {
        setLoading(false)
      }
    }

    if (caseId) {
      fetchCaseData()
    }
  }, [caseId, router])

  const handleSave = async () => {
    if (!caseData) return

    setSaving(true)
    setError(null)
    setSuccess(null)

    try {
      // Save case data to PHP backend
      const result = await saveCaseDetails(caseData)

      if (result.success) {
        setSuccess("Case data saved successfully!")
        // Refresh case data
        const updatedData = await callPhpApi(`cases/details.php?id=${encodeURIComponent(caseId)}`)
        if (updatedData) {
          setCaseData(updatedData)
        }
      } else {
        setError(result.message || "Failed to save case data")
      }
    } catch (error) {
      console.error("Error saving case data:", error)
      setError("An error occurred while saving. Please try again.")
    } finally {
      setSaving(false)
    }
  }

  const handlePrint = () => {
    window.print()
  }

  const handleAddRespondent = () => {
    if (!caseData) return

    const newRespondent: Respondent = {
      id: 0, // Will be assigned by the backend
      name: "",
      rank: "Civilian",
      unit: "N/A",
      justification: "Primary Suspect",
      remarks: "",
    }

    setCaseData({
      ...caseData,
      respondents: [...(caseData.respondents || []), newRespondent],
    })
  }

  const handleRespondentChange = (index: number, field: keyof Respondent, value: string) => {
    if (!caseData || !caseData.respondents) return

    const updatedRespondents = [...caseData.respondents]
    updatedRespondents[index] = {
      ...updatedRespondents[index],
      [field]: value,
    }

    setCaseData({
      ...caseData,
      respondents: updatedRespondents,
    })
  }

  const handleRemoveRespondent = (index: number) => {
    if (!caseData || !caseData.respondents) return

    const updatedRespondents = [...caseData.respondents]
    updatedRespondents.splice(index, 1)

    setCaseData({
      ...caseData,
      respondents: updatedRespondents,
    })
  }

  if (loading) {
    return (
      <div className="min-h-screen flex flex-col bg-secondary">
        <Navbar />
        <div className="container mx-auto px-4 py-6 flex-1 flex items-center justify-center">
          <div className="text-center">
            <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-primary mx-auto mb-4"></div>
            <p>Loading case details...</p>
          </div>
        </div>
      </div>
    )
  }

  if (error && !caseData) {
    return (
      <div className="min-h-screen flex flex-col bg-secondary">
        <Navbar />
        <div className="container mx-auto px-4 py-6">
          <div className="bg-destructive/10 text-destructive p-4 rounded-lg flex items-center gap-3 mb-4">
            <AlertCircle className="h-5 w-5" />
            <p>{error}</p>
          </div>
          <Link href="/search" className="text-primary hover:underline">
            Return to Search
          </Link>
        </div>
      </div>
    )
  }

  return (
    <div className="min-h-screen flex flex-col bg-secondary">
      <Navbar />

      <div className="container mx-auto px-4 py-6">
        <Link href="/search" className="text-primary hover:underline mb-4 inline-block">
          Back to Search
        </Link>

        {error && (
          <div className="bg-destructive/10 text-destructive p-4 rounded-lg flex items-center gap-3 mb-4">
            <AlertCircle className="h-5 w-5" />
            <p>{error}</p>
          </div>
        )}

        {success && (
          <div className="bg-green-100 text-green-800 p-4 rounded-lg flex items-center gap-3 mb-4">
            <div className="h-5 w-5 rounded-full bg-green-500 text-white flex items-center justify-center">✓</div>
            <p>{success}</p>
          </div>
        )}

        {usingMockData && !error && (
          <div className="bg-blue-50 text-blue-800 p-4 rounded-lg flex items-center gap-3 mb-4 print:hidden">
            <Info className="h-5 w-5" />
            <p>Using demo data. PHP API connection is disabled or unavailable.</p>
          </div>
        )}

        <div className="bg-white p-6 rounded-lg shadow-sm mb-6">
          <div className="text-center mb-6 print:mb-8">
            <p className="text-sm">Republic of the Philippines</p>
            <h1 className="text-xl font-bold uppercase">National Police Commission</h1>
            <p className="text-sm">IMCI Building, 163 Quezon Avenue</p>
            <p className="text-sm">North Triangle, Diliman, Quezon City</p>
            <p className="text-sm">www.napolcom.gov.ph</p>
            <div className="mt-4">
              <h2 className="text-lg font-bold uppercase">Inspection, Monitoring, and Investigation Service</h2>
              <h3 className="text-base font-bold uppercase">Criminal Profiling Data</h3>
            </div>
          </div>

          <div className="flex justify-end space-x-2 mb-6 print:hidden">
            <Button variant="outline" onClick={handlePrint}>
              <Printer className="h-4 w-4 mr-2" />
              Print Form
            </Button>
            <Button onClick={handleSave} disabled={saving}>
              <Save className="h-4 w-4 mr-2" />
              {saving ? "Saving..." : "Save Case"}
            </Button>
          </div>

          <Tabs defaultValue="details" className="print:hidden" value={activeTab} onValueChange={setActiveTab}>
            <TabsList className="mb-4">
              <TabsTrigger value="details">Case Details</TabsTrigger>
              <TabsTrigger value="access-history">
                <UserCheck className="h-4 w-4 mr-2" />
                Access History
              </TabsTrigger>
            </TabsList>

            <TabsContent value="access-history">
              <Card>
                <CardHeader>
                  <CardTitle className="text-lg flex items-center">
                    <Clock className="h-5 w-5 mr-2 text-muted-foreground" />
                    Access History
                  </CardTitle>
                </CardHeader>
                <CardContent>
                  {caseData?.access_logs && caseData.access_logs.length > 0 ? (
                    <Table>
                      <TableHeader>
                        <TableRow>
                          <TableHead>User</TableHead>
                          <TableHead>Badge Number</TableHead>
                          <TableHead>Accessed On</TableHead>
                        </TableRow>
                      </TableHeader>
                      <TableBody>
                        {caseData.access_logs.map((log, index) => (
                          <TableRow key={index}>
                            <TableCell>{log.name || log.user_name}</TableCell>
                            <TableCell>{log.badge_number}</TableCell>
                            <TableCell>
                              {new Date(log.accessed_at).toLocaleDateString()}{" "}
                              {new Date(log.accessed_at).toLocaleTimeString()}
                            </TableCell>
                          </TableRow>
                        ))}
                      </TableBody>
                    </Table>
                  ) : (
                    <p className="text-center py-4 text-muted-foreground">No access history available.</p>
                  )}
                </CardContent>
              </Card>
            </TabsContent>

            <TabsContent value="details">
              <div className="mb-6">
                <div className="flex flex-col md:flex-row gap-4 mb-4">
                  <div className="flex-1">
                    <Label htmlFor="case-number" className="text-muted-foreground text-sm">
                      Case Number
                    </Label>
                    <div id="case-number" className="font-medium">
                      {caseData?.case_number}
                    </div>
                  </div>
                  <div className="flex-1">
                    <Label htmlFor="case-title" className="text-muted-foreground text-sm">
                      Case Title
                    </Label>
                    <div id="case-title" className="font-medium">
                      {caseData?.case_title}
                    </div>
                  </div>
                  <div className="w-40">
                    <Label htmlFor="status" className="text-muted-foreground text-sm">
                      Status
                    </Label>
                    <Select
                      value={caseData?.status || "Open"}
                      onValueChange={(value) => caseData && setCaseData({ ...caseData, status: value })}
                    >
                      <SelectTrigger id="status">
                        <SelectValue placeholder="Select status" />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem value="Open">Open</SelectItem>
                        <SelectItem value="Closed">Closed</SelectItem>
                        <SelectItem value="Pending">Pending</SelectItem>
                        <SelectItem value="Under Review">Under Review</SelectItem>
                      </SelectContent>
                    </Select>
                  </div>
                </div>

                <Table>
                  <TableHeader>
                    <TableRow>
                      <TableHead className="w-1/6">Case No.</TableHead>
                      <TableHead className="w-1/6">Respondent</TableHead>
                      <TableHead className="w-1/6">Rank</TableHead>
                      <TableHead className="w-1/6">Unit</TableHead>
                      <TableHead className="w-1/6">Justification of Offense</TableHead>
                      <TableHead className="w-1/6">Remarks</TableHead>
                      <TableHead className="w-10 print:hidden"></TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    {caseData?.respondents && caseData.respondents.length > 0 ? (
                      caseData.respondents.map((respondent, index) => (
                        <TableRow key={index}>
                          <TableCell>{caseData.case_number}</TableCell>
                          <TableCell>
                            <Input
                              value={respondent.name}
                              onChange={(e) => handleRespondentChange(index, "name", e.target.value)}
                              className="h-8 min-h-8"
                            />
                          </TableCell>
                          <TableCell>
                            <Select
                              value={respondent.rank}
                              onValueChange={(value) => handleRespondentChange(index, "rank", value)}
                            >
                              <SelectTrigger className="h-8">
                                <SelectValue />
                              </SelectTrigger>
                              <SelectContent>
                                <SelectItem value="Officer">Officer</SelectItem>
                                <SelectItem value="Sergeant">Sergeant</SelectItem>
                                <SelectItem value="Lieutenant">Lieutenant</SelectItem>
                                <SelectItem value="Captain">Captain</SelectItem>
                                <SelectItem value="Inspector">Inspector</SelectItem>
                                <SelectItem value="Civilian">Civilian</SelectItem>
                              </SelectContent>
                            </Select>
                          </TableCell>
                          <TableCell>
                            <Select
                              value={respondent.unit}
                              onValueChange={(value) => handleRespondentChange(index, "unit", value)}
                            >
                              <SelectTrigger className="h-8">
                                <SelectValue />
                              </SelectTrigger>
                              <SelectContent>
                                <SelectItem value="Headquarters">Headquarters</SelectItem>
                                <SelectItem value="Patrol">Patrol</SelectItem>
                                <SelectItem value="Investigation">Investigation</SelectItem>
                                <SelectItem value="N/A">N/A</SelectItem>
                              </SelectContent>
                            </Select>
                          </TableCell>
                          <TableCell>
                            <Select
                              value={respondent.justification}
                              onValueChange={(value) => handleRespondentChange(index, "justification", value)}
                            >
                              <SelectTrigger className="h-8">
                                <SelectValue />
                              </SelectTrigger>
                              <SelectContent>
                                <SelectItem value="Primary Suspect">Primary Suspect</SelectItem>
                                <SelectItem value="Accomplice">Accomplice</SelectItem>
                                <SelectItem value="Witness">Witness</SelectItem>
                              </SelectContent>
                            </Select>
                          </TableCell>
                          <TableCell>
                            <Input
                              value={respondent.remarks}
                              onChange={(e) => handleRespondentChange(index, "remarks", e.target.value)}
                              className="h-8 min-h-8"
                            />
                          </TableCell>
                          <TableCell className="print:hidden">
                            <Button
                              variant="ghost"
                              size="sm"
                              className="h-8 w-8 p-0 text-muted-foreground hover:text-destructive"
                              onClick={() => handleRemoveRespondent(index)}
                            >
                              ✕
                            </Button>
                          </TableCell>
                        </TableRow>
                      ))
                    ) : (
                      <TableRow>
                        <TableCell colSpan={7} className="text-center py-4 text-muted-foreground">
                          No respondents added yet. Click "Add Respondent" to add one.
                        </TableCell>
                      </TableRow>
                    )}
                    <TableRow className="print:hidden">
                      <TableCell colSpan={7}>
                        <Button
                          variant="ghost"
                          size="sm"
                          className="text-primary hover:bg-primary/10"
                          onClick={handleAddRespondent}
                        >
                          + Add Respondent
                        </Button>
                      </TableCell>
                    </TableRow>
                  </TableBody>
                </Table>
              </div>

              <div className="mb-6">
                <Card>
                  <CardHeader>
                    <CardTitle>Complainant Information</CardTitle>
                  </CardHeader>
                  <CardContent>
                    <Textarea
                      value={caseData?.description || ""}
                      onChange={(e) => caseData && setCaseData({ ...caseData, description: e.target.value })}
                      rows={3}
                      placeholder="Enter complainant details including name, contact information, address, and statement"
                    />
                  </CardContent>
                </Card>
              </div>

              <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <Card>
                  <CardHeader>
                    <CardTitle className="text-center">Conducted by:</CardTitle>
                  </CardHeader>
                  <CardContent className="text-center">
                    <div className="border-b border-black pb-2 mb-2 mx-auto w-64">
                      <Input defaultValue="Michael Joy E. Eden" className="text-center border-none" />
                    </div>
                    <Input
                      defaultValue="Evaluator/In-Charge Investigator"
                      className="text-center border-none text-sm"
                    />
                    <div className="mt-4">
                      <Label className="block mb-1">Date:</Label>
                      <Input type="date" className="w-40 mx-auto" />
                    </div>
                  </CardContent>
                </Card>

                <Card>
                  <CardHeader>
                    <CardTitle className="text-center">Recommending Approval:</CardTitle>
                  </CardHeader>
                  <CardContent className="text-center">
                    <div className="border-b border-black pb-2 mb-2 mx-auto w-64">
                      <Input defaultValue="Chrysostom C. Onidoan" className="text-center border-none" />
                    </div>
                    <Input
                      defaultValue="Acting Chief, Investigation Division"
                      className="text-center border-none text-sm"
                    />
                    <div className="mt-4">
                      <Label className="block mb-1">Date:</Label>
                      <Input type="date" className="w-40 mx-auto" />
                    </div>
                  </CardContent>
                </Card>

                <Card>
                  <CardHeader>
                    <CardTitle className="text-center">Approved by:</CardTitle>
                  </CardHeader>
                  <CardContent className="text-center">
                    <div className="border-b border-black pb-2 mb-2 mx-auto w-64">
                      <Input defaultValue="Dr. Edman P. Pares" className="text-center border-none" />
                    </div>
                    <Input defaultValue="Staff Service Chief, IMS" className="text-center border-none text-sm" />
                    <div className="mt-4">
                      <RadioGroup
                        value={approvalStatus || ""}
                        onValueChange={setApprovalStatus}
                        className="flex justify-center space-x-4"
                      >
                        <div className="flex items-center space-x-1">
                          <RadioGroupItem value="approved" id="approved" />
                          <Label htmlFor="approved">Approved</Label>
                        </div>
                        <div className="flex items-center space-x-1">
                          <RadioGroupItem value="disapproved" id="disapproved" />
                          <Label htmlFor="disapproved">Disapproved</Label>
                        </div>
                      </RadioGroup>
                    </div>
                  </CardContent>
                </Card>
              </div>
            </TabsContent>
          </Tabs>

          {/* Print version - always visible in print mode */}
          <div className="hidden print:block">
            <div className="mb-6">
              <div className="flex flex-col md:flex-row gap-4 mb-4">
                <div className="flex-1">
                  <p className="text-sm text-gray-500">Case Number</p>
                  <p className="font-medium">{caseData?.case_number}</p>
                </div>
                <div className="flex-1">
                  <p className="text-sm text-gray-500">Case Title</p>
                  <p className="font-medium">{caseData?.case_title}</p>
                </div>
                <div className="w-40">
                  <p className="text-sm text-gray-500">Status</p>
                  <p className="font-medium">{caseData?.status}</p>
                </div>
              </div>

              <table className="w-full border-collapse mb-6">
                <thead>
                  <tr>
                    <th className="border border-gray-300 px-3 py-2 text-left">Case No.</th>
                    <th className="border border-gray-300 px-3 py-2 text-left">Respondent</th>
                    <th className="border border-gray-300 px-3 py-2 text-left">Rank</th>
                    <th className="border border-gray-300 px-3 py-2 text-left">Unit</th>
                    <th className="border border-gray-300 px-3 py-2 text-left">Justification</th>
                    <th className="border border-gray-300 px-3 py-2 text-left">Remarks</th>
                  </tr>
                </thead>
                <tbody>
                  {caseData?.respondents && caseData.respondents.length > 0 ? (
                    caseData.respondents.map((respondent, index) => (
                      <tr key={index}>
                        <td className="border border-gray-300 px-3 py-2">{caseData.case_number}</td>
                        <td className="border border-gray-300 px-3 py-2">{respondent.name}</td>
                        <td className="border border-gray-300 px-3 py-2">{respondent.rank}</td>
                        <td className="border border-gray-300 px-3 py-2">{respondent.unit}</td>
                        <td className="border border-gray-300 px-3 py-2">{respondent.justification}</td>
                        <td className="border border-gray-300 px-3 py-2">{respondent.remarks}</td>
                      </tr>
                    ))
                  ) : (
                    <tr>
                      <td colSpan={6} className="border border-gray-300 px-3 py-2 text-center">
                        No respondents
                      </td>
                    </tr>
                  )}
                </tbody>
              </table>
            </div>

            <div className="mb-6">
              <h3 className="font-bold mb-2">Complainant Information</h3>
              <div className="border border-gray-300 p-3 rounded">
                {caseData?.description || "No complainant information provided."}
              </div>
            </div>

            <div className="grid grid-cols-3 gap-6 mb-6">
              <div>
                <h3 className="font-bold text-center mb-2">Conducted by:</h3>
                <div className="text-center">
                  <div className="border-b border-black pb-2 mb-2 mx-auto">Michael Joy E. Eden</div>
                  <div className="text-sm">Evaluator/In-Charge Investigator</div>
                  <div className="mt-4">
                    <p className="mb-1">Date: _______________</p>
                  </div>
                </div>
              </div>

              <div>
                <h3 className="font-bold text-center mb-2">Recommending Approval:</h3>
                <div className="text-center">
                  <div className="border-b border-black pb-2 mb-2 mx-auto">Chrysostom C. Onidoan</div>
                  <div className="text-sm">Acting Chief, Investigation Division</div>
                  <div className="mt-4">
                    <p className="mb-1">Date: _______________</p>
                  </div>
                </div>
              </div>

              <div>
                <h3 className="font-bold text-center mb-2">Approved by:</h3>
                <div className="text-center">
                  <div className="border-b border-black pb-2 mb-2 mx-auto">Dr. Edman P. Pares</div>
                  <div className="text-sm">Staff Service Chief, IMS</div>
                  <div className="mt-4">
                    <div className="flex justify-center space-x-4">
                      <div className="flex items-center space-x-1">
                        <input type="checkbox" checked={approvalStatus === "approved"} readOnly />
                        <span>Approved</span>
                      </div>
                      <div className="flex items-center space-x-1">
                        <input type="checkbox" checked={approvalStatus === "disapproved"} readOnly />
                        <span>Disapproved</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  )
}

