import { useEffect } from "react";
import { C } from "../../constants/theme";
import { Body } from "../../components/ui";
import { goToAppAuth } from "../../constants/appAuth";

export function AppAuthRedirect() {
  useEffect(() => { goToAppAuth(); }, []);
  return (
    <section style={{ minHeight:"calc(100vh - 60px)", display:"flex", alignItems:"center", justifyContent:"center", background:C.bg }}>
      <Body muted center>Redirecting to EasyGrox sign in…</Body>
    </section>
  );
}
