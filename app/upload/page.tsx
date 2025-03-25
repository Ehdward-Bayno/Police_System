"use client"

import type React from "react"

import { useState, useRef } from "react"
import Navbar from "@/components/navbar"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Textarea } from "@/components/ui/textarea"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select"
import { Alert, AlertDescription } from "@/components/ui/alert"
import { Upload, X, FileSpreadsheet } from "lucide-react"
import Link from "next/link"
import * as XLSX from "xlsx"

export default function UploadPage() {
  const fileInputRef = useRef<HTMLInputElement>(null)
  const [formData, setFormData] = useState({
    caseNumber: "",
    caseTitle: "",
    officerName: "",
    rank: "",
    caseDescription: "",
    documentType: "",
  })
  const [file, setFile] = useState<File | null>(null)
  const [isDragging, setIsDragging] = useState(false)
  const [isUploading, setIsUploading] = useState(false)
  const [uploadSuccess, setUploadSuccess] = useState(false)
  const [error, setError] = useState("")

  const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) => {
    setFormData({
      ...formData,
      [e.target.name]: e.target.value,
    })
  }

  const handleSelectChange = (name: string, value: string) => {
    setFormData({
      ...formData,
      [name]: value,
    })
  }

  const handleDragOver = (e: React.DragEvent) => {
    e.preventDefault()
    setIsDragging(true)
  }

  const handleDragLeave = () => {
    setIsDragging(false)
  }

  const handleDrop = (e: React.DragEvent) => {
    e.preventDefault()
    setIsDragging(false)

    if (e.dataTransfer.files && e.dataTransfer.files.length > 0) {
      const droppedFile = e.dataTransfer.files[0]
      handleFile(droppedFile)
    }
  }

  const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    if (e.target.files && e.target.files.length > 0) {
      const selectedFile = e.target.files[0]
      handleFile(selectedFile)
    }
  }

  const handleFile = (selectedFile: File) => {
    setFile(selectedFile)

    // If it's an Excel file, try to parse it
    if (selectedFile.name.endsWith(".xlsx") || selectedFile.name.endsWith(".xls")) {
      const reader = new FileReader()
      reader.onload = (e) => {
        try {
          const data = new Uint8Array(e.target?.result as ArrayBuffer)
          const workbook = XLSX.read(data, { type: "array" })
          const firstSheet = workbook.Sheets[workbook.SheetNames[0]]
          const jsonData = XLSX.utils.sheet_to_json(firstSheet)

          if (jsonData.length > 0) {
            const firstRow = jsonData[0] as any

            // Update form with data from Excel
            setFormData({
              caseNumber: firstRow.CaseNumber || firstRow["Case Number"] || formData.caseNumber,
              caseTitle: firstRow.CaseTitle || firstRow["Case Title"] || formData.caseTitle,
              officerName: firstRow.OfficerName || firstRow["Officer Name"] || formData.officerName,
              rank: firstRow.Rank || formData.rank,
              caseDescription: firstRow.Description || firstRow["Case Description"] || formData.caseDescription,
              documentType: formData.documentType,
            })
          }
        } catch (error) {
          console.error("Error parsing Excel file:", error)
        }
      }
      reader.readAsArrayBuffer(selectedFile)
    }
  }

  const removeFile = () => {
    setFile(null)
    if (fileInputRef.current) {
      fileInputRef.current.value = ""
    }
  }

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault()
    setIsUploading(true)
    setError("")

    try {
      // In a real app, you would make an API call to your PHP backend
      // For demo purposes, we'll simulate a successful upload
      await new Promise((resolve) => setTimeout(resolve, 1500))

      setUploadSuccess(true)

      // Reset form after successful upload
      setTimeout(() => {
        setFormData({
          caseNumber: "",
          caseTitle: "",
          officerName: "",
          rank: "",
          caseDescription: "",
          documentType: "",
        })
        setFile(null)
        setUploadSuccess(false)
      }, 3000)
    } catch (err) {
      setError("Upload failed. Please try again.")
    } finally {
      setIsUploading(false)
    }
  }

  return (
    <div className="min-h-screen flex flex-col bg-secondary">
      <Navbar />

      <div className="container mx-auto px-4 py-6">
        <Link href="/dashboard" className="text-primary hover:underline mb-4 inline-block">
          Back to Home
        </Link>

        <Card>
          <CardHeader>
            <CardTitle>Upload Case Files</CardTitle>
          </CardHeader>
          <CardContent>
            {uploadSuccess && (
              <Alert className="mb-4 bg-green-50 text-green-800 border-green-200">
                <AlertDescription>File uploaded successfully!</AlertDescription>
              </Alert>
            )}

            {error && (
              <Alert variant="destructive" className="mb-4">
                <AlertDescription>{error}</AlertDescription>
              </Alert>
            )}

            <form onSubmit={handleSubmit}>
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div className="space-y-2">
                  <Label htmlFor="caseNumber">Case Number *</Label>
                  <Input
                    id="caseNumber"
                    name="caseNumber"
                    placeholder="Enter case number"
                    value={formData.caseNumber}
                    onChange={handleChange}
                    required
                  />
                </div>

                <div className="space-y-2">
                  <Label htmlFor="caseTitle">Case Title *</Label>
                  <Input
                    id="caseTitle"
                    name="caseTitle"
                    placeholder="Enter case title"
                    value={formData.caseTitle}
                    onChange={handleChange}
                    required
                  />
                </div>

                <div className="space-y-2">
                  <Label htmlFor="officerName">Officer Name *</Label>
                  <Input
                    id="officerName"
                    name="officerName"
                    placeholder="Enter officer name"
                    value={formData.officerName}
                    onChange={handleChange}
                    required
                  />
                </div>

                <div className="space-y-2">
                  <Label htmlFor="rank">Rank</Label>
                  <Select value={formData.rank} onValueChange={(value) => handleSelectChange("rank", value)}>
                    <SelectTrigger>
                      <SelectValue placeholder="Select rank" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="Officer">Officer</SelectItem>
                      <SelectItem value="Sergeant">Sergeant</SelectItem>
                      <SelectItem value="Lieutenant">Lieutenant</SelectItem>
                      <SelectItem value="Captain">Captain</SelectItem>
                      <SelectItem value="Inspector">Inspector</SelectItem>
                      <SelectItem value="Chief">Chief</SelectItem>
                    </SelectContent>
                  </Select>
                </div>
              </div>

              <div className="space-y-2 mb-4">
                <Label htmlFor="caseDescription">Case Description</Label>
                <Textarea
                  id="caseDescription"
                  name="caseDescription"
                  placeholder="Enter a brief description of the case"
                  value={formData.caseDescription}
                  onChange={handleChange}
                  rows={4}
                />
              </div>

              <div className="space-y-2 mb-4">
                <Label htmlFor="documentType">Document Type</Label>
                <Select
                  value={formData.documentType}
                  onValueChange={(value) => handleSelectChange("documentType", value)}
                >
                  <SelectTrigger>
                    <SelectValue placeholder="Select document type" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="Case Document">Case Document</SelectItem>
                    <SelectItem value="Evidence">Evidence</SelectItem>
                    <SelectItem value="Report">Report</SelectItem>
                    <SelectItem value="Statement">Statement</SelectItem>
                    <SelectItem value="Other">Other</SelectItem>
                  </SelectContent>
                </Select>
              </div>

              <div className="space-y-2 mb-6">
                <Label>Upload Files *</Label>
                <div
                  className={`border-2 border-dashed rounded-md p-6 text-center ${
                    isDragging ? "border-primary bg-primary/5" : "border-gray-300"
                  }`}
                  onDragOver={handleDragOver}
                  onDragLeave={handleDragLeave}
                  onDrop={handleDrop}
                >
                  {file ? (
                    <div className="flex items-center justify-between bg-secondary p-3 rounded">
                      <div className="flex items-center">
                        <FileSpreadsheet className="h-8 w-8 text-primary mr-2" />
                        <div>
                          <p className="text-sm font-medium">{file.name}</p>
                          <p className="text-xs text-gray-500">{(file.size / 1024).toFixed(2)} KB</p>
                        </div>
                      </div>
                      <Button type="button" variant="ghost" size="sm" onClick={removeFile}>
                        <X className="h-4 w-4" />
                      </Button>
                    </div>
                  ) : (
                    <>
                      <Upload className="h-10 w-10 text-gray-400 mx-auto mb-2" />
                      <p className="text-sm text-gray-600 mb-1">Click to upload or drag and drop</p>
                      <p className="text-xs text-gray-500">PDF, DOC, DOCX, JPG, PNG, XLSX (max 10MB each)</p>
                      <input
                        ref={fileInputRef}
                        type="file"
                        className="hidden"
                        onChange={handleFileChange}
                        accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.xlsx,.xls"
                      />
                      <Button
                        type="button"
                        variant="outline"
                        size="sm"
                        className="mt-4"
                        onClick={() => fileInputRef.current?.click()}
                      >
                        Select File
                      </Button>
                    </>
                  )}
                </div>
              </div>

              <div className="flex justify-end space-x-2">
                <Button
                  type="button"
                  variant="outline"
                  onClick={() => {
                    setFormData({
                      caseNumber: "",
                      caseTitle: "",
                      officerName: "",
                      rank: "",
                      caseDescription: "",
                      documentType: "",
                    })
                    setFile(null)
                  }}
                >
                  Cancel
                </Button>
                <Button type="submit" disabled={isUploading || !formData.caseNumber || !formData.caseTitle || !file}>
                  {isUploading ? "Uploading..." : "Upload Files"}
                </Button>
              </div>
            </form>
          </CardContent>
        </Card>
      </div>
    </div>
  )
}

