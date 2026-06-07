export function goToAppAuth() {
  if (typeof window === "undefined") return;
  const prefix = window.location.pathname.startsWith("/vellor") ? "/vellor" : "";
  window.location.href = `${window.location.origin}${prefix}/admin/login`;
}

