"use client";

import { ReactNode, useEffect, useState } from "react";
import { useRouter } from "next/navigation";

export default function AdminAuthGuard({
  children,
}: {
  children: ReactNode;
}) {
  const router = useRouter();

  const [loading, setLoading] = useState(true);
  const [authenticated, setAuthenticated] =
    useState(false);

  useEffect(() => {
    async function checkAdmin() {

      const token = localStorage.getItem(
        "admin_token"
      );

      if (!token) {
        router.replace("/admin/login");
        return;
      }

      try {

        const response = await fetch(
          "http://127.0.0.1:8000/api/admin/me",
          {
            headers: {
              Accept: "application/json",
              Authorization: `Bearer ${token}`,
            },
          }
        );

        if (!response.ok) {

          localStorage.removeItem(
            "admin_token"
          );

          localStorage.removeItem(
            "admin_user"
          );

          router.replace("/admin/login");

          return;
        }

        setAuthenticated(true);

      } catch {

        router.replace("/admin/login");

      } finally {

        setLoading(false);

      }
    }

    checkAdmin();

  }, [router]);


  if (loading) {
    return (
      <div className="flex min-h-screen items-center justify-center">
        Loading...
      </div>
    );
  }


  if (!authenticated) {
    return null;
  }


  return children;
}