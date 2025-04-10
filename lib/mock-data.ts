// Mock data for the application when PHP API is not available

import { MOCK_DATA_DELAY } from "./config"

// Types
export interface AccessLog {
  accessed_at: string
  user_name: string
  case_number: string
  case_title: string
}

export interface CaseStats {
  total: number
  open: number
  closed: number
  pending: number
}

export interface Case {
  id: number
  case_number: string
  case_title: string
  officer_name: string
  rank: string
  status: string
  description?: string
  created_at: string
  last_accessed_by?: string
  last_accessed_at?: string
  respondents?: Respondent[]
  access_logs?: AccessLog[]
}

export interface Respondent {
  id: number
  name: string
  rank: string
  unit: string
  justification: string
  remarks: string
}

// Mock data
export const mockRecentActivity: AccessLog[] = [
  {
    accessed_at: new Date().toISOString(),
    user_name: "John Smith",
    case_number: "PD-2023-001",
    case_title: "Downtown Burglary",
  },
  {
    accessed_at: new Date(Date.now() - 3600000).toISOString(), // 1 hour ago
    user_name: "Jane Doe",
    case_number: "PD-2023-015",
    case_title: "Fraud Investigation",
  },
  {
    accessed_at: new Date(Date.now() - 86400000).toISOString(), // 1 day ago
    user_name: "Michael Johnson",
    case_number: "PD-2023-022",
    case_title: "Vehicle Theft",
  },
]

export const mockCaseStats: CaseStats = {
  total: 25,
  open: 12,
  closed: 8,
  pending: 5,
}

export const mockCases: Case[] = [
  {
    id: 1,
    case_number: "PD-2023-001",
    case_title: "Downtown Burglary",
    officer_name: "John Smith",
    rank: "Inspector",
    status: "Open",
    description: "Burglary reported at Main Street store. Multiple items stolen including electronics.",
    created_at: new Date(Date.now() - 7 * 86400000).toISOString(), // 7 days ago
    last_accessed_by: "Jane Doe",
    last_accessed_at: new Date().toISOString(),
    respondents: [
      {
        id: 1,
        name: "Robert Johnson",
        rank: "Civilian",
        unit: "N/A",
        justification: "Primary Suspect",
        remarks: "Prior convictions for similar offenses",
      },
    ],
  },
  {
    id: 2,
    case_number: "PD-2023-015",
    case_title: "Fraud Investigation",
    officer_name: "Jane Doe",
    rank: "Sergeant",
    status: "Closed",
    description: "Credit card fraud affecting multiple victims. Suspect apprehended.",
    created_at: new Date(Date.now() - 14 * 86400000).toISOString(), // 14 days ago
    last_accessed_by: "John Smith",
    last_accessed_at: new Date(Date.now() - 86400000).toISOString(), // 1 day ago
    respondents: [
      {
        id: 2,
        name: "Sarah Williams",
        rank: "Civilian",
        unit: "N/A",
        justification: "Primary Suspect",
        remarks: "Confessed to the crime",
      },
    ],
  },
  {
    id: 3,
    case_number: "PD-2023-022",
    case_title: "Vehicle Theft",
    officer_name: "Michael Johnson",
    rank: "Officer",
    status: "Pending",
    description: "Luxury vehicle stolen from downtown parking garage. Security footage available.",
    created_at: new Date(Date.now() - 3 * 86400000).toISOString(), // 3 days ago
    last_accessed_by: "John Smith",
    last_accessed_at: new Date(Date.now() - 43200000).toISOString(), // 12 hours ago
    respondents: [
      {
        id: 3,
        name: "Unknown Suspect",
        rank: "Civilian",
        unit: "N/A",
        justification: "Primary Suspect",
        remarks: "Identity unknown, investigation ongoing",
      },
    ],
  },
  {
    id: 4,
    case_number: "PD-2023-028",
    case_title: "Assault Case",
    officer_name: "Emily Wilson",
    rank: "Lieutenant",
    status: "Open",
    description: "Assault reported outside nightclub. Victim sustained minor injuries.",
    created_at: new Date(Date.now() - 2 * 86400000).toISOString(), // 2 days ago
    respondents: [
      {
        id: 4,
        name: "James Miller",
        rank: "Civilian",
        unit: "N/A",
        justification: "Primary Suspect",
        remarks: "Identified by witnesses",
      },
    ],
  },
  {
    id: 5,
    case_number: "PD-2023-030",
    case_title: "Vandalism Report",
    officer_name: "David Brown",
    rank: "Officer",
    status: "Open",
    description: "Multiple instances of graffiti reported in central park area.",
    created_at: new Date(Date.now() - 86400000).toISOString(), // 1 day ago
    respondents: [],
  },
]

// Mock API functions
export async function getMockRecentActivity(): Promise<AccessLog[]> {
  await new Promise((resolve) => setTimeout(resolve, MOCK_DATA_DELAY))
  return [...mockRecentActivity]
}

export async function getMockCaseStats(): Promise<CaseStats> {
  await new Promise((resolve) => setTimeout(resolve, MOCK_DATA_DELAY))
  return { ...mockCaseStats }
}

export async function getMockCases(): Promise<Case[]> {
  await new Promise((resolve) => setTimeout(resolve, MOCK_DATA_DELAY))
  return [...mockCases]
}

export async function getMockCaseDetails(caseNumber: string): Promise<Case | null> {
  await new Promise((resolve) => setTimeout(resolve, MOCK_DATA_DELAY))
  const caseItem = mockCases.find((c) => c.case_number === caseNumber)

  if (!caseItem) {
    return null
  }

  // Add access logs if they don't exist
  if (!caseItem.access_logs) {
    caseItem.access_logs = [
      {
        accessed_at: new Date().toISOString(),
        user_name: "Current User",
        case_number: caseItem.case_number,
        case_title: caseItem.case_title,
      },
      {
        accessed_at: new Date(Date.now() - 86400000).toISOString(), // 1 day ago
        user_name: "John Smith",
        case_number: caseItem.case_number,
        case_title: caseItem.case_title,
      },
    ]
  }

  return { ...caseItem }
}

export async function mockSaveCaseDetails(caseData: any): Promise<{ success: boolean; message: string }> {
  await new Promise((resolve) => setTimeout(resolve, MOCK_DATA_DELAY))
  return {
    success: true,
    message: "Case saved successfully (mock)",
  }
}

