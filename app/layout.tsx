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
