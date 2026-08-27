import {
  API_URL,
} from "./apiConfig";

export async function storeApiFetch(
  endpoint: string,
  options: RequestInit = {}
) {
  const headers =
    new Headers(options.headers);

  headers.set(
    "Accept",
    "application/json"
  );

  if (
    options.body &&
    !(options.body instanceof FormData)
  ) {
    headers.set(
      "Content-Type",
      "application/json"
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