"use client"

import Link from "next/link"
import { usePathname, useRouter } from "next/navigation"
import { useState, useEffect } from "react"
import { Button } from "@/components/ui/button"
import { Shield, LogOut } from "lucide-react"

export default function Navbar() {
  const pathname = usePathname()
  const router = useRouter()
  const [user, setUser] = useState<{ name?: string; email?: string } | null>(null)

  useEffect(() => {
    // Check if user is logged in
    const userData = localStorage.getItem("user")
    if (userData) {
      setUser(JSON.parse(userData))
    } else {
      router.push("/login")
    }
  }, [router])

  const handleLogout = () => {
    localStorage.removeItem("user")
    router.push("/login")
  }

  return (
    <header className="bg-primary text-white">
      <div className="container mx-auto px-4 py-3">
        <div className="flex items-center justify-between">
          <div className="flex items-center space-x-2">
            <Shield className="h-6 w-6" />
            <div>
              <h1 className="text-xl font-bold">National Police Commission</h1>
              <p className="text-sm">Criminal Profiling System</p>
            </div>
          </div>

          <nav className="hidden md:flex items-center space-x-6">
            <Link href="/dashboard" className={`hover:text-gray-200 ${pathname === "/dashboard" ? "font-bold" : ""}`}>
              Home
            </Link>
            <Link href="/search" className={`hover:text-gray-200 ${pathname === "/search" ? "font-bold" : ""}`}>
              Search
            </Link>
            <Link href="/upload" className={`hover:text-gray-200 ${pathname === "/upload" ? "font-bold" : ""}`}>
              Upload
            </Link>
          </nav>

          <Button
            variant="ghost"
            size="sm"
            className="text-white hover:text-gray-200 hover:bg-primary/80"
            onClick={handleLogout}
          >
            <LogOut className="h-4 w-4 mr-2" />
            Logout
          </Button>
        </div>
      </div>
    </header>
  )
}

