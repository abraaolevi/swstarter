import { QueryClient, QueryClientProvider } from "@tanstack/react-query"
import { StrictMode } from "react"
import { createRoot } from "react-dom/client"
import { BrowserRouter } from "react-router-dom"
import App from "./App.tsx"
import "./index.css"

const ONE_MINUTE_IN_MS = 60 * 1000;

const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      gcTime: 10 * ONE_MINUTE_IN_MS,
      staleTime: 5 * ONE_MINUTE_IN_MS,
      retry: 2,
      refetchOnWindowFocus: false,
      refetchOnReconnect: false,
      refetchOnMount: false,
    },
  },
});

createRoot(document.getElementById("root")!).render(
  <StrictMode>
    <QueryClientProvider client={queryClient}>
      <BrowserRouter>
          <App />
      </BrowserRouter>
    </QueryClientProvider>
  </StrictMode>
);
