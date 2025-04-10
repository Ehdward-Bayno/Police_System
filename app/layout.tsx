<<<<<<< HEAD
import type React from "react"
import type { Metadata } from "next"
import { Inter } from "next/font/google"
import "./globals.css"
import { ThemeProvider } from "@/components/theme-provider"

const inter = Inter({ subsets: ["latin"] })

export const metadata: Metadata = {
  title: "National Police Commission - Criminal Profiling System",
  description: "Criminal Profiling System for the National Police Commission",
    generator: 'v0.dev'
}

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode
}>) {
  return (
    <html lang="en">
      <body className={inter.className}>
        <ThemeProvider attribute="class" defaultTheme="light">
          {children}
        </ThemeProvider>
      </body>
    </html>
  )
}



import './globals.css'
=======
"use client"; // Ensure this is treated as a client component

import { Inter } from "next/font/google";
import "./globals.css";
import { ThemeProviderWrapper } from "@/components/theme-provider-wrapper";

const inter = Inter({ subsets: ["latin"] });

// Ensure only ONE default export exists
export default function RootLayout({ children }: { children: React.ReactNode }) {
  return (
    <html lang="en">
      <body className={inter.className}>
        <ThemeProviderWrapper>{children}</ThemeProviderWrapper>
      </body>
    </html>
  );
}

// Client wrapper to ensure theme updates correctly
const ClientWrapper = ({ children }: { children: React.ReactNode }) => {
  const [mounted, setMounted] = useState(false);

  useEffect(() => {
    setMounted(true);
    document.documentElement.className = "light"; // Ensure class is set properly
    document.documentElement.style.colorScheme = "light";
  }, []);

  if (!mounted) return <div />; // Prevents hydration mismatch

  return (
    <ThemeProvider attribute="class" defaultTheme="light">
      {children}
    </ThemeProvider>
  );
};
>>>>>>> fb3bf7cf9b3167aad1cfc0ab7d9b91837188eb8b
