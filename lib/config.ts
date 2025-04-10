// Application configuration

// Set this to false to disable real API calls and use mock data instead
export const USE_REAL_API = false

// PHP API URL - only used if USE_REAL_API is true
export const PHP_API_URL = process.env.NEXT_PUBLIC_PHP_API_URL || "http://localhost/police_system/api"

// Mock data delay in milliseconds (to simulate API latency)
export const MOCK_DATA_DELAY = 500

