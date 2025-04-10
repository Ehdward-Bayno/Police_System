// This file provides utility functions to connect the Next.js frontend with PHP backend

import { USE_REAL_API, PHP_API_URL } from "./config"
import {
  getMockRecentActivity,
  getMockCaseStats,
  getMockCases,
  getMockCaseDetails,
  mockSaveCaseDetails,
} from "./mock-data"

/**
 * Function to make API calls to PHP backend
 * @param endpoint - The PHP endpoint to call
 * @param method - HTTP method (GET, POST, PUT, DELETE)
 * @param data - Data to send to the endpoint
 * @returns Promise with the response data
 */
export async function callPhpApi(endpoint: string, method = "GET", data: any = null) {
  // If we're configured to use mock data, route to the appropriate mock function
  if (!USE_REAL_API) {
    console.log(`Using mock data for: ${endpoint}`)
    return handleMockApiCall(endpoint, data)
  }

  const url = `${PHP_API_URL}/${endpoint}`
  console.log(`Making API call to: ${url}`)

  const options: RequestInit = {
    method,
    headers: {
      "Content-Type": "application/json",
    },
    credentials: "include", // Include cookies for session management
  }

  if (data && (method === "POST" || method === "PUT")) {
    options.body = JSON.stringify(data)
  }

  try {
    console.log(`Fetching with options:`, options)
    const response = await fetch(url, options)

    // Get the response text first
    const responseText = await response.text()

    // Check if the response is valid JSON
    let responseData
    try {
      // Only try to parse as JSON if it looks like JSON
      if (responseText.trim().startsWith("{") || responseText.trim().startsWith("[")) {
        responseData = JSON.parse(responseText)
      } else {
        // Log the non-JSON response for debugging
        console.error(`API returned non-JSON response: ${responseText.substring(0, 200)}...`)
        console.log(`Falling back to mock data for: ${endpoint}`)
        return handleMockApiCall(endpoint, data)
      }
    } catch (parseError) {
      console.error(`Failed to parse JSON response: ${parseError}`)
      console.error(`Response text: ${responseText.substring(0, 200)}...`)
      console.log(`Falling back to mock data for: ${endpoint}`)
      return handleMockApiCall(endpoint, data)
    }

    if (!response.ok) {
      console.error(`API call failed: ${response.status} ${response.statusText}`)
      console.error(`URL: ${url}`)
      console.log(`Falling back to mock data for: ${endpoint}`)
      return handleMockApiCall(endpoint, data)
    }

    console.log(`API response:`, responseData)
    return responseData
  } catch (error) {
    console.error("API call error:", error)
    console.log(`Falling back to mock data for: ${endpoint}`)
    return handleMockApiCall(endpoint, data)
  }
}

/**
 * Function to handle mock API calls
 * @param endpoint - The endpoint being called
 * @param data - Any data passed to the call
 * @returns Mock data for the endpoint
 */
async function handleMockApiCall(endpoint: string, data: any = null) {
  // Route to the appropriate mock function based on the endpoint
  if (endpoint === "dashboard/recent-activity.php") {
    return getMockRecentActivity()
  } else if (endpoint === "dashboard/case-stats.php") {
    return getMockCaseStats()
  } else if (endpoint === "cases/list.php") {
    return getMockCases()
  } else if (endpoint.startsWith("cases/details.php")) {
    // Extract case ID from endpoint
    const urlParams = new URLSearchParams(endpoint.split("?")[1])
    const caseId = urlParams.get("id")
    return getMockCaseDetails(caseId || "")
  } else if (endpoint === "cases/save.php") {
    return mockSaveCaseDetails(data)
  }

  // Default fallback for unhandled endpoints
  console.warn(`No mock handler for endpoint: ${endpoint}`)
  return { message: "Mock data not available for this endpoint" }
}

/**
 * Function to handle file uploads to PHP backend
 * @param endpoint - The PHP endpoint to call
 * @param formData - FormData object containing files and other form data
 * @returns Promise with the response data
 */
export async function uploadFileToPhp(endpoint: string, formData: FormData) {
  // If we're configured to use mock data, return a mock success response
  if (!USE_REAL_API) {
    console.log(`Using mock data for file upload: ${endpoint}`)
    await new Promise((resolve) => setTimeout(resolve, 1000)) // Simulate upload delay
    return {
      success: true,
      message: "File uploaded successfully (mock)",
      file: {
        name: formData.get("file") ? (formData.get("file") as File).name : "mock-file.pdf",
        path: "uploads/mock-file.pdf",
        type: "application/pdf",
        size: 1024 * 1024, // 1MB
      },
    }
  }

  const url = `${PHP_API_URL}/${endpoint}`

  try {
    const response = await fetch(url, {
      method: "POST",
      body: formData,
      credentials: "include", // Include cookies for session management
    })

    // Get the response text first
    const responseText = await response.text()

    // Try to parse as JSON
    try {
      if (responseText.trim().startsWith("{") || responseText.trim().startsWith("[")) {
        return JSON.parse(responseText)
      } else {
        console.error(`File upload returned non-JSON response: ${responseText.substring(0, 200)}...`)
        throw new Error(`File upload returned non-JSON response`)
      }
    } catch (parseError) {
      console.error(`Failed to parse JSON response: ${parseError}`)
      throw new Error(`Invalid JSON response from file upload API`)
    }
  } catch (error) {
    console.error("File upload error:", error)
    throw error
  }
}

/**
 * Function to handle user authentication with PHP backend
 * @param email - User email
 * @param password - User password
 * @returns Promise with the user data
 */
export async function loginUser(email: string, password: string) {
  // If we're configured to use mock data, return a mock success response
  if (!USE_REAL_API) {
    console.log(`Using mock data for login`)
    await new Promise((resolve) => setTimeout(resolve, 1000)) // Simulate login delay
    return {
      success: true,
      message: "Login successful (mock)",
      user: {
        id: 1,
        name: "Demo User",
        email: email,
        badge_number: "PD-12345",
      },
    }
  }

  return callPhpApi("auth/login.php", "POST", { email, password })
}

/**
 * Function to register a new user with PHP backend
 * @param userData - User registration data
 * @returns Promise with the user data
 */
export async function registerUser(userData: any) {
  // If we're configured to use mock data, return a mock success response
  if (!USE_REAL_API) {
    console.log(`Using mock data for registration`)
    await new Promise((resolve) => setTimeout(resolve, 1000)) // Simulate registration delay
    return {
      success: true,
      message: "Registration successful (mock)",
      user: {
        id: 1,
        name: userData.name,
        email: userData.email,
        badge_number: userData.badgeNumber,
      },
    }
  }

  return callPhpApi("auth/register.php", "POST", userData)
}

/**
 * Function to search for officers in the PHP backend
 * @param query - Search query
 * @returns Promise with the search results
 */
export async function searchOfficers(query: string) {
  return callPhpApi(`officers/search.php?q=${encodeURIComponent(query)}`)
}

/**
 * Function to get case details from PHP backend
 * @param caseId - Case ID
 * @returns Promise with the case data
 */
export async function getCaseDetails(caseId: string) {
  return callPhpApi(`cases/details.php?id=${encodeURIComponent(caseId)}`)
}

/**
 * Function to save case details to PHP backend
 * @param caseData - Case data to save
 * @returns Promise with the response data
 */
export async function saveCaseDetails(caseData: any) {
  return callPhpApi("cases/save.php", "POST", caseData)
}

