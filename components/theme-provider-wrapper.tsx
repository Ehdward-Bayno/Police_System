"use client";

import { ThemeProvider } from "@/components/theme-provider"; // Ensure theme-provider.tsx exists!

export function ThemeProviderWrapper({ children }: { children: React.ReactNode }) {
  return <ThemeProvider attribute="class" defaultTheme="light">{children}</ThemeProvider>;
}
