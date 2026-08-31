export const API_URL =
  process.env.NEXT_PUBLIC_API_URL ||
  "https://banglesmart-api.onrender.com/api";

export const BACKEND_URL =
  process.env.NEXT_PUBLIC_BACKEND_URL ||
  "https://banglesmart-api.onrender.com";


export async function apiFetch(
  endpoint: string,
  options: RequestInit = {}
) {
  const token =
    typeof window !== "undefined"
      ? localStorage.getItem(
          "admin_token"
        )
      : null;

  const headers =
    new Headers(options.headers);

  headers.set(
    "Accept",
    "application/json"
  );

  /*
  |--------------------------------------------------------------------------
  | Only set JSON Content-Type when body is NOT FormData
  |--------------------------------------------------------------------------
  */

  if (
    options.body &&
    !(options.body instanceof FormData)
  ) {
    headers.set(
      "Content-Type",
      "application/json"
    );
  }

  /*
  |--------------------------------------------------------------------------
  | Authorization code
  |--------------------------------------------------------------------------
  */

  if (token) {
    headers.set(
      "Authorization",
      `Bearer ${token}`
    );
  }

  return fetch(
    `${API_URL}${endpoint}`,
    {
      ...options,
      headers,
    }
  );
} 