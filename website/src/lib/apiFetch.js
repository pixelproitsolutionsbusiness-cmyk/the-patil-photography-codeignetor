const BASE_URL = import.meta.env.BASE_URL || "/";
const RAW_BASE_URL = typeof import.meta.env.VITE_API_URL === "string" ? import.meta.env.VITE_API_URL : "";
const HAS_ABSOLUTE_BASE = /^https?:\/\//i.test(RAW_BASE_URL);
const API_BASE_URL = HAS_ABSOLUTE_BASE ? RAW_BASE_URL.replace(/\/$/, "") : "";
const API_PREFIX = "/api";

// If we are in a subfolder (like /stagging/), we need to prefix the API calls
const SUBFOLDER_PREFIX = BASE_URL.replace(/\/$/, "");

const buildAbsoluteUrl = (path) => {
  if (typeof path !== "string") return path;
  
  // If we have an absolute API URL (like https://api.mysite.com), use it
  if (API_BASE_URL && path.startsWith(`${API_PREFIX}/`)) {
    const trimmedPath = path.replace(/^\/api/, "");
    return `${API_BASE_URL}${trimmedPath}`;
  }

  // If we are in a subfolder and path starts with /api/, prefix it
  if (SUBFOLDER_PREFIX && SUBFOLDER_PREFIX !== "" && path.startsWith(`${API_PREFIX}/`)) {
    return `${SUBFOLDER_PREFIX}${path}`;
  }

  return path;
};

const patchFetch = () => {
  if (typeof window === "undefined") return;
  if (window.__API_FETCH_PATCHED__) return;

  const nativeFetch = window.fetch.bind(window);

  window.fetch = (input, init) => {
    if (typeof input === "string") {
      return nativeFetch(buildAbsoluteUrl(input), init);
    }

    if (input instanceof Request) {
      try {
        const relative = new URL(input.url, window.location.origin);
        if (relative.origin === window.location.origin && relative.pathname.startsWith(`${API_PREFIX}/`)) {
          const rewrittenUrl = buildAbsoluteUrl(relative.pathname + relative.search + relative.hash);
          const clonedRequest = new Request(rewrittenUrl, input);
          return nativeFetch(clonedRequest, init);
        }
      } catch (error) {
        console.warn("[apiFetch] Unable to rewrite Request: ", error);
      }
    }

    return nativeFetch(input, init);
  };

  window.__API_FETCH_PATCHED__ = true;

  if (import.meta.env.DEV) {
    console.info(`[apiFetch] Proxying "/api/*" requests to ${API_BASE_URL}`);
  }
};

export const getImageUrl = (url) => {
  if (!url) return "";
  if (typeof url !== "string") return url;
  if (url.startsWith("http") || url.startsWith("data:")) return url;

  const siteBase = import.meta.env.BASE_URL || "/";
  const apiBase = import.meta.env.VITE_API_URL || "/api/";
  const apiBaseUrl = apiBase.endsWith("/") ? apiBase : `${apiBase}/`;

  // 1. If it's a database upload, use the API base
  if (url.startsWith("uploads/") || url.startsWith("/uploads/")) {
    const cleanPath = url.startsWith("/") ? url.substring(1) : url;
    return `${apiBaseUrl}${cleanPath}`;
  }

  // 2. If it's a static asset (assets/...), use the site base
  if (url.startsWith("assets/") || url.startsWith("/assets/")) {
    const cleanPath = url.startsWith("/") ? url.substring(1) : url;
    return `${siteBase}${cleanPath}`;
  }

  // Fallback
  if (url.startsWith("/")) {
    return `${siteBase}${url.substring(1)}`;
  }
  return `${siteBase}${url}`;
};

patchFetch();

