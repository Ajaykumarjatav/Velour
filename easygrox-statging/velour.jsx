import { useState, useEffect, useCallback, cloneElement } from "react";

/* ═══════════════════════════════════════════════════════════════
   EasyGrox · Business management, elevated
   Fully responsive: mobile → tablet → desktop
   Design tokens from DESIGN.md
   Font: Plus Jakarta Sans

   SOURCE OF TRUTH — edit this file, then run: npm run sync
   Module map (auto-generated under src/):
     constants/theme.js          → C, F, R
     hooks/useWindowWidth.js     → useW
     components/icons/           → I, Ic
     components/ui/              → Chip, Label, H1–H4, Body, Btn, Card, …
     components/mockups/         → DashMock, BookingMock
     components/layout/          → Nav, Footer, FeaturePageShell
     components/shared/          → StatBadge, PainGain, mocks, Quote, …
     pages/                      → Home, Pricing, Signup, Login, SimplePage, …
     pages/features/             → AppointmentsPage … WebsitePage
     App.jsx                     → App (from export default below)
═══════════════════════════════════════════════════════════════ */

const C = {
  bg:"#f6faf9", surface:"#ffffff", low:"#f0f4f3", mid:"#ebefee",
  ink:"#181c1c", inkVar:"#3e4949", inkSoft:"#6e7979", outlineVar:"#bdc9c8",
  teal:"#006565", tealDark:"#004f4f", tealLight:"#e0f2f2", tealBorder:"#b2dfdf",
  err:"#ba1a1a", errBg:"#fef2f2", errBorder:"#fecaca",
  warn:"#92400e", warnBg:"#fffbeb", warnBorder:"#fde68a",
  sh1:"0 4px 20px rgba(26,26,26,0.06)", sh2:"0 8px 32px rgba(26,26,26,0.12)",
};
const F = "'Plus Jakarta Sans',system-ui,sans-serif";
const R = "12px";
const brandLogoLight = `${import.meta.env?.BASE_URL ?? "/"}images/easygrox-logo-light.png`;
const brandLogoDark = `${import.meta.env?.BASE_URL ?? "/"}images/easygrox-logo-dark.png`;

/* Live EasyGrox app — signup / sign-in open the Laravel admin (not on marketing site) */
function goToAppAuth() {
  if (typeof window === "undefined") return;
  const prefix = window.location.pathname.startsWith("/vellor") ? "/vellor" : "";
  window.location.href = `${window.location.origin}${prefix}/admin/login`;
}

/* ─── RESPONSIVE HOOK ───────────────────────────────────── */
function useW() {
  const [w, setW] = useState(typeof window !== "undefined" ? window.innerWidth : 1200);
  useEffect(() => {
    const fn = () => setW(window.innerWidth);
    window.addEventListener("resize", fn);
    return () => window.removeEventListener("resize", fn);
  }, []);
  return { w, sm: w < 640, md: w < 900, lg: w >= 900 };
}

/* ─── ICONS ──────────────────────────────────────────────── */
const I = {
  calendar: <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.7" strokeLinecap="round" strokeLinejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>,
  users:    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.7" strokeLinecap="round" strokeLinejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>,
  pos:      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.7" strokeLinecap="round" strokeLinejoin="round"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>,
  user:     <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.7" strokeLinecap="round" strokeLinejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>,
  bag:      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.7" strokeLinecap="round" strokeLinejoin="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>,
  globe:    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.7" strokeLinecap="round" strokeLinejoin="round"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>,
  bar:      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.7" strokeLinecap="round" strokeLinejoin="round"><path d="M18 20V10M12 20V4M6 20v-6"/></svg>,
  trend:    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.7" strokeLinecap="round" strokeLinejoin="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>,
  bell:     <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.7" strokeLinecap="round" strokeLinejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>,
  tag:      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.7" strokeLinecap="round" strokeLinejoin="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>,
  mail:     <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.7" strokeLinecap="round" strokeLinejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>,
  phone:    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.7" strokeLinecap="round" strokeLinejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.56 1.18h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.91a16 16 0 0 0 6 6l.91-.91a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>,
  link:     <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.7" strokeLinecap="round" strokeLinejoin="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>,
  shield:   <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.7" strokeLinecap="round" strokeLinejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>,
  lock:     <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.7" strokeLinecap="round" strokeLinejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>,
  refresh:  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.7" strokeLinecap="round" strokeLinejoin="round"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>,
  settings: <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.7" strokeLinecap="round" strokeLinejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>,
  zap:      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.7" strokeLinecap="round" strokeLinejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>,
  check:    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round"><polyline points="20 6 9 17 4 12"/></svg>,
  xmark:    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>,
  chevR:    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><polyline points="9 18 15 12 9 6"/></svg>,
  chevD:    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><polyline points="6 9 12 15 18 9"/></svg>,
  close:    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>,
  plus:     <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>,
  minus:    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round"><line x1="5" y1="12" x2="19" y2="12"/></svg>,
  eye:      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.7" strokeLinecap="round" strokeLinejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>,
  eyeOff:   <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.7" strokeLinecap="round" strokeLinejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>,
  menu:     <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>,
  search:   <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.7" strokeLinecap="round" strokeLinejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>,
  play:     <svg viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"/></svg>,
  star:     <svg viewBox="0 0 24 24" fill="currentColor" stroke="none"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>,
  map:      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.7" strokeLinecap="round" strokeLinejoin="round"><polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6"/><line x1="8" y1="2" x2="8" y2="18"/><line x1="16" y1="6" x2="16" y2="22"/></svg>,
  gift:     <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.7" strokeLinecap="round" strokeLinejoin="round"><polyline points="20 12 20 22 4 22 4 12"/><rect x="2" y="7" width="20" height="5"/><path d="M12 22V7"/><path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"/><path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"/></svg>,
  repeat:   <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.7" strokeLinecap="round" strokeLinejoin="round"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg>,
  briefcase:<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.7" strokeLinecap="round" strokeLinejoin="round"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>,
  layers:   <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.7" strokeLinecap="round" strokeLinejoin="round"><polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/></svg>,
  target:   <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.7" strokeLinecap="round" strokeLinejoin="round"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/></svg>,
  clock:    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.7" strokeLinecap="round" strokeLinejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>,
  invoice:  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.7" strokeLinecap="round" strokeLinejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>,
  /* Industry icons */
  scissors: <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.7" strokeLinecap="round" strokeLinejoin="round"><circle cx="6" cy="6" r="3"/><circle cx="6" cy="18" r="3"/><line x1="20" y1="4" x2="8.12" y2="15.88"/><line x1="14.47" y1="14.48" x2="20" y2="20"/><line x1="8.12" y1="8.12" x2="12" y2="12"/></svg>,
  razor:    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.7" strokeLinecap="round" strokeLinejoin="round"><path d="M6 2h12l2 6H4L6 2z"/><rect x="3" y="8" width="18" height="10" rx="2"/><line x1="9" y1="12" x2="15" y2="12"/><line x1="9" y1="15" x2="15" y2="15"/></svg>,
  nail:     <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.7" strokeLinecap="round" strokeLinejoin="round"><ellipse cx="12" cy="8" rx="6" ry="7"/><path d="M8 14s1 4 4 4 4-4 4-4"/><line x1="12" y1="18" x2="12" y2="22"/></svg>,
  lotus:    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.7" strokeLinecap="round" strokeLinejoin="round"><path d="M12 22c0-6-4-10-4-10s4-1 4 4c0-5 4-4 4-4s-4 4-4 10z"/><path d="M12 12C12 6 8 4 4 6c0 0 2 4 8 6z"/><path d="M12 12c0-6 4-8 8-6 0 0-2 4-8 6z"/><path d="M12 12c-6 0-8 4-6 8 0 0 4-2 6-8z"/><path d="M12 12c6 0 8 4 6 8 0 0-4-2-6-8z"/></svg>,
  lipstick: <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.7" strokeLinecap="round" strokeLinejoin="round"><rect x="8" y="12" width="8" height="10" rx="1"/><path d="M8 12V8l4-6 4 6v4"/><line x1="12" y1="12" x2="12" y2="22"/></svg>,
  ink:      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.7" strokeLinecap="round" strokeLinejoin="round"><path d="M12 2C8 2 4 6 4 11c0 5 4 9 8 11 4-2 8-6 8-11 0-5-4-9-8-9z"/><path d="M9 12a3 3 0 0 0 6 0"/></svg>,
  paw:      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.7" strokeLinecap="round" strokeLinejoin="round"><circle cx="11" cy="4" r="2"/><circle cx="18" cy="8" r="2"/><circle cx="4" cy="8" r="2"/><path d="M12 17c-3 0-6 2-6 4h12c0-2-3-4-6-4z"/><ellipse cx="12" cy="13" rx="4" ry="3"/></svg>,
};

const Ic = ({ n, sz = 20, col }) => {
  const icon = I[n];
  if (!icon) return null;
  const color = col || "currentColor";
  const filled = icon.props?.fill === "currentColor";
  return (
    <span style={{ display:"inline-flex", alignItems:"center", justifyContent:"center", width:sz, height:sz, minWidth:sz, minHeight:sz, flexShrink:0, color, lineHeight:0 }} aria-hidden="true">
      {cloneElement(icon, {
        width: sz,
        height: sz,
        color,
        ...(filled ? { fill: color } : { stroke: color }),
        style: { display: "block", width: sz, height: sz, flexShrink: 0 },
      })}
    </span>
  );
};

/* ─── BASE COMPONENTS ────────────────────────────────────── */
const Chip = ({ children, teal, warn, sm, style = {} }) => (
  <span style={{ display:"inline-flex", alignItems:"center", gap:4, background:teal ? C.tealLight : warn ? C.warnBg : C.mid, color:teal ? C.teal : warn ? C.warn : C.inkVar, border:`1px solid ${teal ? C.tealBorder : warn ? C.warnBorder : C.outlineVar}`, borderRadius:9999, fontSize:sm ? 11 : 12, fontWeight:600, fontFamily:F, padding:sm ? "3px 10px" : "4px 13px", letterSpacing:"0.03em", ...style }}>
    {children}
  </span>
);

const Label = ({ children, center, light }) => (
  <div style={{ fontFamily:F, fontSize:11.5, fontWeight:700, letterSpacing:"0.08em", textTransform:"uppercase", color:light ? "rgba(255,255,255,0.5)" : C.teal, marginBottom:10, textAlign:center ? "center" : undefined }}>
    {children}
  </div>
);

function H1({ children, style = {} }) {
  return <h1 style={{ fontFamily:F, fontSize:"clamp(28px,5vw,52px)", fontWeight:800, lineHeight:1.1, letterSpacing:"-0.02em", color:C.ink, margin:0, ...style }}>{children}</h1>;
}
function H2({ children, light, center, style = {} }) {
  return <h2 style={{ fontFamily:F, fontSize:"clamp(22px,3.5vw,38px)", fontWeight:800, lineHeight:1.15, letterSpacing:"-0.015em", color:light ? "#fff" : C.ink, margin:0, textAlign:center ? "center" : undefined, ...style }}>{children}</h2>;
}
function H3({ children, light, style = {} }) {
  return <h3 style={{ fontFamily:F, fontSize:"clamp(17px,2.2vw,22px)", fontWeight:700, lineHeight:1.3, color:light ? "#fff" : C.ink, margin:0, ...style }}>{children}</h3>;
}
function Body({ children, muted, light, sm, center, style = {} }) {
  return <p style={{ fontFamily:F, fontSize:sm ? 13.5 : 15.5, lineHeight:1.7, color:light ? "rgba(255,255,255,0.7)" : muted ? C.inkVar : C.ink, margin:0, textAlign:center ? "center" : undefined, ...style }}>{children}</p>;
}
function H4({ children, style = {} }) {
  return <h4 style={{ fontFamily:F, fontSize:16, fontWeight:700, color:C.ink, margin:0, ...style }}>{children}</h4>;
}
const Div = ({ style = {} }) => <div style={{ height:1, background:C.outlineVar, opacity:0.5, ...style }} />;

function Btn({ children, v = "primary", onClick, full, sm, style = {} }) {
  const [hov, setHov] = useState(false);
  const h = sm ? 40 : 48;
  const px = sm ? "0 18px" : "0 24px";
  const fs = sm ? 13 : 14;
  const variants = {
    primary:    { background:hov ? C.tealDark : C.teal, color:"#fff", border:"none" },
    outline:    { background:"transparent", color:C.teal, border:`1.5px solid ${C.teal}` },
    ghost:      { background:hov ? C.tealLight : C.low, color:C.inkVar, border:`1px solid ${C.outlineVar}` },
    white:      { background:hov ? "rgba(255,255,255,0.2)" : "rgba(255,255,255,0.12)", color:"#fff", border:"1.5px solid rgba(255,255,255,0.4)" },
    whiteSolid: { background:hov ? C.tealLight : "#fff", color:C.teal, border:"none" },
  };
  const s = variants[v] || variants.primary;
  return (
    <button onClick={onClick}
      onMouseEnter={() => setHov(true)} onMouseLeave={() => setHov(false)}
      style={{ display:"inline-flex", alignItems:"center", justifyContent:"center", gap:7, height:h, padding:px, borderRadius:R, fontSize:fs, fontWeight:600, cursor:"pointer", fontFamily:F, width:full ? "100%" : "auto", whiteSpace:"nowrap", letterSpacing:"0.02em", transition:"all .18s", ...s, ...style }}>
      {children}
    </button>
  );
}

const Divider = ({ style = {} }) => <div style={{ height:1, background:C.outlineVar, opacity:0.5, ...style }} />;

function Card({ children, style = {}, tealTop, tealBorder: tb, ink }) {
  const [hov, setHov] = useState(false);
  return (
    <div onMouseEnter={() => setHov(true)} onMouseLeave={() => setHov(false)}
      style={{ background:ink ? C.teal : C.surface, borderRadius:R, border:`1px solid ${tb ? C.teal : ink ? "transparent" : C.outlineVar}`, padding:"24px 20px", transition:"all .2s", boxShadow:hov ? C.sh2 : C.sh1, borderTop:tealTop ? `3px solid ${C.teal}` : undefined, ...style }}>
      {children}
    </div>
  );
}

const IcBox = ({ n, big }) => (
  <div style={{ width:big ? 52 : 44, height:big ? 52 : 44, borderRadius:R, background:C.tealLight, border:`1px solid ${C.tealBorder}`, display:"flex", alignItems:"center", justifyContent:"center", marginBottom:14, color:C.teal, flexShrink:0 }}>
    <Ic n={n} sz={big ? 24 : 20} col={C.teal} />
  </div>
);

/* ─── APP MOCKUPS ────────────────────────────────────────── */
function DashMock({ compact }) {
  const bars = [32, 48, 42, 64, 55, 72, 88];
  const appts = [
    { name:"Ananya S.", svc:"Hair Colour", time:"10:00", status:"confirmed" },
    { name:"Ritu M.",   svc:"Haircut",     time:"11:30", status:"active" },
    { name:"Meera K.",  svc:"Keratin",     time:"1:00",  status:"upcoming" },
  ];
  return (
    <div style={{ background:"#1a1f2e", borderRadius:R, overflow:"hidden", boxShadow:"0 24px 64px rgba(0,0,0,0.2)", fontFamily:F }}>
      <div style={{ background:"rgba(255,255,255,0.06)", padding:"9px 14px", display:"flex", alignItems:"center", gap:7, borderBottom:"1px solid rgba(255,255,255,0.08)" }}>
        <div style={{ display:"flex", gap:4 }}>
          {["#ff6b6b","#ffd93d","#6bcb77"].map((cl,i) => <div key={i} style={{ width:8, height:8, borderRadius:"50%", background:cl }} />)}
        </div>
        <div style={{ flex:1, background:"rgba(255,255,255,0.06)", borderRadius:5, height:17, display:"flex", alignItems:"center", padding:"0 9px" }}>
          <span style={{ fontSize:9, color:"rgba(255,255,255,0.22)", fontFamily:"monospace" }}>easygrox.com/dashboard</span>
        </div>
      </div>
      <div style={{ padding:compact ? 12 : 18 }}>
        <div style={{ display:"flex", justifyContent:"space-between", alignItems:"center", marginBottom:14 }}>
          <div>
            <div style={{ color:"rgba(255,255,255,0.35)", fontSize:9.5, marginBottom:2 }}>Wednesday, 21 May 2026</div>
            <div style={{ color:"#fff", fontSize:12.5, fontWeight:700 }}>Good morning, Priya</div>
          </div>
          <div style={{ background:C.teal, borderRadius:8, padding:"4px 10px", fontSize:10, color:"#fff", fontWeight:600, cursor:"pointer" }}>+ New Booking</div>
        </div>
        <div style={{ display:"grid", gridTemplateColumns:"1fr 1fr 1fr 1fr", gap:7, marginBottom:13 }}>
          {[{l:"Revenue",v:"₹14,200"},{l:"Appointments",v:"11"},{l:"Website",v:"108 visits"},{l:"Avg Ticket",v:"₹1,291"}].map((s,i) => (
            <div key={i} style={{ background:"rgba(255,255,255,0.07)", borderRadius:8, padding:"9px 8px" }}>
              <div style={{ color:"#fff", fontSize:13.5, fontWeight:800 }}>{s.v}</div>
              <div style={{ color:"rgba(255,255,255,0.35)", fontSize:8.5, marginTop:2 }}>{s.l}</div>
            </div>
          ))}
        </div>
        {!compact && (
          <div style={{ background:"rgba(255,255,255,0.04)", borderRadius:8, padding:"11px 12px", marginBottom:11 }}>
            <div style={{ display:"flex", justifyContent:"space-between", marginBottom:8 }}>
              <span style={{ color:"rgba(255,255,255,0.35)", fontSize:8.5, fontWeight:600 }}>REVENUE THIS WEEK</span>
              <span style={{ color:C.tealBorder, fontSize:8.5, fontWeight:600 }}>₹68,400</span>
            </div>
            <div style={{ display:"flex", alignItems:"flex-end", gap:3, height:36 }}>
              {bars.map((h,i) => <div key={i} style={{ flex:1, borderRadius:"2px 2px 0 0", height:`${h}%`, background:i===6 ? C.teal : "rgba(0,101,101,0.4)" }} />)}
            </div>
          </div>
        )}
        <div style={{ background:"rgba(255,255,255,0.04)", borderRadius:8, padding:"10px 12px" }}>
          <div style={{ color:"rgba(255,255,255,0.35)", fontSize:8.5, fontWeight:600, marginBottom:8 }}>TODAY&#39;S APPOINTMENTS</div>
          {appts.slice(0, compact ? 2 : 3).map((a, i) => (
            <div key={i} style={{ display:"flex", alignItems:"center", gap:7, padding:"5px 0", borderBottom:i < (compact ? 1 : 2) ? "1px solid rgba(255,255,255,0.06)" : "none" }}>
              <div style={{ width:24, height:24, borderRadius:6, background:C.teal, display:"flex", alignItems:"center", justifyContent:"center", fontSize:9, fontWeight:800, color:"#fff", flexShrink:0 }}>
                {a.name.split(" ").map(n => n[0]).join("")}
              </div>
              <div style={{ flex:1, minWidth:0 }}>
                <div style={{ color:"#fff", fontSize:10.5, fontWeight:600, overflow:"hidden", textOverflow:"ellipsis", whiteSpace:"nowrap" }}>{a.name} · {a.svc}</div>
                <div style={{ color:"rgba(255,255,255,0.3)", fontSize:8.5 }}>{a.time}</div>
              </div>
              <div style={{ background:a.status === "active" ? "rgba(255,200,0,0.15)" : a.status === "confirmed" ? "rgba(0,101,101,0.35)" : "rgba(255,255,255,0.08)", borderRadius:4, padding:"2px 7px", fontSize:8.5, color:a.status === "active" ? "#ffd93d" : a.status === "confirmed" ? C.tealBorder : "rgba(255,255,255,0.4)", flexShrink:0 }}>
                {a.status === "active" ? "Active" : a.status === "confirmed" ? "Confirmed" : "Upcoming"}
              </div>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
}

function BookingMock({ small }) {
  const w = small ? 150 : 200;
  return (
    <div style={{ width:w, background:C.surface, borderRadius:R, overflow:"hidden", boxShadow:C.sh2, border:`1px solid ${C.outlineVar}`, flexShrink:0 }}>
      <div style={{ background:C.teal, height:20, display:"flex", alignItems:"center", justifyContent:"center" }}>
        <div style={{ width:40, height:4, background:"rgba(255,255,255,0.2)", borderRadius:3 }} />
      </div>
      <div style={{ padding:small ? 9 : 12 }}>
        <div style={{ textAlign:"center", marginBottom:9, paddingBottom:9, borderBottom:`1px solid ${C.outlineVar}` }}>
          <div style={{ width:26, height:26, borderRadius:"50%", background:C.tealLight, border:`1px solid ${C.tealBorder}`, margin:"0 auto 5px", display:"flex", alignItems:"center", justifyContent:"center" }}>
            <Ic n="scissors" sz={12} col={C.teal} />
          </div>
          <div style={{ fontSize:small ? 9.5 : 11, fontWeight:800, color:C.ink }}>Luxe Hair Studio</div>
          <div style={{ fontSize:8.5, color:C.inkVar }}>Jaipur · Book Online</div>
        </div>
        <div style={{ background:C.teal, borderRadius:R, padding:"6px", textAlign:"center", color:"#fff", fontSize:10, fontWeight:600, marginBottom:9, cursor:"pointer" }}>
          Book Appointment
        </div>
        {[{n:"Haircut",p:"₹500"},{n:"Colour",p:"₹1,200"},{n:"Keratin",p:"₹2,800"}].map((s,i) => (
          <div key={i} style={{ display:"flex", justifyContent:"space-between", alignItems:"center", padding:"4px 0", borderBottom:i < 2 ? `1px solid ${C.outlineVar}` : "none" }}>
            <span style={{ fontSize:9.5, color:C.ink, fontWeight:500 }}>{s.n}</span>
            <span style={{ fontSize:9.5, color:C.teal, fontWeight:700 }}>{s.p}</span>
          </div>
        ))}
      </div>
    </div>
  );
}

/* ─── NAV ────────────────────────────────────────────────── */
function Nav({ nav }) {
  const { sm, md } = useW();
  const [mOpen, setMOpen] = useState(false);
  const [dd, setDd]       = useState(null);
  const [banner, setBanner] = useState(true);
  const [scrolled, setScrolled] = useState(false);

  useEffect(() => {
    const fn = () => setScrolled(window.scrollY > 20);
    window.addEventListener("scroll", fn);
    return () => window.removeEventListener("scroll", fn);
  }, []);

  const links = [
    { label:"Features",    items:["Appointments & Calendar","Staff Management","POS & Billing","Free Booking Website","Analytics & Reports","Marketing Campaigns","Client Management","Retail Inventory","Review Management","Multi-Location"] },
    { label:"By Business", items:["Hair Salon","Barber Shop","Nail Studio","Spa & Massage","Tattoo Studio","Makeup Artist","Pet Grooming"] },
    { label:"Pricing" },
    { label:"Resources",   items:["How It Works","Help Centre","Getting Started","Blog","About EasyGrox","Contact Us"] },
  ];

  const rm = { "Pricing":"pricing","Appointments & Calendar":"appointments","Staff Management":"staffMgmt","POS & Billing":"pos","Free Booking Website":"website","Analytics & Reports":"analytics","Marketing Campaigns":"marketing","Client Management":"clients","Retail Inventory":"retail","Review Management":"reviews","Multi-Location":"multiLocation","How It Works":"howItWorks","Help Centre":"helpCenter","Getting Started":"gettingStarted","Blog":"blog","About EasyGrox":"about","Contact Us":"contact" };
  const biz = ["Hair Salon","Barber Shop","Nail Studio","Spa & Massage","Tattoo Studio","Makeup Artist","Pet Grooming"];

  const go = (label, item) => {
    setDd(null); setMOpen(false);
    const t = item || label;
    if (biz.includes(t)) nav("bizType", { type: t });
    else if (rm[t]) nav(rm[t]);
    else if (label === "Pricing") nav("pricing");
    else if (label === "Features") nav("features");
    else nav("home");
  };

  return (
    <>
      {/* BANNER */}
      {banner && (
        <div style={{ background:C.teal, padding:"8px 16px", display:"flex", alignItems:"center", justifyContent:"center", gap:10, position:"relative", flexWrap:"wrap" }}>
          <span style={{ fontFamily:F, fontSize:13, color:"rgba(255,255,255,0.88)", textAlign:"center" }}>
            Every account includes a <strong style={{ color:"#fff" }}>free booking website</strong>
          </span>
          <button onClick={() => nav("website")} style={{ background:"rgba(255,255,255,0.18)", border:"1px solid rgba(255,255,255,0.35)", color:"#fff", padding:"3px 12px", borderRadius:9999, fontSize:11.5, cursor:"pointer", fontWeight:600, fontFamily:F, whiteSpace:"nowrap" }}>Learn more</button>
          <button onClick={() => setBanner(false)} style={{ position:"absolute", right:12, top:"50%", transform:"translateY(-50%)", background:"none", border:"none", color:"rgba(255,255,255,0.5)", cursor:"pointer", padding:4, display:"flex", alignItems:"center" }}>
            <Ic n="close" sz={14} col="rgba(255,255,255,0.5)" />
          </button>
        </div>
      )}

      {/* NAV BAR */}
      <nav style={{ background:scrolled ? "rgba(246,250,249,0.97)" : C.bg, backdropFilter:"blur(12px)", borderBottom:`1px solid ${C.outlineVar}`, height:60, display:"flex", alignItems:"center", position:"sticky", top:0, zIndex:200, boxShadow:scrolled ? C.sh1 : "none", transition:"all .2s" }}>
        <div style={{ maxWidth:1200, margin:"0 auto", padding:"0 16px", display:"flex", alignItems:"center", width:"100%", gap:0 }}>

          {/* LOGO */}
          <button onClick={() => go("home")} style={{ background:"none", border:"none", cursor:"pointer", display:"flex", alignItems:"center", marginRight:md ? 16 : 32, flexShrink:0, padding:0 }}>
            <img src={brandLogoLight} alt="EasyGrox" style={{ height:44, width:"auto", display:"block" }} />
          </button>

          {/* DESKTOP NAV */}
          {!md && (
            <div style={{ display:"flex", gap:0, flex:1, alignItems:"center" }}>
              {links.map((lnk, i) => (
                <div key={i} style={{ position:"relative" }} onMouseEnter={() => setDd(i)} onMouseLeave={() => setDd(null)}>
                  <button onClick={() => go(lnk.label)} style={{ background:"none", border:"none", cursor:"pointer", padding:"10px 12px", fontSize:13.5, fontWeight:500, color:dd === i ? C.teal : C.inkVar, fontFamily:F, display:"flex", alignItems:"center", gap:4, transition:"color .14s" }}>
                    {lnk.label}
                    {lnk.items && <Ic n="chevD" sz={10} col="inherit" />}
                  </button>
                  {lnk.items && dd === i && (
                    <div style={{ position:"absolute", top:"calc(100% + 4px)", left:0, background:C.surface, borderRadius:R, boxShadow:C.sh2, border:`1px solid ${C.outlineVar}`, padding:"6px 0", minWidth:220, zIndex:300 }}>
                      {lnk.items.map((item, ii) => (
                        <button key={ii} onClick={() => go(lnk.label, item)} style={{ display:"flex", alignItems:"center", gap:9, width:"100%", padding:"10px 16px", background:"none", border:"none", textAlign:"left", fontSize:13.5, color:C.inkVar, cursor:"pointer", fontFamily:F, transition:"all .1s" }}
                          onMouseEnter={e => { e.currentTarget.style.background = C.tealLight; e.currentTarget.style.color = C.teal; }}
                          onMouseLeave={e => { e.currentTarget.style.background = "none"; e.currentTarget.style.color = C.inkVar; }}>
                          <Ic n="chevR" sz={11} col={C.inkSoft} />{item}
                        </button>
                      ))}
                    </div>
                  )}
                </div>
              ))}
            </div>
          )}

          {/* DESKTOP BUTTONS */}
          {!md && (
            <div style={{ display:"flex", gap:8, alignItems:"center" }}>
              <Btn v="ghost" sm onClick={() => nav("login")}>Sign in</Btn>
              <Btn sm onClick={() => nav("signup")}>Start Free</Btn>
            </div>
          )}

          {/* MOBILE HAMBURGER */}
          {md && (
            <div style={{ display:"flex", gap:8, marginLeft:"auto", alignItems:"center" }}>
              <Btn sm onClick={() => nav("signup")}>Start Free</Btn>
              <button onClick={() => setMOpen(!mOpen)} style={{ background:"none", border:"none", cursor:"pointer", padding:6, display:"flex", alignItems:"center" }}>
                <Ic n={mOpen ? "close" : "menu"} sz={22} col={C.ink} />
              </button>
            </div>
          )}
        </div>
      </nav>

      {/* MOBILE MENU DRAWER */}
      {md && mOpen && (
        <div style={{ position:"fixed", top:0, left:0, right:0, bottom:0, zIndex:300, display:"flex", flexDirection:"column" }}>
          <div style={{ position:"absolute", inset:0, background:"rgba(24,28,28,0.5)", backdropFilter:"blur(4px)" }} onClick={() => setMOpen(false)} />
          <div style={{ position:"absolute", top:0, right:0, bottom:0, width:Math.min(320, window.innerWidth), background:C.surface, boxShadow:C.sh2, display:"flex", flexDirection:"column", overflowY:"auto" }}>
            <div style={{ padding:"16px 20px", borderBottom:`1px solid ${C.outlineVar}`, display:"flex", justifyContent:"space-between", alignItems:"center" }}>
              <img src={brandLogoLight} alt="EasyGrox" style={{ height:36, width:"auto", display:"block" }} />
              <button onClick={() => setMOpen(false)} style={{ background:"none", border:"none", cursor:"pointer", padding:4 }}>
                <Ic n="close" sz={20} col={C.inkVar} />
              </button>
            </div>
            <div style={{ flex:1, padding:"12px 0" }}>
              {links.map((lnk, i) => (
                <div key={i}>
                  <button onClick={() => { if (!lnk.items) go(lnk.label); else setDd(dd === i ? null : i); }}
                    style={{ width:"100%", padding:"13px 20px", background:"none", border:"none", textAlign:"left", fontSize:15, fontWeight:600, color:C.ink, cursor:"pointer", fontFamily:F, display:"flex", justifyContent:"space-between", alignItems:"center" }}>
                    {lnk.label}
                    {lnk.items && <Ic n={dd === i ? "chevD" : "chevR"} sz={14} col={C.inkVar} />}
                  </button>
                  {lnk.items && dd === i && (
                    <div style={{ background:C.low, borderTop:`1px solid ${C.outlineVar}`, borderBottom:`1px solid ${C.outlineVar}` }}>
                      {lnk.items.map((item, ii) => (
                        <button key={ii} onClick={() => go(lnk.label, item)} style={{ display:"block", width:"100%", padding:"11px 28px", background:"none", border:"none", textAlign:"left", fontSize:14, color:C.teal, cursor:"pointer", fontFamily:F, fontWeight:500 }}>
                          {item}
                        </button>
                      ))}
                    </div>
                  )}
                </div>
              ))}
            </div>
            <div style={{ padding:"16px 20px", borderTop:`1px solid ${C.outlineVar}`, display:"flex", flexDirection:"column", gap:10 }}>
              <Btn full onClick={() => { setMOpen(false); nav("signup"); }}>Start for Free</Btn>
              <Btn v="outline" full onClick={() => { setMOpen(false); nav("login"); }}>Sign in</Btn>
            </div>
          </div>
        </div>
      )}
    </>
  );
}

/* ─── FOOTER ─────────────────────────────────────────────── */
function Footer({ nav }) {
  const { sm } = useW();
  const cols = [
    { h:"Features",    links:["Appointments & Calendar","POS & Billing","Client Management","Staff Management","Retail Inventory","Free Booking Website","Analytics & Reports","Marketing Campaigns","Review Management","Multi-Location"] },
    { h:"By Business", links:["Hair Salon","Barber Shop","Nail Studio","Spa & Massage","Tattoo Studio","Makeup Artist","Pet Grooming"] },
    { h:"Company",     links:["How It Works","About EasyGrox","Blog","Contact Us","Help Centre","Getting Started","Pricing"] },
  ];
  const rm = { "Pricing":"pricing","Free Booking Website":"website","Analytics & Reports":"analytics","How It Works":"howItWorks","Help Centre":"helpCenter","Getting Started":"gettingStarted","Blog":"blog","About EasyGrox":"about","Contact Us":"contact","Appointments & Calendar":"appointments","Staff Management":"staffMgmt","POS & Billing":"pos","Marketing Campaigns":"marketing","Review Management":"reviews","Multi-Location":"multiLocation","Client Management":"clients","Retail Inventory":"retail" };
  const biz = ["Hair Salon","Barber Shop","Nail Studio","Spa & Massage","Tattoo Studio","Makeup Artist","Pet Grooming"];

  return (
    <footer style={{ background:C.ink, padding:"52px 0 0" }}>
      <div style={{ maxWidth:1200, margin:"0 auto", padding:"0 20px" }}>
        <div style={{ display:"grid", gridTemplateColumns:sm ? "1fr" : "1.4fr 1fr 1fr 1fr", gap:sm ? 32 : 40, paddingBottom:44 }}>
          <div>
            <div style={{ marginBottom:14 }}>
              <img src={brandLogoDark} alt="EasyGrox" style={{ height:30, width:"auto", display:"block" }} />
            </div>
            <p style={{ fontFamily:F, fontSize:13.5, color:"rgba(255,255,255,0.42)", lineHeight:1.7, marginBottom:20 }}>Run your calendar, clients, and team in one calm place. Free booking website included.</p>
            <div style={{ display:"flex", gap:8 }}>
              {[I.mail, I.phone, I.link, I.globe].map((ic, i) => (
                <div key={i} style={{ width:34, height:34, borderRadius:8, background:"rgba(255,255,255,0.07)", border:"1px solid rgba(255,255,255,0.1)", display:"flex", alignItems:"center", justifyContent:"center", cursor:"pointer", color:"rgba(255,255,255,0.45)" }}
                  onMouseEnter={e => e.currentTarget.style.background = "rgba(255,255,255,0.14)"}
                  onMouseLeave={e => e.currentTarget.style.background = "rgba(255,255,255,0.07)"}>
                  {ic}
                </div>
              ))}
            </div>
          </div>
          {cols.map((col, ci) => (
            <div key={ci}>
              <div style={{ fontFamily:F, fontSize:10, fontWeight:700, letterSpacing:"0.08em", color:"rgba(255,255,255,0.28)", textTransform:"uppercase", marginBottom:14 }}>{col.h}</div>
              {col.links.map((l, li) => (
                <button key={li} onClick={() => { if (biz.includes(l)) nav("bizType", { type:l }); else nav(rm[l] || "home"); }}
                  style={{ display:"block", background:"none", border:"none", color:"rgba(255,255,255,0.42)", fontSize:13.5, padding:"5px 0", cursor:"pointer", textAlign:"left", fontFamily:F, transition:"color .14s" }}
                  onMouseEnter={e => e.currentTarget.style.color = "#fff"}
                  onMouseLeave={e => e.currentTarget.style.color = "rgba(255,255,255,0.42)"}>
                  {l}
                </button>
              ))}
            </div>
          ))}
        </div>
        <div style={{ borderTop:"1px solid rgba(255,255,255,0.08)", padding:"18px 0", display:"flex", flexWrap:"wrap", justifyContent:"space-between", alignItems:"center", gap:10 }}>
          <span style={{ fontFamily:F, fontSize:12, color:"rgba(255,255,255,0.22)" }}>© 2026 EasyGrox · Privacy · Terms · Security</span>
          <Chip teal sm>Free website on every plan</Chip>
        </div>
      </div>
    </footer>
  );
}

/* ─── HOME PAGE ──────────────────────────────────────────── */
function Home({ nav }) {
  const { sm, md } = useW();
  const [tab, setTab] = useState(0);
  const [openFaq, setOpenFaq] = useState(null);
  const [annual, setAnnual] = useState(false);

  const features = [
    { n:"calendar", t:"Appointment Calendar",  d:"Online bookings, walk-ins, and manual entries — one colour-coded calendar for your whole team." },
    { n:"users",    t:"Staff Management",       d:"Individual logins, shift scheduling, leave tracking, role-based access, and personal dashboards." },
    { n:"pos",      t:"POS & Billing",          d:"Bill services and retail in under 60 seconds. All payment types. Digital receipts instantly." },
    { n:"user",     t:"Client Management",      d:"Full visit history, colour notes, spend tracking, preferences, and loyalty per client." },
    { n:"bag",      t:"Retail Inventory",       d:"Track stock, get low-stock alerts, and sell products at the POS in the same transaction." },
    { n:"globe",    t:"Free Booking Website",   d:"Hosted, mobile-ready, Google-indexed booking website — auto-generated and free on every plan.", free:true },
    { n:"bar",      t:"Website Analytics",      d:"Visitors, traffic sources, top pages, and booking conversion — tracked automatically." },
    { n:"trend",    t:"Business Analytics",     d:"Revenue, staff, clients, retail, appointments — six dashboards in one app." },
    { n:"bell",     t:"Automated Reminders",    d:"SMS and WhatsApp confirmations and reminders. Salons report 50-60% fewer no-shows." },
    { n:"tag",      t:"Marketing Campaigns",    d:"SMS campaigns, birthday offers, and re-engagement — built into your dashboard." },
  ];

  const bizTypes = [
    { icon:"scissors", t:"Hair Salon",    d:"Stylists, colour, retail" },
    { icon:"razor",    t:"Barber Shop",   d:"Queues, cuts, billing" },
    { icon:"nail",     t:"Nail Studio",   d:"Technicians, gel, art" },
    { icon:"lotus",    t:"Spa & Massage", d:"Rooms, therapists, packages" },
    { icon:"lipstick", t:"Makeup Artist", d:"Sessions, bridal, kit" },
    { icon:"ink",      t:"Tattoo Studio", d:"Artists, deposits, artwork" },
    { icon:"paw",      t:"Pet Grooming",  d:"Pets, profiles, recurring" },
  ];

  const tabs = [
    { label:"Dashboard",    pts:["Today revenue, appointments, website visits","KPI cards updated in real time","Weekly revenue bar chart","Upcoming appointment list with status","Quick actions from the home screen"] },
    { label:"Calendar",     pts:["All bookings across all staff in one view","Online, walk-in, and manual — all unified","Drag-and-drop rescheduling with SMS to client","Staff column view — see everyone side by side","Switch between daily, weekly, and monthly"] },
    { label:"POS",          pts:["Service pre-filled from appointment card","Bill services and retail together","Cash, card, UPI, wallet — all accepted","Digital receipt via WhatsApp or SMS","End-of-day revenue and cash summary"] },
    { label:"Analytics",    pts:["Revenue, appointments, staff, clients, retail","Website visitor data alongside business data","Booking conversion rate from your website","Staff utilisation and performance comparison","Period-over-period trend built in"] },
  ];

  const plans = [
    { plan:"Solo",    for:"Independent stylists", price:"₹799",  apr:"₹666",  feats:["1 store · 1 staff login","Appointment calendar & POS","Client management","Automated SMS reminders","Free hosted booking website","Website visitor analytics"], hi:false },
    { plan:"Business",for:"Salons with a team",   price:"₹1,999",apr:"₹1,666", feats:["Everything in Solo","Up to 20 staff members","Shift scheduling & leave","Retail inventory","Full analytics (6 dashboards)","SMS & email campaigns","Free website + full analytics"], hi:true },
    { plan:"Multi-Location",for:"Multiple branches",price:"₹3,999",apr:"₹3,332",feats:["Everything in Business","Unlimited branches","Website per branch","Consolidated analytics","Priority support"], hi:false },
  ];

  const testimonials = [
    { q:"Before EasyGrox I managed bookings on WhatsApp. Now clients book through my website, my team has their schedules, and I see exactly what each stylist earned this month.", n:"Priya Sharma", r:"Luxe Hair Studio, Jaipur" },
    { q:"The free booking website was a genuine surprise. Analytics showing where my traffic comes from helped me put the link in my Instagram bio and watch conversions climb.", n:"Ravi Nair",   r:"The Barbers Co., Kochi" },
    { q:"Managing 3 spa branches from one login — each with its own website and reports — is something no previous software could do at this price.", n:"Meena Kapoor", r:"Serenity Spa, Mumbai" },
  ];

  const faqs = [
    { q:"Is the booking website really free forever?", a:"Yes. Every plan — including Solo — includes a fully hosted, mobile-responsive booking website at zero extra cost. No domain purchase, no hosting fee, no renewals ever." },
    { q:"How is EasyGrox different from Fresha or Booksy?", a:"Fresha and Booksy are marketplaces — your salon listed alongside competitors. EasyGrox gives you your own branded website. Clients come directly to you. Your data, your brand." },
    { q:"How long does setup take?", a:"Most salon owners complete setup and publish their booking website within 20-30 minutes. The app guides you step by step with a progress checklist." },
    { q:"Can I manage multiple branches?", a:"Yes. Multi-Location plan gives every branch its own website, dashboard, and analytics — all from one login." },
    { q:"Do I need to download an app?", a:"No. EasyGrox is a Progressive Web App that works in any browser on any device — phone, tablet, or desktop. No download needed." },
  ];

  const sec = { padding:sm ? "56px 0" : "88px 0" };
  const px = { padding:sm ? "0 16px" : "0 24px", maxWidth:1200, margin:"0 auto" };

  return (
    <>
      {/* HERO */}
      <section style={{ padding:sm ? "56px 0 48px" : "88px 0 72px", background:C.bg }}>
        <div style={{ ...px, display:"grid", gridTemplateColumns:md ? "1fr" : "1fr 1fr", gap:md ? 40 : 64, alignItems:"center" }}>
          <div>
            <Chip teal style={{ marginBottom:18 }}>Business management, elevated</Chip>
            <H1 style={{ marginBottom:18, marginTop:14 }}>Run your calendar, clients, and team in one calm place</H1>
            <Body muted style={{ fontSize:sm ? 16 : 18, marginBottom:32, lineHeight:1.7 }}>
              Appointments, staff, billing, inventory, marketing — and a <strong style={{ color:C.teal }}>free hosted booking website</strong> with built-in analytics. One login. Set up in 30 minutes.
            </Body>
            <div style={{ display:"flex", gap:12, marginBottom:28, flexWrap:"wrap" }}>
              <Btn onClick={() => nav("signup")}>Start for Free</Btn>
              <Btn v="outline">Watch Demo</Btn>
            </div>
            <div style={{ display:"flex", gap:8, flexWrap:"wrap" }}>
              {["No credit card","Free website included","30 min setup"].map((t, i) => (
                <span key={i} style={{ display:"flex", alignItems:"center", gap:5, fontFamily:F, fontSize:12.5, color:C.inkVar, background:C.surface, border:`1px solid ${C.outlineVar}`, borderRadius:9999, padding:"4px 12px", boxShadow:C.sh1 }}>
                  <Ic n="check" sz={11} col={C.teal} /> {t}
                </span>
              ))}
            </div>
          </div>
          <div style={{ position:"relative", paddingBottom:md ? 0 : 28 }}>
            <DashMock />
            {!md && (
              <div style={{ position:"absolute", bottom:0, right:-12 }}>
                <BookingMock />
              </div>
            )}
          </div>
          {md && (
            <div style={{ display:"flex", justifyContent:"center" }}>
              <BookingMock />
            </div>
          )}
        </div>
      </section>

      {/* PROOF BAR */}
      <div style={{ background:C.surface, borderTop:`1px solid ${C.outlineVar}`, borderBottom:`1px solid ${C.outlineVar}` }}>
        <div style={{ ...px, padding:"14px 16px" }}>
          <div style={{ display:"flex", flexWrap:"wrap", alignItems:"center", justifyContent:"center", gap:sm ? 14 : 32 }}>
            <div style={{ display:"flex", alignItems:"center", gap:6 }}>
              <div style={{ display:"flex", gap:1 }}>{[1,2,3,4,5].map(s => <Ic key={s} n="star" sz={14} col={C.teal} />)}</div>
              <span style={{ fontFamily:F, fontSize:13, color:C.inkVar, marginLeft:4 }}>4.9 / 5 · 2,000+ reviews</span>
            </div>
            {!sm && <div style={{ width:1, height:18, background:C.outlineVar }} />}
            <span style={{ fontFamily:F, fontSize:13, color:C.inkVar }}>Trusted by <strong style={{ color:C.ink }}>10,000+</strong> beauty businesses</span>
          </div>
        </div>
      </div>

      {/* BUSINESS TYPES */}
      <section style={{ ...sec, background:C.low }}>
        <div style={{ ...px }}>
          <div style={{ textAlign:"center", marginBottom:40 }}>
            <Label center>Specialised for your business</Label>
            <H2 center>Built for every beauty and wellness business</H2>
          </div>
          <div style={{ display:"grid", gridTemplateColumns:sm ? "repeat(4,1fr)" : "repeat(7,1fr)", gap:10 }}>
            {bizTypes.map((b, i) => (
              <div key={i}
                style={{ background:C.surface, borderRadius:R, padding:sm ? "14px 6px" : "20px 8px", textAlign:"center", cursor:"pointer", boxShadow:C.sh1, border:`1px solid ${C.outlineVar}`, transition:"all .2s" }}
                onMouseEnter={e => { e.currentTarget.style.borderColor = C.teal; e.currentTarget.style.transform = "translateY(-3px)"; e.currentTarget.style.boxShadow = C.sh2; }}
                onMouseLeave={e => { e.currentTarget.style.borderColor = C.outlineVar; e.currentTarget.style.transform = "none"; e.currentTarget.style.boxShadow = C.sh1; }}
                onClick={() => nav("bizType", { type:b.t })}>
                <div style={{ display:"flex", justifyContent:"center", marginBottom:8 }}>
                  <Ic n={b.icon} sz={sm ? 20 : 24} col={C.teal} />
                </div>
                <div style={{ fontFamily:F, fontSize:sm ? 10.5 : 12, fontWeight:700, color:C.ink }}>{b.t}</div>
                {!sm && <div style={{ fontFamily:F, fontSize:10, color:C.inkVar, marginTop:2, lineHeight:1.4 }}>{b.d}</div>}
              </div>
            ))}
          </div>
        </div>
      </section>

      <Divider />

      {/* FREE WEBSITE */}
      <section style={{ ...sec, background:C.bg }}>
        <div style={{ ...px }}>
          <div style={{ display:"grid", gridTemplateColumns:md ? "1fr" : "1fr auto", gap:md ? 32 : 64, alignItems:"center" }}>
            <div>
              <Label>Free — Every Plan</Label>
              <H2 style={{ marginBottom:18, marginTop:10 }}>Your salon gets a free booking website — hosted by us</H2>
              <Body muted style={{ marginBottom:28, fontSize:sm ? 15 : 17 }}>
                No domain purchase. No hosting fee. No developer. Complete your store setup in the app — your booking website is ready to publish with one click.
              </Body>
              <div style={{ display:"grid", gridTemplateColumns:sm ? "1fr" : "1fr 1fr", gap:14, marginBottom:28 }}>
                {[
                  { n:"link",    t:"Free Custom URL",      d:"yoursalon.easygrox.com or connect your own domain." },
                  { n:"shield",  t:"Free Hosting Forever", d:"Fast, secure servers — zero cost, zero renewals." },
                  { n:"refresh", t:"Always Up-to-Date",    d:"Change a price — website updates in seconds." },
                  { n:"bar",     t:"Built-in Analytics",   d:"Visitors, sources, conversion tracked automatically." },
                  { n:"bell",    t:"Online Booking 24/7",  d:"Clients book at any hour without calling you." },
                  { n:"globe",   t:"Google-Indexable",     d:"New clients find you in Google Search." },
                ].map((f, i) => (
                  <div key={i} style={{ display:"flex", gap:10, alignItems:"flex-start" }}>
                    <div style={{ width:34, height:34, borderRadius:R, background:C.tealLight, border:`1px solid ${C.tealBorder}`, display:"flex", alignItems:"center", justifyContent:"center", flexShrink:0 }}>
                      <Ic n={f.n} sz={15} col={C.teal} />
                    </div>
                    <div>
                      <div style={{ fontFamily:F, fontSize:13.5, fontWeight:600, color:C.ink, marginBottom:2 }}>{f.t}</div>
                      <div style={{ fontFamily:F, fontSize:12.5, color:C.inkVar, lineHeight:1.55 }}>{f.d}</div>
                    </div>
                  </div>
                ))}
              </div>
              <Btn v="outline" onClick={() => nav("website")}>Learn More About the Free Website</Btn>
            </div>
            <div style={{ display:"flex", justifyContent:"center" }}>
              <BookingMock />
            </div>
          </div>
        </div>
      </section>

      {/* FEATURES GRID */}
      <section style={{ ...sec, background:C.low }}>
        <div style={{ ...px }}>
          <div style={{ textAlign:"center", marginBottom:44 }}>
            <Label center>Everything connected</Label>
            <H2 center>Ten tools. One login.</H2>
            <Body muted center style={{ maxWidth:520, margin:"12px auto 0", fontSize:sm ? 15 : 16 }}>One connected system — every feature shares the same client, booking, and analytics data. No stitching tools together.</Body>
          </div>
          <div style={{ display:"grid", gridTemplateColumns:sm ? "1fr 1fr" : md ? "repeat(3,1fr)" : "repeat(5,1fr)", gap:12 }}>
            {features.map((f, i) => (
              <div key={i}
                style={{ background:f.free ? C.teal : C.surface, borderRadius:R, padding:"20px 16px", border:`1px solid ${f.free ? C.teal : C.outlineVar}`, cursor:"pointer", transition:"all .2s", position:"relative", boxShadow:f.free ? "0 8px 24px rgba(0,101,101,0.25)" : C.sh1 }}
                onMouseEnter={e => { if (!f.free) { e.currentTarget.style.borderColor = C.teal; e.currentTarget.style.transform = "translateY(-2px)"; e.currentTarget.style.boxShadow = C.sh2; } }}
                onMouseLeave={e => { if (!f.free) { e.currentTarget.style.borderColor = C.outlineVar; e.currentTarget.style.transform = "none"; e.currentTarget.style.boxShadow = C.sh1; } }}>
                {f.free && <div style={{ position:"absolute", top:10, right:12 }}><Chip sm style={{ background:"rgba(255,255,255,0.18)", color:"rgba(255,255,255,0.8)", border:"1px solid rgba(255,255,255,0.25)" }}>Free</Chip></div>}
                <div style={{ color:f.free ? "#fff" : C.teal, marginBottom:10 }}><Ic n={f.n} sz={20} col={f.free ? "#fff" : C.teal} /></div>
                <div style={{ fontFamily:F, fontSize:13.5, fontWeight:700, color:f.free ? "#fff" : C.ink, marginBottom:5 }}>{f.t}</div>
                <div style={{ fontFamily:F, fontSize:12.5, color:f.free ? "rgba(255,255,255,0.62)" : C.inkVar, lineHeight:1.55 }}>{f.d}</div>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* PRODUCT SHOWCASE */}
      <section style={{ ...sec, background:C.bg }}>
        <div style={{ ...px }}>
          <div style={{ textAlign:"center", marginBottom:36 }}>
            <Label center>Real screens. Real workflow.</Label>
            <H2 center>The app in action</H2>
          </div>
          <div style={{ display:"flex", gap:8, marginBottom:28, justifyContent:"center", flexWrap:"wrap" }}>
            {tabs.map((t, i) => (
              <button key={i} onClick={() => setTab(i)} style={{ padding:"9px 18px", borderRadius:9999, border:`1.5px solid ${tab === i ? C.teal : C.outlineVar}`, background:tab === i ? C.teal : C.surface, color:tab === i ? "#fff" : C.inkVar, fontWeight:600, fontSize:sm ? 13 : 13.5, cursor:"pointer", transition:"all .18s", fontFamily:F, boxShadow:tab === i ? "0 4px 16px rgba(0,101,101,0.25)" : C.sh1 }}>
                {t.label}
              </button>
            ))}
          </div>
          <div style={{ display:"grid", gridTemplateColumns:md ? "1fr" : "1fr 1fr", gap:md ? 28 : 56, alignItems:"start" }}>
            {!md && <DashMock compact />}
            <div>
              <H3 style={{ marginBottom:8, color:C.teal }}>{tabs[tab].label}</H3>
              <Divider style={{ margin:"14px 0" }} />
              {tabs[tab].pts.map((p, i) => (
                <div key={i} style={{ display:"flex", gap:10, marginBottom:12, alignItems:"flex-start" }}>
                  <div style={{ width:20, height:20, borderRadius:6, background:C.tealLight, border:`1px solid ${C.tealBorder}`, display:"flex", alignItems:"center", justifyContent:"center", flexShrink:0, marginTop:2 }}>
                    <Ic n="check" sz={11} col={C.teal} />
                  </div>
                  <Body muted style={{ fontSize:14.5, lineHeight:1.6 }}>{p}</Body>
                </div>
              ))}
              <div style={{ marginTop:22 }}>
                <Btn v="outline" onClick={() => nav("appointments")}>Explore Features</Btn>
              </div>
            </div>
            {md && <DashMock compact />}
          </div>
        </div>
      </section>

      {/* ANALYTICS DARK SECTION */}
      <section style={{ ...sec, background:C.teal }}>
        <div style={{ ...px }}>
          <div style={{ textAlign:"center", marginBottom:44 }}>
            <Label center light>Complete visibility</Label>
            <H2 light center style={{ maxWidth:580, margin:"12px auto 14px" }}>Your website and business — both fully tracked, automatically</H2>
            <Body light center style={{ maxWidth:500, margin:"0 auto" }}>Unlike standalone booking tools, EasyGrox tracks both the management data and the website data — in one dashboard.</Body>
          </div>
          <div style={{ display:"grid", gridTemplateColumns:sm ? "1fr" : "1fr 1fr", gap:16, marginBottom:32 }}>
            {[
              { title:"Business Analytics", ico:"trend",  items:[{ico:"pos",l:"Revenue",v:"₹1,24,800/mo"},{ico:"calendar",l:"Appointments",v:"312"},{ico:"user",l:"Retention",v:"74%"},{ico:"users",l:"Staff Utilisation",v:"88%"}] },
              { title:"Website Analytics",  ico:"globe",  items:[{ico:"globe",l:"Visitors",v:"2,480/mo"},{ico:"bar",l:"Top Source",v:"Instagram 42%"},{ico:"target",l:"Conversion",v:"18.3%"},{ico:"calendar",l:"Online Bookings",v:"314"}] },
            ].map((col, ci) => (
              <div key={ci} style={{ background:"rgba(255,255,255,0.12)", borderRadius:R, padding:22, border:"1px solid rgba(255,255,255,0.18)" }}>
                <div style={{ display:"flex", alignItems:"center", gap:7, marginBottom:16 }}>
                  <Ic n={col.ico} sz={14} col="rgba(255,255,255,0.6)" />
                  <div style={{ fontFamily:F, fontSize:10, fontWeight:700, color:"rgba(255,255,255,0.5)", letterSpacing:"0.08em", textTransform:"uppercase" }}>{col.title}</div>
                </div>
                <div style={{ display:"grid", gridTemplateColumns:"1fr 1fr", gap:9 }}>
                  {col.items.map((m, mi) => (
                    <div key={mi} style={{ background:"rgba(255,255,255,0.12)", borderRadius:R, padding:13 }}>
                      <Ic n={m.ico} sz={14} col="rgba(255,255,255,0.5)" />
                      <div style={{ fontFamily:F, color:"#fff", fontSize:17, fontWeight:800, marginTop:6 }}>{m.v}</div>
                      <div style={{ fontFamily:F, color:"rgba(255,255,255,0.45)", fontSize:10.5, marginTop:2 }}>{m.l}</div>
                    </div>
                  ))}
                </div>
              </div>
            ))}
          </div>
          <div style={{ textAlign:"center" }}>
            <Btn v="whiteSolid" onClick={() => nav("analytics")}>Explore Analytics</Btn>
          </div>
        </div>
      </section>

      {/* TESTIMONIALS */}
      <section style={{ ...sec, background:C.low }}>
        <div style={{ ...px }}>
          <div style={{ textAlign:"center", marginBottom:40 }}>
            <Label center>From real salon owners</Label>
            <H2 center>Trusted across India</H2>
          </div>
          <div style={{ display:"grid", gridTemplateColumns:sm ? "1fr" : md ? "1fr 1fr" : "1fr 1fr 1fr", gap:16 }}>
            {testimonials.map((t, i) => (
              <Card key={i} tealTop style={{ padding:"26px 22px" }}>
                <div style={{ display:"flex", gap:1, marginBottom:14 }}>
                  {[1,2,3,4,5].map(s => <Ic key={s} n="star" sz={13} col={C.teal} />)}
                </div>
                <Body style={{ fontSize:14.5, lineHeight:1.78, marginBottom:20, fontStyle:"italic", color:C.inkVar }}>"{t.q}"</Body>
                <Divider style={{ marginBottom:16 }} />
                <div style={{ fontFamily:F, fontSize:13.5, fontWeight:700, color:C.ink }}>{t.n}</div>
                <div style={{ fontFamily:F, fontSize:12.5, color:C.inkVar, marginTop:2 }}>{t.r}</div>
              </Card>
            ))}
          </div>
        </div>
      </section>

      {/* PRICING */}
      <section style={{ ...sec, background:C.bg }}>
        <div style={{ ...px }}>
          <div style={{ textAlign:"center", marginBottom:40 }}>
            <Label center>No hidden fees</Label>
            <H2 center>Simple pricing. Free website on every plan.</H2>
          </div>
          {/* Toggle */}
          <div style={{ display:"flex", justifyContent:"center", marginBottom:36 }}>
            <div style={{ display:"inline-flex", alignItems:"center", gap:12, background:C.surface, border:`1px solid ${C.outlineVar}`, borderRadius:9999, padding:"6px 6px 6px 18px", boxShadow:C.sh1 }}>
              <span style={{ fontFamily:F, fontSize:13.5, fontWeight:annual ? 400 : 600, color:annual ? C.inkVar : C.ink }}>Monthly</span>
              <div onClick={() => setAnnual(!annual)} style={{ width:44, height:24, borderRadius:9999, background:annual ? C.teal : C.outlineVar, position:"relative", cursor:"pointer", transition:"background .2s" }}>
                <div style={{ width:18, height:18, borderRadius:"50%", background:"#fff", position:"absolute", top:3, left:annual ? 23 : 3, transition:"left .2s", boxShadow:"0 1px 4px rgba(0,0,0,0.15)" }} />
              </div>
              <span style={{ fontFamily:F, fontSize:13.5, fontWeight:annual ? 600 : 400, color:annual ? C.ink : C.inkVar }}>Annual</span>
              {annual && <Chip teal sm>Save 2 months</Chip>}
            </div>
          </div>
          <div style={{ display:"grid", gridTemplateColumns:sm ? "1fr" : md ? "1fr 1fr" : "1fr 1fr 1fr", gap:16 }}>
            {plans.map((p, i) => (
              <div key={i} style={{ background:p.hi ? C.teal : C.surface, borderRadius:R, padding:"30px 24px", border:`1px solid ${p.hi ? C.teal : C.outlineVar}`, boxShadow:p.hi ? "0 16px 40px rgba(0,101,101,0.28)" : C.sh1, transform:p.hi && !sm ? "scale(1.03)" : "none", position:"relative" }}>
                {p.hi && <div style={{ position:"absolute", top:-13, left:"50%", transform:"translateX(-50%)" }}><span style={{ background:C.surface, color:C.teal, fontFamily:F, fontSize:10, fontWeight:700, padding:"3px 14px", borderRadius:9999, border:`1px solid ${C.tealBorder}`, whiteSpace:"nowrap", textTransform:"uppercase", letterSpacing:"0.06em" }}>Most Popular</span></div>}
                <div style={{ fontFamily:F, fontSize:10, fontWeight:700, letterSpacing:"0.08em", textTransform:"uppercase", color:p.hi ? "rgba(255,255,255,0.4)" : C.inkVar, marginBottom:4 }}>{p.plan}</div>
                <div style={{ fontFamily:F, fontSize:13, color:p.hi ? "rgba(255,255,255,0.5)" : C.inkVar, marginBottom:18 }}>{p.for}</div>
                <div style={{ display:"flex", alignItems:"baseline", gap:4, marginBottom:20 }}>
                  <span style={{ fontFamily:F, fontSize:34, fontWeight:800, color:p.hi ? "#fff" : C.ink }}>{annual ? p.apr : p.price}</span>
                  <span style={{ fontFamily:F, fontSize:13, color:p.hi ? "rgba(255,255,255,0.4)" : C.inkVar }}>/mo</span>
                </div>
                <Divider style={{ background:p.hi ? "rgba(255,255,255,0.15)" : undefined, marginBottom:20 }} />
                {p.feats.map((f, fi) => (
                  <div key={fi} style={{ display:"flex", gap:9, marginBottom:11, alignItems:"flex-start" }}>
                    <Ic n="check" sz={13} col={p.hi ? "rgba(255,255,255,0.6)" : C.teal} style={{ marginTop:2, flexShrink:0 }} />
                    <span style={{ fontFamily:F, fontSize:13.5, color:p.hi ? "rgba(255,255,255,0.8)" : C.inkVar }}>{f}</span>
                  </div>
                ))}
                <button onClick={() => nav("signup")} style={{ width:"100%", height:46, marginTop:20, borderRadius:R, border:`1px solid ${p.hi ? "rgba(255,255,255,0.2)" : C.teal}`, background:p.hi ? "rgba(255,255,255,0.12)" : C.teal, color:"#fff", fontWeight:600, fontSize:14, cursor:"pointer", fontFamily:F, transition:"all .18s" }}
                  onMouseEnter={e => e.currentTarget.style.opacity = "0.85"}
                  onMouseLeave={e => e.currentTarget.style.opacity = "1"}>
                  {p.plan === "Multi-Location" ? "Contact Us" : "Start Free"}
                </button>
              </div>
            ))}
          </div>
          <div style={{ textAlign:"center", marginTop:24 }}>
            <button onClick={() => nav("pricing")} style={{ background:"none", border:"none", color:C.teal, fontFamily:F, fontSize:13.5, fontWeight:600, cursor:"pointer", textDecoration:"underline" }}>
              See full pricing and feature comparison
            </button>
          </div>
        </div>
      </section>

      {/* FAQ */}
      <section style={{ ...sec, background:C.low }}>
        <div style={{ ...px }}>
          <div style={{ display:"grid", gridTemplateColumns:md ? "1fr" : "1fr 1.2fr", gap:md ? 36 : 72, alignItems:"start" }}>
            <div>
              <Label>Common questions</Label>
              <H2 style={{ marginTop:10, marginBottom:18 }}>Everything you need to know</H2>
              <Body muted style={{ marginBottom:24 }}>Our team responds within 24 hours. No bots.</Body>
              <Btn v="outline" onClick={() => nav("contact")}>Talk to Us</Btn>
            </div>
            <div>
              {faqs.map((f, i) => (
                <div key={i} style={{ borderBottom:`1px solid ${C.outlineVar}` }}>
                  <button onClick={() => setOpenFaq(openFaq === i ? null : i)} style={{ width:"100%", padding:"16px 0", background:"none", border:"none", cursor:"pointer", display:"flex", justifyContent:"space-between", alignItems:"center", gap:14, fontFamily:F, textAlign:"left" }}>
                    <span style={{ fontSize:15, fontWeight:600, color:C.ink }}>{f.q}</span>
                    <Ic n={openFaq === i ? "minus" : "plus"} sz={14} col={C.teal} />
                  </button>
                  {openFaq === i && <div style={{ paddingBottom:16, fontFamily:F, fontSize:14.5, color:C.inkVar, lineHeight:1.75 }}>{f.a}</div>}
                </div>
              ))}
            </div>
          </div>
        </div>
      </section>

      {/* CTA */}
      <section style={{ ...sec, background:C.teal }}>
        <div style={{ ...px, textAlign:"center" }}>
          <Label center light>The calm alternative to chaos</Label>
          <H2 light center style={{ maxWidth:540, margin:"12px auto 16px" }}>Your salon, running like it should</H2>
          <Body light center style={{ maxWidth:420, margin:"0 auto 32px" }}>Sign up free. Set up your store. Publish your free website. Manage smarter — in under 30 minutes.</Body>
          <div style={{ display:"flex", gap:12, justifyContent:"center", flexWrap:"wrap" }}>
            <Btn onClick={() => nav("signup")}>Start for Free</Btn>
            <Btn v="white">Watch Demo</Btn>
          </div>
          <p style={{ fontFamily:F, color:"rgba(255,255,255,0.32)", fontSize:12, marginTop:20 }}>No credit card · Free website · Cancel anytime</p>
        </div>
      </section>
    </>
  );
}

/* ─── SHARED FEATURE COMPONENTS ──────────────────────────────── */
const StatBadge = ({ value, label, good }) => (
  <div style={{ background:good ? C.tealLight : C.errBg, borderRadius:R, padding:"14px 18px", border:`1px solid ${good ? C.tealBorder : C.errBorder}`, textAlign:"center" }}>
    <div style={{ fontFamily:F, fontSize:24, fontWeight:800, color:good ? C.teal : C.err, lineHeight:1 }}>{value}</div>
    <div style={{ fontFamily:F, fontSize:12, color:good ? C.teal : C.err, marginTop:5, fontWeight:500 }}>{label}</div>
  </div>
);

/* ─── REUSABLE SHARED COMPONENTS ────────────────────────── */

/* Workflow step component */
const WorkflowStep = ({ n, title, desc, note, final }) => (
  <div style={{ display:"flex", gap:16, alignItems:"flex-start" }}>
    <div style={{ display:"flex", flexDirection:"column", alignItems:"center", flexShrink:0 }}>
      <div style={{ width:36, height:36, borderRadius:"50%", background:final ? C.teal : C.tealLight, border:`2px solid ${C.tealBorder}`, display:"flex", alignItems:"center", justifyContent:"center", fontFamily:F, fontSize:13, fontWeight:800, color:final ? "#fff" : C.teal }}>
        {final ? <Ic n="check" sz={16} col="#fff" /> : n}
      </div>
      {!final && <div style={{ width:2, height:32, background:C.tealLight, marginTop:4 }} />}
    </div>
    <div style={{ paddingTop:6, paddingBottom:final ? 0 : 16 }}>
      <div style={{ fontFamily:F, fontSize:14.5, fontWeight:700, color:C.ink, marginBottom:4 }}>{title}</div>
      <div style={{ fontFamily:F, fontSize:13.5, color:C.inkVar, lineHeight:1.6 }}>{desc}</div>
      {note && <div style={{ marginTop:6 }}><Chip teal sm>{note}</Chip></div>}
    </div>
  </div>
);

/* Pain vs gain comparison */
function PainGain({ pains, gains }) {
  const { sm } = useW();
  return (
    <div style={{ borderRadius:R, overflow:"hidden", border:`1px solid ${C.outlineVar}`, boxShadow:C.sh1 }}>
      {/* Header row */}
      <div style={{ display:"grid", gridTemplateColumns:sm ? "1fr" : "1fr 1fr", gap:0 }}>
        <div style={{ background:"#fef2f2", padding:"16px 24px", display:"flex", alignItems:"center", gap:10, borderBottom:`2px solid #fecaca`, borderRight:sm ? "none" : `1px solid ${C.outlineVar}` }}>
          <div style={{ width:28, height:28, borderRadius:"50%", background:"#fee2e2", border:"1.5px solid #fca5a5", display:"flex", alignItems:"center", justifyContent:"center", flexShrink:0 }}>
            <Ic n="xmark" sz={13} col="#dc2626" />
          </div>
          <div>
            <div style={{ fontFamily:F, fontSize:12, fontWeight:800, letterSpacing:"0.06em", color:"#dc2626", textTransform:"uppercase" }}>Without EasyGrox</div>
            <div style={{ fontFamily:F, fontSize:11.5, color:"#b91c1c", marginTop:1 }}>How most salons operate today</div>
          </div>
        </div>
        <div style={{ background:C.tealLight, padding:"16px 24px", display:"flex", alignItems:"center", gap:10, borderBottom:`2px solid ${C.tealBorder}`, borderTop:sm ? `1px solid ${C.outlineVar}` : "none" }}>
          <div style={{ width:28, height:28, borderRadius:"50%", background:"rgba(0,101,101,0.15)", border:`1.5px solid ${C.teal}`, display:"flex", alignItems:"center", justifyContent:"center", flexShrink:0 }}>
            <Ic n="check" sz={13} col={C.teal} />
          </div>
          <div>
            <div style={{ fontFamily:F, fontSize:12, fontWeight:800, letterSpacing:"0.06em", color:C.teal, textTransform:"uppercase" }}>With EasyGrox</div>
            <div style={{ fontFamily:F, fontSize:11.5, color:C.tealDark, marginTop:1 }}>How your business operates on EasyGrox</div>
          </div>
        </div>
      </div>

      {/* Paired rows */}
      {pains.map((pain, i) => (
        <div key={i} style={{ display:"grid", gridTemplateColumns:sm ? "1fr" : "1fr 1fr", gap:0 }}>
          {/* Pain side */}
          <div style={{ background:i % 2 === 0 ? "#fff9f9" : "#fef2f2", padding:"18px 24px", display:"flex", gap:12, alignItems:"flex-start", borderBottom:`1px solid #fee2e2`, borderRight:sm ? "none" : `1px solid ${C.outlineVar}`, borderTop:sm ? "none" : "none" }}>
            <div style={{ width:22, height:22, borderRadius:6, background:"#fee2e2", border:"1px solid #fca5a5", display:"flex", alignItems:"center", justifyContent:"center", flexShrink:0, marginTop:1 }}>
              <span style={{ fontFamily:F, fontSize:10, fontWeight:800, color:"#dc2626" }}>{i + 1}</span>
            </div>
            <span style={{ fontFamily:F, fontSize:13.5, color:"#7f1d1d", lineHeight:1.62, fontWeight:400 }}>{pain}</span>
          </div>
          {/* Gain side */}
          <div style={{ background:i % 2 === 0 ? C.surface : C.tealLight, padding:"18px 24px", display:"flex", gap:12, alignItems:"flex-start", borderBottom:`1px solid ${C.tealBorder}`, borderTop:sm ? `1px solid ${C.outlineVar}` : "none" }}>
            <div style={{ width:22, height:22, borderRadius:6, background:C.teal, border:`1px solid ${C.tealDark}`, display:"flex", alignItems:"center", justifyContent:"center", flexShrink:0, marginTop:1 }}>
              <Ic n="check" sz={11} col="#fff" />
            </div>
            <span style={{ fontFamily:F, fontSize:13.5, color:C.tealDark, lineHeight:1.62, fontWeight:500 }}>{gains[i] || ""}</span>
          </div>
        </div>
      ))}

      {/* Footer CTA strip */}
      <div style={{ display:"grid", gridTemplateColumns:sm ? "1fr" : "1fr 1fr", gap:0 }}>
        <div style={{ background:"#fef2f2", padding:"14px 24px", display:"flex", alignItems:"center", gap:8, borderTop:"1px solid #fecaca" }}>
          <Ic n="xmark" sz={13} col="#dc2626" />
          <span style={{ fontFamily:F, fontSize:12.5, color:"#b91c1c", fontWeight:500 }}>Manual processes, missed opportunities, daily stress</span>
        </div>
        <div style={{ background:C.teal, padding:"14px 24px", display:"flex", alignItems:"center", gap:8, borderTop:`1px solid ${C.tealDark}`, borderLeft:sm ? "none" : `1px solid ${C.tealDark}` }}>
          <Ic n="check" sz={13} col="#fff" />
          <span style={{ fontFamily:F, fontSize:12.5, color:"rgba(255,255,255,0.9)", fontWeight:500 }}>One platform. Everything connected. Operations on autopilot.</span>
        </div>
      </div>
    </div>
  );
}

/* Scenario card */
const Scenario = ({ emoji, title, desc, outcome }) => (
  <Card style={{ padding:"22px 20px" }}>
    <div style={{ fontSize:28, marginBottom:12, lineHeight:1 }}>{emoji}</div>
    <H4 style={{ marginBottom:6 }}>{title}</H4>
    <Body sm muted style={{ marginBottom:10, lineHeight:1.6 }}>{desc}</Body>
    <div style={{ display:"flex", alignItems:"flex-start", gap:7, background:C.tealLight, border:`1px solid ${C.tealBorder}`, borderRadius:8, padding:"8px 12px" }}>
      <Ic n="zap" sz={13} col={C.teal} style={{ marginTop:1, flexShrink:0 }} />
      <span style={{ fontFamily:F, fontSize:12.5, color:C.tealDark, fontWeight:500, lineHeight:1.5 }}>{outcome}</span>
    </div>
  </Card>
);

/* Testimonial */
const Quote = ({ text, name, role, metric }) => (
  <div style={{ background:C.surface, borderRadius:R, padding:"24px 22px", border:`1px solid ${C.outlineVar}`, boxShadow:C.sh1 }}>
    <div style={{ display:"flex", gap:1, marginBottom:14 }}>
      {[1,2,3,4,5].map(s => <Ic key={s} n="star" sz={13} col={C.teal} />)}
    </div>
    {metric && (
      <div style={{ background:C.tealLight, border:`1px solid ${C.tealBorder}`, borderRadius:8, padding:"8px 14px", marginBottom:14, display:"inline-block" }}>
        <span style={{ fontFamily:F, fontSize:13, fontWeight:700, color:C.teal }}>{metric}</span>
      </div>
    )}
    <p style={{ fontFamily:F, fontSize:14.5, lineHeight:1.78, fontStyle:"italic", color:C.inkVar, marginBottom:18 }}>"{text}"</p>
    <Div style={{ marginBottom:14 }} />
    <div style={{ fontFamily:F, fontSize:13.5, fontWeight:700, color:C.ink }}>{name}</div>
    <div style={{ fontFamily:F, fontSize:12.5, color:C.inkVar, marginTop:2 }}>{role}</div>
  </div>
);

/* Mini calendar mockup */
const CalendarMock = () => {
  const slots = [
    { time:"10:00", name:"Ananya S.", svc:"Hair Colour + Cut", staff:"Priya", w:2, color:"rgba(0,101,101,0.18)", border:C.teal },
    { time:"10:00", name:"", svc:"", staff:"Raj", w:1, color:"#f0f4f3", border:C.outlineVar, empty:true },
    { time:"10:00", name:"Walk-in", svc:"Haircut", staff:"Sunita", w:1, color:"rgba(146,64,14,0.1)", border:"#d97706" },
    { time:"11:00", name:"Ritu M.", svc:"Keratin Treatment", staff:"Priya", w:1, color:"rgba(0,101,101,0.18)", border:C.teal },
    { time:"11:00", name:"Meera K.", svc:"Blowdry Styling", staff:"Raj", w:1, color:"rgba(0,101,101,0.12)", border:C.tealBorder },
    { time:"11:00", name:"Shreya P.", svc:"Hair Colour", staff:"Sunita", w:1, color:"rgba(0,101,101,0.18)", border:C.teal },
    { time:"12:00", name:"Lunch Break", svc:"", staff:"Priya", w:1, color:"#f4f4f4", border:"#d1d5db", empty:true },
    { time:"12:00", name:"Pooja N.", svc:"Haircut", staff:"Raj", w:1, color:"rgba(0,101,101,0.12)", border:C.tealBorder },
    { time:"12:00", name:"", svc:"Available", staff:"Sunita", w:1, color:"#f9fef9", border:"#bbf7d0", empty:true },
  ];

  return (
    <div style={{ background:C.surface, borderRadius:R, overflow:"hidden", border:`1px solid ${C.outlineVar}`, boxShadow:C.sh2, fontFamily:F }}>
      <div style={{ background:C.teal, padding:"12px 16px", display:"flex", justifyContent:"space-between", alignItems:"center" }}>
        <div style={{ color:"#fff", fontSize:13, fontWeight:700 }}>Wednesday, 21 May 2026</div>
        <div style={{ display:"flex", gap:6 }}>
          {["Day","Week","Month"].map((v, i) => (
            <span key={i} style={{ fontSize:10.5, padding:"3px 9px", borderRadius:6, background:i === 0 ? "rgba(255,255,255,0.2)" : "rgba(255,255,255,0.08)", color:"#fff", fontWeight:i === 0 ? 700 : 400 }}>{v}</span>
          ))}
        </div>
      </div>
      <div style={{ padding:"12px 14px" }}>
        <div style={{ display:"grid", gridTemplateColumns:"48px 1fr 1fr 1fr", gap:6, marginBottom:8 }}>
          <div />
          {["Priya","Raj","Sunita"].map((s, i) => (
            <div key={i} style={{ background:C.low, borderRadius:7, padding:"6px", textAlign:"center" }}>
              <div style={{ width:22, height:22, borderRadius:"50%", background:C.teal, display:"flex", alignItems:"center", justifyContent:"center", fontSize:9.5, fontWeight:800, color:"#fff", margin:"0 auto 3px" }}>{s[0]}</div>
              <div style={{ fontSize:10, color:C.inkVar, fontWeight:600 }}>{s}</div>
            </div>
          ))}
        </div>
        {[
          { time:"10:00", row:[slots[0], slots[1], slots[2]] },
          { time:"11:00", row:[slots[3], slots[4], slots[5]] },
          { time:"12:00", row:[slots[6], slots[7], slots[8]] },
        ].map((timeRow, ri) => (
          <div key={ri} style={{ display:"grid", gridTemplateColumns:"48px 1fr 1fr 1fr", gap:6, marginBottom:6 }}>
            <div style={{ fontSize:10, color:C.inkSoft, paddingTop:10, textAlign:"right", paddingRight:6 }}>{timeRow.time}</div>
            {timeRow.row.map((slot, si) => (
              <div key={si} style={{ background:slot.color, borderRadius:6, padding:"6px 8px", borderLeft:`2px solid ${slot.border}`, minHeight:44 }}>
                {!slot.empty ? (
                  <>
                    <div style={{ fontSize:10, fontWeight:700, color:C.ink, overflow:"hidden", textOverflow:"ellipsis", whiteSpace:"nowrap" }}>{slot.name}</div>
                    <div style={{ fontSize:9.5, color:C.inkVar, overflow:"hidden", textOverflow:"ellipsis", whiteSpace:"nowrap" }}>{slot.svc}</div>
                  </>
                ) : (
                  <div style={{ fontSize:9.5, color:C.inkSoft, fontStyle:"italic" }}>{slot.svc || "Blocked"}</div>
                )}
              </div>
            ))}
          </div>
        ))}
        <div style={{ marginTop:10, padding:"8px 12px", background:C.tealLight, borderRadius:8, display:"flex", justifyContent:"space-between", alignItems:"center" }}>
          <span style={{ fontSize:11, color:C.teal, fontWeight:600 }}>8 of 12 slots filled today</span>
          <span style={{ fontSize:11, color:C.teal }}>3 online bookings · 1 walk-in</span>
        </div>
      </div>
    </div>
  );
};

/* POS Mockup */
const POSMock = () => (
  <div style={{ background:"#1a1f2e", borderRadius:R, overflow:"hidden", boxShadow:"0 20px 60px rgba(0,0,0,0.22)", fontFamily:F }}>
    <div style={{ background:"rgba(255,255,255,0.06)", padding:"9px 14px", display:"flex", alignItems:"center", gap:7, borderBottom:"1px solid rgba(255,255,255,0.08)" }}>
      <div style={{ display:"flex", gap:4 }}>{["#ff6b6b","#ffd93d","#6bcb77"].map((cl, i) => <div key={i} style={{ width:8, height:8, borderRadius:"50%", background:cl }} />)}</div>
      <span style={{ fontSize:9, color:"rgba(255,255,255,0.22)", fontFamily:"monospace", marginLeft:6 }}>easygrox.com/billing · Ananya Sharma</span>
    </div>
    <div style={{ padding:16, display:"grid", gridTemplateColumns:"1fr 1fr", gap:12 }}>
      <div>
        <div style={{ color:"rgba(255,255,255,0.38)", fontSize:9, fontWeight:600, marginBottom:10 }}>SERVICES & RETAIL</div>
        {[
          { item:"Hair Colour + Highlights", type:"Service", price:"₹1,200", staff:"Priya" },
          { item:"Blowdry Finish",           type:"Service", price:"₹300",   staff:"Priya" },
          { item:"Kerastase Mask 200ml",     type:"Retail",  price:"₹850",   staff:"—" },
        ].map((row, i) => (
          <div key={i} style={{ display:"flex", justifyContent:"space-between", padding:"7px 0", borderBottom:"1px solid rgba(255,255,255,0.06)" }}>
            <div>
              <div style={{ color:"#fff", fontSize:10.5, fontWeight:600 }}>{row.item}</div>
              <div style={{ color:"rgba(255,255,255,0.35)", fontSize:8.5 }}>{row.type} {row.staff !== "—" ? `· ${row.staff}` : ""}</div>
            </div>
            <div style={{ color:"#76d6d5", fontSize:10.5, fontWeight:700 }}>{row.price}</div>
          </div>
        ))}
        <div style={{ paddingTop:10 }}>
          <div style={{ display:"flex", justifyContent:"space-between", fontSize:9, color:"rgba(255,255,255,0.4)", marginBottom:4 }}>
            <span>Subtotal</span><span>₹2,350</span>
          </div>
          <div style={{ display:"flex", justifyContent:"space-between", fontSize:9, color:"#f87171", marginBottom:6 }}>
            <span>Member Discount (10%)</span><span>-₹235</span>
          </div>
          <div style={{ display:"flex", justifyContent:"space-between", borderTop:"1px solid rgba(255,255,255,0.1)", paddingTop:7 }}>
            <span style={{ color:"#fff", fontSize:12, fontWeight:700 }}>Total</span>
            <span style={{ color:"#76d6d5", fontSize:14, fontWeight:800 }}>₹2,115</span>
          </div>
        </div>
      </div>
      <div>
        <div style={{ color:"rgba(255,255,255,0.38)", fontSize:9, fontWeight:600, marginBottom:10 }}>PAYMENT</div>
        <div style={{ display:"grid", gridTemplateColumns:"1fr 1fr", gap:6, marginBottom:12 }}>
          {[{l:"UPI", a:true},{l:"Card"},{l:"Cash"},{l:"Wallet"}].map((m, i) => (
            <div key={i} style={{ background:m.a ? "rgba(0,101,101,0.5)" : "rgba(255,255,255,0.06)", borderRadius:7, padding:"8px", textAlign:"center", border:`1px solid ${m.a ? C.teal : "rgba(255,255,255,0.08)"}`, cursor:"pointer" }}>
              <div style={{ color:m.a ? "#76d6d5" : "rgba(255,255,255,0.45)", fontSize:10, fontWeight:600 }}>{m.l}</div>
            </div>
          ))}
        </div>
        <button style={{ width:"100%", padding:"11px", borderRadius:R, background:C.teal, border:"none", color:"#fff", fontSize:12, fontWeight:700, cursor:"pointer" }}>
          Collect ₹2,115
        </button>
        <div style={{ color:"rgba(255,255,255,0.3)", fontSize:9, textAlign:"center", marginTop:6 }}>Send receipt via WhatsApp · SMS · Email</div>
        <div style={{ marginTop:10, background:"rgba(255,255,255,0.04)", borderRadius:7, padding:"8px 10px" }}>
          <div style={{ color:"rgba(255,255,255,0.35)", fontSize:9, marginBottom:4 }}>Today so far</div>
          <div style={{ display:"flex", justifyContent:"space-between" }}>
            <span style={{ color:"#fff", fontSize:11, fontWeight:700 }}>₹14,200</span>
            <span style={{ color:"#76d6d5", fontSize:11 }}>9 transactions</span>
          </div>
        </div>
      </div>
    </div>
  </div>
);

/* Analytics Mockup */
const AnalyticsMock = () => {
  const bars = [38, 52, 44, 68, 55, 74, 100];
  return (
    <div style={{ background:"#1a1f2e", borderRadius:R, overflow:"hidden", boxShadow:"0 20px 60px rgba(0,0,0,0.22)", fontFamily:F }}>
      <div style={{ background:"rgba(255,255,255,0.06)", padding:"9px 14px", display:"flex", alignItems:"center", gap:7, borderBottom:"1px solid rgba(255,255,255,0.08)" }}>
        <div style={{ display:"flex", gap:4 }}>{["#ff6b6b","#ffd93d","#6bcb77"].map((cl, i) => <div key={i} style={{ width:8, height:8, borderRadius:"50%", background:cl }} />)}</div>
        <span style={{ fontSize:9, color:"rgba(255,255,255,0.22)", fontFamily:"monospace", marginLeft:6 }}>easygrox.com/analytics</span>
      </div>
      <div style={{ padding:16 }}>
        <div style={{ display:"flex", justifyContent:"space-between", alignItems:"center", marginBottom:14 }}>
          <div style={{ color:"#fff", fontSize:12.5, fontWeight:700 }}>Analytics Dashboard</div>
          <div style={{ display:"flex", gap:4 }}>
            {["Today","Week","Month"].map((p, i) => <span key={i} style={{ fontSize:9, padding:"2px 8px", borderRadius:9999, background:i === 2 ? "rgba(0,101,101,0.5)" : "rgba(255,255,255,0.07)", color:i === 2 ? "#76d6d5" : "rgba(255,255,255,0.4)" }}>{p}</span>)}
          </div>
        </div>
        <div style={{ background:"rgba(255,255,255,0.05)", borderRadius:8, padding:"12px 14px", marginBottom:12 }}>
          <div style={{ display:"flex", justifyContent:"space-between", marginBottom:10 }}>
            <div>
              <div style={{ color:"rgba(255,255,255,0.35)", fontSize:9 }}>Monthly Revenue</div>
              <div style={{ color:"#fff", fontSize:22, fontWeight:800 }}>₹1,24,800</div>
              <div style={{ color:"#6bcb77", fontSize:9.5, marginTop:2 }}>+18.4% vs last month</div>
            </div>
            <div style={{ textAlign:"right" }}>
              <div style={{ color:"rgba(255,255,255,0.35)", fontSize:9 }}>Booking Conversion</div>
              <div style={{ color:"#76d6d5", fontSize:16, fontWeight:700 }}>18.3%</div>
            </div>
          </div>
          <div style={{ display:"flex", alignItems:"flex-end", gap:3, height:44 }}>
            {bars.map((h, i) => <div key={i} style={{ flex:1, borderRadius:"2px 2px 0 0", height:`${h}%`, background:i === 6 ? C.teal : "rgba(0,101,101,0.35)" }} />)}
          </div>
        </div>
        <div style={{ display:"grid", gridTemplateColumns:"1fr 1fr 1fr", gap:8 }}>
          {[{l:"Appointments",v:"312",c:"#76d6d5"},{l:"Retention Rate",v:"74%",c:"#6bcb77"},{l:"No-show Rate",v:"4.1%",c:"#ffd93d"}].map((s, i) => (
            <div key={i} style={{ background:"rgba(255,255,255,0.06)", borderRadius:7, padding:"9px 10px" }}>
              <div style={{ color:s.c, fontSize:14, fontWeight:800 }}>{s.v}</div>
              <div style={{ color:"rgba(255,255,255,0.38)", fontSize:9, marginTop:2 }}>{s.l}</div>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
};

/* Client Profile Mockup */
const ClientMock = () => (
  <div style={{ background:"#1a1f2e", borderRadius:R, overflow:"hidden", boxShadow:"0 20px 60px rgba(0,0,0,0.22)", fontFamily:F }}>
    <div style={{ background:"rgba(255,255,255,0.06)", padding:"9px 14px", display:"flex", alignItems:"center", gap:7, borderBottom:"1px solid rgba(255,255,255,0.08)" }}>
      <div style={{ display:"flex", gap:4 }}>{["#ff6b6b","#ffd93d","#6bcb77"].map((cl, i) => <div key={i} style={{ width:8, height:8, borderRadius:"50%", background:cl }} />)}</div>
      <span style={{ fontSize:9, color:"rgba(255,255,255,0.22)", fontFamily:"monospace", marginLeft:6 }}>easygrox.com/clients/ananya-sharma</span>
    </div>
    <div style={{ padding:16 }}>
      <div style={{ display:"flex", gap:12, alignItems:"center", marginBottom:14, paddingBottom:12, borderBottom:"1px solid rgba(255,255,255,0.08)" }}>
        <div style={{ width:42, height:42, borderRadius:"50%", background:C.teal, display:"flex", alignItems:"center", justifyContent:"center", fontSize:15, fontWeight:800, color:"#fff", flexShrink:0 }}>AS</div>
        <div style={{ flex:1 }}>
          <div style={{ color:"#fff", fontSize:13, fontWeight:700 }}>Ananya Sharma</div>
          <div style={{ color:"rgba(255,255,255,0.38)", fontSize:9.5 }}>Client since Jan 2024 · 28 visits</div>
        </div>
        <div style={{ background:"rgba(0,101,101,0.35)", borderRadius:9999, padding:"3px 10px" }}>
          <span style={{ color:"#76d6d5", fontSize:9, fontWeight:700 }}>VIP Client</span>
        </div>
      </div>
      <div style={{ display:"grid", gridTemplateColumns:"1fr 1fr 1fr", gap:7, marginBottom:12 }}>
        {[{l:"Total Spent",v:"₹32,450"},{l:"Avg/Visit",v:"₹1,159"},{l:"Last Visit",v:"12 May"}].map((s, i) => (
          <div key={i} style={{ background:"rgba(255,255,255,0.06)", borderRadius:7, padding:"8px 9px", textAlign:"center" }}>
            <div style={{ color:"#76d6d5", fontSize:12, fontWeight:700 }}>{s.v}</div>
            <div style={{ color:"rgba(255,255,255,0.35)", fontSize:8 }}>{s.l}</div>
          </div>
        ))}
      </div>
      <div style={{ background:"rgba(255,255,255,0.04)", borderRadius:8, padding:"10px 12px", marginBottom:10 }}>
        <div style={{ color:"rgba(255,255,255,0.35)", fontSize:9, fontWeight:600, marginBottom:7 }}>COLOUR FORMULA NOTES</div>
        <div style={{ color:"rgba(255,255,255,0.7)", fontSize:11, lineHeight:1.6 }}>Wella 9/1 + 30vol. Highlights: Blondor Freelights. Processing: 35min. Toner: /81. Sensitivity: None. Preferred: Priya.</div>
      </div>
      <div style={{ color:"rgba(255,255,255,0.35)", fontSize:9, fontWeight:600, marginBottom:7 }}>RECENT VISITS</div>
      {[{d:"12 May",s:"Hair Colour + Cut",a:"₹1,500"},{d:"28 Apr",s:"Keratin Treatment",a:"₹2,800"}].map((v, i) => (
        <div key={i} style={{ display:"flex", justifyContent:"space-between", padding:"5px 0", borderBottom:i < 1 ? "1px solid rgba(255,255,255,0.06)" : "none" }}>
          <div>
            <div style={{ color:"#fff", fontSize:10 }}>{v.s}</div>
            <div style={{ color:"rgba(255,255,255,0.3)", fontSize:8.5 }}>{v.d}</div>
          </div>
          <div style={{ color:"#76d6d5", fontSize:10, fontWeight:700 }}>{v.a}</div>
        </div>
      ))}
    </div>
  </div>
);

/* Dashboard/Staff mockup */
const StaffMock = () => (
  <div style={{ background:"#1a1f2e", borderRadius:R, overflow:"hidden", boxShadow:"0 20px 60px rgba(0,0,0,0.22)", fontFamily:F }}>
    <div style={{ background:"rgba(255,255,255,0.06)", padding:"9px 14px", display:"flex", alignItems:"center", gap:7, borderBottom:"1px solid rgba(255,255,255,0.08)" }}>
      <div style={{ display:"flex", gap:4 }}>{["#ff6b6b","#ffd93d","#6bcb77"].map((cl, i) => <div key={i} style={{ width:8, height:8, borderRadius:"50%", background:cl }} />)}</div>
      <span style={{ fontSize:9, color:"rgba(255,255,255,0.22)", fontFamily:"monospace", marginLeft:6 }}>easygrox.com/staff</span>
    </div>
    <div style={{ padding:16 }}>
      <div style={{ display:"flex", justifyContent:"space-between", alignItems:"center", marginBottom:14 }}>
        <div style={{ color:"#fff", fontSize:12.5, fontWeight:700 }}>Team Performance</div>
        <div style={{ background:"rgba(0,101,101,0.3)", borderRadius:9999, padding:"3px 10px", fontSize:9, color:"#76d6d5", fontWeight:600 }}>This Month</div>
      </div>
      {[
        { name:"Priya Sharma",  role:"Senior Stylist", appts:118, rev:"₹38,400", rating:4.9, util:91, col:C.teal },
        { name:"Raj Kumar",     role:"Stylist",        appts:96,  rev:"₹29,200", rating:4.7, util:78, col:"#8b4523" },
        { name:"Sunita Devi",   role:"Junior Stylist", appts:74,  rev:"₹21,800", rating:4.8, util:65, col:"#4a6741" },
      ].map((s, i) => (
        <div key={i} style={{ background:"rgba(255,255,255,0.05)", borderRadius:8, padding:"10px 12px", marginBottom:8 }}>
          <div style={{ display:"flex", alignItems:"center", gap:9, marginBottom:8 }}>
            <div style={{ width:30, height:30, borderRadius:"50%", background:s.col, display:"flex", alignItems:"center", justifyContent:"center", fontSize:11, fontWeight:800, color:"#fff", flexShrink:0 }}>{s.name.split(" ").map(n => n[0]).join("")}</div>
            <div style={{ flex:1 }}>
              <div style={{ color:"#fff", fontSize:11, fontWeight:600 }}>{s.name}</div>
              <div style={{ color:"rgba(255,255,255,0.35)", fontSize:8.5 }}>{s.role}</div>
            </div>
            <div style={{ textAlign:"right" }}>
              <div style={{ color:"#76d6d5", fontSize:10.5, fontWeight:700 }}>{s.rev}</div>
              <div style={{ color:"#ffd93d", fontSize:9 }}>★ {s.rating}</div>
            </div>
          </div>
          <div style={{ display:"flex", gap:8, alignItems:"center" }}>
            <div style={{ flex:1, background:"rgba(255,255,255,0.08)", borderRadius:9999, height:4, overflow:"hidden" }}>
              <div style={{ width:`${s.util}%`, height:"100%", background:C.teal, borderRadius:9999 }} />
            </div>
            <span style={{ color:"rgba(255,255,255,0.4)", fontSize:8.5, flexShrink:0 }}>{s.util}% · {s.appts} appts</span>
          </div>
        </div>
      ))}
    </div>
  </div>
);

/* Booking website mockup */
const WebsiteMock = () => (
  <div style={{ background:C.surface, borderRadius:R, overflow:"hidden", boxShadow:C.sh2, border:`1px solid ${C.outlineVar}`, fontFamily:F }}>
    <div style={{ background:C.teal, padding:"10px 16px", display:"flex", alignItems:"center", gap:8 }}>
      <div style={{ display:"flex", gap:4 }}>{[1,2,3].map(i => <div key={i} style={{ width:8, height:8, borderRadius:"50%", background:"rgba(255,255,255,0.2)" }} />)}</div>
      <div style={{ flex:1, background:"rgba(255,255,255,0.15)", borderRadius:6, height:17, display:"flex", alignItems:"center", padding:"0 9px", gap:5 }}>
        <Ic n="lock" sz={9} col="rgba(255,255,255,0.7)" />
        <span style={{ fontSize:9, color:"rgba(255,255,255,0.85)", fontFamily:"monospace" }}>luxehair.easygrox.com</span>
      </div>
    </div>
    <div style={{ padding:14 }}>
      <div style={{ textAlign:"center", marginBottom:14, paddingBottom:12, borderBottom:`1px solid ${C.outlineVar}` }}>
        <div style={{ width:36, height:36, borderRadius:"50%", background:C.tealLight, border:`2px solid ${C.teal}`, margin:"0 auto 8px", display:"flex", alignItems:"center", justifyContent:"center" }}>
          <Ic n="scissors" sz={16} col={C.teal} />
        </div>
        <div style={{ fontSize:13, fontWeight:800, color:C.ink }}>Luxe Hair Studio</div>
        <div style={{ fontSize:9.5, color:C.inkVar, margin:"2px 0" }}>Jaipur · Open until 8 PM · 4.9 stars</div>
        <div style={{ display:"flex", gap:5, justifyContent:"center", marginTop:8, flexWrap:"wrap" }}>
          {["Hair","Colour","Keratin","Bridal"].map((t, i) => <Chip key={i} teal sm>{t}</Chip>)}
        </div>
      </div>
      <div style={{ background:C.teal, borderRadius:R, padding:"9px", textAlign:"center", color:"#fff", fontSize:11, fontWeight:700, marginBottom:12, cursor:"pointer" }}>
        Book Appointment
      </div>
      <div style={{ marginBottom:12 }}>
        <div style={{ fontSize:10, fontWeight:700, color:C.ink, marginBottom:8 }}>Popular Services</div>
        {[
          { name:"Haircut & Styling",    dur:"45 min", price:"₹500", avail:"Today" },
          { name:"Hair Colour",          dur:"90 min", price:"₹1,200", avail:"Tomorrow" },
          { name:"Keratin Treatment",    dur:"2 hrs",  price:"₹2,800", avail:"Thu" },
        ].map((s, i) => (
          <div key={i} style={{ display:"flex", justifyContent:"space-between", alignItems:"center", padding:"6px 0", borderBottom:i < 2 ? `1px solid ${C.outlineVar}` : "none" }}>
            <div>
              <div style={{ fontSize:10.5, fontWeight:600, color:C.ink }}>{s.name}</div>
              <div style={{ fontSize:9, color:C.inkVar }}>{s.dur} · Next: {s.avail}</div>
            </div>
            <div style={{ display:"flex", alignItems:"center", gap:7 }}>
              <span style={{ fontSize:10.5, fontWeight:700, color:C.teal }}>{s.price}</span>
              <span style={{ background:C.tealLight, borderRadius:6, padding:"2px 7px", fontSize:8.5, color:C.teal, fontWeight:600 }}>Book</span>
            </div>
          </div>
        ))}
      </div>
      <div style={{ background:C.low, borderRadius:R, padding:"10px 12px" }}>
        <div style={{ fontSize:10, fontWeight:700, color:C.ink, marginBottom:8 }}>Choose Your Stylist</div>
        <div style={{ display:"flex", gap:8 }}>
          {[{n:"Priya",r:"Senior Stylist",a:"Next: 10am"},{n:"Raj",r:"Stylist",a:"Next: 12pm"},{n:"Sunita",r:"Junior",a:"Available"}].map((s, i) => (
            <div key={i} style={{ flex:1, background:C.surface, borderRadius:8, padding:"8px 6px", textAlign:"center", border:`1px solid ${C.outlineVar}`, cursor:"pointer" }}>
              <div style={{ width:24, height:24, borderRadius:"50%", background:C.teal, display:"flex", alignItems:"center", justifyContent:"center", fontSize:9, fontWeight:800, color:"#fff", margin:"0 auto 4px" }}>{s.n[0]}</div>
              <div style={{ fontSize:9, color:C.ink, fontWeight:600 }}>{s.n}</div>
              <div style={{ fontSize:8, color:C.teal }}>{s.a}</div>
            </div>
          ))}
        </div>
      </div>
    </div>
  </div>
);


/* ─── FEATURE PAGE SHELL + ALL 10 FEATURE PAGES ─────────────── */
function FeaturePageShell({ nav, title, label, tagline, mockup, children, relatedPages }) {
  const { sm, md } = useW();
  const px = { padding:`0 ${sm ? 16 : 24}px`, maxWidth:1200, margin:"0 auto" };
  const SP = sm ? "52px 0" : "80px 0";

  return (
    <>
      {/* ─── 1. HERO ─── */}
      <section style={{ padding:sm ? "52px 0 40px" : "80px 0 64px", background:C.bg }}>
        <div style={{ ...px }}>
          <div style={{ display:"flex", gap:6, alignItems:"center", marginBottom:24, fontFamily:F, fontSize:13, color:C.inkVar, flexWrap:"wrap" }}>
            <button onClick={() => nav("home")} style={{ background:"none", border:"none", color:C.inkVar, cursor:"pointer", fontFamily:F, fontSize:13 }}>Home</button>
            <Ic n="chevR" sz={11} col={C.inkSoft} />
            <button onClick={() => nav("features")} style={{ background:"none", border:"none", color:C.inkVar, cursor:"pointer", fontFamily:F, fontSize:13 }}>Features</button>
            <Ic n="chevR" sz={11} col={C.inkSoft} />
            <span style={{ color:C.teal, fontWeight:600 }}>{label}</span>
          </div>
          <div style={{ display:"grid", gridTemplateColumns:md ? "1fr" : "1fr 1fr", gap:md ? 36 : 64, alignItems:"start" }}>
            <div>
              <Label>{label}</Label>
              <H1 style={{ marginBottom:20, marginTop:10 }}>{title}</H1>
              <Body muted style={{ fontSize:sm ? 16 : 18, marginBottom:32, lineHeight:1.72 }}>{tagline}</Body>
              <div style={{ display:"flex", gap:12, flexWrap:"wrap", marginBottom:24 }}>
                <Btn onClick={() => nav("signup")}>Start for Free — No Credit Card</Btn>
                <Btn v="outline">Watch Feature Demo</Btn>
              </div>
              <div style={{ display:"flex", gap:8, flexWrap:"wrap" }}>
                {["Setup in 30 minutes","Free website included","No technical skill needed"].map((t, i) => (
                  <span key={i} style={{ display:"flex", alignItems:"center", gap:5, fontFamily:F, fontSize:12.5, color:C.inkVar, background:C.surface, border:`1px solid ${C.outlineVar}`, borderRadius:9999, padding:"4px 12px" }}>
                    <Ic n="check" sz={11} col={C.teal} />{t}
                  </span>
                ))}
              </div>
            </div>
            <div>{mockup}</div>
          </div>
        </div>
      </section>

      {/* ─── CHILDREN (all page-specific sections) ─── */}
      {children}

      {/* ─── RELATED FEATURES ─── */}
      {relatedPages && relatedPages.length > 0 && (
        <section style={{ padding:SP, background:C.bg }}>
          <div style={{ ...px }}>
            <H3 style={{ marginBottom:20 }}>Works best alongside</H3>
            <div style={{ display:"flex", gap:10, flexWrap:"wrap" }}>
              {relatedPages.map((rp, i) => (
                <button key={i} onClick={() => nav(rp.key)}
                  style={{ display:"flex", alignItems:"center", gap:8, padding:"11px 18px", borderRadius:R, border:`1px solid ${C.outlineVar}`, background:C.surface, fontSize:14, fontWeight:600, color:C.teal, cursor:"pointer", fontFamily:F, transition:"all .15s", boxShadow:C.sh1 }}
                  onMouseEnter={e => { e.currentTarget.style.borderColor = C.teal; e.currentTarget.style.background = C.tealLight; }}
                  onMouseLeave={e => { e.currentTarget.style.borderColor = C.outlineVar; e.currentTarget.style.background = C.surface; }}>
                  {rp.label} <Ic n="chevR" sz={13} col={C.teal} />
                </button>
              ))}
            </div>
          </div>
        </section>
      )}

      {/* ─── FINAL CTA ─── */}
      <section style={{ padding:sm ? "52px 0" : "72px 0", background:C.teal }}>
        <div style={{ ...px, textAlign:"center" }}>
          <H2 light center style={{ marginBottom:14 }}>Ready to transform how you manage {label.toLowerCase()}?</H2>
          <Body light center style={{ marginBottom:28, maxWidth:480, margin:"0 auto 28px" }}>Start free today. Your booking website is included from day one. Setup takes 30 minutes.</Body>
          <div style={{ display:"flex", gap:12, justifyContent:"center", flexWrap:"wrap" }}>
            <Btn onClick={() => nav("signup")}>Start for Free</Btn>
            <Btn v="white" onClick={() => nav("pricing")}>See Pricing</Btn>
          </div>
          <p style={{ fontFamily:F, color:"rgba(255,255,255,0.35)", fontSize:12, marginTop:18 }}>No credit card · Free website · Cancel anytime</p>
        </div>
      </section>
    </>
  );
}

/* ═══════════════════════════════════════════════════════════
   1. APPOINTMENTS & CALENDAR
═══════════════════════════════════════════════════════════ */
function AppointmentsPage({ nav }) {
  const { sm, md } = useW();
  const px = { padding:`0 ${sm ? 16 : 24}px`, maxWidth:1200, margin:"0 auto" };
  const SP = sm ? "52px 0" : "80px 0";
  const [activeTab, setActiveTab] = useState(0);

  const viewTypes = [
    { label:"Daily View", desc:"See every appointment across all staff columns for the current day. Perfect for the front desk — instant clarity on who is with which client, what is upcoming, and where gaps exist.", tags:["Online bookings","Walk-ins","Manual entries","Status colour codes"] },
    { label:"Weekly View", desc:"Plan the whole week at a glance. Spot over-booked days and under-utilised gaps. Drag appointments between days or staff members without losing any booking data.", tags:["Drag-and-drop reschedule","Staff workload balance","Gap identification","Week comparison"] },
    { label:"Monthly View", desc:"See the big picture — peak periods, slow weeks, holiday gaps. Use monthly view every Monday morning to pre-fill slow days before they happen.", tags:["Peak period planning","Holiday management","Promo timing","Capacity planning"] },
  ];

  return (
    <FeaturePageShell nav={nav} label="Appointments & Calendar" title="Your entire salon. One calendar. Total control."
      tagline="Every booking your salon handles — online, walk-in, phone, manual — lands on one live calendar. Staff assigned, time blocked, reminders sent. From the moment a client books to the moment they leave, EasyGrox handles the operational layer so your team just focuses on the work."
      mockup={<CalendarMock />}
      relatedPages={[{key:"pos",label:"POS & Billing"},{key:"clients",label:"Client Management"},{key:"staffMgmt",label:"Staff Management"},{key:"website",label:"Free Booking Website"}]}>

      {/* ─── 2. PAIN REALITY SECTION ─── */}
      <section style={{ padding:SP, background:C.low }}>
        <div style={{ ...px }}>
          <div style={{ textAlign:"center", marginBottom:44 }}>
            <Label center>The daily reality without EasyGrox</Label>
            <H2 center style={{ maxWidth:580, margin:"12px auto 0" }}>Does your morning look like this?</H2>
          </div>
          <PainGain
            pains={[
              "WhatsApp fills up overnight with booking requests you have to manually sort",
              "Two clients arrive at 11am for the same stylist — double-booking chaos",
              "Walk-ins interrupt scheduled clients and you have no system to manage the queue",
              "3 no-shows today because reminders were sent manually (or not at all)",
              "Your team has no idea who they are seeing next — checking a paper register",
              "You cannot see which hours are peak and which are dead without a spreadsheet",
            ]}
            gains={[
              "Online bookings fill your calendar automatically while you sleep",
              "Double-bookings are impossible — the system blocks conflicting time slots",
              "Walk-ins added in 10 seconds and assigned to the next available staff",
              "Every client gets automatic SMS/WhatsApp 24h before — no-shows drop by 60%",
              "Every staff member sees their own day on their phone — no paper needed",
              "Peak hour heatmap shows you exactly when demand is highest",
            ]}
          />
        </div>
      </section>

      {/* ─── 3. WORKFLOW WALKTHROUGH ─── */}
      <section style={{ padding:SP, background:C.bg }}>
        <div style={{ ...px }}>
          <div style={{ display:"grid", gridTemplateColumns:md ? "1fr" : "1fr 1fr", gap:64, alignItems:"start" }}>
            <div>
              <Label>A booking from start to finish</Label>
              <H2 style={{ marginBottom:14, marginTop:10 }}>How a booking actually flows through EasyGrox</H2>
              <Body muted style={{ marginBottom:32 }}>From the moment a client books online to the moment they walk out — every step is handled without your team lifting a finger.</Body>
              <WorkflowStep n="1" title="Client books online, any time" desc="Client visits your free EasyGrox booking website, browses services and stylist availability, and confirms — 2am or 2pm, it works." note="From your free booking website" />
              <WorkflowStep n="2" title="Appointment lands in calendar instantly" desc="The booking appears in your calendar, assigned to the correct staff member, with the service duration blocked." note="Zero manual entry" />
              <WorkflowStep n="3" title="Confirmation SMS sent automatically" desc="Client receives a branded confirmation message with appointment time, location, and a one-tap reschedule link." note="Automatic" />
              <WorkflowStep n="4" title="Reminder sent 24 hours before" desc="Client gets a WhatsApp or SMS reminder the day before — reducing no-shows to an industry-low 4-6%." note="Configurable timing" />
              <WorkflowStep n="5" title="Staff notified on their personal dashboard" desc="Each stylist sees their own upcoming appointments, can check client history and colour formulas before arrival." note="Staff mobile view" />
              <WorkflowStep n="6" title="Client arrives, appointment marked active" desc="Front desk taps the appointment to mark it in-progress. Status updates live across all staff dashboards." note="Real-time updates" />
              <WorkflowStep n="7" title="Service complete — bill opened instantly" desc="Tap Complete and the billing screen opens pre-loaded with services. Bill in under 60 seconds." note="Connected to POS" final />
            </div>
            <div style={{ position:"sticky", top:80 }}>
              <CalendarMock />
              <div style={{ marginTop:16, display:"grid", gridTemplateColumns:"1fr 1fr", gap:10 }}>
                <StatBadge value="60%" label="Fewer no-shows" good />
                <StatBadge value="10s" label="Walk-in added" good />
                <StatBadge value="0" label="Double-bookings possible" good />
                <StatBadge value="24/7" label="Online booking open" good />
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* ─── 4. CALENDAR VIEWS ─── */}
      <section style={{ padding:SP, background:C.low }}>
        <div style={{ ...px }}>
          <div style={{ textAlign:"center", marginBottom:40 }}>
            <Label center>Three views. One calendar.</Label>
            <H2 center>See your business the way you need to</H2>
          </div>
          <div style={{ display:"flex", gap:8, justifyContent:"center", marginBottom:32, flexWrap:"wrap" }}>
            {viewTypes.map((v, i) => (
              <button key={i} onClick={() => setActiveTab(i)} style={{ padding:"9px 20px", borderRadius:9999, border:`1.5px solid ${activeTab === i ? C.teal : C.outlineVar}`, background:activeTab === i ? C.teal : C.surface, color:activeTab === i ? "#fff" : C.inkVar, fontWeight:600, fontSize:sm ? 13 : 13.5, cursor:"pointer", transition:"all .18s", fontFamily:F }}>
                {v.label}
              </button>
            ))}
          </div>
          <Card style={{ padding:"32px 28px" }}>
            <div style={{ display:"grid", gridTemplateColumns:md ? "1fr" : "1fr 1fr", gap:40, alignItems:"center" }}>
              <div>
                <H3 style={{ marginBottom:14 }}>{viewTypes[activeTab].label}</H3>
                <Body muted style={{ marginBottom:22, fontSize:15 }}>{viewTypes[activeTab].desc}</Body>
                <div style={{ display:"flex", flexWrap:"wrap", gap:8 }}>
                  {viewTypes[activeTab].tags.map((t, i) => <Chip key={i} teal sm>{t}</Chip>)}
                </div>
              </div>
              <CalendarMock />
            </div>
          </Card>
        </div>
      </section>

      {/* ─── 5. REAL SCENARIOS ─── */}
      <section style={{ padding:SP, background:C.bg }}>
        <div style={{ ...px }}>
          <div style={{ textAlign:"center", marginBottom:40 }}>
            <Label center>Real situations. Real solutions.</Label>
            <H2 center>How EasyGrox handles the daily chaos</H2>
          </div>
          <div style={{ display:"grid", gridTemplateColumns:sm ? "1fr" : md ? "1fr 1fr" : "repeat(3,1fr)", gap:16 }}>
            <Scenario emoji="🚶" title="Walk-in during a full afternoon" desc="Your 3pm rush — 3 stylists all booked. A walk-in arrives asking for a quick trim." outcome="Front desk checks the calendar in real time, spots Raj has a 20-min buffer between 3:40 and 4pm, slots the walk-in instantly. No chaos." />
            <Scenario emoji="❌" title="Client cancels 2 hours before" desc="Meena cancels her 2pm keratin treatment with 2 hours notice. That is 2 hours of Priya's time unbooked." outcome="EasyGrox flags the gap. You run a quick WhatsApp blast to the waiting-list segment. The slot fills in 40 minutes." />
            <Scenario emoji="📱" title="Late-night online booking surge" desc="You post a reel at 9pm. 12 clients try to book between 9pm and midnight." outcome="All 12 land directly in your calendar — assigned to the correct stylist, time blocked, confirmations sent. You wake up to a full tomorrow." />
            <Scenario emoji="💇" title="Regular client colour appointment" desc="Ananya books her regular monthly colour with Priya. It is a 3-hour service." outcome="EasyGrox blocks 3 hours of Priya's calendar, stores the colour formula in the appointment card, and sends a reminder with prep instructions the day before." />
            <Scenario emoji="🎂" title="Birthday booking peak — December" desc="December is your busiest month. You need to see exactly which days are overloaded and which have gaps." outcome="Monthly view shows the full December grid. You spot two over-booked Saturdays and open online slots for Sundays to absorb the overflow." />
            <Scenario emoji="⏰" title="Staff member calls in sick" desc="Sunita calls in sick at 7am. She has 6 appointments today." outcome="Open Sunita's day view, bulk-reassign her appointments to available staff with one action. All 6 clients get automatic SMS notifications." />
          </div>
        </div>
      </section>

      {/* ─── 6. CAPABILITIES GRID ─── */}
      <section style={{ padding:SP, background:C.low }}>
        <div style={{ ...px }}>
          <div style={{ textAlign:"center", marginBottom:40 }}>
            <Label center>Full capability set</Label>
            <H2 center>Every appointment management tool you need</H2>
          </div>
          <div style={{ display:"grid", gridTemplateColumns:sm ? "1fr" : md ? "1fr 1fr" : "repeat(4,1fr)", gap:14 }}>
            {[
              { n:"calendar", t:"Live Calendar",         d:"Real-time calendar that updates instantly across all devices as bookings arrive." },
              { n:"users",    t:"Staff Column View",     d:"See every team member side by side on the same screen." },
              { n:"globe",    t:"Online Booking",        d:"Clients book from your free website 24/7. Appears in calendar instantly." },
              { n:"zap",      t:"Walk-In in 10 Seconds", d:"Tap time slot, select service, assign staff — confirmed." },
              { n:"bell",     t:"Auto Reminders",        d:"SMS and WhatsApp before every appointment. Configurable timing." },
              { n:"refresh",  t:"Drag-and-Drop Move",   d:"Reschedule by dragging. Client notified automatically." },
              { n:"clock",    t:"Buffer Time",           d:"Set automatic gaps between appointments per service or per staff." },
              { n:"shield",   t:"Double-Booking Guard",  d:"System blocks conflicting bookings. Impossible to double-book." },
              { n:"repeat",   t:"Recurring Appointments",d:"Set weekly or monthly recurring bookings for regular clients." },
              { n:"tag",      t:"Booking Labels",        d:"Colour-code by service type, status, or staff member." },
              { n:"bar",      t:"Peak Hour Heatmap",     d:"See which hours drive the most bookings. Schedule accordingly." },
              { n:"lock",     t:"Role-Based Calendar",   d:"Front desk sees all. Staff see only their own appointments." },
            ].map((f, i) => (
              <Card key={i} style={{ padding:"20px 16px" }}>
                <IcBox n={f.n} />
                <H4 style={{ marginBottom:6 }}>{f.t}</H4>
                <Body sm muted style={{ lineHeight:1.6 }}>{f.d}</Body>
              </Card>
            ))}
          </div>
        </div>
      </section>

      {/* ─── 7. SOCIAL PROOF ─── */}
      <section style={{ padding:SP, background:C.bg }}>
        <div style={{ ...px }}>
          <div style={{ textAlign:"center", marginBottom:40 }}>
            <Label center>What owners say</Label>
            <H2 center>From salons already running on EasyGrox</H2>
          </div>
          <div style={{ display:"grid", gridTemplateColumns:sm ? "1fr" : md ? "1fr 1fr" : "1fr 1fr 1fr", gap:16 }}>
            <Quote metric="No-shows dropped from 22% to 4%" text="The automatic reminders changed everything. Before EasyGrox I was manually messaging clients the night before. Now it just happens and my no-show rate has crashed." name="Priya Sharma" role="Luxe Hair Studio, Jaipur" />
            <Quote metric="8 extra bookings per week" text="Online booking through my EasyGrox website fills my calendar overnight. I wake up to 3-4 new appointments that came in after 9pm. That used to be zero." name="Ravi Nair" role="The Barbers Co., Kochi" />
            <Quote metric="Staff spend 0 mins on scheduling" text="My team used to spend 20 minutes every morning cross-referencing paper registers. Now they open the app and their day is there. Walk-ins no longer cause panic." name="Sunita Patel" role="Studio One, Ahmedabad" />
          </div>
        </div>
      </section>

      {/* ─── 8. SETUP / ACTIVATION ─── */}
      <section style={{ padding:SP, background:C.tealLight, borderTop:`1px solid ${C.tealBorder}` }}>
        <div style={{ ...px }}>
          <div style={{ display:"grid", gridTemplateColumns:md ? "1fr" : "1fr 1fr", gap:48, alignItems:"center" }}>
            <div>
              <Label>Business activation</Label>
              <H2 style={{ marginBottom:16, marginTop:10 }}>Your calendar is live in under 30 minutes</H2>
              <Body muted style={{ marginBottom:28, fontSize:16 }}>After signup, the app guides you through a step-by-step setup checklist. When you add your services and staff, your booking calendar activates automatically.</Body>
              <div style={{ display:"flex", flexDirection:"column", gap:12 }}>
                {[
                  { step:"Create account here (30 seconds)", done:true },
                  { step:"Add your services and duration (10 minutes)", done:false },
                  { step:"Add staff and assign services (5 minutes)", done:false },
                  { step:"Set your opening hours (2 minutes)", done:false },
                  { step:"Publish your booking website (1 click)", done:false },
                ].map((s, i) => (
                  <div key={i} style={{ display:"flex", gap:10, alignItems:"center" }}>
                    <div style={{ width:22, height:22, borderRadius:"50%", background:s.done ? C.teal : C.surface, border:`2px solid ${s.done ? C.teal : C.tealBorder}`, display:"flex", alignItems:"center", justifyContent:"center", flexShrink:0 }}>
                      {s.done && <Ic n="check" sz={12} col="#fff" />}
                    </div>
                    <span style={{ fontFamily:F, fontSize:14, color:s.done ? C.teal : C.inkVar, fontWeight:s.done ? 600 : 400 }}>{s.step}</span>
                  </div>
                ))}
              </div>
            </div>
            <div>
              <div style={{ background:C.surface, borderRadius:R, padding:"28px 24px", border:`1px solid ${C.tealBorder}`, boxShadow:C.sh1 }}>
                <div style={{ display:"flex", justifyContent:"space-between", alignItems:"center", marginBottom:20 }}>
                  <H4>Setup Progress</H4>
                  <span style={{ fontFamily:F, fontSize:14, fontWeight:700, color:C.teal }}>40% complete</span>
                </div>
                <div style={{ background:C.tealLight, borderRadius:9999, height:8, marginBottom:24, overflow:"hidden" }}>
                  <div style={{ width:"40%", height:"100%", background:C.teal, borderRadius:9999 }} />
                </div>
                {["Business details","Services added","Staff added","Hours set","Website published"].map((item, i) => (
                  <div key={i} style={{ display:"flex", justifyContent:"space-between", alignItems:"center", padding:"10px 0", borderBottom:i < 4 ? `1px solid ${C.outlineVar}` : "none" }}>
                    <div style={{ display:"flex", gap:10, alignItems:"center" }}>
                      <div style={{ width:20, height:20, borderRadius:"50%", background:i < 2 ? C.teal : C.surface, border:`1.5px solid ${i < 2 ? C.teal : C.outlineVar}`, display:"flex", alignItems:"center", justifyContent:"center", flexShrink:0 }}>
                        {(i < 2) && <Ic n="check" sz={11} col="#fff" />}
                      </div>
                      <span style={{ fontFamily:F, fontSize:13.5, color:i < 2 ? C.teal : C.inkVar, fontWeight:i < 2 ? 600 : 400 }}>{item}</span>
                    </div>
                    {i < 2 ? <Chip teal sm>Done</Chip> : <span style={{ fontFamily:F, fontSize:12, color:C.inkSoft }}>Pending</span>}
                  </div>
                ))}
                <Btn full onClick={() => nav("signup")} style={{ marginTop:20 }}>Complete Setup Now</Btn>
              </div>
            </div>
          </div>
        </div>
      </section>
    </FeaturePageShell>
  );
}

/* ═══════════════════════════════════════════════════════════
   2. POS & BILLING
═══════════════════════════════════════════════════════════ */
function POSPage({ nav }) {
  const { sm, md } = useW();
  const px = { padding:`0 ${sm ? 16 : 24}px`, maxWidth:1200, margin:"0 auto" };
  const SP = sm ? "52px 0" : "80px 0";

  return (
    <FeaturePageShell nav={nav} label="POS & Billing" title="Bill every client in under 60 seconds."
      tagline="From completed appointment to payment collected — three taps, one screen, under a minute. Services, retail, discounts, all payment types, and a digital receipt. Every rupee tracked automatically."
      mockup={<POSMock />}
      relatedPages={[{key:"appointments",label:"Appointments"},{key:"retail",label:"Retail Inventory"},{key:"analytics",label:"Analytics"},{key:"clients",label:"Client Management"}]}>

      <section style={{ padding:SP, background:C.low }}>
        <div style={{ ...px }}>
          <PainGain
            pains={["Billing takes 5 minutes — client is waiting while you add up prices on a calculator","Cash register cannot split a service + retail sale — two separate receipts","No record of which payment method each client used — cash reconciliation is a nightmare","Discounts applied inconsistently by different staff — no tracking","Client wants a receipt — you write it by hand or send a photo of the bill","End-of-day total takes 30 minutes to calculate manually"]}
            gains={["Bill any client in under 60 seconds — service pre-filled from appointment card","Services and retail in one transaction — one receipt, one payment","All payment types tracked separately — cash, card, UPI, wallet with daily reconciliation","Create discount codes with expiry and track every redemption automatically","Digital receipt sent via WhatsApp, SMS, or email the moment payment is collected","End-of-day summary generated automatically — revenue, tips, discounts, payment split"]}
          />
        </div>
      </section>

      <section style={{ padding:SP, background:C.bg }}>
        <div style={{ ...px }}>
          <div style={{ textAlign:"center", marginBottom:40 }}>
            <Label center>The billing workflow</Label>
            <H2 center>From appointment complete to receipt sent</H2>
          </div>
          <div style={{ display:"grid", gridTemplateColumns:md ? "1fr" : "1fr 1fr", gap:64, alignItems:"start" }}>
            <div>
              <WorkflowStep n="1" title="Tap Complete on the appointment card" desc="When service is done, the front desk taps Complete. The POS screen opens pre-loaded with the service, staff, and client." note="Zero manual entry" />
              <WorkflowStep n="2" title="Add any retail products sold" desc="Tap to add shampoos, serums, or any retail product sold alongside the service. Price and inventory update automatically." note="Inventory auto-deducted" />
              <WorkflowStep n="3" title="Apply discount if applicable" desc="Select from pre-created discount codes (member discount, birthday offer, walk-in special). Discount amount calculated instantly." note="Tracked in analytics" />
              <WorkflowStep n="4" title="Select payment method" desc="Cash, card, UPI, or wallet — tap to select. Multiple payment types for one transaction (split payment) also supported." note="All types tracked" />
              <WorkflowStep n="5" title="Collect and confirm" desc="Tap Collect. Payment confirmed. The daily revenue counter updates immediately." note="Real-time revenue" />
              <WorkflowStep n="6" title="Receipt sent automatically" desc="Client receives a professional itemised receipt via WhatsApp or SMS. No printing. No paper." note="WhatsApp + SMS" final />
            </div>
            <div style={{ position:"sticky", top:80 }}>
              <POSMock />
              <div style={{ marginTop:16, display:"grid", gridTemplateColumns:"1fr 1fr", gap:10 }}>
                <StatBadge value="60s" label="Average billing time" good />
                <StatBadge value="100%" label="Transactions tracked" good />
                <StatBadge value="0" label="Manual calculations" good />
                <StatBadge value="4 types" label="Payment methods" good />
              </div>
            </div>
          </div>
        </div>
      </section>

      <section style={{ padding:SP, background:C.low }}>
        <div style={{ ...px }}>
          <div style={{ textAlign:"center", marginBottom:40 }}>
            <Label center>Business scenarios</Label>
            <H2 center>How POS handles real situations</H2>
          </div>
          <div style={{ display:"grid", gridTemplateColumns:sm ? "1fr" : md ? "1fr 1fr" : "repeat(3,1fr)", gap:16 }}>
            <Scenario emoji="💳" title="Member discount at checkout" desc="A loyalty member gets a 15% discount on all services." outcome="Front desk applies the Member discount code. POS shows original price, discount amount, and final total. Discount is logged and tracked in your analytics." />
            <Scenario emoji="🛍️" title="Service + retail in one bill" desc="Priya does a colour treatment and the client also buys a Kerastase shampoo." outcome="Both added to one bill — one payment, one receipt. Retail revenue automatically separated from service revenue in reports." />
            <Scenario emoji="💰" title="Split payment request" desc="Client wants to pay half by card and half by UPI." outcome="POS handles split payments — enter the card amount first, collect, then UPI for the remainder. Both tracked separately in daily settlement." />
          </div>
        </div>
      </section>

      <section style={{ padding:SP, background:C.bg }}>
        <div style={{ ...px }}>
          <div style={{ textAlign:"center", marginBottom:40 }}>
            <Label center>Full capability set</Label>
            <H2 center>Everything inside POS and Billing</H2>
          </div>
          <div style={{ display:"grid", gridTemplateColumns:sm ? "1fr" : md ? "1fr 1fr" : "repeat(4,1fr)", gap:14 }}>
            {[
              { n:"zap",      t:"60-Second Checkout",    d:"Pre-loaded from appointment. Service, client, staff already filled." },
              { n:"bag",      t:"Retail + Service",      d:"Sell products alongside services in one transaction." },
              { n:"pos",      t:"All Payment Types",     d:"Cash, card, UPI, wallet. Split payment supported." },
              { n:"tag",      t:"Discounts and Offers",  d:"Create codes with expiry. Track every redemption." },
              { n:"invoice",  t:"Digital Receipts",      d:"Sent instantly via WhatsApp or SMS. Professional format." },
              { n:"gift",     t:"Packages and Bundles",  d:"Pre-paid packages (5 haircuts for ₹2,000). Balance tracked." },
              { n:"users",    t:"Multi-Staff Billing",   d:"Bill services by different staff. Commission tracked per person." },
              { n:"bar",      t:"Daily Financial Summary",d:"Revenue, payment split, discount impact. Every evening." },
            ].map((f, i) => (
              <Card key={i} style={{ padding:"20px 16px" }}>
                <IcBox n={f.n} />
                <H4 style={{ marginBottom:6 }}>{f.t}</H4>
                <Body sm muted style={{ lineHeight:1.6 }}>{f.d}</Body>
              </Card>
            ))}
          </div>
        </div>
      </section>
    </FeaturePageShell>
  );
}

/* ═══════════════════════════════════════════════════════════
   3. CLIENT MANAGEMENT
═══════════════════════════════════════════════════════════ */
function ClientsPage({ nav }) {
  const { sm, md } = useW();
  const px = { padding:`0 ${sm ? 16 : 24}px`, maxWidth:1200, margin:"0 auto" };
  const SP = sm ? "52px 0" : "80px 0";

  return (
    <FeaturePageShell nav={nav} label="Client Management" title="Every client remembered. Every visit tracked."
      tagline="A complete profile for every client — built automatically from each visit, service, product, colour formula, and payment. Your team knows every client's history before they walk through the door."
      mockup={<ClientMock />}
      relatedPages={[{key:"appointments",label:"Appointments"},{key:"marketing",label:"Marketing"},{key:"analytics",label:"Analytics"},{key:"pos",label:"POS & Billing"}]}>

      <section style={{ padding:SP, background:C.low }}>
        <div style={{ ...px }}>
          <PainGain
            pains={["Colour formulas written on paper cards — lost, faded, scattered","Regular client comes in and a different stylist has no idea of their history","No record of which products a client bought last time — missed upsell opportunity","Cannot see which clients have not visited in 60 days — no retention strategy","Client birthday passes without a personalised offer — missed loyalty moment","No way to know who your most valuable clients are without a spreadsheet"]}
            gains={["Colour formulas, treatment notes, and preferences stored permanently in the client profile","Any staff member can see the full history before the appointment — client feels known","Every product purchase logged — staff can recommend a refill at the next visit","At-risk clients flagged automatically — trigger a re-engagement campaign with one click","Birthday and first-visit anniversary tracked — automated offer sent on the day","VIP client ranking updates automatically by spend and visit frequency"]}
          />
        </div>
      </section>

      <section style={{ padding:SP, background:C.bg }}>
        <div style={{ ...px }}>
          <div style={{ textAlign:"center", marginBottom:40 }}>
            <Label center>What lives in a client profile</Label>
            <H2 center>The complete client intelligence card</H2>
          </div>
          <div style={{ display:"grid", gridTemplateColumns:md ? "1fr" : "1fr 1fr", gap:48, alignItems:"start" }}>
            <ClientMock />
            <div>
              {[
                { n:"user",    t:"Personal Details",       d:"Name, contact, birthday, first visit date, preferred communication channel." },
                { n:"scissors",t:"Service and Visit History",d:"Every service received, with the staff member, date, and duration. Colour formulas stored per visit." },
                { n:"bag",    t:"Product Purchase History",d:"Every retail product bought — brand, amount, date. Used for refill reminders and upselling." },
                { n:"bar",    t:"Spend Analytics",         d:"Total lifetime spend, average per visit, highest single transaction, and month-over-month trend." },
                { n:"star",   t:"VIP and Loyalty Status",  d:"Auto-upgraded to VIP at your spend threshold. Used for exclusive offers and priority booking." },
                { n:"bell",   t:"Birthday and Anniversary",d:"First visit anniversary tracked. Feeds directly into automated campaign triggers." },
                { n:"repeat", t:"Visit Frequency Pattern", d:"How often this client visits. Flagged as at-risk when they are overdue." },
                { n:"shield", t:"Preferences and Allergies",d:"Noted once by any staff member — available to the whole team permanently." },
              ].map((f, i) => (
                <div key={i} style={{ display:"flex", gap:12, marginBottom:20, alignItems:"flex-start" }}>
                  <div style={{ width:38, height:38, borderRadius:R, background:C.tealLight, border:`1px solid ${C.tealBorder}`, display:"flex", alignItems:"center", justifyContent:"center", flexShrink:0 }}>
                    <Ic n={f.n} sz={17} col={C.teal} />
                  </div>
                  <div>
                    <div style={{ fontFamily:F, fontSize:14, fontWeight:700, color:C.ink, marginBottom:3 }}>{f.t}</div>
                    <div style={{ fontFamily:F, fontSize:13.5, color:C.inkVar, lineHeight:1.55 }}>{f.d}</div>
                  </div>
                </div>
              ))}
            </div>
          </div>
        </div>
      </section>

      <section style={{ padding:SP, background:C.low }}>
        <div style={{ ...px }}>
          <div style={{ textAlign:"center", marginBottom:40 }}>
            <Label center>Retention intelligence</Label>
            <H2 center>Know who is about to leave before they do</H2>
          </div>
          <div style={{ display:"grid", gridTemplateColumns:sm ? "1fr" : md ? "1fr 1fr" : "repeat(3,1fr)", gap:16 }}>
            <Scenario emoji="🚨" title="At-risk client flagged" desc="Ananya always visits every 5 weeks. It has now been 8 weeks." outcome="EasyGrox flags her as at-risk in your client list. You send a personal re-engagement offer directly from her profile — she books the next day." />
            <Scenario emoji="🎂" title="Birthday week outreach" desc="14 clients have birthdays this month. Each deserves a personalised message." outcome="Birthday automation sends each a personalised WhatsApp with a birthday discount code on their birthday morning. Bookings follow within 48 hours." />
            <Scenario emoji="👑" title="VIP segment review" desc="You want to identify and reward your top 10 clients before a peak season." outcome="Sort by total lifetime spend. Your VIP list is ready. Craft an exclusive preview offer and send via EasyGrox marketing — all from one screen." />
          </div>
        </div>
      </section>
    </FeaturePageShell>
  );
}

/* ═══════════════════════════════════════════════════════════
   4. STAFF & HR MANAGEMENT
═══════════════════════════════════════════════════════════ */
function StaffPage({ nav }) {
  const { sm, md } = useW();
  const px = { padding:`0 ${sm ? 16 : 24}px`, maxWidth:1200, margin:"0 auto" };
  const SP = sm ? "52px 0" : "80px 0";

  return (
    <FeaturePageShell nav={nav} label="Staff Management" title="Every team member in their place. Every shift on track."
      tagline="Individual logins, personal dashboards, shift scheduling, leave management, and detailed performance tracking — so your team runs independently without you micromanaging every detail."
      mockup={<StaffMock />}
      relatedPages={[{key:"appointments",label:"Appointments"},{key:"analytics",label:"Analytics"},{key:"pos",label:"POS & Billing"}]}>

      <section style={{ padding:SP, background:C.low }}>
        <div style={{ ...px }}>
          <PainGain
            pains={["Staff share one login — no accountability for who changed what","No idea which stylist generated the most revenue this month without a spreadsheet","Leave requests come via WhatsApp — forgotten, lost, miscommunicated","Walk-in assigned to wrong staff because someone checked the wrong roster","Staff cannot see their own schedule — they call or WhatsApp you to check","No performance benchmark — how do you know who deserves a raise or needs support?"]}
            gains={["Each staff member has their own secure login and personal schedule view","Revenue per staff updated daily — see exactly who earns the most for your business","Leave requests submitted and approved inside the app — auto-blocked in the calendar","Role-based access — staff only see what is relevant to their work","Staff check their own schedule, tasks, and clients from their phone","Monthly performance report per staff — revenue, rating, utilisation, attendance"]}
          />
        </div>
      </section>

      <section style={{ padding:SP, background:C.bg }}>
        <div style={{ ...px }}>
          <div style={{ display:"grid", gridTemplateColumns:md ? "1fr" : "1fr 1fr", gap:64, alignItems:"start" }}>
            <div>
              <Label>Role-based access levels</Label>
              <H2 style={{ marginBottom:14, marginTop:10 }}>Three roles. Every team covered.</H2>
              <Body muted style={{ marginBottom:32 }}>Set what each team member can see and do inside EasyGrox. No confusion, no accidental edits, no privacy issues.</Body>
              {[
                { role:"Owner", color:C.teal, perms:["Full access to all dashboards","Revenue, staff, and client analytics","Pricing and service management","User access management","Financial reports and settings"] },
                { role:"Front Desk", color:"#8b4523", perms:["All bookings and calendar","Client check-in and walk-ins","POS billing and payments","Client profiles and notes","Cannot see owner-level financials"] },
                { role:"Staff", color:"#4a6741", perms:["Personal schedule view only","Own appointment list","Own client notes","Own performance metrics","No billing or financial access"] },
              ].map((r, i) => (
                <div key={i} style={{ background:C.surface, borderRadius:R, border:`1px solid ${C.outlineVar}`, padding:"18px 20px", marginBottom:12, borderLeft:`4px solid ${r.color}`, boxShadow:C.sh1 }}>
                  <div style={{ display:"flex", gap:10, alignItems:"center", marginBottom:10 }}>
                    <div style={{ width:28, height:28, borderRadius:"50%", background:r.color, display:"flex", alignItems:"center", justifyContent:"center", fontSize:11, fontWeight:800, color:"#fff" }}>{r.role[0]}</div>
                    <span style={{ fontFamily:F, fontSize:15, fontWeight:700, color:C.ink }}>{r.role}</span>
                  </div>
                  <div style={{ display:"flex", flexWrap:"wrap", gap:6 }}>
                    {r.perms.map((p, pi) => (
                      <span key={pi} style={{ fontFamily:F, fontSize:12.5, color:C.inkVar, background:C.low, borderRadius:9999, padding:"3px 10px", border:`1px solid ${C.outlineVar}` }}>{p}</span>
                    ))}
                  </div>
                </div>
              ))}
            </div>
            <StaffMock />
          </div>
        </div>
      </section>

      <section style={{ padding:SP, background:C.low }}>
        <div style={{ ...px }}>
          <div style={{ textAlign:"center", marginBottom:40 }}>
            <Label center>Operational scenarios</Label>
            <H2 center>How EasyGrox handles daily team management</H2>
          </div>
          <div style={{ display:"grid", gridTemplateColumns:sm ? "1fr" : md ? "1fr 1fr" : "repeat(3,1fr)", gap:16 }}>
            <Scenario emoji="🏖️" title="Staff leave request" desc="Priya requests 3 days leave for Diwali via the app." outcome="Request appears in your approvals screen. You approve — those 3 days are automatically blocked from her calendar. Her clients are shown as unavailable." />
            <Scenario emoji="📊" title="Monthly performance review" desc="End of month — who earned the most for your salon? Who needs support?" outcome="Open the staff performance dashboard. Revenue per staff, appointments completed, utilisation rate, and client rating — all compared side by side." />
            <Scenario emoji="🔑" title="New junior stylist onboarded" desc="You hire a new junior. She should see only her own schedule — not billing." outcome="Create her account with Staff role. She logs in to see only her day and clients. No financial exposure, no clutter — just her work." />
          </div>
        </div>
      </section>
    </FeaturePageShell>
  );
}

/* ═══════════════════════════════════════════════════════════
   5. INVENTORY & RETAIL MANAGEMENT
═══════════════════════════════════════════════════════════ */
function RetailPage({ nav }) {
  const { sm, md } = useW();
  const px = { padding:`0 ${sm ? 16 : 24}px`, maxWidth:1200, margin:"0 auto" };
  const SP = sm ? "52px 0" : "80px 0";

  return (
    <FeaturePageShell nav={nav} label="Retail Inventory" title="Know exactly what is on your shelves. Always."
      tagline="Track every product, get low-stock alerts before you run out, see which products and staff generate the most retail revenue — all connected to your POS so inventory updates the moment a sale is made."
      mockup={<AnalyticsMock />}
      relatedPages={[{key:"pos",label:"POS & Billing"},{key:"analytics",label:"Analytics"},{key:"clients",label:"Client Management"}]}>

      <section style={{ padding:SP, background:C.low }}>
        <div style={{ ...px }}>
          <PainGain
            pains={["Mid-service you reach for a product and the bottle is empty — client waiting","No system to track which products are sold — reorder based on guessing","Best-selling shampoo runs out on a Saturday — no alert, no backup plan","Staff sell retail informally — no record, no commission tracking","Retail margin invisible — you do not know which products make you money","Overstock of slow-moving product taking up shelf space and locking up cash"]}
            gains={["Low-stock alert fires before you run out — reorder with days to spare","Every retail sale logged at POS — complete sales history per product","Stock threshold set per product — automatic alert when quantity drops below minimum","Retail sales tracked per staff member — see who recommends and sells most","Cost price and selling price tracked — gross margin calculated automatically","Slow-mover report shows which products to discount or stop stocking"]}
          />
        </div>
      </section>

      <section style={{ padding:SP, background:C.bg }}>
        <div style={{ ...px }}>
          <div style={{ textAlign:"center", marginBottom:40 }}>
            <Label center>Full capability set</Label>
            <H2 center>Everything inside Retail and Inventory</H2>
          </div>
          <div style={{ display:"grid", gridTemplateColumns:sm ? "1fr" : md ? "1fr 1fr" : "repeat(4,1fr)", gap:14 }}>
            {[
              { n:"bag",     t:"Product Catalogue",      d:"Add every product with name, brand, category, price, cost, and stock level." },
              { n:"bell",    t:"Low-Stock Alerts",       d:"Set minimum quantity per product. Instant alert on your dashboard when hit." },
              { n:"bar",     t:"Sales Analytics",        d:"Best-sellers by units and revenue. Retail as % of total business." },
              { n:"refresh", t:"Stock Adjustments",      d:"Log arrivals, breakage, returns. Every movement tracked with timestamp." },
              { n:"users",   t:"Staff Sales Tracking",   d:"Which team member sells the most retail — by product and by value." },
              { n:"pos",     t:"POS Integration",        d:"Sell at POS — inventory deducts automatically. No double entry." },
              { n:"trend",   t:"Margin Tracking",        d:"Cost vs. selling price per product. Know your most profitable lines." },
              { n:"repeat",  t:"Reorder Suggestions",    d:"Based on sales velocity — EasyGrox recommends reorder quantities." },
            ].map((f, i) => (
              <Card key={i} style={{ padding:"20px 16px" }}>
                <IcBox n={f.n} />
                <H4 style={{ marginBottom:6 }}>{f.t}</H4>
                <Body sm muted style={{ lineHeight:1.6 }}>{f.d}</Body>
              </Card>
            ))}
          </div>
        </div>
      </section>
    </FeaturePageShell>
  );
}

/* ═══════════════════════════════════════════════════════════
   6. MARKETING MANAGEMENT
═══════════════════════════════════════════════════════════ */
function MarketingPage({ nav }) {
  const { sm, md } = useW();
  const px = { padding:`0 ${sm ? 16 : 24}px`, maxWidth:1200, margin:"0 auto" };
  const SP = sm ? "52px 0" : "80px 0";

  return (
    <FeaturePageShell nav={nav} label="Marketing Campaigns" title="Fill your calendar. Keep your clients coming back."
      tagline="SMS campaigns, email offers, birthday automations, and re-engagement sequences — all powered by your own client data. No separate marketing tool. No manual lists. Just send."
      mockup={<AnalyticsMock />}
      relatedPages={[{key:"clients",label:"Client Management"},{key:"analytics",label:"Analytics"},{key:"website",label:"Free Booking Website"}]}>

      <section style={{ padding:SP, background:C.low }}>
        <div style={{ ...px }}>
          <PainGain
            pains={["Tuesday is dead — no bookings, no system to fill it, no way to reach clients fast","A client has not visited in 3 months — you have no way to know or reach out","Birthday passes with no personalised message — missed loyalty moment","Running a promotion means exporting a client list and using a separate tool","No idea which marketing messages actually resulted in bookings","Sending the same promotion to everyone — loyal clients and first-timers get the same message"]}
            gains={["Send a Tuesday flash offer via SMS to 200 clients in under 3 minutes — gaps fill","At-risk clients identified automatically — re-engagement SMS sent with your approval","Birthday automation sends a personal discount on the exact birthday, every time","Segments built from your client data — no export, no manual list, just filter and send","Campaign analytics show exactly how many bookings each message generated","Target by behaviour — at-risk clients, big spenders, fans of a specific service"]}
          />
        </div>
      </section>

      <section style={{ padding:SP, background:C.bg }}>
        <div style={{ ...px }}>
          <div style={{ textAlign:"center", marginBottom:40 }}>
            <Label center>Campaign types</Label>
            <H2 center>Six ways to reach your clients inside EasyGrox</H2>
          </div>
          <div style={{ display:"grid", gridTemplateColumns:sm ? "1fr" : md ? "1fr 1fr" : "repeat(3,1fr)", gap:16 }}>
            {[
              { n:"phone",  t:"SMS Campaign",        d:"Direct to mobile. Highest open rates of any channel. Perfect for flash offers and slot fills.", tag:"Instant delivery" },
              { n:"mail",   t:"Email Campaign",      d:"Professional template with logo, service imagery, offer details, and a booking link.", tag:"Rich formatting" },
              { n:"gift",   t:"Birthday Automation", d:"Set once. Runs forever. Every client gets a personalised birthday offer on their exact day.", tag:"Fully automated" },
              { n:"repeat", t:"Re-Engagement Series",d:"Clients who have not visited in 30, 60, or 90 days get a tailored win-back message automatically.", tag:"Runs itself" },
              { n:"target", t:"Smart Segments",      d:"Filter by service history, spend level, last visit, or VIP status. Send to exactly the right people.", tag:"Precision targeting" },
              { n:"users",  t:"Staff-linked Offers", d:"Send on behalf of a specific stylist — Priya has 3 openings Saturday — with a direct booking link.", tag:"Personal feel" },
            ].map((f, i) => (
              <Card key={i} tealTop style={{ padding:"22px 18px" }}>
                <div style={{ display:"flex", justifyContent:"space-between", alignItems:"flex-start", marginBottom:14 }}>
                  <IcBox n={f.n} />
                  <Chip teal sm>{f.tag}</Chip>
                </div>
                <H4 style={{ marginBottom:8 }}>{f.t}</H4>
                <Body sm muted style={{ lineHeight:1.6 }}>{f.d}</Body>
              </Card>
            ))}
          </div>
        </div>
      </section>

      <section style={{ padding:SP, background:C.low }}>
        <div style={{ ...px }}>
          <div style={{ textAlign:"center", marginBottom:40 }}>
            <Label center>Marketing workflows</Label>
            <H2 center>Campaigns that run on your client data</H2>
          </div>
          <div style={{ display:"grid", gridTemplateColumns:sm ? "1fr" : md ? "1fr 1fr" : "repeat(3,1fr)", gap:16 }}>
            <Scenario emoji="📅" title="Filling a slow Tuesday" desc="It is Monday at 5pm. Tuesday has 4 empty slots." outcome="Open Marketing, filter segment Last booked more than 30 days ago. Send SMS: Tuesday slots available, 15% off for this week only. 3 bookings confirmed by 8pm." />
            <Scenario emoji="💤" title="Win back a lapsed client" desc="Ananya has not visited in 9 weeks. She usually comes every 5." outcome="EasyGrox flags her as at-risk. You approve sending her a personalised SMS: We miss you Ananya — here is 20% off your next visit. She books within 24 hours." />
            <Scenario emoji="🌸" title="Pre-season bridal push" desc="January is approaching. Wedding season bookings should be opening." outcome="Filter segment Clients who have booked Bridal service in the past. Send a preview WhatsApp about bridal packages with a direct booking link. 12 enquiries in 48 hours." />
          </div>
        </div>
      </section>
    </FeaturePageShell>
  );
}

/* ═══════════════════════════════════════════════════════════
   7. REVIEW MANAGEMENT
═══════════════════════════════════════════════════════════ */
function ReviewsPage({ nav }) {
  const { sm, md } = useW();
  const px = { padding:`0 ${sm ? 16 : 24}px`, maxWidth:1200, margin:"0 auto" };
  const SP = sm ? "52px 0" : "80px 0";

  return (
    <FeaturePageShell nav={nav} label="Review Management" title="Build your reputation. Automatically."
      tagline="Every completed appointment triggers a review request at the right moment. Collect ratings, spot issues before they become problems, and showcase your reputation on your booking website."
      mockup={<ClientMock />}
      relatedPages={[{key:"clients",label:"Client Management"},{key:"marketing",label:"Marketing"},{key:"website",label:"Free Booking Website"}]}>

      <section style={{ padding:SP, background:C.low }}>
        <div style={{ ...px }}>
          <PainGain
            pains={["Happy clients never leave reviews — only unhappy ones do — your rating suffers","Manually asking every client for a review is awkward and gets forgotten","Negative review appears on Google — you find out 2 weeks later with no context","No idea which staff member gets the best client feedback","Reviews scattered across Google, WhatsApp, and social — no consolidated view","Cannot showcase your review reputation on your booking website"]}
            gains={["Automatic review request sent after every appointment — at the perfect moment","Rating collected inside EasyGrox before client is asked to share publicly","Poor rating triggers an internal alert — you can resolve the issue before it goes online","Rating tracked per staff member — see who clients love and who needs coaching","All reviews in one dashboard — respond, track, and analyze from one screen","Top reviews displayed on your free booking website to build booking confidence"]}
          />
        </div>
      </section>

      <section style={{ padding:SP, background:C.bg }}>
        <div style={{ ...px }}>
          <div style={{ textAlign:"center", marginBottom:40 }}>
            <Label center>Review workflow</Label>
            <H2 center>How the reputation engine works</H2>
          </div>
          <div style={{ maxWidth:640, margin:"0 auto" }}>
            <WorkflowStep n="1" title="Appointment marked complete" desc="Staff taps Complete on the appointment card. Service confirmed, billing triggered." note="Automatic trigger" />
            <WorkflowStep n="2" title="Review request sent via WhatsApp" desc="Client receives a short message: How was your visit today? Rate with one tap." note="1-5 star rating" />
            <WorkflowStep n="3" title="If 5 stars — publicly share" desc="Client prompted to share the rating on Google or your booking website. High-satisfaction clients go public." note="Builds online presence" />
            <WorkflowStep n="4" title="If 3 stars or below — internal alert" desc="You see the low rating in your dashboard immediately. Reach out to resolve before it impacts your public reputation." note="Protect reputation" />
            <WorkflowStep n="5" title="Review appears in your dashboard" desc="All ratings tracked per staff member and overall. Average rating updated in real time." note="Staff performance" />
            <WorkflowStep n="6" title="Top reviews shown on booking website" desc="Best reviews displayed on your EasyGrox booking website — building social proof for new clients deciding whether to book." note="Converts visitors to bookings" final />
          </div>
        </div>
      </section>
    </FeaturePageShell>
  );
}

/* ═══════════════════════════════════════════════════════════
   8. REPORTS & ANALYTICS
═══════════════════════════════════════════════════════════ */
function AnalyticsPage({ nav }) {
  const { sm, md } = useW();
  const px = { padding:`0 ${sm ? 16 : 24}px`, maxWidth:1200, margin:"0 auto" };
  const SP = sm ? "52px 0" : "80px 0";
  const [activeArea, setActiveArea] = useState(0);

  const areas = [
    { label:"Revenue",      icon:"bar",    metrics:["Total revenue daily / weekly / monthly","Revenue by service vs. retail","Revenue by staff member","Revenue by service category","Payment method breakdown","Period comparison vs. last month"] },
    { label:"Appointments", icon:"calendar",metrics:["Total bookings completed per period","Cancellation rate and reasons","No-show rate","Peak booking hours heatmap","Online vs. walk-in vs. manual split","Average appointment duration accuracy"] },
    { label:"Staff",        icon:"users",  metrics:["Revenue generated per staff member","Appointments completed per staff","Utilisation rate (booked vs. available)","Average client rating per staff","Attendance and punctuality records","Month-on-month performance trend"] },
    { label:"Clients",      icon:"user",   metrics:["New vs. returning client ratio","Client retention rate","Average spend per visit","Lifetime value per client","At-risk clients (not visited 60+ days)","Top 10 most loyal clients by spend"] },
    { label:"Retail",       icon:"bag",    metrics:["Best-selling products by units and revenue","Retail revenue as % of total","Stock movement per product","Products running below minimum","Retail revenue per staff member","Category performance breakdown"] },
    { label:"Website",      icon:"globe",  metrics:["Total visitors daily / weekly / monthly","Traffic sources (Google, Instagram, WhatsApp, direct)","Most-viewed service pages","Booking conversion rate","Bookings originated from website","Drop-off points in booking funnel"] },
  ];

  return (
    <FeaturePageShell nav={nav} label="Analytics & Reports" title="Stop guessing. Start knowing."
      tagline="Revenue, appointments, staff performance, client behaviour, retail sales, and your booking website — six analytics dashboards in one app, updated in real time. Read your full business in 60 seconds every morning."
      mockup={<AnalyticsMock />}
      relatedPages={[{key:"staffMgmt",label:"Staff Management"},{key:"marketing",label:"Marketing"},{key:"website",label:"Free Booking Website"},{key:"retail",label:"Retail Inventory"}]}>

      <section style={{ padding:SP, background:C.low }}>
        <div style={{ ...px }}>
          <PainGain
            pains={["End-of-month revenue requires pulling receipts and calculating on a calculator","No idea which stylist generates the most revenue without a complex spreadsheet","Cannot see which services are growing and which are declining over time","No-show rate tracked nowhere — you suspect it is high but have no number","Website traffic and offline business in separate tools — no combined view","Retail margin invisible — you guess which products make money"]}
            gains={["Revenue dashboard updates every time a payment is collected — live, accurate, effortless","Revenue per staff updated daily — ranked from highest to lowest automatically","Service popularity chart shows growth and decline trends week by week","No-show rate tracked and benchmarked against industry standard — you know exactly where you stand","Website analytics sit alongside business analytics — one unified daily picture","Retail margin tracked per product — know exactly where your profit comes from"]}
          />
        </div>
      </section>

      <section style={{ padding:SP, background:C.bg }}>
        <div style={{ ...px }}>
          <div style={{ textAlign:"center", marginBottom:40 }}>
            <Label center>Six analytics dashboards</Label>
            <H2 center>One daily view. Complete business intelligence.</H2>
          </div>
          <div style={{ display:"flex", gap:8, flexWrap:"wrap", justifyContent:"center", marginBottom:32 }}>
            {areas.map((a, i) => (
              <button key={i} onClick={() => setActiveArea(i)} style={{ display:"flex", alignItems:"center", gap:6, padding:"9px 16px", borderRadius:9999, border:`1.5px solid ${activeArea === i ? C.teal : C.outlineVar}`, background:activeArea === i ? C.teal : C.surface, color:activeArea === i ? "#fff" : C.inkVar, fontWeight:600, fontSize:13, cursor:"pointer", fontFamily:F, transition:"all .18s" }}>
                <Ic n={a.icon} sz={15} col={activeArea === i ? "#fff" : C.inkVar} />
                {a.label}
              </button>
            ))}
          </div>
          <Card style={{ padding:"32px 28px" }}>
            <div style={{ display:"grid", gridTemplateColumns:md ? "1fr" : "1fr 1fr", gap:40 }}>
              <div>
                <div style={{ display:"flex", alignItems:"center", gap:10, marginBottom:20 }}>
                  <IcBox n={areas[activeArea].icon} big />
                  <div>
                    <H3>{areas[activeArea].label} Analytics</H3>
                    <Body sm muted style={{ marginTop:3 }}>Updated in real time · No setup required</Body>
                  </div>
                </div>
                {areas[activeArea].metrics.map((m, i) => (
                  <div key={i} style={{ display:"flex", gap:10, marginBottom:12, alignItems:"flex-start" }}>
                    <div style={{ width:18, height:18, borderRadius:5, background:C.tealLight, border:`1px solid ${C.tealBorder}`, display:"flex", alignItems:"center", justifyContent:"center", flexShrink:0, marginTop:2 }}>
                      <Ic n="check" sz={10} col={C.teal} />
                    </div>
                    <Body sm muted style={{ lineHeight:1.6 }}>{m}</Body>
                  </div>
                ))}
              </div>
              <AnalyticsMock />
            </div>
          </Card>
        </div>
      </section>

      <section style={{ padding:SP, background:C.low }}>
        <div style={{ ...px }}>
          <div style={{ textAlign:"center", marginBottom:40 }}>
            <Label center>Morning ritual</Label>
            <H2 center>Your business read in 60 seconds</H2>
          </div>
          <div style={{ maxWidth:700, margin:"0 auto" }}>
            {[
              { time:"8:01 AM", action:"Open EasyGrox Dashboard", detail:"Yesterday revenue, today appointments, and website visitor count at a glance." },
              { time:"8:02 AM", action:"Check no-show rate", detail:"2 no-shows yesterday. Rate at 4.1%. Industry benchmark is 8%. You are performing well." },
              { time:"8:03 AM", action:"Review staff performance", detail:"Priya had her best revenue day this month. Sunita has 3 empty slots — consider reassigning a walk-in." },
              { time:"8:04 AM", action:"Check website analytics", detail:"108 website visitors yesterday. 18 bookings. 16.7% conversion — up from 14% last week." },
              { time:"8:05 AM", action:"Open today's calendar", detail:"Full picture loaded. 12 bookings, 2 gaps in the afternoon. You decide to run a quick flash offer for today at 9am." },
            ].map((row, i) => (
              <div key={i} style={{ display:"grid", gridTemplateColumns:"80px 1fr", gap:16, marginBottom:20, alignItems:"flex-start" }}>
                <Chip teal sm style={{ justifyContent:"center" }}>{row.time}</Chip>
                <div>
                  <div style={{ fontFamily:F, fontSize:14.5, fontWeight:700, color:C.ink, marginBottom:4 }}>{row.action}</div>
                  <div style={{ fontFamily:F, fontSize:13.5, color:C.inkVar, lineHeight:1.6 }}>{row.detail}</div>
                </div>
              </div>
            ))}
          </div>
        </div>
      </section>
    </FeaturePageShell>
  );
}

/* ═══════════════════════════════════════════════════════════
   9. MULTI-LOCATION MANAGEMENT
═══════════════════════════════════════════════════════════ */
function MultiLocationPage({ nav }) {
  const { sm, md } = useW();
  const px = { padding:`0 ${sm ? 16 : 24}px`, maxWidth:1200, margin:"0 auto" };
  const SP = sm ? "52px 0" : "80px 0";

  return (
    <FeaturePageShell nav={nav} label="Multi-Location Management" title="All your branches. One login."
      tagline="Manage every location from a single owner dashboard. Each branch gets its own booking website, staff, calendar, and analytics. You see the consolidated view — or drill into any branch in seconds."
      mockup={<StaffMock />}
      relatedPages={[{key:"analytics",label:"Analytics"},{key:"staffMgmt",label:"Staff Management"},{key:"website",label:"Free Booking Website"}]}>

      <section style={{ padding:SP, background:C.low }}>
        <div style={{ ...px }}>
          <PainGain
            pains={["Managing 2 branches means 2 separate systems, 2 logins, and manual consolidation every month","Cannot compare Jaipur and Kochi performance without building a spreadsheet","Each location has its own booking number — clients calling the wrong branch","No consolidated revenue view — you manually add up totals across locations","Staff moved between branches creates a scheduling nightmare","One branch is underperforming but you cannot see it until month-end"]}
            gains={["One login — see all branches, or switch to any specific branch view","Side-by-side performance comparison across all locations updated daily","Each branch has its own booking website with its own URL — clients go directly","Consolidated revenue dashboard shows total business and per-branch breakdown","Staff can be assigned across locations with shared client history","Underperforming branch flagged in your dashboard — intervene before month-end"]}
          />
        </div>
      </section>

      <section style={{ padding:SP, background:C.bg }}>
        <div style={{ ...px }}>
          <div style={{ textAlign:"center", marginBottom:40 }}>
            <Label center>Multi-location capabilities</Label>
            <H2 center>Built for growing salon businesses</H2>
          </div>
          <div style={{ display:"grid", gridTemplateColumns:sm ? "1fr" : md ? "1fr 1fr" : "repeat(4,1fr)", gap:14 }}>
            {[
              { n:"globe",    t:"Website Per Branch",     d:"Each branch gets its own booking website with its own URL. Clients book the right location." },
              { n:"bar",      t:"Consolidated Analytics", d:"Total revenue, appointments, and clients across all branches — one view." },
              { n:"users",    t:"Branch-Level Staff",     d:"Staff assigned per branch. Cross-branch moves tracked with shared client history." },
              { n:"map",      t:"Branch Comparison",      d:"Revenue, no-show rate, utilisation — compare any two branches side by side." },
              { n:"lock",     t:"Branch-Level Access",    d:"Branch managers see only their location. Owner sees all." },
              { n:"calendar", t:"Per-Branch Calendars",   d:"Each branch has its own calendar. No cross-contamination of bookings." },
              { n:"tag",      t:"Shared Client Database", d:"Clients who visit multiple branches have one unified profile." },
              { n:"shield",   t:"Centralised Settings",   d:"Update pricing, services, or policies from the owner dashboard — push to all branches." },
            ].map((f, i) => (
              <Card key={i} style={{ padding:"20px 16px" }}>
                <IcBox n={f.n} />
                <H4 style={{ marginBottom:6 }}>{f.t}</H4>
                <Body sm muted style={{ lineHeight:1.6 }}>{f.d}</Body>
              </Card>
            ))}
          </div>
        </div>
      </section>

      <section style={{ padding:SP, background:C.low }}>
        <div style={{ ...px }}>
          <div style={{ textAlign:"center", marginBottom:40 }}>
            <Label center>Growth scenarios</Label>
            <H2 center>Multi-location situations EasyGrox handles daily</H2>
          </div>
          <div style={{ display:"grid", gridTemplateColumns:sm ? "1fr" : md ? "1fr 1fr" : "repeat(3,1fr)", gap:16 }}>
            <Scenario emoji="📊" title="Monthly business review" desc="You manage 3 branches across 2 cities. You need the monthly performance picture." outcome="Open consolidated analytics. See total revenue, per-branch breakdown, best-performing location, and which branch has the highest no-show rate. 60 seconds." />
            <Scenario emoji="🏪" title="Opening a new branch" desc="You are launching a third location in a new city." outcome="Create the new branch in EasyGrox settings. Add staff, services, and hours. Publish a new booking website for that branch in under an hour. Done." />
            <Scenario emoji="👤" title="Client visits a second location" desc="An existing client from Jaipur visits your new Kochi branch for the first time." outcome="Staff at Kochi open the client profile — full history, colour formulas, preferences, and spend from Jaipur are all there. Client feels known." />
          </div>
        </div>
      </section>
    </FeaturePageShell>
  );
}

/* ═══════════════════════════════════════════════════════════
   10. FREE BOOKING WEBSITE
═══════════════════════════════════════════════════════════ */
function WebsitePage({ nav }) {
  const { sm, md } = useW();
  const px = { padding:`0 ${sm ? 16 : 24}px`, maxWidth:1200, margin:"0 auto" };
  const SP = sm ? "52px 0" : "80px 0";

  return (
    <FeaturePageShell nav={nav} label="Free Booking Website" title="Your salon. Your website. Zero cost."
      tagline="A professional, mobile-optimised booking website — hosted by EasyGrox at zero cost, auto-generated from your store data, and published with one click. Clients book directly with you, 24 hours a day. No competitors. No marketplace. Just your brand."
      mockup={<WebsiteMock />}
      relatedPages={[{key:"analytics",label:"Analytics"},{key:"appointments",label:"Appointments"},{key:"marketing",label:"Marketing"}]}>

      <section style={{ padding:SP, background:C.low }}>
        <div style={{ ...px }}>
          <PainGain
            pains={["Clients book through Fresha or Booksy — listed alongside all your competitors","Your website developer charges ₹20,000 to build a basic booking page and ₹5,000/year to maintain","Every time you add a service or update a price, you have to message the developer or update manually","Clients call after 8pm to book — you miss the call, they book somewhere else","No visibility into how many people visit your online presence or whether they are booking","Marketplace takes a commission on every booking through their platform"]}
            gains={["Your own branded booking website with your logo — no competitors visible anywhere on the page","Completely free — no hosting fee, no domain purchase, no developer, no renewals ever","Update a price inside EasyGrox and your website reflects it in seconds — fully automatic","Clients book at midnight, weekends, or during service — 24/7 with no intervention","Full visitor analytics in your EasyGrox dashboard — traffic sources, conversion rate, top pages","Zero commission — clients book directly with you, every rupee stays in your business"]}
          />
        </div>
      </section>

      <section style={{ padding:SP, background:C.bg }}>
        <div style={{ ...px }}>
          <div style={{ display:"grid", gridTemplateColumns:md ? "1fr" : "1fr 1fr", gap:64, alignItems:"start" }}>
            <div>
              <Label>How your website goes live</Label>
              <H2 style={{ marginBottom:14, marginTop:10 }}>From zero to published in under 30 minutes</H2>
              <Body muted style={{ marginBottom:32 }}>Your booking website is built automatically from your store data. The moment you complete setup, it is ready to publish.</Body>
              <WorkflowStep n="1" title="Complete your store setup in the app" desc="Add your business details, logo, services with prices, staff profiles, and opening hours." note="Inside the app" />
              <WorkflowStep n="2" title="Preview your website" desc="Your website is auto-generated and ready to preview inside the app. See exactly what clients will see." note="Preview button" />
              <WorkflowStep n="3" title="Set your custom URL" desc="Choose yoursalon.easygrox.com as your URL. Or connect your own domain in one setting — no technical steps." note="Free URL included" />
              <WorkflowStep n="4" title="Click Publish" desc="One click. Your website is live, hosted on fast and secure servers, mobile-optimised and accepting bookings immediately." note="Goes live instantly" />
              <WorkflowStep n="5" title="Share your booking link" desc="Add the link to your Instagram bio, WhatsApp status, Google Business Profile, and all your marketing materials." note="Your clients book" />
              <WorkflowStep n="6" title="Watch bookings arrive" desc="Every booking from your website appears instantly in your calendar — assigned to the correct staff, reminder sent automatically." note="Fully automated" final />
            </div>
            <div style={{ position:"sticky", top:80 }}>
              <WebsiteMock />
              <div style={{ marginTop:16, display:"grid", gridTemplateColumns:"1fr 1fr", gap:10 }}>
                <StatBadge value="₹0" label="Hosting cost ever" good />
                <StatBadge value="24/7" label="Booking availability" good />
                <StatBadge value="1 click" label="To publish" good />
                <StatBadge value="0" label="Developers needed" good />
              </div>
            </div>
          </div>
        </div>
      </section>

      <section style={{ padding:SP, background:C.low }}>
        <div style={{ ...px }}>
          <div style={{ textAlign:"center", marginBottom:40 }}>
            <Label center>What your website includes</Label>
            <H2 center>A complete booking website — free forever</H2>
          </div>
          <div style={{ display:"grid", gridTemplateColumns:sm ? "1fr" : md ? "1fr 1fr" : "repeat(3,1fr)", gap:14 }}>
            {[
              { n:"link",    t:"Free Custom URL",      d:"yoursalon.easygrox.com. Connect your own domain anytime with one setting." },
              { n:"shield",  t:"Free Hosting Forever", d:"Hosted on fast, secure, SSL-encrypted servers. No bill. No renewal. Ever." },
              { n:"refresh", t:"Always Up-to-Date",    d:"Change a price, add a service, update your hours — website updates in seconds." },
              { n:"calendar",t:"Live Online Booking",  d:"Clients browse services, choose a stylist, pick a time, and confirm — all on your website." },
              { n:"globe",   t:"Google-Indexable",     d:"Your service pages are indexable by Google. New clients search and find you organically." },
              { n:"bar",     t:"Visitor Analytics",    d:"Every visit, traffic source, and booking tracked automatically in your EasyGrox dashboard." },
              { n:"users",   t:"Staff Profiles",       d:"Each staff member has their own page with photo, specialisations, and available times." },
              { n:"star",    t:"Reviews Showcased",    d:"Your top client reviews displayed to build confidence in new visitors." },
              { n:"map",     t:"Per-Branch Websites",  d:"Multi-location businesses get a separate website per branch — each with its own URL." },
            ].map((f, i) => (
              <Card key={i} style={{ padding:"20px 16px" }}>
                <IcBox n={f.n} />
                <H4 style={{ marginBottom:6 }}>{f.t}</H4>
                <Body sm muted style={{ lineHeight:1.6 }}>{f.d}</Body>
              </Card>
            ))}
          </div>
        </div>
      </section>

      <section style={{ padding:SP, background:C.bg }}>
        <div style={{ ...px }}>
          <div style={{ textAlign:"center", marginBottom:40 }}>
            <Label center>Website vs marketplace comparison</Label>
            <H2 center>Why your own website beats a directory listing</H2>
          </div>
          <div style={{ display:"grid", gridTemplateColumns:sm ? "1fr" : "1fr 1fr", gap:16 }}>
            <Card style={{ padding:"28px 24px", border:`1px solid ${C.outlineVar}` }}>
              <div style={{ fontFamily:F, fontSize:11, fontWeight:700, letterSpacing:"0.08em", textTransform:"uppercase", color:C.err, marginBottom:18 }}>Fresha / Booksy Directory</div>
              {["Your salon listed alongside every local competitor","Clients browse by price — you compete to be cheapest","Platform takes commission on every booking","Your client data belongs to the platform — not you","Platform controls your profile, branding, and visibility","Zero control over how your brand is presented"].map((p, i) => (
                <div key={i} style={{ display:"flex", gap:9, marginBottom:12, alignItems:"flex-start" }}>
                  <Ic n="xmark" sz={14} col={C.err} style={{ marginTop:2, flexShrink:0 }} />
                  <Body sm muted style={{ lineHeight:1.55 }}>{p}</Body>
                </div>
              ))}
            </Card>
            <Card ink style={{ padding:"28px 24px", border:"none" }}>
              <div style={{ fontFamily:F, fontSize:11, fontWeight:700, letterSpacing:"0.08em", textTransform:"uppercase", color:"rgba(255,255,255,0.45)", marginBottom:18 }}>Your EasyGrox Booking Website</div>
              {["Your salon — no competitors visible anywhere on your page","Clients choose you because they found you directly","Zero commission — every rupee stays in your business","Your client data — always exportable, always yours","Your logo, your brand, your URL, your domain","Full control over services, prices, photos, and messaging"].map((g, i) => (
                <div key={i} style={{ display:"flex", gap:9, marginBottom:12, alignItems:"flex-start" }}>
                  <div style={{ width:18, height:18, borderRadius:5, background:"rgba(255,255,255,0.15)", border:"1px solid rgba(255,255,255,0.2)", display:"flex", alignItems:"center", justifyContent:"center", flexShrink:0, marginTop:1 }}>
                    <Ic n="check" sz={10} col="#fff" />
                  </div>
                  <Body sm light style={{ lineHeight:1.55 }}>{g}</Body>
                </div>
              ))}
            </Card>
          </div>
        </div>
      </section>

      <section style={{ padding:SP, background:C.low }}>
        <div style={{ ...px }}>
          <div style={{ textAlign:"center", marginBottom:40 }}>
            <Label center>Real results</Label>
            <H2 center>What happens when clients can book online</H2>
          </div>
          <div style={{ display:"grid", gridTemplateColumns:sm ? "1fr" : md ? "1fr 1fr" : "repeat(3,1fr)", gap:16 }}>
            <Quote metric="+8 bookings per week" text="I added my EasyGrox link to my Instagram bio and within 2 weeks I was getting 8 extra bookings per week from people who found me on Instagram at 9pm when I was already closed." name="Priya Sharma" role="Luxe Hair Studio, Jaipur" />
            <Quote metric="48% of bookings now online" text="Half my bookings now come through the website without me being involved. I wake up to a confirmed calendar. The manual booking phone calls have almost stopped." name="Ravi Nair" role="The Barbers Co., Kochi" />
            <Quote metric="Zero website maintenance cost" text="I was paying a developer ₹5,000 a month to maintain my old website. My EasyGrox website updates itself when I change prices. The saving paid for my EasyGrox subscription 2.5 times over." name="Meena Kapoor" role="Serenity Spa, Mumbai" />
          </div>
        </div>
      </section>
    </FeaturePageShell>
  );
}



/* ─── BUSINESS TYPE PAGE ─────────────────────────────────── */
function BizType({ nav, type }) {
  const { sm, md } = useW();
  const px = { padding:`0 ${sm ? 16 : 24}px`, maxWidth:1200, margin:"0 auto" };
  const SP = sm ? "56px 0" : "88px 0";
  const [openFaq, setOpenFaq] = useState(null);
  const [activeSvc, setActiveSvc] = useState(0);

  const iconMap = {
    "Hair Salon":"scissors","Barber Shop":"razor","Nail Studio":"nail",
    "Spa & Massage":"lotus","Makeup Artist":"lipstick","Tattoo Studio":"ink","Pet Grooming":"paw"
  };
  const icon = iconMap[type] || "scissors";

  const BD = {
    "Hair Salon": {
      sub:"Stylists · Colour Treatments · Walk-ins · Retail",
      heroHead:"Run your hair salon like a well-oiled machine — from the first booking to the final invoice.",
      heroSub:"Manage every stylist's calendar, track colour formulas, bill services and retail in one place, and give clients a free booking website to book from anywhere.",
      stats:[{v:"60%",l:"Fewer no-shows"},{v:"30 min",l:"Setup time"},{v:"24/7",l:"Online booking"},{v:"₹0",l:"Website cost"}],
      challenges:[
        {icon:"phone",  t:"Booking chaos on WhatsApp",          d:"Appointment requests come in at all hours — DMs, calls, messages — and you spend your evenings sorting through them instead of resting."},
        {icon:"calendar",t:"Double-bookings and overlaps",       d:"Two clients land at the same time for the same stylist because there is no shared calendar and no blocking system."},
        {icon:"user",   t:"Colour formulas lost forever",        d:"Each stylist keeps their own notes — or nothing at all. When a client returns and their stylist is off, no one knows what formula was used."},
        {icon:"bag",    t:"Retail disappears without a trace",   d:"Shampoos and serums are sold informally at the desk — no record, no stock tracking, no idea which products generate the most revenue."},
        {icon:"repeat", t:"Clients stop returning silently",     d:"A regular who used to come every 5 weeks has not been in for 9 weeks. You have no system to flag this or reach out before they choose someone else."},
        {icon:"bar",    t:"End-of-day revenue is a guessing game",d:"Counting receipts, estimating tips, reconciling cash — the daily financial close takes 30 minutes and is still inaccurate."},
      ],
      workflow:[
        {n:"1",icon:"globe",  t:"Client books online — any time",  d:"Your EasyGrox booking website is live 24/7. Clients browse services, choose their preferred stylist, and pick a slot — at midnight or 6am. The booking lands in your calendar instantly."},
        {n:"2",icon:"calendar",t:"Calendar blocks the slot",       d:"The stylist's calendar is updated immediately. No double-booking is possible. Walk-ins are added at the front desk in 10 seconds with the same system."},
        {n:"3",icon:"bell",   t:"Reminders go out automatically", d:"The client gets a confirmation SMS immediately after booking. 24 hours before their appointment, another reminder fires. No manual messaging needed."},
        {n:"4",icon:"scissors",t:"Stylist reviews client history", d:"Before the appointment, the stylist checks the client profile — previous colour formula, service history, notes, and preferences. Every visit, every product, right there."},
        {n:"5",icon:"pos",    t:"Billing done in 60 seconds",      d:"Tap Complete on the appointment. The POS opens pre-loaded with the service. Add any retail sold, apply a discount, collect payment. Digital receipt sent via WhatsApp."},
        {n:"6",icon:"tag",    t:"Automated follow-up triggered",   d:"3 days later, the client gets a follow-up message. 5 weeks out, a rebooking nudge. Loyalty milestones trigger automatic birthday offers and VIP rewards."},
      ],
      features:[
        {n:"calendar",t:"Stylist-Specific Calendar",d:"Every stylist has their own column. Online bookings, walk-ins, and blocks all in one view."},
        {n:"user",    t:"Colour Formula Storage",   d:"Formula, developer strength, processing time, toner — saved permanently to every client profile."},
        {n:"pos",     t:"Service + Retail Billing", d:"Services and shampoos in one transaction. All payment types. Receipt via WhatsApp."},
        {n:"bag",     t:"Retail Inventory",         d:"Track every bottle on your shelf. Low-stock alerts before you run out."},
        {n:"bell",    t:"Auto Reminders",           d:"Booking confirmation + 24h reminder. Salons report up to 60% fewer no-shows."},
        {n:"tag",     t:"Marketing Campaigns",      d:"SMS birthday offers, re-engagement for lapsed clients, flash promotions for slow days."},
        {n:"bar",     t:"Revenue Analytics",        d:"Revenue by stylist, by service category, by day. Peak hours heatmap. Compare weeks."},
        {n:"globe",   t:"Free Booking Website",     d:"Mobile-ready, SEO-indexed, live 24/7. Auto-generated from your store setup."},
      ],
      staffPoints:["Each stylist logs in to their own dashboard — their schedule, their clients, their performance","Assign services to specific stylists — clients only see that stylist when booking that service","Track revenue generated, appointments completed, and client rating per stylist this month","Shift scheduling and leave approval managed inside the app — no WhatsApp back-and-forth","Set role-based access so junior staff see only their work, not financial data"],
      clientPoints:["Every client has a permanent profile with colour formulas, allergies, and visit history","Clients who return to a different stylist are served from the same record — no awkward questions","Automatic rebooking nudge at the right interval — your regulars never slip away silently","VIP status auto-assigned at spend threshold — use it for priority booking or exclusive offers","Birthday and first-visit anniversary offers sent automatically — zero manual effort"],
      metrics:[{v:"₹1,24,800",l:"Avg monthly revenue tracked"},{v:"74%",l:"Client retention rate"},{v:"4.1%",l:"No-show rate (industry avg: 8%)"},{v:"48%",l:"Bookings from website"}],
      activation:[
        {step:"Create your account", where:"This website", done:true},
        {step:"Add your services with prices and durations", where:"App setup · 10 min", done:false},
        {step:"Create stylist profiles and assign services", where:"App setup · 5 min", done:false},
        {step:"Set your salon hours and booking rules", where:"App setup · 3 min", done:false},
        {step:"Preview and publish your free booking website", where:"One click · instant", done:false},
        {step:"Add your booking link to Instagram and WhatsApp", where:"Share · 2 min", done:false},
      ],
      testimonial:{q:"Before EasyGrox I lost hours every week managing WhatsApp bookings. Now my clients book online, my team has their own schedules, and I see each stylist revenue every morning. Setup took 25 minutes.", n:"Priya Sharma", r:"Luxe Hair Studio, Jaipur", metric:"8 more bookings per week"},
      faqs:[
        {q:"Can each stylist see only their own appointments?",a:"Yes. Each stylist gets their own login with a personal dashboard showing only their schedule, their tasks, and their client notes. They cannot see financial data or other staff records unless you give them access."},
        {q:"How does colour formula storage work?",a:"When a stylist completes a colour service, they add the formula to the client profile — developer strength, timing, toner used, and notes. It is saved permanently and visible to any stylist who serves that client in the future."},
        {q:"Does the booking website show individual stylists?",a:"Yes. Clients can browse your service menu and choose a preferred stylist. The website shows each stylist's availability in real time based on their EasyGrox calendar."},
        {q:"How does online booking connect to my calendar?",a:"Instantly and automatically. When a client books through your EasyGrox website, the appointment appears in your calendar in real time — no manual transfer, no phone call, no notification to action."},
      ],
    },
    "Barber Shop": {
      sub:"Queues · Fast Cuts · Walk-ins · Barber Performance",
      heroHead:"Keep your chairs full, your queues moving, and your barbers performing — all from one dashboard.",
      heroSub:"Walk-in queue management, fast billing for quick services, barber-specific schedules, and a free booking website so clients can book ahead and skip the wait.",
      stats:[{v:"10s",l:"Walk-in added"},{v:"60s",l:"Bill any client"},{v:"24/7",l:"Online booking"},{v:"₹0",l:"Website cost"}],
      challenges:[
        {icon:"users",   t:"Walk-ins pile up with no system",     d:"Three people walk in at once. Your barbers are booked. No one knows who is next, who has been waiting longest, or how long the wait will be."},
        {icon:"phone",   t:"Calls during your busiest hour",      d:"Saturday at noon, your phone rings constantly — clients asking if there is a wait. You lose focus mid-cut every time."},
        {icon:"pos",     t:"Daily revenue counted manually",      d:"End of day you count cash, try to recall how many card payments were made, and estimate tips. The total never quite adds up."},
        {icon:"bar",     t:"No barber performance comparison",    d:"You have 3 barbers but no idea which one generates the most revenue, serves the most clients, or gets the best feedback."},
        {icon:"bag",     t:"Retail sold but never tracked",       d:"Wax, beard oil, and styling products are sold from the counter but no record exists — no stock tracking, no sales history, no reorder system."},
        {icon:"repeat",  t:"Regulars drift away quietly",         d:"A regular who came every 2 weeks has not been in for 6 weeks. You have no alert, no system, and no way to reach out before he finds another shop."},
      ],
      workflow:[
        {n:"1",icon:"zap",    t:"Walk-in arrives — logged in 10 seconds", d:"Front desk opens EasyGrox, taps Walk-In, selects the service and the next available barber. Logged, assigned, and in the queue. No paper, no confusion."},
        {n:"2",icon:"calendar",t:"Advance booking fills gaps automatically",d:"Clients who booked through your website pre-fill your morning calendar. You walk in knowing exactly which barbers are booked and which slots are open."},
        {n:"3",icon:"bell",   t:"Reminder keeps clients from forgetting",  d:"Every advance booking gets an automatic SMS the morning of their appointment. Clients arrive on time. Barbers are not left waiting for a no-show."},
        {n:"4",icon:"pos",    t:"Fast billing — three taps",               d:"Service complete. Tap Complete. Select payment method. Collect. Receipt sent. Under 60 seconds. Queue moves. Clients do not wait at the counter."},
        {n:"5",icon:"bar",    t:"Daily summary ready at close",            d:"Total revenue, number of clients, payment method split, and each barber's individual tally — available the moment you close for the day."},
        {n:"6",icon:"repeat", t:"Regular client nudge fires automatically",d:"3 weeks since his last visit? The system sends him a personalised message — no action from you, no manual tracking required."},
      ],
      features:[
        {n:"zap",     t:"Walk-In Queue",          d:"Add a walk-in in 10 seconds. Assign to the next available barber. Queue managed digitally."},
        {n:"calendar",t:"Barber Schedules",        d:"Each barber has their own bookable calendar. Clients see real-time availability."},
        {n:"pos",     t:"Fast Checkout",           d:"Three taps from service complete to payment collected. Never a queue at the counter."},
        {n:"bag",     t:"Retail Tracking",         d:"Track wax, gel, and beard products. Low-stock alert when a product runs low."},
        {n:"bar",     t:"Barber Performance",      d:"Revenue, appointments, and client rating per barber. Compare month on month."},
        {n:"bell",    t:"Automated Reminders",     d:"Morning reminder for every advance booking. No-shows drop significantly."},
        {n:"repeat",  t:"Lapsed Client Campaigns", d:"Automatically message regulars who have not been in for 3+ weeks."},
        {n:"globe",   t:"Free Booking Website",    d:"Clients book ahead and skip the walk-in wait. Your chairs stay full."},
      ],
      staffPoints:["Each barber has their own login — they see their schedule, their earnings, their clients","Set which services each barber performs — clients only see available barbers for each service","Track cuts per day, revenue per month, and average client rating per barber","Leave requests submitted through the app — automatically blocked in the calendar when approved","Front desk role sees bookings and billing — not the owner-level financial dashboard"],
      clientPoints:["Every returning client has a profile — preferences, past services, product purchases","Clients who book ahead skip the walk-in queue — your loyal regulars feel rewarded","Automatic 3-week nudge keeps regulars from drifting to another shop","Birthday offer sent automatically on the right day — personal touch, zero effort","Online booking available 24/7 — clients book from Instagram at 10pm, arrive next morning"],
      metrics:[{v:"312",l:"Appointments managed monthly"},{v:"3 wk",l:"Avg return frequency"},{v:"91%",l:"Chair utilisation rate"},{v:"4.8★",l:"Avg client rating"}],
      activation:[
        {step:"Create your account", where:"This website", done:true},
        {step:"Add your services — haircut, beard trim, etc.", where:"App setup · 8 min", done:false},
        {step:"Create barber profiles and assign services", where:"App setup · 5 min", done:false},
        {step:"Set opening hours and walk-in preferences", where:"App setup · 3 min", done:false},
        {step:"Publish your free booking website", where:"One click · instant", done:false},
        {step:"Share booking link — Instagram bio, WhatsApp status", where:"2 min", done:false},
      ],
      testimonial:{q:"I added the EasyGrox booking link to my Instagram and within a week I had clients booking ahead for the first time. My Saturday rush is now half walk-ins, half advance — it runs so much smoother.", n:"Ravi Kumar", r:"Classic Cuts, Pune", metric:"Chair utilisation up to 91%"},
      faqs:[
        {q:"How does walk-in management work?",a:"When a walk-in arrives, your front desk taps Walk-In in EasyGrox, selects the service and assigns a barber. The appointment is logged instantly. All walk-ins and advance bookings appear on the same calendar so barbers always know what is next."},
        {q:"Can clients book a specific barber online?",a:"Yes. Your EasyGrox booking website shows each barber's available time slots. Clients can choose a barber or select Any available and the system assigns automatically based on availability."},
        {q:"Does EasyGrox work for a single-barber shop?",a:"Yes. The Solo plan is designed specifically for independent barbers or single-operator shops. One login, one calendar, full POS, and your free booking website — all included."},
        {q:"How does the lapsed client re-engagement work?",a:"You set the interval — 3 weeks, 4 weeks, whatever fits your service. When a regular client exceeds that interval, EasyGrox flags them and automatically sends a personalised WhatsApp or SMS message with an offer to rebook."},
      ],
    },
    "Nail Studio": {
      sub:"Nail Technicians · Gel & Art · Retail Products",
      heroHead:"Every nail technician booked, every client preference remembered, every retail product tracked.",
      heroSub:"Precision scheduling for nail studios — service-specific booking, technician columns, client nail art notes, retail inventory, and a free booking website that works while you work.",
      stats:[{v:"0",l:"Double-bookings possible"},{v:"24/7",l:"Online booking"},{v:"10s",l:"Walk-in logged"},{v:"₹0",l:"Website cost"}],
      challenges:[
        {icon:"calendar",t:"Technician bookings overlap",          d:"Without a shared calendar, two technicians get double-booked for the same slot. One client waits, one leaves. The morning starts badly."},
        {icon:"phone",   t:"After-hours booking requests flood in", d:"Clients DM at 11pm asking for gel refill slots next week. You see 14 unread messages in the morning and spend 30 minutes sorting them out."},
        {icon:"bag",     t:"Gel and polish stock runs out mid-week",d:"A client wants a specific OPI shade and it ran out 3 days ago. No alert fired. No reorder was made. You have to explain and suggest an alternative."},
        {icon:"user",    t:"Client preferences are in someone head",d:"Which base coat works for Priya? What shape does she always get? What shade was it last time? If her regular technician is off, no one knows."},
        {icon:"clock",   t:"Refill timing is guesswork",           d:"Gel nails need a refill every 3 weeks. You have no system to know which clients are due — and no way to remind them before they book elsewhere."},
        {icon:"bar",     t:"Retail sales are invisible",            d:"Polish, cuticle oil, and nail tools are sold at the counter with no record. At month-end, you have no idea what your retail revenue was."},
      ],
      workflow:[
        {n:"1",icon:"globe",   t:"Client books through your website",    d:"Your nail studio booking page shows each technician's services and available slots. Clients pick their service — gel refill, full set, nail art — and confirm. Booking appears instantly."},
        {n:"2",icon:"calendar",t:"Technician column updated in real time",d:"The right technician sees the new booking in their calendar. Duration blocked precisely — gel nails and nail art have different durations and the system knows each."},
        {n:"3",icon:"bell",    t:"Reminder sent automatically",           d:"Client gets a confirmation immediately and a reminder 24 hours before. The reminder includes prep instructions — remove old gel at home, arrive with clean nails."},
        {n:"4",icon:"user",   t:"Technician reviews client notes",       d:"Before the appointment, the technician checks the client card — preferred shape, favourite colour family, skin sensitivities, last nail art design. Every visit builds on the last."},
        {n:"5",icon:"pos",    t:"Billing includes service and retail",   d:"Gel full set plus the OPI polish the client wanted to take home — one transaction. Inventory auto-deducted. Receipt sent via WhatsApp."},
        {n:"6",icon:"repeat", t:"Refill reminder fires at 3 weeks",      d:"21 days after the appointment, EasyGrox sends the client an automatic refill reminder. Clients rebook with you — not with the studio down the street."},
      ],
      features:[
        {n:"calendar",t:"Technician Column View",  d:"Each technician has their own bookable column. Service durations set precisely per service."},
        {n:"user",    t:"Client Nail Notes",        d:"Shape preference, colour family, allergies, and past designs stored permanently."},
        {n:"repeat",  t:"Refill Reminders",         d:"Automated 3-week refill nudge. Clients rebook before they look elsewhere."},
        {n:"bag",     t:"Polish and Gel Inventory", d:"Track every product. Low-stock alert before your best-selling shade runs out."},
        {n:"pos",     t:"Retail + Service Billing", d:"Sell a polish take-home alongside the service — one receipt, inventory auto-deducted."},
        {n:"bell",    t:"Auto Reminders",           d:"Confirmation + 24h reminder with prep instructions included."},
        {n:"bar",     t:"Service Analytics",        d:"Which services are growing? Which technician earns the most? Weekly comparison."},
        {n:"globe",   t:"Free Booking Website",     d:"Showcase your nail art portfolio. Clients book a specific technician online."},
      ],
      staffPoints:["Each nail technician has their own calendar column — clients choose them specifically","Set service specialisations — only show gel technicians when a client books gel services","Track revenue generated, services completed, and client satisfaction per technician","Leave requests block their calendar automatically — no manual schedule editing","Junior technicians only see their own column — no access to studio financials"],
      clientPoints:["Client profile stores every nail visit — colour, shape, art design, and products used","Any technician can serve a client with full context — no repeated questions","3-week refill reminder keeps clients returning before they drift away","Preference notes mean every visit feels personalised — not generic","Portfolio shown on booking website builds confidence before first visit"],
      metrics:[{v:"3 wks",l:"Avg refill return cycle"},{v:"82%",l:"Client rebooking rate"},{v:"₹680",l:"Avg retail per client"},{v:"0",l:"Double-bookings"}],
      activation:[
        {step:"Create your account", where:"This website", done:true},
        {step:"Add nail services — gel, full set, nail art, removal", where:"App setup · 10 min", done:false},
        {step:"Add technician profiles and assign services", where:"App setup · 5 min", done:false},
        {step:"Set studio hours and booking lead time", where:"App setup · 3 min", done:false},
        {step:"Publish your free booking website with nail art portfolio", where:"One click", done:false},
        {step:"Add booking link to Instagram bio and WhatsApp", where:"2 min", done:false},
      ],
      testimonial:{q:"My clients used to DM me at midnight for gel refill slots. Now they book through my EasyGrox website and I wake up to a filled calendar. The 3-week refill reminder has been a game changer — my rebooking rate went from 60% to 82%.", n:"Meera Joshi", r:"Gloss Nail Studio, Mumbai", metric:"Rebooking rate 60% to 82%"},
      faqs:[
        {q:"Can I set different durations for different nail services?",a:"Yes. When you add each service, you set the duration individually. Gel full set takes 90 minutes, nail art takes 120, a basic manicure takes 45. The booking system blocks exactly the right amount of time for each."},
        {q:"How does the refill reminder work?",a:"When a gel nail appointment is completed, EasyGrox automatically schedules a reminder message to go to the client 21 days later. The message is personalised with their name and a direct booking link. You set it once and it runs for every gel appointment automatically."},
        {q:"Can clients book a specific technician online?",a:"Yes. Your booking website shows each technician's profile, specialisations, and available time slots. Clients choose the technician or select Any and the system assigns based on availability."},
        {q:"How is retail inventory tracked?",a:"Every retail product sold through the EasyGrox POS automatically deducts from your inventory count. When any product drops below your set minimum stock level, an alert appears on your dashboard so you can reorder before running out."},
      ],
    },
    "Spa & Massage": {
      sub:"Therapists · Treatment Rooms · Packages · Wellness Memberships",
      heroHead:"Fill your treatment rooms, retain your wellness clients, and manage your therapists — all in one calm platform.",
      heroSub:"Room scheduling, therapist assignment, wellness package management, multi-session tracking, and a free booking website — built for spas and massage centres that want to operate with clarity.",
      stats:[{v:"0",l:"Room double-bookings"},{v:"100%",l:"Package tracking"},{v:"24/7",l:"Online booking"},{v:"₹0",l:"Website cost"}],
      challenges:[
        {icon:"calendar",t:"Room double-bookings happen too often",    d:"Without a room management layer, two therapists book the same treatment room for overlapping sessions. One client arrives to find the room in use."},
        {icon:"invoice", t:"Packages and memberships tracked on paper",d:"A client bought a 5-session deep tissue package. How many sessions have they used? The notebook is not in front of you right now and neither is the answer."},
        {icon:"bar",     t:"Therapist utilisation completely invisible",d:"Some therapists are overbooked while others have idle hours. You discover this at month-end, not when you could do something about it."},
        {icon:"clock",   t:"Long sessions need precise buffer time",   d:"A 90-minute hot stone massage needs 15 minutes cleanup between sessions. Without buffer management, the next client arrives before the room is ready."},
        {icon:"repeat",  t:"Wellness clients need gentle follow-up",   d:"A client who comes monthly for a deep tissue session missed last month. No alert. No outreach. By the time you notice, they have found a closer spa."},
        {icon:"gift",    t:"Packages expire without either party knowing",d:"A client bought a 3-month membership 4 months ago. You have no system to flag expired packages or prompt a renewal before the relationship goes cold."},
      ],
      workflow:[
        {n:"1",icon:"globe",   t:"Client books a session online",         d:"Your spa booking website shows every service with duration and pricing. Clients book a therapist, choose a session length, and confirm. Room is automatically assigned."},
        {n:"2",icon:"calendar",t:"Room blocked with buffer automatically", d:"The booking blocks the treatment room for the session duration plus your configured buffer time. The next booking cannot start until the room is cleared."},
        {n:"3",icon:"bell",    t:"Preparation reminder sent to therapist", d:"30 minutes before the session, the therapist's dashboard shows the upcoming client — their package history, preferences, pressure notes, and any allergies."},
        {n:"4",icon:"user",   t:"Package redemption logged at checkout",  d:"Client uses session 3 of their 5-session package. The front desk taps Redeem and the package balance updates automatically. Client sees their remaining sessions."},
        {n:"5",icon:"pos",    t:"Billing — retail and add-ons included",  d:"Aromatherapy add-on, the facial serum the client wanted to take home — added to the session bill in one transaction. Receipt sent instantly."},
        {n:"6",icon:"repeat", t:"Renewal nudge fires before expiry",      d:"When a package has one session remaining, EasyGrox sends the client a renewal message automatically. Most clients renew before the final session."},
      ],
      features:[
        {n:"calendar",t:"Room Management",         d:"Each treatment room has its own schedule. Buffer time configured per service type."},
        {n:"gift",    t:"Wellness Packages",        d:"Create and sell multi-session packages. Balance tracked automatically per client."},
        {n:"users",   t:"Therapist Scheduling",     d:"Therapist-specific calendars with specialisation mapping and utilisation tracking."},
        {n:"repeat",  t:"Membership Renewals",      d:"Automatic renewal nudge before expiry. Retain more members with zero manual effort."},
        {n:"user",    t:"Client Preference Notes",  d:"Pressure preference, allergies, focus areas, and oils — stored permanently."},
        {n:"bell",    t:"Session Reminders",        d:"Longer spa sessions get reminders with preparation instructions included."},
        {n:"bar",     t:"Package Analytics",        d:"Redemption rate, renewal rate, revenue from packages vs. walk-in sessions."},
        {n:"globe",   t:"Free Booking Website",     d:"Service menu, therapist profiles, and packages all showcased. Book online 24/7."},
      ],
      staffPoints:["Therapist-specific schedules — each therapist's specialisations drive what clients can book with them","Utilisation rate tracked per therapist — see who has capacity and who is fully booked","Shift scheduling with leave management — leave requests auto-block the therapist calendar","Task assignment for room preparation, product restocking, and client setup before sessions","Separate access levels — therapists see their own schedule, managers see the full picture"],
      clientPoints:["Full client preference profile — pressure, areas, oils, allergies, past session notes","Clients feel deeply known at every visit — even with a different therapist","Package balance visible to client at checkout — they see exactly what is remaining","Monthly wellness clients get a gentle check-in if they miss their usual slot","Membership anniversaries trigger a personalised renewal offer automatically"],
      metrics:[{v:"₹8,400",l:"Avg package revenue per client"},{v:"78%",l:"Package renewal rate"},{v:"94%",l:"Room utilisation"},{v:"4.9★",l:"Avg client rating"}],
      activation:[
        {step:"Create your account", where:"This website", done:true},
        {step:"Add spa services with session lengths and room types", where:"App setup · 12 min", done:false},
        {step:"Create therapist profiles and assign specialisations", where:"App setup · 5 min", done:false},
        {step:"Configure treatment rooms and buffer times", where:"App setup · 5 min", done:false},
        {step:"Create wellness packages and membership tiers", where:"App setup · 8 min", done:false},
        {step:"Publish your free spa booking website", where:"One click · instant", done:false},
      ],
      testimonial:{q:"Managing 6 treatment rooms manually was a nightmare. EasyGrox eliminated every double-booking issue we had. The package tracking alone saves my front desk 2 hours a day. Our renewal rate went from 55% to 78%.", n:"Sunita Kapoor", r:"The Wellness Retreat, Bangalore", metric:"Package renewal rate 55% to 78%"},
      faqs:[
        {q:"How does room management work?",a:"When you set up your spa in EasyGrox, you add each treatment room as a resource. When a session is booked, EasyGrox automatically assigns it to an available room and blocks it for the session duration plus your configured buffer time. No two sessions can overlap in the same room."},
        {q:"How are wellness packages tracked?",a:"When a client purchases a package — say 5 deep tissue sessions — EasyGrox creates a package record linked to their client profile. Each time a session is redeemed at the front desk, the balance decrements. The client and your team can always see exactly how many sessions remain."},
        {q:"Can I configure different buffer times for different services?",a:"Yes. You set buffer time per service when you add it in your store setup. A 90-minute hot stone massage might have a 20-minute buffer while a 30-minute express massage has a 10-minute buffer. The calendar enforces these automatically."},
        {q:"How does the membership renewal reminder work?",a:"When a package has one session remaining, EasyGrox triggers an automatic SMS or WhatsApp message to the client encouraging renewal. You can customise the message and include a renewal offer or discount code to incentivise re-purchase."},
      ],
    },
    "Tattoo Studio": {
      sub:"Tattoo Artists · Session Deposits · Artwork History · Multi-Session",
      heroHead:"Manage your artists, protect your deposits, and build every client relationship — session by session.",
      heroSub:"Session booking with deposit capture, client artwork archive, multi-session progress tracking, and a free studio website that showcases every artist's portfolio and live availability.",
      stats:[{v:"100%",l:"Deposits tracked"},{v:"0",l:"Scheduling conflicts"},{v:"24/7",l:"Online enquiry"},{v:"₹0",l:"Website cost"}],
      challenges:[
        {icon:"invoice", t:"Deposits collected but never properly tracked",d:"You take a deposit to confirm a session booking. It is written in a notebook or remembered. By appointment day, no one is certain what the deposit amount was or whether it was paid."},
        {icon:"user",    t:"Client artwork references stored everywhere",   d:"Reference images in WhatsApp, design briefs in email, sketches in a folder. When a client comes back for continuation work, piecing together the brief takes 20 minutes."},
        {icon:"calendar",t:"Session scheduling is done on WhatsApp",        d:"Scheduling a sleeve across 4 sessions means 4 separate WhatsApp conversations, manually avoiding overlaps, and hoping nothing gets lost. It always does."},
        {icon:"users",   t:"Walk-ins disrupt in-progress sessions",         d:"An artist is 90 minutes into a detailed piece. A walk-in arrives asking for a small tattoo. Your front desk has no system to manage this without interrupting the session."},
        {icon:"clock",   t:"No buffer between sessions for preparation",    d:"One session ends and the next client arrives in the waiting area before the artist has cleaned up and set up. Rushed transitions affect quality and professionalism."},
        {icon:"layers",  t:"Multi-session progress invisible to everyone",  d:"A client is on session 3 of a sleeve. What was completed in session 2? What is the plan for today? Without a record, every session starts with a memory exercise."},
      ],
      workflow:[
        {n:"1",icon:"globe",   t:"Client enquires through your website",  d:"Your studio website shows each artist's portfolio and availability. Clients request a consultation or session booking directly — no DMs to a personal phone."},
        {n:"2",icon:"invoice", t:"Deposit captured at booking",           d:"The booking is confirmed when the deposit is paid. Amount logged against the client record. Deducted from the final bill automatically on session day."},
        {n:"3",icon:"user",    t:"Design brief and references stored",     d:"Artwork references, the agreed design brief, skin tone notes, and placement details — all uploaded to the client profile during or after the consultation."},
        {n:"4",icon:"calendar",t:"Session scheduled with buffer built in",d:"The artist's calendar blocks the session duration plus cleanup buffer. The next booking cannot start until the setup period is complete."},
        {n:"5",icon:"bell",    t:"Session reminder with care instructions",d:"Client gets a reminder 24 hours before with pre-session care instructions — moisturise, eat well, wear appropriate clothing for the placement area."},
        {n:"6",icon:"repeat",  t:"Progress logged after each session",    d:"Artist adds session notes — areas completed, ink used, healing instructions given. The next session brief is built from these notes. No memory required."},
      ],
      features:[
        {n:"invoice", t:"Deposit Management",      d:"Deposit captured and tracked at booking. Deducted from final session bill automatically."},
        {n:"user",    t:"Client Artwork Archive",  d:"References, design briefs, and session notes stored permanently per client."},
        {n:"repeat",  t:"Multi-Session Tracking",  d:"Log progress after each session — areas done, what remains, next session plan."},
        {n:"calendar",t:"Artist Scheduling",       d:"Artist-specific calendars with buffer time between sessions built in automatically."},
        {n:"bell",    t:"Pre-Session Reminders",   d:"Care instructions sent automatically 24 hours before every session."},
        {n:"globe",   t:"Studio Website",          d:"Artist portfolio pages with individual availability. Accept session enquiries 24/7."},
        {n:"bar",     t:"Studio Revenue Tracking", d:"Revenue per artist, per session type. Deposits vs. completions. Monthly summary."},
        {n:"lock",    t:"Session Confidentiality", d:"Each artist sees only their own client records. Studio owner sees everything."},
      ],
      staffPoints:["Each artist has their own calendar, their own client records, and their own portfolio page on your website","Set buffer time per artist — some need 15 minutes setup, others 30. The calendar enforces it","Track revenue per artist, sessions completed per month, and average session value","Artists submit leave requests in the app — their calendar blocks automatically when approved","Role-based access — artists see their own work, studio manager sees all financials"],
      clientPoints:["Full artwork history per client — every session, every reference, every design note","Clients return for continuation work and find their full brief ready — no need to explain again","Pre-session care reminders mean clients arrive prepared — better healing, better results","Deposit tracking means no awkward conversations about what was paid and when","Multi-session clients see their progress tracked — they feel invested in the journey"],
      metrics:[{v:"100%",l:"Deposits tracked automatically"},{v:"4 hrs",l:"Avg session duration managed"},{v:"67%",l:"Multi-session client rate"},{v:"4.8★",l:"Avg studio rating"}],
      activation:[
        {step:"Create your account", where:"This website", done:true},
        {step:"Add session types with deposit amounts and durations", where:"App setup · 10 min", done:false},
        {step:"Create artist profiles and upload portfolio images", where:"App setup · 15 min", done:false},
        {step:"Configure session buffers and booking rules", where:"App setup · 5 min", done:false},
        {step:"Publish your studio website with artist profiles", where:"One click · instant", done:false},
        {step:"Share studio booking link on Instagram and social", where:"2 min", done:false},
      ],
      testimonial:{q:"I used to track deposits in a notebook and manage session scheduling across 3 WhatsApp chats. EasyGrox gave every artist their own schedule and every client a proper profile. Deposit disputes went to zero. My artists are happier.", n:"Aryan Mehta", r:"Inkwork Studio, Delhi", metric:"Deposit disputes: zero"},
      faqs:[
        {q:"How does deposit management work?",a:"When you set up a service in EasyGrox, you configure the deposit amount required to confirm a booking. When a client books, the deposit is recorded against their profile. On session day, the front desk sees the deposit paid and it is automatically deducted from the final bill."},
        {q:"How does multi-session progress tracking work?",a:"After each session, the artist opens the client profile and adds session notes — areas completed, ink references, healing instructions given, and the plan for the next session. This builds a complete session history that any team member can access before the next appointment."},
        {q:"Can each artist have their own portfolio page on the studio website?",a:"Yes. Each artist profile you create in EasyGrox appears as their own page on your studio booking website, with a bio, specialisations, and available session slots. Clients can browse artists and book directly with their preferred tattooist."},
        {q:"What happens if a client needs to reschedule a session?",a:"The front desk or the client can reschedule from the booking confirmation. The system updates the artist's calendar, notifies both artist and client, and retains the deposit against the rescheduled session."},
      ],
    },
    "Makeup Artist": {
      sub:"Bridal Sessions · Photoshoots · Kit Inventory · Client Briefs",
      heroHead:"Manage every booking, every brief, and every session — from trial to wedding day.",
      heroSub:"Session scheduling with client brief capture, bridal package management, kit inventory tracking, and a professional booking website that showcases your portfolio and takes bookings while you work.",
      stats:[{v:"100%",l:"Briefs captured"},{v:"24/7",l:"Online booking"},{v:"₹0",l:"Website cost"},{v:"5 min",l:"Invoice generated"}],
      challenges:[
        {icon:"phone",   t:"Enquiries spread across every platform",      d:"Instagram DMs. WhatsApp messages. Calls. Emails. A bridal enquiry comes in on Tuesday through Instagram and you see it on Thursday. The bride already booked someone else."},
        {icon:"gift",    t:"Bridal sessions are hard to coordinate",      d:"A bridal package has a trial, a mehendi session, and a wedding morning session — possibly for the bride plus bridesmaids. Coordinating these across WhatsApp threads loses details."},
        {icon:"bag",     t:"Kit inventory is managed by memory",          d:"You reach for a foundation shade mid-session and the bottle is empty. There was no alert, no system. You apologise and improvise while the client waits."},
        {icon:"user",    t:"Client skin details live in your head",       d:"Meera has dry skin, uses no fragrance products, and wants a dewy finish. Her skin undertone is warm. If you did not write it down somewhere you can find right now, this information is at risk."},
        {icon:"invoice", t:"Invoicing is done manually after every session",d:"After a full bridal morning you sit down to write an invoice. Then WhatsApp it. Then follow up when it is not paid. This takes time you do not have."},
        {icon:"calendar",t:"Calendar conflicts happen without a system",  d:"You double-book a trial session because your mental calendar and your WhatsApp threads are not the same thing. The client calls to confirm and you have to have an uncomfortable conversation."},
      ],
      workflow:[
        {n:"1",icon:"globe",   t:"Client enquires through your website",  d:"Your portfolio website shows your work, packages, and pricing. Clients submit a session enquiry directly from the site — no DMs, no missed messages."},
        {n:"2",icon:"user",    t:"Client brief captured before the session",d:"Before confirming, the client fills a brief — skin type, undertone, allergies, finish preference, occasion, and reference images. All stored permanently in their profile."},
        {n:"3",icon:"calendar",t:"Session added to your calendar",        d:"Trial, pre-wedding, and wedding sessions added as a linked package. Each session blocks your calendar with travel time built in if applicable."},
        {n:"4",icon:"bell",    t:"Pre-session reminders sent automatically",d:"Client receives a reminder 24 hours before with prep instructions — skin care routine to follow, what to avoid, what to wear during the session."},
        {n:"5",icon:"pos",     t:"Invoice generated in 5 minutes",        d:"Session complete. Open the billing screen, confirm services rendered, tap Generate Invoice. Sent to the client via WhatsApp or email. Follow-up automated if unpaid."},
        {n:"6",icon:"tag",     t:"Follow-up and rebooking automated",     d:"Post-session, an automatic thank-you message is sent. If the client is a regular, a rebooking nudge fires at the right interval. Bridal clients receive a review request."},
      ],
      features:[
        {n:"globe",   t:"Portfolio Booking Website", d:"Your work showcased, packages priced, and bookings accepted 24/7. No developer needed."},
        {n:"user",    t:"Client Brief Storage",      d:"Skin type, undertone, allergies, references, and finish preference stored per client."},
        {n:"gift",    t:"Bridal Package Management", d:"Trial, mehendi, wedding sessions — managed as one linked package with individual billing."},
        {n:"bag",     t:"Kit Inventory Tracking",    d:"Track foundations, palettes, and brushes. Low-stock alert before you run out mid-kit."},
        {n:"invoice", t:"Instant Invoice Generation",d:"Professional invoice created in 5 minutes. Sent via WhatsApp. Follow-up automated."},
        {n:"calendar",t:"Session Calendar",          d:"All sessions in one calendar. Travel time built in. No more mental juggling."},
        {n:"bell",    t:"Prep Reminders",            d:"Skin prep instructions sent automatically before every session."},
        {n:"bar",     t:"Revenue by Session Type",   d:"Track bridal vs. editorial vs. event makeup revenue. See which work is most profitable."},
      ],
      staffPoints:["If you work with a team of artists, each has their own calendar and client records","Assign session types per artist — only show a senior artist for bridal, junior for casual sessions","Track revenue per artist, sessions completed, and average client rating this month","Artists can update client briefs and session notes from their own login — no shared passwords","Role-based access means artists see their work and clients, not studio-level financials"],
      clientPoints:["Every client has a permanent brief — their skin, their preferences, their reference images","Returning clients feel remembered and understood from the first sentence of every session","Bridal clients have all three sessions linked — trial notes inform the wedding day approach","Post-session review request builds your portfolio testimonial and Google presence","Client referral tracking helps you understand which clients bring new bookings"],
      metrics:[{v:"₹12,400",l:"Avg bridal package value"},{v:"4.9★",l:"Avg client rating"},{v:"88%",l:"Bridal client return rate"},{v:"24/7",l:"Enquiries captured"}],
      activation:[
        {step:"Create your account", where:"This website", done:true},
        {step:"Add your session types and bridal packages", where:"App setup · 10 min", done:false},
        {step:"Upload portfolio images to your profile", where:"App setup · 10 min", done:false},
        {step:"Set your availability and travel buffer times", where:"App setup · 5 min", done:false},
        {step:"Publish your booking website with portfolio", where:"One click · instant", done:false},
        {step:"Add booking link to Instagram bio and linktree", where:"2 min", done:false},
      ],
      testimonial:{q:"I used to manage bridal bookings across 5 WhatsApp groups. EasyGrox gave me one calendar, one client record per bride, and invoicing that takes 5 minutes. I have not missed a booking enquiry since I launched my website.", n:"Nisha Arora", r:"Studio N Makeup, Chandigarh", metric:"Zero missed enquiries"},
      faqs:[
        {q:"How does bridal package management work?",a:"When you create a bridal package in EasyGrox, you define the sessions it includes — trial, pre-wedding, and wedding day. All three sessions appear as linked bookings in your calendar. Each can be invoiced separately or together. Notes from the trial inform the wedding day session automatically."},
        {q:"Can I capture a client brief before the session?",a:"Yes. Your booking confirmation includes a brief form for the client to complete — skin type, undertone, allergies, finish preference, and reference images. This populates their client profile before you meet them."},
        {q:"How does kit inventory tracking work?",a:"You add every product in your kit to the EasyGrox inventory — foundation shades, palettes, brushes, setting sprays. You set a minimum quantity for each. When a product drops below that level, a low-stock alert appears on your dashboard."},
        {q:"What does the booking website include?",a:"Your EasyGrox booking website includes your portfolio images, a service and package menu with pricing, a session enquiry and booking form, and your availability calendar. Clients can browse your work, understand your pricing, and book or enquire directly — no calls or DMs needed."},
      ],
    },
    "Pet Grooming": {
      sub:"Pet Profiles · Recurring Schedules · Grooming History · Reminders",
      heroHead:"Every pet remembered. Every grooming appointment on time. Every owner delighted.",
      heroSub:"Pet profiles with breed and grooming history, recurring appointment scheduling, automated grooming reminders, retail product tracking, and a free booking website so pet owners can book from their phone.",
      stats:[{v:"100%",l:"Pet profiles tracked"},{v:"6 wk",l:"Auto rebooking cycle"},{v:"24/7",l:"Online booking"},{v:"₹0",l:"Website cost"}],
      challenges:[
        {icon:"paw",     t:"Pet details scattered across notes and memory",  d:"Which shampoo does Bruno react to? What blade length does Coco get? Is Max due for a nail trim as well? The answers are in three different places — or nowhere."},
        {icon:"repeat",  t:"Recurring grooming schedules fall apart",        d:"Goldens need a groom every 6 weeks. You have 40 dogs on irregular schedules and no system to track when each one is due or remind the owner before they forget."},
        {icon:"bag",     t:"Pet products run out without warning",           d:"A dog arrives for a medicated shampoo treatment and the stock ran out 3 days ago. No alert fired. No reorder was made. The owner has to reschedule."},
        {icon:"users",   t:"Walk-in groomings disrupt the whole schedule",   d:"A pet owner walks in with a large dog needing a full groom. Your grooming table is booked. There is no queue system and no way to give an accurate wait time."},
        {icon:"user",    t:"No central record of grooming preferences",      d:"If the regular groomer is off, the replacement has no idea what the pet gets, how they behave, or what the owner prefers. Every session starts from zero."},
        {icon:"bar",     t:"Retail sales tracked on paper or not at all",   d:"Medicated shampoos, conditioners, and grooming accessories are sold at the counter. No inventory record, no stock alert, no monthly retail revenue figure."},
      ],
      workflow:[
        {n:"1",icon:"globe",   t:"Owner books online through your website",d:"Your grooming website shows your services with prices and available slots. Owners select their pet from their profile or create a new pet entry. Booking confirmed in seconds."},
        {n:"2",icon:"user",    t:"Pet profile loaded for the groomer",    d:"Before the appointment, the groomer reviews the pet card — breed, coat type, preferred blade, sensitive areas, past reactions, and any health notes. No surprises."},
        {n:"3",icon:"calendar",t:"Grooming session blocked precisely",    d:"The calendar blocks the right duration — a large dog needs more time than a small one. Service type and breed determine duration. No rushed sessions."},
        {n:"4",icon:"bell",    t:"Reminder sent to the owner",            d:"Owner gets a reminder 24 hours before with preparation tips — do not feed heavily before the appointment, bring vaccination records if required."},
        {n:"5",icon:"pos",     t:"Billing includes grooming plus retail",  d:"Full groom plus the medicated shampoo the owner wanted to take home — one transaction. Inventory deducted. Receipt sent via WhatsApp."},
        {n:"6",icon:"repeat",  t:"Rebooking nudge fires at 6 weeks",      d:"When the grooming cycle for that breed is due, EasyGrox sends the owner a personalised reminder with a direct booking link. The cycle continues automatically."},
      ],
      features:[
        {n:"paw",     t:"Pet Profile System",       d:"Breed, coat, blade preference, health notes, allergies, and full grooming history per pet."},
        {n:"repeat",  t:"Recurring Schedules",      d:"Set a grooming cycle per breed. Automatic rebooking reminder at the right interval."},
        {n:"bell",    t:"Owner Reminders",          d:"24h reminder with prep instructions. Grooming due alerts sent at cycle completion."},
        {n:"bag",     t:"Product Inventory",        d:"Track medicated shampoos, conditioners, and accessories. Low-stock alert before runout."},
        {n:"pos",     t:"Grooming + Retail Billing",d:"Grooming service plus product take-home — one transaction, one receipt."},
        {n:"calendar",t:"Groomer Schedules",        d:"Each groomer has their own calendar. Walk-ins added in 10 seconds to the queue."},
        {n:"bar",     t:"Business Analytics",       d:"Revenue by service, by groomer, by breed. See which services drive the most revenue."},
        {n:"globe",   t:"Free Booking Website",     d:"Pet owners book from their phone at any hour. Your grooming table stays full."},
      ],
      staffPoints:["Each groomer has their own calendar column — their schedule, their pets, their notes","Set service types per groomer — a junior handles small dogs, senior handles large breeds","Track grooming sessions completed, revenue generated, and owner satisfaction per groomer","Leave requests approved in the app — groomer calendar blocked automatically when approved","Walk-in assignments managed from a shared front desk view — no groomer is overloaded"],
      clientPoints:["Every pet has a permanent profile — the whole team knows the dog before it walks through the door","Pet owners feel reassured that their pet is remembered and handled correctly each time","Recurring grooming reminders keep owners returning on schedule without being nagged","Breed-specific notes mean every groomer handles the pet correctly — even if it is a first meeting","Online booking means owners can schedule at midnight — no more playing phone tag"],
      metrics:[{v:"87%",l:"Client rebooking rate"},{v:"6 wks",l:"Avg grooming return cycle"},{v:"₹2,400",l:"Avg monthly spend per pet"},{v:"4.9★",l:"Avg owner rating"}],
      activation:[
        {step:"Create your account", where:"This website", done:true},
        {step:"Add grooming services with breed-based durations", where:"App setup · 10 min", done:false},
        {step:"Create groomer profiles and assign services", where:"App setup · 5 min", done:false},
        {step:"Set grooming cycles per breed type", where:"App setup · 5 min", done:false},
        {step:"Publish your free pet grooming booking website", where:"One click · instant", done:false},
        {step:"Share booking link with existing clients and on social", where:"2 min", done:false},
      ],
      testimonial:{q:"I have 80 regular dogs across 40 families. Keeping track of every pet's grooming history and schedule manually was exhausting. EasyGrox gives every dog their own profile and the auto rebooking reminders have been a revelation. My rebooking rate went from 70% to 87%.", n:"Priya Nair", r:"Pawsome Grooming, Kochi", metric:"Rebooking rate 70% to 87%"},
      faqs:[
        {q:"Can I store different information for each pet?",a:"Yes. Each pet has their own profile with breed, coat type, grooming preferences (blade length, style), health notes, product sensitivities, and a full grooming history. If a pet reacts to a certain shampoo, it is noted in their profile and flagged for every future visit."},
        {q:"How does the recurring grooming reminder work?",a:"When you set up each service, you configure the recommended grooming cycle for that service — 6 weeks for most breeds, 4 weeks for high-maintenance coats. When the cycle period is reached after a completed appointment, EasyGrox automatically sends the owner a personalised reminder with a direct booking link."},
        {q:"Can clients book for multiple pets in one session?",a:"Yes. An owner with two dogs can book separate grooming sessions for each pet in one visit. Each pet has its own service, its own groomer assignment, and its own billing line — but the owner receives one combined receipt."},
        {q:"How does retail inventory tracking work for grooming products?",a:"You add every grooming product to your EasyGrox inventory with a minimum stock level. Every time a product is sold at the POS — medicated shampoo, conditioner, brush — the stock count decrements automatically. When it drops below minimum, an alert appears on your dashboard."},
      ],
    },
  };

  const d = BD[type] || BD["Hair Salon"];

  return (
    <>
      {/* ─── 1. HERO ─── */}
      <section style={{ padding:sm ? "56px 0 44px" : "88px 0 72px", background:C.bg }}>
        <div style={{ ...px }}>
          <div style={{ display:"flex", gap:6, alignItems:"center", marginBottom:24, fontFamily:F, fontSize:13, color:C.inkVar, flexWrap:"wrap" }}>
            <button onClick={() => nav("home")} style={{ background:"none", border:"none", color:C.inkVar, cursor:"pointer", fontFamily:F, fontSize:13 }}>Home</button>
            <Ic n="chevR" sz={11} col={C.inkSoft} />
            <button onClick={() => nav("home")} style={{ background:"none", border:"none", color:C.inkVar, cursor:"pointer", fontFamily:F, fontSize:13 }}>By Business</button>
            <Ic n="chevR" sz={11} col={C.inkSoft} />
            <span style={{ color:C.teal, fontWeight:600 }}>{type}</span>
          </div>
          <div style={{ display:"grid", gridTemplateColumns:md ? "1fr" : "1fr 1fr", gap:md ? 36 : 64, alignItems:"center" }}>
            <div>
              <div style={{ display:"flex", alignItems:"center", gap:12, marginBottom:20 }}>
                <div style={{ width:52, height:52, borderRadius:R, background:C.tealLight, border:`1px solid ${C.tealBorder}`, display:"flex", alignItems:"center", justifyContent:"center" }}>
                  <Ic n={icon} sz={26} col={C.teal} />
                </div>
                <div>
                  <Chip teal sm>{d.sub}</Chip>
                </div>
              </div>
              <H1 style={{ marginBottom:20, lineHeight:1.08 }}>{d.heroHead}</H1>
              <Body muted style={{ fontSize:sm ? 15.5 : 17.5, marginBottom:32, lineHeight:1.72 }}>{d.heroSub}</Body>
              <div style={{ display:"flex", gap:12, flexWrap:"wrap", marginBottom:28 }}>
                <Btn onClick={() => nav("signup")}>Create Free Account</Btn>
                <Btn v="outline" onClick={() => nav("productTour")}>Watch Product Tour</Btn>
              </div>
              <div style={{ display:"flex", flexWrap:"wrap", gap:8 }}>
                {["No credit card required","Free booking website","Setup in 30 minutes","Cancel anytime"].map((t, i) => (
                  <span key={i} style={{ display:"flex", alignItems:"center", gap:5, fontFamily:F, fontSize:12.5, color:C.inkVar, background:C.surface, border:`1px solid ${C.outlineVar}`, borderRadius:9999, padding:"4px 12px" }}>
                    <Ic n="check" sz={11} col={C.teal} />{t}
                  </span>
                ))}
              </div>
            </div>
            <div>
              <CalendarMock />
              <div style={{ display:"grid", gridTemplateColumns:"1fr 1fr 1fr 1fr", gap:10, marginTop:14 }}>
                {d.stats.map((s, i) => (
                  <div key={i} style={{ background:C.surface, borderRadius:R, padding:"14px 10px", textAlign:"center", border:`1px solid ${C.outlineVar}`, boxShadow:C.sh1 }}>
                    <div style={{ fontFamily:F, fontSize:22, fontWeight:800, color:C.teal }}>{s.v}</div>
                    <div style={{ fontFamily:F, fontSize:11, color:C.inkVar, marginTop:3 }}>{s.l}</div>
                  </div>
                ))}
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* ─── 2. INDUSTRY CHALLENGES ─── */}
      <section style={{ padding:SP, background:C.low }}>
        <style>{`
          @keyframes fadeUpChallenge {
            from { opacity:0; transform:translateY(28px); }
            to   { opacity:1; transform:translateY(0); }
          }
          @keyframes pulseRing {
            0%,100% { box-shadow: 0 0 0 0 rgba(186,26,26,0.18); }
            50%      { box-shadow: 0 0 0 8px rgba(186,26,26,0); }
          }
          .challenge-card {
            opacity:0;
            animation: fadeUpChallenge 0.52s cubic-bezier(0.22,1,0.36,1) forwards;
          }
          .challenge-card:hover .challenge-icon-ring {
            animation: pulseRing 1.4s ease infinite;
            transform: scale(1.08);
          }
          .challenge-card:hover {
            transform: translateY(-4px) !important;
            box-shadow: 0 12px 36px rgba(186,26,26,0.13) !important;
            border-color: #fca5a5 !important;
          }
          .challenge-icon-ring {
            transition: transform 0.22s ease;
          }
        `}</style>
        <div style={{ ...px }}>
          <div style={{ textAlign:"center", marginBottom:48 }}>
            <Label center>We know the daily reality</Label>
            <H2 center>Running a {type} is harder than it looks</H2>
            <Body muted center style={{ maxWidth:520, margin:"14px auto 0", fontSize:sm ? 14.5 : 16 }}>These are the operational problems that cost you time, money, and clients — every single week.</Body>
          </div>
          <div style={{ display:"grid", gridTemplateColumns:sm ? "1fr" : md ? "1fr 1fr" : "repeat(3,1fr)", gap:18 }}>
            {d.challenges.map((c, i) => (
              <div key={i} className="challenge-card"
                style={{
                  background:C.surface,
                  borderRadius:R,
                  padding:"28px 22px",
                  border:`1px solid ${C.errBorder}`,
                  boxShadow:C.sh1,
                  transition:"transform 0.22s ease, box-shadow 0.22s ease, border-color 0.22s ease",
                  animationDelay:`${i * 0.09}s`,
                  cursor:"default",
                  position:"relative",
                  overflow:"hidden",
                }}>
                {/* Subtle top accent line */}
                <div style={{ position:"absolute", top:0, left:0, right:0, height:3, background:`linear-gradient(90deg, ${C.err} 0%, #f87171 100%)`, borderRadius:"12px 12px 0 0" }} />
                {/* Icon */}
                <div className="challenge-icon-ring" style={{
                  width:52, height:52, borderRadius:14,
                  background:"#fef2f2",
                  border:"1.5px solid #fecaca",
                  display:"flex", alignItems:"center", justifyContent:"center",
                  marginBottom:18, color:C.err,
                }}>
                  <Ic n={c.icon || "zap"} sz={22} col={C.err} />
                </div>
                {/* Number badge */}
                <div style={{ position:"absolute", top:20, right:20, width:22, height:22, borderRadius:"50%", background:"#fef2f2", border:"1px solid #fecaca", display:"flex", alignItems:"center", justifyContent:"center" }}>
                  <span style={{ fontFamily:F, fontSize:10.5, fontWeight:800, color:C.err }}>{i + 1}</span>
                </div>
                {/* Title */}
                <div style={{ fontFamily:F, fontSize:14.5, fontWeight:700, color:C.ink, marginBottom:10, lineHeight:1.35 }}>{c.t}</div>
                {/* Description */}
                <div style={{ fontFamily:F, fontSize:13.5, color:C.inkVar, lineHeight:1.68 }}>{c.d}</div>
              </div>
            ))}
          </div>
          {/* Bottom summary strip */}
          <div style={{ marginTop:36, background:C.surface, borderRadius:R, border:`1px solid ${C.outlineVar}`, padding:sm ? "18px 20px" : "20px 28px", display:"flex", flexWrap:"wrap", gap:16, alignItems:"center", justifyContent:"space-between", boxShadow:C.sh1 }}>
            <div style={{ display:"flex", alignItems:"center", gap:12 }}>
              <div style={{ width:36, height:36, borderRadius:10, background:C.errBg, border:`1px solid ${C.errBorder}`, display:"flex", alignItems:"center", justifyContent:"center", flexShrink:0 }}>
                <Ic n="xmark" sz={16} col={C.err} />
              </div>
              <Body sm muted style={{ fontWeight:500 }}>These problems cost the average {type.toLowerCase()} owner <strong style={{ color:C.ink }}>8+ hours per week</strong> in manual admin and missed revenue.</Body>
            </div>
            <Btn sm onClick={() => nav("signup")} style={{ flexShrink:0 }}>See How EasyGrox Fixes This</Btn>
          </div>
        </div>
      </section>

      {/* ─── 3. HOW THE PLATFORM HELPS (Pain vs Gain) ─── */}
      <section style={{ padding:SP, background:C.bg }}>
        <div style={{ ...px }}>
          <div style={{ textAlign:"center", marginBottom:44 }}>
            <Label center>The EasyGrox difference</Label>
            <H2 center>What changes when you switch to EasyGrox</H2>
          </div>
          <PainGain
            pains={d.challenges.map(c => c.t + " — " + c.d.split(".")[0])}
            gains={d.features.map(f => f.t + " — " + f.d)}
          />
          <div style={{ textAlign:"center", marginTop:32 }}>
            <Btn onClick={() => nav("signup")}>Create Free Account</Btn>
          </div>
        </div>
      </section>

      {/* ─── 4. DAILY OPERATIONS WORKFLOW ─── */}
      <section style={{ padding:SP, background:C.low }}>
        <div style={{ ...px }}>
          <div style={{ display:"grid", gridTemplateColumns:md ? "1fr" : "1fr 1fr", gap:64, alignItems:"start" }}>
            <div>
              <Label>Daily operations workflow</Label>
              <H2 style={{ marginBottom:14, marginTop:10 }}>How a typical day flows through EasyGrox</H2>
              <Body muted style={{ marginBottom:36, fontSize:sm ? 14.5 : 16 }}>From the first booking to the final payment — every operational step is handled inside one platform. No switching between apps, no lost messages, no manual updates.</Body>
              {d.workflow.map((step, i) => (
                <WorkflowStep key={i} n={step.n} title={step.t} desc={step.d} note={null} final={i === d.workflow.length - 1} />
              ))}
            </div>
            <div style={{ position:md ? "static" : "sticky", top:80 }}>
              <DashMock />
              <div style={{ marginTop:16, background:C.tealLight, borderRadius:R, padding:"16px 20px", border:`1px solid ${C.tealBorder}` }}>
                <div style={{ fontFamily:F, fontSize:13, fontWeight:700, color:C.teal, marginBottom:4 }}>Every step is connected</div>
                <div style={{ fontFamily:F, fontSize:13, color:C.inkVar, lineHeight:1.6 }}>Bookings, billing, reminders, and follow-up — all in one platform. Your team does not switch apps. Your data does not get lost.</div>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* ─── 5. FEATURE HIGHLIGHTS ─── */}
      <section style={{ padding:SP, background:C.bg }}>
        <div style={{ ...px }}>
          <div style={{ textAlign:"center", marginBottom:44 }}>
            <Label center>Built for {type}s</Label>
            <H2 center>The features that matter for your business</H2>
            <Body muted center style={{ maxWidth:520, margin:"14px auto 0", fontSize:sm ? 14.5 : 16 }}>Not generic software with everything turned on. The features below are precisely what a {type.toLowerCase()} needs — nothing more, nothing irrelevant.</Body>
          </div>
          <div style={{ display:"grid", gridTemplateColumns:sm ? "1fr 1fr" : md ? "repeat(3,1fr)" : "repeat(4,1fr)", gap:14 }}>
            {d.features.map((f, i) => (
              <Card key={i} style={{ padding:"22px 18px", cursor:"default" }}>
                <IcBox n={f.n} />
                <H4 style={{ marginBottom:7 }}>{f.t}</H4>
                <Body sm muted style={{ lineHeight:1.6 }}>{f.d}</Body>
              </Card>
            ))}
          </div>
        </div>
      </section>

      {/* ─── 6. STAFF & TEAM MANAGEMENT ─── */}
      <section style={{ padding:SP, background:C.low }}>
        <div style={{ ...px }}>
          <div style={{ display:"grid", gridTemplateColumns:md ? "1fr" : "1fr 1fr", gap:64, alignItems:"start" }}>
            <div>
              <Label>Staff and team management</Label>
              <H2 style={{ marginBottom:14, marginTop:10 }}>Your team runs independently — without you managing every detail</H2>
              <Body muted style={{ marginBottom:32 }}>Give every team member their own login. Let them manage their own schedule, see their clients, and track their performance. You see the full picture from the owner dashboard.</Body>
              {d.staffPoints.map((pt, i) => (
                <div key={i} style={{ display:"flex", gap:12, marginBottom:16, alignItems:"flex-start" }}>
                  <div style={{ width:22, height:22, borderRadius:6, background:C.tealLight, border:`1px solid ${C.tealBorder}`, display:"flex", alignItems:"center", justifyContent:"center", flexShrink:0, marginTop:2 }}>
                    <Ic n="check" sz={11} col={C.teal} />
                  </div>
                  <Body sm muted style={{ lineHeight:1.65 }}>{pt}</Body>
                </div>
              ))}
              <div style={{ marginTop:24 }}>
                <Btn v="outline" onClick={() => nav("staffMgmt")}>Explore Staff Management</Btn>
              </div>
            </div>
            <StaffMock />
          </div>
        </div>
      </section>

      {/* ─── 7. CLIENT / CUSTOMER EXPERIENCE ─── */}
      <section style={{ padding:SP, background:C.bg }}>
        <div style={{ ...px }}>
          <div style={{ display:"grid", gridTemplateColumns:md ? "1fr" : "1fr 1fr", gap:64, alignItems:"start" }}>
            <ClientMock />
            <div>
              <Label>Client experience and retention</Label>
              <H2 style={{ marginBottom:14, marginTop:10 }}>Every client feels remembered — even on their first visit back after months</H2>
              <Body muted style={{ marginBottom:32 }}>A complete client profile built from every visit. Your team knows their history before they sit down. Automated follow-up keeps them returning without any manual effort from you.</Body>
              {d.clientPoints.map((pt, i) => (
                <div key={i} style={{ display:"flex", gap:12, marginBottom:16, alignItems:"flex-start" }}>
                  <div style={{ width:22, height:22, borderRadius:6, background:C.tealLight, border:`1px solid ${C.tealBorder}`, display:"flex", alignItems:"center", justifyContent:"center", flexShrink:0, marginTop:2 }}>
                    <Ic n="check" sz={11} col={C.teal} />
                  </div>
                  <Body sm muted style={{ lineHeight:1.65 }}>{pt}</Body>
                </div>
              ))}
              <div style={{ marginTop:24 }}>
                <Btn v="outline" onClick={() => nav("clients")}>Explore Client Management</Btn>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* ─── 8. BUSINESS INSIGHTS & REPORTS ─── */}
      <section style={{ padding:SP, background:C.teal }}>
        <div style={{ ...px }}>
          <div style={{ textAlign:"center", marginBottom:48 }}>
            <Label center light>Business insights and reports</Label>
            <H2 light center style={{ maxWidth:580, margin:"12px auto 14px" }}>Read your entire business in 60 seconds every morning</H2>
            <Body light center style={{ maxWidth:500, margin:"0 auto" }}>Six analytics dashboards — revenue, appointments, staff performance, client behaviour, retail sales, and your booking website. Updated in real time.</Body>
          </div>
          <div style={{ display:"grid", gridTemplateColumns:md ? "1fr" : "1fr 1fr", gap:32, alignItems:"center", marginBottom:36 }}>
            <div style={{ display:"grid", gridTemplateColumns:"1fr 1fr", gap:12 }}>
              {d.metrics.map((m, i) => (
                <div key={i} style={{ background:"rgba(255,255,255,0.12)", borderRadius:R, padding:"22px 18px", border:"1px solid rgba(255,255,255,0.18)", textAlign:"center" }}>
                  <div style={{ fontFamily:F, fontSize:26, fontWeight:800, color:"#fff" }}>{m.v}</div>
                  <div style={{ fontFamily:F, fontSize:12, color:"rgba(255,255,255,0.55)", marginTop:6 }}>{m.l}</div>
                </div>
              ))}
            </div>
            <AnalyticsMock />
          </div>
          <div style={{ textAlign:"center" }}>
            <Btn v="whiteSolid" onClick={() => nav("analytics")}>Explore Analytics</Btn>
          </div>
        </div>
      </section>

      {/* ─── 9. WEBSITE & ONLINE BOOKING ─── */}
      <section style={{ padding:SP, background:C.bg }}>
        <div style={{ ...px }}>
          <div style={{ display:"grid", gridTemplateColumns:md ? "1fr" : "1fr 1fr", gap:64, alignItems:"center" }}>
            <div>
              <Label>Free booking website — included</Label>
              <H2 style={{ marginBottom:16, marginTop:10 }}>Your {type.toLowerCase()} gets its own booking website — at zero cost</H2>
              <Body muted style={{ fontSize:sm ? 15 : 17, marginBottom:28, lineHeight:1.72 }}>No developer. No hosting fee. No domain purchase. Complete your store setup inside the app and your booking website is ready to publish with one click. Mobile-ready, Google-indexed, accepting bookings 24 hours a day.</Body>
              <div style={{ display:"flex", flexDirection:"column", gap:14, marginBottom:28 }}>
                {[
                  {n:"link",    t:"Free Custom URL",     d:"yourbusiness.easygrox.com — or connect your own domain with one setting."},
                  {n:"shield",  t:"Free Hosting Forever",d:"Hosted on secure servers. Zero cost. Zero renewal fees. Ever."},
                  {n:"refresh", t:"Always Current",      d:"Update a price inside EasyGrox and your website reflects it in seconds."},
                  {n:"bar",     t:"Visitor Analytics",   d:"Traffic sources, booking conversion, and most-viewed services — all tracked automatically."},
                ].map((f, i) => (
                  <div key={i} style={{ display:"flex", gap:12, alignItems:"flex-start" }}>
                    <div style={{ width:36, height:36, borderRadius:R, background:C.tealLight, border:`1px solid ${C.tealBorder}`, display:"flex", alignItems:"center", justifyContent:"center", flexShrink:0 }}>
                      <Ic n={f.n} sz={16} col={C.teal} />
                    </div>
                    <div>
                      <div style={{ fontFamily:F, fontSize:14, fontWeight:700, color:C.ink, marginBottom:2 }}>{f.t}</div>
                      <div style={{ fontFamily:F, fontSize:13, color:C.inkVar }}>{f.d}</div>
                    </div>
                  </div>
                ))}
              </div>
              <Btn v="outline" onClick={() => nav("website")}>Explore the Free Website Feature</Btn>
            </div>
            <WebsiteMock />
          </div>
        </div>
      </section>

      {/* ─── 10. MULTI-LOCATION ─── */}
      <section style={{ padding:SP, background:C.low }}>
        <div style={{ ...px }}>
          <div style={{ display:"grid", gridTemplateColumns:md ? "1fr" : "1fr 1fr", gap:64, alignItems:"center" }}>
            <div>
              <Label>Built to scale</Label>
              <H2 style={{ marginBottom:16, marginTop:10 }}>When you open a second location — or a third — EasyGrox is already ready</H2>
              <Body muted style={{ fontSize:sm ? 15 : 16, marginBottom:28, lineHeight:1.72 }}>The Multi-Location plan gives every branch its own booking website, its own staff and calendar, and its own analytics — all visible from a single owner login. Compare branch performance, share client records, and manage your whole business from one dashboard.</Body>
              <div style={{ display:"grid", gridTemplateColumns:"1fr 1fr", gap:12 }}>
                {[
                  {n:"globe",    t:"Website per branch",      d:"Each location gets its own booking website with its own URL."},
                  {n:"bar",      t:"Consolidated analytics",  d:"Total revenue across all branches — or drill into any one."},
                  {n:"users",    t:"Shared client database",  d:"A client who visits two branches has one unified profile."},
                  {n:"lock",     t:"Branch-level access",     d:"Branch managers see their location. You see everything."},
                ].map((f, i) => (
                  <div key={i} style={{ background:C.surface, borderRadius:R, padding:"16px 14px", border:`1px solid ${C.outlineVar}`, boxShadow:C.sh1 }}>
                    <div style={{ display:"flex", gap:8, alignItems:"center", marginBottom:8 }}>
                      <Ic n={f.n} sz={16} col={C.teal} />
                      <div style={{ fontFamily:F, fontSize:13.5, fontWeight:700, color:C.ink }}>{f.t}</div>
                    </div>
                    <div style={{ fontFamily:F, fontSize:12.5, color:C.inkVar, lineHeight:1.55 }}>{f.d}</div>
                  </div>
                ))}
              </div>
            </div>
            <div>
              <div style={{ background:C.surface, borderRadius:R, border:`1px solid ${C.outlineVar}`, boxShadow:C.sh2, overflow:"hidden" }}>
                <div style={{ background:C.teal, padding:"14px 18px" }}>
                  <div style={{ color:"#fff", fontSize:13, fontWeight:700 }}>Multi-Location Dashboard</div>
                  <div style={{ color:"rgba(255,255,255,0.55)", fontSize:11, marginTop:2 }}>3 branches · consolidated view</div>
                </div>
                <div style={{ padding:16 }}>
                  {[
                    {loc:"Branch 1 — Jaipur",   rev:"₹48,200", appts:124, rating:"4.9"},
                    {loc:"Branch 2 — Jodhpur",  rev:"₹36,800", appts:98,  rating:"4.7"},
                    {loc:"Branch 3 — Udaipur",  rev:"₹29,400", appts:74,  rating:"4.8"},
                  ].map((b, i) => (
                    <div key={i} style={{ padding:"12px 0", borderBottom:i < 2 ? `1px solid ${C.outlineVar}` : "none" }}>
                      <div style={{ display:"flex", justifyContent:"space-between", alignItems:"center", marginBottom:8 }}>
                        <div style={{ fontFamily:F, fontSize:13.5, fontWeight:600, color:C.ink }}>{b.loc}</div>
                        <Chip teal sm>{b.rev}</Chip>
                      </div>
                      <div style={{ display:"flex", gap:16 }}>
                        <div style={{ fontFamily:F, fontSize:12, color:C.inkVar }}>{b.appts} appointments</div>
                        <div style={{ fontFamily:F, fontSize:12, color:C.inkVar }}>★ {b.rating}</div>
                      </div>
                      <div style={{ marginTop:8, background:C.tealLight, borderRadius:9999, height:4, overflow:"hidden" }}>
                        <div style={{ width:`${(parseInt(b.appts)/130)*100}%`, height:"100%", background:C.teal, borderRadius:9999 }} />
                      </div>
                    </div>
                  ))}
                  <div style={{ marginTop:14, padding:"10px 12px", background:C.low, borderRadius:8 }}>
                    <div style={{ display:"flex", justifyContent:"space-between" }}>
                      <span style={{ fontFamily:F, fontSize:12.5, fontWeight:600, color:C.ink }}>Total revenue this month</span>
                      <span style={{ fontFamily:F, fontSize:13, fontWeight:800, color:C.teal }}>₹1,14,400</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* ─── 11. BUSINESS ACTIVATION ─── */}
      <section style={{ padding:SP, background:C.tealLight, borderTop:`1px solid ${C.tealBorder}`, borderBottom:`1px solid ${C.tealBorder}` }}>
        <div style={{ ...px }}>
          <div style={{ display:"grid", gridTemplateColumns:md ? "1fr" : "1fr 1fr", gap:64, alignItems:"center" }}>
            <div>
              <Label>Business activation</Label>
              <H2 style={{ marginBottom:16, marginTop:10 }}>Your {type.toLowerCase()} is live and taking bookings in under 30 minutes</H2>
              <Body muted style={{ fontSize:sm ? 15 : 16, marginBottom:32, lineHeight:1.72 }}>Create your account here. Complete your store setup inside the app — guided step by step with a progress tracker. Publish your free booking website. Start your first day on EasyGrox.</Body>
              {d.activation.map((s, i) => (
                <div key={i} style={{ display:"flex", gap:14, marginBottom:14, alignItems:"flex-start" }}>
                  <div style={{ width:28, height:28, borderRadius:"50%", background:s.done ? C.teal : C.surface, border:`2px solid ${s.done ? C.teal : C.tealBorder}`, display:"flex", alignItems:"center", justifyContent:"center", flexShrink:0, marginTop:2 }}>
                    {s.done ? <Ic n="check" sz={13} col="#fff" /> : <span style={{ fontFamily:F, fontSize:11, fontWeight:800, color:C.teal }}>{i+1}</span>}
                  </div>
                  <div>
                    <div style={{ fontFamily:F, fontSize:14.5, fontWeight:s.done ? 700 : 500, color:s.done ? C.teal : C.ink }}>{s.step}</div>
                    <div style={{ fontFamily:F, fontSize:12, color:C.inkVar, marginTop:2 }}>{s.where}</div>
                  </div>
                </div>
              ))}
              <div style={{ marginTop:28 }}>
                <Btn onClick={() => nav("signup")}>Create Free Account — Start Step 1</Btn>
              </div>
            </div>
            <div>
              <div style={{ background:C.surface, borderRadius:R, padding:"28px 24px", border:`1px solid ${C.tealBorder}`, boxShadow:C.sh2 }}>
                <div style={{ display:"flex", justifyContent:"space-between", alignItems:"center", marginBottom:18 }}>
                  <H4>Store Setup Progress</H4>
                  <Chip teal>1 of {d.activation.length} done</Chip>
                </div>
                <div style={{ background:C.tealLight, borderRadius:9999, height:8, marginBottom:22, overflow:"hidden" }}>
                  <div style={{ width:`${(1/d.activation.length)*100}%`, height:"100%", background:C.teal, borderRadius:9999, transition:"width .4s" }} />
                </div>
                {d.activation.map((s, i) => (
                  <div key={i} style={{ display:"flex", justifyContent:"space-between", alignItems:"center", padding:"10px 0", borderBottom:i < d.activation.length-1 ? `1px solid ${C.outlineVar}` : "none" }}>
                    <div style={{ display:"flex", gap:10, alignItems:"center" }}>
                      <div style={{ width:20, height:20, borderRadius:"50%", background:s.done ? C.teal : C.surface, border:`1.5px solid ${s.done ? C.teal : C.outlineVar}`, display:"flex", alignItems:"center", justifyContent:"center", flexShrink:0 }}>
                        {s.done && <Ic n="check" sz={11} col="#fff" />}
                      </div>
                      <span style={{ fontFamily:F, fontSize:13.5, color:s.done ? C.teal : C.inkVar, fontWeight:s.done ? 600 : 400 }}>{s.step}</span>
                    </div>
                    {s.done ? <Chip teal sm>Done</Chip> : <span style={{ fontFamily:F, fontSize:12, color:C.inkSoft }}>{s.where}</span>}
                  </div>
                ))}
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* ─── 12. TRUST & TESTIMONIAL ─── */}
      <section style={{ padding:SP, background:C.bg }}>
        <div style={{ ...px }}>
          <div style={{ textAlign:"center", marginBottom:44 }}>
            <Label center>From a real {type.toLowerCase()} owner</Label>
            <H2 center>What changes after 30 days on EasyGrox</H2>
          </div>
          <div style={{ maxWidth:720, margin:"0 auto" }}>
            <div style={{ background:C.surface, borderRadius:R, padding:sm ? "28px 22px" : "40px 44px", border:`1px solid ${C.outlineVar}`, boxShadow:C.sh2, borderTop:`4px solid ${C.teal}` }}>
              <div style={{ display:"flex", gap:1, marginBottom:20 }}>
                {[1,2,3,4,5].map(s => <Ic key={s} n="star" sz={16} col={C.teal} />)}
              </div>
              <div style={{ background:C.tealLight, border:`1px solid ${C.tealBorder}`, borderRadius:R, padding:"10px 16px", marginBottom:24, display:"inline-block" }}>
                <span style={{ fontFamily:F, fontSize:14, fontWeight:700, color:C.teal }}>{d.testimonial.metric}</span>
              </div>
              <p style={{ fontFamily:F, fontSize:sm ? 16 : 18, lineHeight:1.8, fontStyle:"italic", color:C.inkVar, marginBottom:28 }}>"{d.testimonial.q}"</p>
              <Divider style={{ marginBottom:22 }} />
              <div style={{ display:"flex", alignItems:"center", gap:14 }}>
                <div style={{ width:44, height:44, borderRadius:"50%", background:C.teal, display:"flex", alignItems:"center", justifyContent:"center", fontFamily:F, fontSize:16, fontWeight:800, color:"#fff", flexShrink:0 }}>
                  {d.testimonial.n.split(" ").map(n => n[0]).join("")}
                </div>
                <div>
                  <div style={{ fontFamily:F, fontSize:15, fontWeight:700, color:C.ink }}>{d.testimonial.n}</div>
                  <div style={{ fontFamily:F, fontSize:13.5, color:C.inkVar, marginTop:2 }}>{d.testimonial.r}</div>
                </div>
              </div>
            </div>
            <div style={{ display:"grid", gridTemplateColumns:"1fr 1fr 1fr", gap:14, marginTop:16 }}>
              {[
                {v:"10,000+",l:"Beauty businesses on EasyGrox"},
                {v:"4.9/5",  l:"Average platform rating"},
                {v:"30 min", l:"Average setup time"},
              ].map((s, i) => (
                <div key={i} style={{ background:C.surface, borderRadius:R, padding:"18px 14px", border:`1px solid ${C.outlineVar}`, textAlign:"center", boxShadow:C.sh1 }}>
                  <div style={{ fontFamily:F, fontSize:22, fontWeight:800, color:C.teal }}>{s.v}</div>
                  <div style={{ fontFamily:F, fontSize:12, color:C.inkVar, marginTop:4 }}>{s.l}</div>
                </div>
              ))}
            </div>
          </div>
        </div>
      </section>

      {/* ─── INDUSTRY FAQ ─── */}
      <section style={{ padding:SP, background:C.low }}>
        <div style={{ ...px }}>
          <div style={{ display:"grid", gridTemplateColumns:md ? "1fr" : "1fr 1.2fr", gap:md ? 36 : 72, alignItems:"start" }}>
            <div>
              <Label>Questions from {type.toLowerCase()} owners</Label>
              <H2 style={{ marginTop:10, marginBottom:16 }}>Things people ask before signing up</H2>
              <Body muted style={{ marginBottom:24 }}>Our team responds within 24 hours on business days. No bots, no automated queues.</Body>
              <Btn v="outline" onClick={() => nav("contact")}>Ask a Question</Btn>
            </div>
            <div>
              {d.faqs.map((f, i) => (
                <div key={i} style={{ borderBottom:`1px solid ${C.outlineVar}` }}>
                  <button onClick={() => setOpenFaq(openFaq === i ? null : i)} style={{ width:"100%", padding:"17px 0", background:"none", border:"none", cursor:"pointer", display:"flex", justifyContent:"space-between", alignItems:"center", gap:14, fontFamily:F, textAlign:"left" }}>
                    <span style={{ fontSize:15, fontWeight:600, color:C.ink }}>{f.q}</span>
                    <Ic n={openFaq === i ? "minus" : "plus"} sz={14} col={C.teal} />
                  </button>
                  {openFaq === i && <div style={{ paddingBottom:18, fontFamily:F, fontSize:14.5, color:C.inkVar, lineHeight:1.78 }}>{f.a}</div>}
                </div>
              ))}
            </div>
          </div>
        </div>
      </section>

      {/* ─── 13. FINAL CTA ─── */}
      <section style={{ padding:sm ? "64px 0" : "96px 0", background:C.teal }}>
        <div style={{ ...px, textAlign:"center" }}>
          <Label center light>Start your free account today</Label>
          <H2 light center style={{ maxWidth:600, margin:"12px auto 18px" }}>Your {type.toLowerCase()} deserves a platform that understands how it actually operates.</H2>
          <Body light center style={{ maxWidth:480, margin:"0 auto 36px" }}>Create your free account. Set up your store inside the app. Publish your free booking website. Start managing smarter — in under 30 minutes.</Body>
          <div style={{ display:"flex", gap:14, justifyContent:"center", flexWrap:"wrap" }}>
            <Btn onClick={() => nav("signup")}>Create Free Account</Btn>
            <Btn v="white" onClick={() => nav("productTour")}>Watch Product Tour</Btn>
          </div>
          <div style={{ display:"flex", gap:16, justifyContent:"center", flexWrap:"wrap", marginTop:22 }}>
            {["No credit card required","Free booking website on every plan","Setup in 30 minutes","Cancel anytime"].map((t, i) => (
              <span key={i} style={{ fontFamily:F, fontSize:12.5, color:"rgba(255,255,255,0.55)", display:"flex", alignItems:"center", gap:5 }}>
                <Ic n="check" sz={11} col="rgba(255,255,255,0.55)" />{t}
              </span>
            ))}
          </div>
        </div>
      </section>
    </>
  );
}



/* ─── HOW IT WORKS ───────────────────────────────────────── */
/* ─── HOW IT WORKS PAGE ──────────────────────────────────── */
function HowItWorks({ nav }) {
  const { sm, md } = useW();
  const px = { padding:`0 ${sm ? 16 : 24}px`, maxWidth:1200, margin:"0 auto" };
  const SP = sm ? "56px 0" : "88px 0";
  const [activePhase, setActivePhase] = useState(0);

  const phases = [
    {
      n:"01", icon:"lock", label:"This website", tag:"30 seconds",
      t:"Create your account", color:C.teal,
      b:"Just your name, email, and password right here. No credit card. No store details asked upfront. One click and you are inside the EasyGrox app, ready to set up your business.",
      steps:[
        {icon:"check", t:"Enter name and email", d:"No credit card required. No billing details at this stage."},
        {icon:"lock",  t:"Set your password",    d:"Minimum 6 characters. Your account is encrypted and secure from the start."},
        {icon:"zap",   t:"Redirected instantly",  d:"The moment you click Create Account, you land inside the EasyGrox app."},
      ],
      milestone:"Account created in 30 seconds",
    },
    {
      n:"02", icon:"settings", label:"Inside the app", tag:"15–20 minutes",
      t:"Set up your business", color:"#7c3aed",
      b:"The app greets you with a guided setup checklist and a progress tracker. Every step is short, clear, and immediately actionable. Most owners complete full setup in under 20 minutes.",
      steps:[
        {icon:"briefcase",t:"Add your business details",  d:"Business name, address, contact, logo, and category. Takes 2 minutes."},
        {icon:"scissors", t:"Add your services",          d:"Every service with name, price, and duration. These auto-populate your booking website."},
        {icon:"users",    t:"Add your team",              d:"Create a profile for each staff member. Set their services and access level."},
        {icon:"clock",    t:"Set your hours",             d:"Opening hours, booking lead time, and buffer time between appointments."},
      ],
      milestone:"Business ready to accept bookings",
    },
    {
      n:"03", icon:"globe", label:"One click", tag:"Under 1 minute",
      t:"Publish your free booking website", color:C.teal,
      b:"Your booking website is built automatically from your store data. Preview it inside the app, choose your custom URL, and click Publish. Your site is live — mobile-ready, Google-indexed, accepting bookings — instantly.",
      steps:[
        {icon:"eye",      t:"Preview your website",       d:"See exactly what your clients will see. Services, staff, prices — all ready."},
        {icon:"link",     t:"Choose your URL",            d:"yoursalon.easygrox.com — or connect your own domain with one setting."},
        {icon:"globe",    t:"Click Publish",              d:"Live in seconds. Hosted free. Accepting bookings 24/7 immediately."},
        {icon:"mail",     t:"Share your booking link",    d:"Add to Instagram bio, WhatsApp status, and Google Business Profile."},
      ],
      milestone:"First online booking received",
    },
    {
      n:"04", icon:"bar", label:"Every day", tag:"Ongoing",
      t:"Manage your business from one dashboard", color:"#7c3aed",
      b:"Your EasyGrox dashboard becomes your morning ritual. Check revenue, review appointments, see staff performance, read your website analytics, and run campaigns — all from the same screen you opened yesterday.",
      steps:[
        {icon:"calendar", t:"Calendar — all bookings",    d:"Online, walk-in, and manual appointments — all staff, one view."},
        {icon:"pos",      t:"POS — bill in 60 seconds",   d:"Service pre-filled, payment collected, receipt sent. Done."},
        {icon:"bar",      t:"Analytics — read in 60s",    d:"Revenue, staff, clients, website — six dashboards, one app."},
        {icon:"tag",      t:"Marketing — send in 3 mins", d:"SMS campaigns, birthday offers, re-engagement — from your client data."},
      ],
      milestone:"Business running on autopilot",
    },
  ];

  const milestones = [
    {icon:"lock",     t:"Account created",        d:"30 seconds",      done:true},
    {icon:"settings", t:"Business setup complete", d:"20 minutes",      done:false},
    {icon:"globe",    t:"Booking website live",    d:"1 click",         done:false},
    {icon:"calendar", t:"First booking received",  d:"Day 1",           done:false},
    {icon:"pos",      t:"First invoice sent",      d:"After first visit",done:false},
    {icon:"bar",      t:"Analytics dashboard live",d:"Automatic",       done:false},
  ];

  const faqs = [
    {q:"Do I need technical skills to set up EasyGrox?",        a:"None at all. The setup flow is a guided checklist — each step has a short explanation and takes 2–5 minutes. If you can fill out a form, you can set up EasyGrox. Most owners complete full setup without reading any documentation."},
    {q:"How long does setup actually take?",                  a:"Account creation takes 30 seconds on this website. Store setup inside the app — services, staff, hours, and booking preferences — takes 15–20 minutes for most businesses. Publishing your booking website takes under a minute. Total: under 30 minutes from zero to live."},
    {q:"Is the booking website really free — forever?",       a:"Yes. Every EasyGrox plan includes a fully hosted, mobile-ready booking website at zero extra cost. No domain purchase, no hosting fee, no renewals. It is live the moment you click Publish and stays free as long as you are a EasyGrox customer."},
    {q:"What happens after I publish my website?",            a:"Clients can immediately find your website, browse your services and staff, and book appointments online. Every booking appears instantly in your EasyGrox calendar. Confirmation SMS is sent to the client automatically. You do nothing manually."},
    {q:"Can I import my existing client list?",               a:"Yes. You can import client data from most salon software via CSV during your setup. Our support team assists with data migration from Phorest, Fresha, Vagaro, and most other platforms at no extra charge."},
  ];

  return (
    <>
      {/* ── HERO ── */}
      <section style={{ padding:sm ? "56px 0 44px" : "88px 0 72px", background:C.bg }}>
        <div style={{ ...px }}>
          <div style={{ display:"grid", gridTemplateColumns:md ? "1fr" : "1fr 1fr", gap:md ? 36 : 72, alignItems:"center" }}>
            <div>
              <Chip teal style={{ marginBottom:18 }}>No technical skill needed</Chip>
              <H1 style={{ marginBottom:20, marginTop:14 }}>From signup to a live booking website in under 30 minutes</H1>
              <Body muted style={{ fontSize:sm ? 15.5 : 17.5, marginBottom:32, lineHeight:1.72 }}>Four steps. Guided the entire way. Your business is live, your calendar is open, and your clients can book online — before lunch.</Body>
              <div style={{ display:"flex", gap:12, flexWrap:"wrap", marginBottom:28 }}>
                <Btn onClick={() => nav("signup")}>Create Free Account</Btn>
                <Btn v="outline" onClick={() => nav("gettingStarted")}>View Setup Guide</Btn>
              </div>
              <div style={{ display:"flex", flexWrap:"wrap", gap:8 }}>
                {["30 sec signup","Free website","No credit card","Guided setup"].map((t, i) => (
                  <span key={i} style={{ display:"flex", alignItems:"center", gap:5, fontFamily:F, fontSize:12.5, color:C.inkVar, background:C.surface, border:`1px solid ${C.outlineVar}`, borderRadius:9999, padding:"4px 12px" }}>
                    <Ic n="check" sz={11} col={C.teal} />{t}
                  </span>
                ))}
              </div>
            </div>
            {/* Progress card */}
            <div style={{ background:C.surface, borderRadius:R, border:`1px solid ${C.outlineVar}`, boxShadow:C.sh2, overflow:"hidden" }}>
              <div style={{ background:C.teal, padding:"16px 22px" }}>
                <div style={{ color:"#fff", fontSize:13, fontWeight:700 }}>Your launch journey</div>
                <div style={{ color:"rgba(255,255,255,0.55)", fontSize:11, marginTop:2 }}>From zero to live booking business</div>
              </div>
              <div style={{ padding:"18px 22px" }}>
                <div style={{ display:"flex", justifyContent:"space-between", alignItems:"center", marginBottom:8 }}>
                  <span style={{ fontFamily:F, fontSize:13, fontWeight:600, color:C.ink }}>Setup Progress</span>
                  <span style={{ fontFamily:F, fontSize:13, fontWeight:700, color:C.teal }}>Step 1 of 4</span>
                </div>
                <div style={{ background:C.low, borderRadius:9999, height:6, marginBottom:20, overflow:"hidden" }}>
                  <div style={{ width:"25%", height:"100%", background:C.teal, borderRadius:9999 }} />
                </div>
                {milestones.map((m, i) => (
                  <div key={i} style={{ display:"flex", gap:12, alignItems:"center", padding:"10px 0", borderBottom:i < milestones.length-1 ? `1px solid ${C.outlineVar}` : "none" }}>
                    <div style={{ width:32, height:32, borderRadius:"50%", background:m.done ? C.teal : C.surface, border:`2px solid ${m.done ? C.teal : C.outlineVar}`, display:"flex", alignItems:"center", justifyContent:"center", flexShrink:0 }}>
                      {m.done ? <Ic n="check" sz={14} col="#fff" /> : <Ic n={m.icon} sz={13} col={C.inkSoft} />}
                    </div>
                    <div style={{ flex:1 }}>
                      <div style={{ fontFamily:F, fontSize:13.5, fontWeight:m.done ? 700 : 400, color:m.done ? C.teal : C.ink }}>{m.t}</div>
                      <div style={{ fontFamily:F, fontSize:11.5, color:C.inkVar }}>{m.d}</div>
                    </div>
                    {m.done ? <Chip teal sm>Done</Chip> : <span style={{ fontFamily:F, fontSize:11, color:C.inkSoft }}>Pending</span>}
                  </div>
                ))}
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* ── PHASE TABS ── */}
      <Divider />
      <section style={{ padding:SP, background:C.low }}>
        <div style={{ ...px }}>
          <div style={{ textAlign:"center", marginBottom:44 }}>
            <Label center>Four phases. Fully guided.</Label>
            <H2 center>Every step explained — before you take it</H2>
          </div>
          {/* Phase selector */}
          <div style={{ display:"grid", gridTemplateColumns:sm ? "1fr 1fr" : "1fr 1fr 1fr 1fr", gap:10, marginBottom:36 }}>
            {phases.map((ph, i) => (
              <button key={i} onClick={() => setActivePhase(i)}
                style={{ padding:sm ? "14px 12px" : "18px 16px", borderRadius:R, border:`2px solid ${activePhase===i ? C.teal : C.outlineVar}`, background:activePhase===i ? C.tealLight : C.surface, cursor:"pointer", textAlign:"left", transition:"all .18s", boxShadow:activePhase===i ? `0 4px 16px rgba(0,101,101,0.18)` : C.sh1 }}>
                <div style={{ display:"flex", alignItems:"center", gap:8, marginBottom:8 }}>
                  <div style={{ width:32, height:32, borderRadius:8, background:activePhase===i ? C.teal : C.low, display:"flex", alignItems:"center", justifyContent:"center", flexShrink:0 }}>
                    <Ic n={ph.icon} sz={15} col={activePhase===i ? "#fff" : C.inkSoft} />
                  </div>
                  <span style={{ fontFamily:F, fontSize:10, fontWeight:700, color:activePhase===i ? C.teal : C.inkSoft, letterSpacing:"0.06em", textTransform:"uppercase" }}>Phase {ph.n}</span>
                </div>
                <div style={{ fontFamily:F, fontSize:sm ? 13 : 14, fontWeight:700, color:activePhase===i ? C.teal : C.ink, marginBottom:3 }}>{ph.t}</div>
                <Chip teal={activePhase===i} sm style={{ marginTop:4 }}>{ph.tag}</Chip>
              </button>
            ))}
          </div>
          {/* Active phase detail */}
          <Card style={{ padding:sm ? "24px 20px" : "36px 40px", borderLeft:`4px solid ${C.teal}`, borderRadius:`0 ${R} ${R} 0` }}>
            <div style={{ display:"grid", gridTemplateColumns:md ? "1fr" : "1fr 1fr", gap:md ? 28 : 56, alignItems:"start" }}>
              <div>
                <div style={{ display:"flex", gap:10, alignItems:"center", marginBottom:16, flexWrap:"wrap" }}>
                  <span style={{ background:C.teal, color:"#fff", fontFamily:F, fontSize:10, fontWeight:700, padding:"3px 12px", borderRadius:9999, textTransform:"uppercase", letterSpacing:"0.06em" }}>Phase {phases[activePhase].n}</span>
                  <Chip teal sm>{phases[activePhase].tag}</Chip>
                  <span style={{ fontFamily:F, fontSize:12, color:C.inkVar }}>{phases[activePhase].label}</span>
                </div>
                <H2 style={{ marginBottom:14 }}>{phases[activePhase].t}</H2>
                <Body muted style={{ marginBottom:28, fontSize:15.5, lineHeight:1.75 }}>{phases[activePhase].b}</Body>
                <div style={{ display:"grid", gridTemplateColumns:"1fr", gap:12 }}>
                  {phases[activePhase].steps.map((s, si) => (
                    <div key={si} style={{ display:"flex", gap:12, alignItems:"flex-start", background:C.low, borderRadius:R, padding:"14px 16px" }}>
                      <div style={{ width:32, height:32, borderRadius:8, background:C.tealLight, border:`1px solid ${C.tealBorder}`, display:"flex", alignItems:"center", justifyContent:"center", flexShrink:0 }}>
                        <Ic n={s.icon} sz={15} col={C.teal} />
                      </div>
                      <div>
                        <div style={{ fontFamily:F, fontSize:14, fontWeight:700, color:C.ink, marginBottom:3 }}>{s.t}</div>
                        <div style={{ fontFamily:F, fontSize:13, color:C.inkVar, lineHeight:1.55 }}>{s.d}</div>
                      </div>
                    </div>
                  ))}
                </div>
              </div>
              <div>
                <div style={{ background:C.tealLight, border:`2px solid ${C.tealBorder}`, borderRadius:R, padding:"24px 22px", textAlign:"center" }}>
                  <div style={{ width:56, height:56, borderRadius:"50%", background:C.teal, display:"flex", alignItems:"center", justifyContent:"center", margin:"0 auto 16px" }}>
                    <Ic n={phases[activePhase].icon} sz={24} col="#fff" />
                  </div>
                  <div style={{ fontFamily:F, fontSize:11, fontWeight:700, color:C.teal, letterSpacing:"0.08em", textTransform:"uppercase", marginBottom:8 }}>Milestone</div>
                  <div style={{ fontFamily:F, fontSize:18, fontWeight:800, color:C.teal, marginBottom:12, lineHeight:1.3 }}>{phases[activePhase].milestone}</div>
                  <Divider style={{ marginBottom:16 }} />
                  <Body sm muted style={{ fontSize:13 }}>When you complete Phase {phases[activePhase].n}, this milestone is unlocked and your business moves to the next stage of readiness.</Body>
                </div>
                <div style={{ marginTop:16, display:"flex", gap:10, flexDirection:"column" }}>
                  {activePhase !== 3 && (
                    <button onClick={() => setActivePhase(activePhase + 1)} style={{ background:C.teal, border:"none", borderRadius:R, padding:"12px 20px", color:"#fff", fontFamily:F, fontSize:13.5, fontWeight:600, cursor:"pointer", display:"flex", alignItems:"center", justifyContent:"space-between", gap:8 }}>
                      Next: Phase 0{activePhase + 2} — {phases[activePhase + 1].t}
                      <Ic n="chevR" sz={14} col="#fff" />
                    </button>
                  )}
                  {activePhase === 3 && (
                    <Btn full onClick={() => nav("signup")}>Create Free Account — Start Phase 01</Btn>
                  )}
                </div>
              </div>
            </div>
          </Card>
        </div>
      </section>

      {/* ── WHAT YOU CAN DO ── */}
      <section style={{ padding:SP, background:C.bg }}>
        <div style={{ ...px }}>
          <div style={{ textAlign:"center", marginBottom:44 }}>
            <Label center>After setup is complete</Label>
            <H2 center>Everything you can do from Day 1</H2>
            <Body muted center style={{ maxWidth:520, margin:"14px auto 0" }}>Once your store is live, you have the full EasyGrox platform — every feature, connected, ready to use.</Body>
          </div>
          <div style={{ display:"grid", gridTemplateColumns:sm ? "1fr" : md ? "1fr 1fr" : "repeat(3,1fr)", gap:14 }}>
            {[
              {n:"calendar",t:"Accept online bookings",        d:"Clients book through your free website — slots fill overnight while you sleep."},
              {n:"zap",     t:"Manage walk-ins in 10 seconds", d:"Add a walk-in, assign a staff member, and it is in the calendar instantly."},
              {n:"pos",     t:"Bill any client in 60 seconds", d:"Services and retail in one transaction. All payment types. WhatsApp receipt."},
              {n:"bell",    t:"Reminders go out automatically",d:"Confirmation and 24h reminder to every client — zero manual effort."},
              {n:"user",    t:"Build client profiles",         d:"Every visit, service, and product tracked per client. Automatically."},
              {n:"bar",     t:"Read your analytics daily",     d:"Revenue, staff, clients, and website — six dashboards, one morning read."},
              {n:"tag",     t:"Send marketing campaigns",       d:"SMS to lapsed clients, birthday offers, flash promos — all from your client data."},
              {n:"users",   t:"Manage your team",              d:"Shifts, leave, performance, and personal dashboards for every staff member."},
              {n:"globe",   t:"Your website stays current",    d:"Change a price inside EasyGrox — your booking website updates in seconds."},
            ].map((f, i) => (
              <Card key={i} style={{ padding:"20px 18px" }}>
                <IcBox n={f.n} />
                <H4 style={{ marginBottom:7 }}>{f.t}</H4>
                <Body sm muted style={{ lineHeight:1.6 }}>{f.d}</Body>
              </Card>
            ))}
          </div>
        </div>
      </section>

      {/* ── FAQ ── */}
      <section style={{ padding:SP, background:C.low }}>
        <div style={{ ...px, maxWidth:760, margin:"0 auto" }}>
          <H2 center style={{ marginBottom:36 }}>Questions about setup</H2>
          {faqs.map((f, i) => (
            <div key={i} style={{ borderBottom:`1px solid ${C.outlineVar}` }}>
              <button onClick={() => setActivePhase(activePhase === i + 10 ? -1 : i + 10)} style={{ width:"100%", padding:"17px 0", background:"none", border:"none", cursor:"pointer", display:"flex", justifyContent:"space-between", alignItems:"center", gap:14, fontFamily:F, textAlign:"left" }}>
                <span style={{ fontSize:15, fontWeight:600, color:C.ink }}>{f.q}</span>
                <Ic n={activePhase === i + 10 ? "minus" : "plus"} sz={14} col={C.teal} />
              </button>
              {activePhase === i + 10 && <div style={{ paddingBottom:18, fontFamily:F, fontSize:14.5, color:C.inkVar, lineHeight:1.78 }}>{f.a}</div>}
            </div>
          ))}
        </div>
      </section>

      {/* ── CTA ── */}
      <section style={{ padding:sm ? "56px 0" : "88px 0", background:C.teal }}>
        <div style={{ ...px, textAlign:"center" }}>
          <Label center light>Start Phase 01 right now</Label>
          <H2 light center style={{ maxWidth:540, margin:"12px auto 18px" }}>30 seconds to create your account. 30 minutes to go live.</H2>
          <Body light center style={{ maxWidth:440, margin:"0 auto 32px" }}>No credit card. No technical skill needed. Guided the entire way.</Body>
          <div style={{ display:"flex", gap:12, justifyContent:"center", flexWrap:"wrap" }}>
            <Btn onClick={() => nav("signup")}>Create Free Account</Btn>
            <Btn v="white" onClick={() => nav("gettingStarted")}>View Setup Guide First</Btn>
          </div>
        </div>
      </section>
    </>
  );
}

/* ─── HELP CENTRE PAGE ───────────────────────────────────── */
function HelpCentre({ nav }) {
  const { sm, md } = useW();
  const px = { padding:`0 ${sm ? 16 : 24}px`, maxWidth:1200, margin:"0 auto" };
  const SP = sm ? "52px 0" : "80px 0";
  const [activeCategory, setActiveCategory] = useState(0);
  const [search, setSearch] = useState("");

  const categories = [
    {
      icon:"zap",     label:"Getting Started",
      articles:[
        {t:"How to create your EasyGrox account",          d:"Step-by-step walkthrough from the signup page to your first login.",        time:"2 min read"},
        {t:"Setting up your store for the first time",   d:"Adding business details, logo, address, and choosing your business category.", time:"4 min read"},
        {t:"Adding your services and pricing",           d:"Create your full service menu with prices, durations, and categories.",       time:"3 min read"},
        {t:"Creating your first staff profile",          d:"Add a team member, set their services, and configure their access level.",   time:"3 min read"},
        {t:"Publishing your free booking website",       d:"Preview your website, set your custom URL, and go live in under 1 minute.", time:"2 min read"},
      ],
    },
    {
      icon:"calendar",label:"Appointments & Calendar",
      articles:[
        {t:"How online bookings appear in your calendar", d:"What happens the moment a client books through your website.",              time:"2 min read"},
        {t:"Adding a walk-in appointment",                d:"How to log a walk-in in 10 seconds and assign a staff member.",            time:"1 min read"},
        {t:"Rescheduling or cancelling a booking",        d:"Move or cancel any appointment and automatically notify the client.",      time:"2 min read"},
        {t:"Setting buffer time between appointments",    d:"Configure automatic gaps between sessions per service or per staff.",      time:"2 min read"},
        {t:"Understanding appointment status labels",     d:"Confirmed, Active, Completed, No-Show — what each status means.",         time:"2 min read"},
      ],
    },
    {
      icon:"pos",     label:"POS & Billing",
      articles:[
        {t:"How to bill a client after a service",       d:"Open the billing screen, confirm services, select payment, collect.",      time:"2 min read"},
        {t:"Accepting UPI, card, and cash payments",     d:"All payment types available in the POS and how each is tracked.",          time:"2 min read"},
        {t:"Adding retail products to a service bill",   d:"Sell a shampoo alongside a service in one transaction.",                   time:"2 min read"},
        {t:"Creating and applying discount codes",        d:"Set up percentage or fixed discounts and apply them at checkout.",         time:"3 min read"},
        {t:"Sending a digital receipt to a client",      d:"Send via WhatsApp, SMS, or email — instantly after payment.",              time:"1 min read"},
      ],
    },
    {
      icon:"users",   label:"Staff Management",
      articles:[
        {t:"Adding a new staff member",                  d:"Create a profile, assign services, and set their access level.",           time:"3 min read"},
        {t:"Understanding role-based access levels",     d:"Owner, Front Desk, and Staff — what each role can see and do.",            time:"3 min read"},
        {t:"Setting up shift schedules",                 d:"Configure weekly working hours per staff member.",                         time:"3 min read"},
        {t:"Approving a leave request",                  d:"Review and approve leave — calendar blocks automatically.",                time:"2 min read"},
        {t:"Viewing staff performance reports",          d:"Revenue, appointments, utilisation, and rating per team member.",          time:"2 min read"},
      ],
    },
    {
      icon:"bag",     label:"Retail & Inventory",
      articles:[
        {t:"Adding products to your inventory",          d:"Create product entries with price, cost, category, and stock level.",      time:"3 min read"},
        {t:"Setting minimum stock levels and alerts",    d:"Configure when low-stock alerts fire for each product.",                   time:"2 min read"},
        {t:"Tracking retail sales by staff member",      d:"See which team member sells the most retail each month.",                 time:"2 min read"},
        {t:"Adjusting stock manually",                   d:"Log stock arrivals, breakage, or returns with a reason and timestamp.",   time:"2 min read"},
        {t:"Reading your retail analytics",              d:"Best-sellers, margin, and retail revenue as % of total business.",        time:"2 min read"},
      ],
    },
    {
      icon:"globe",   label:"Booking Website",
      articles:[
        {t:"Understanding your free booking website",   d:"What is included, how it works, and what clients experience.",             time:"3 min read"},
        {t:"Customising your website URL",              d:"Set yoursalon.easygrox.com or connect your own domain.",                     time:"2 min read"},
        {t:"Adding photos and business description",    d:"Upload images and write your business profile from inside the app.",       time:"3 min read"},
        {t:"Reading your website analytics",            d:"Visitors, traffic sources, most-viewed pages, and booking conversion.",   time:"3 min read"},
        {t:"Sharing your booking link effectively",     d:"Instagram bio, WhatsApp status, Google Business, and QR code.",           time:"2 min read"},
      ],
    },
    {
      icon:"bar",     label:"Reports & Analytics",
      articles:[
        {t:"Your morning analytics routine",            d:"What to check every morning and what each number means.",                  time:"4 min read"},
        {t:"Reading revenue reports",                   d:"Daily, weekly, monthly revenue — by service, staff, and category.",       time:"3 min read"},
        {t:"Understanding your no-show rate",           d:"How it is calculated, what a healthy rate is, and how to reduce it.",     time:"3 min read"},
        {t:"Client retention and at-risk clients",      d:"How EasyGrox flags clients who are overdue and how to re-engage them.",     time:"3 min read"},
        {t:"Staff utilisation rate explained",          d:"What utilisation rate means and how to use it for scheduling decisions.", time:"3 min read"},
      ],
    },
    {
      icon:"tag",     label:"Marketing",
      articles:[
        {t:"Sending your first SMS campaign",           d:"Choose a segment, write a message, send — and see the results.",          time:"4 min read"},
        {t:"Setting up birthday automation",            d:"Configure once — personalised birthday offers sent automatically forever.", time:"3 min read"},
        {t:"Re-engagement campaigns for lapsed clients",d:"How to automatically reach clients who have not visited in 30+ days.",    time:"3 min read"},
        {t:"Reading campaign analytics",                d:"Delivery rate, bookings generated, and revenue attributed per campaign.", time:"2 min read"},
        {t:"Targeting a specific client segment",       d:"Filter by visit frequency, spend level, last service, and more.",        time:"3 min read"},
      ],
    },
  ];

  const filtered = search.trim().length > 0
    ? categories.map(c => ({ ...c, articles:c.articles.filter(a => a.t.toLowerCase().includes(search.toLowerCase()) || a.d.toLowerCase().includes(search.toLowerCase())) })).filter(c => c.articles.length > 0)
    : categories;

  return (
    <>
      {/* ── HERO ── */}
      <section style={{ padding:sm ? "52px 0 36px" : "80px 0 56px", background:C.teal }}>
        <div style={{ ...px, textAlign:"center" }}>
          <Label center light>Help Centre</Label>
          <H1 style={{ color:"#fff", marginBottom:16, marginTop:10 }}>How can we help you today?</H1>
          <Body light center style={{ fontSize:sm ? 15 : 17, marginBottom:32, maxWidth:480, margin:"0 auto 32px" }}>Search articles, browse by category, or contact our team. We respond within 24 hours — no bots.</Body>
          {/* Search */}
          <div style={{ maxWidth:560, margin:"0 auto", position:"relative" }}>
            <div style={{ position:"absolute", left:16, top:"50%", transform:"translateY(-50%)", pointerEvents:"none" }}>
              <Ic n="search" sz={18} col={C.inkSoft} />
            </div>
            <input value={search} onChange={e => setSearch(e.target.value)} placeholder="Search help articles — e.g. walk-in, billing, staff schedule..."
              style={{ width:"100%", height:52, borderRadius:R, border:`2px solid ${C.tealBorder}`, padding:"0 16px 0 46px", fontSize:15, fontFamily:F, outline:"none", boxSizing:"border-box", boxShadow:"0 4px 20px rgba(0,0,0,0.15)" }}
              onFocus={e => e.target.style.border = `2px solid ${C.teal}`}
              onBlur={e => e.target.style.border = `2px solid ${C.tealBorder}`} />
          </div>
          <div style={{ display:"flex", gap:8, justifyContent:"center", flexWrap:"wrap", marginTop:16 }}>
            {["Walk-in","No-shows","Billing","Staff schedule","Website","Reminders"].map((tag, i) => (
              <button key={i} onClick={() => setSearch(tag)} style={{ background:"rgba(255,255,255,0.15)", border:"1px solid rgba(255,255,255,0.3)", borderRadius:9999, padding:"5px 14px", fontSize:12.5, color:"rgba(255,255,255,0.85)", fontFamily:F, cursor:"pointer", fontWeight:500, transition:"all .15s" }}
                onMouseEnter={e => e.currentTarget.style.background = "rgba(255,255,255,0.25)"}
                onMouseLeave={e => e.currentTarget.style.background = "rgba(255,255,255,0.15)"}>
                {tag}
              </button>
            ))}
          </div>
        </div>
      </section>

      {/* ── CATEGORIES + ARTICLES ── */}
      <section style={{ padding:SP, background:C.bg }}>
        <div style={{ ...px }}>
          {search.trim().length === 0 && (
            <>
              {/* Category tabs */}
              <div style={{ display:"flex", gap:8, flexWrap:"wrap", marginBottom:36, justifyContent:sm ? "flex-start" : "center" }}>
                {categories.map((c, i) => (
                  <button key={i} onClick={() => setActiveCategory(i)}
                    style={{ display:"flex", alignItems:"center", gap:6, padding:"8px 16px", borderRadius:9999, border:`1.5px solid ${activeCategory===i ? C.teal : C.outlineVar}`, background:activeCategory===i ? C.teal : C.surface, color:activeCategory===i ? "#fff" : C.inkVar, fontWeight:600, fontSize:sm ? 12.5 : 13, cursor:"pointer", fontFamily:F, transition:"all .18s", whiteSpace:"nowrap" }}>
                    <Ic n={c.icon} sz={14} col={activeCategory===i ? "#fff" : C.inkVar} />
                    {c.label}
                  </button>
                ))}
              </div>
              {/* Active category articles */}
              <div style={{ display:"grid", gridTemplateColumns:sm ? "1fr" : "repeat(2,1fr)", gap:14 }}>
                {categories[activeCategory].articles.map((a, i) => (
                  <div key={i} style={{ background:C.surface, borderRadius:R, padding:"20px 20px", border:`1px solid ${C.outlineVar}`, boxShadow:C.sh1, cursor:"pointer", transition:"all .2s", display:"flex", gap:14, alignItems:"flex-start" }}
                    onMouseEnter={e => { e.currentTarget.style.borderColor = C.teal; e.currentTarget.style.transform = "translateY(-2px)"; e.currentTarget.style.boxShadow = C.sh2; }}
                    onMouseLeave={e => { e.currentTarget.style.borderColor = C.outlineVar; e.currentTarget.style.transform = "none"; e.currentTarget.style.boxShadow = C.sh1; }}>
                    <div style={{ width:36, height:36, borderRadius:9, background:C.tealLight, border:`1px solid ${C.tealBorder}`, display:"flex", alignItems:"center", justifyContent:"center", flexShrink:0 }}>
                      <Ic n={categories[activeCategory].icon} sz={17} col={C.teal} />
                    </div>
                    <div style={{ flex:1 }}>
                      <div style={{ fontFamily:F, fontSize:14.5, fontWeight:700, color:C.ink, marginBottom:5, lineHeight:1.35 }}>{a.t}</div>
                      <div style={{ fontFamily:F, fontSize:13, color:C.inkVar, lineHeight:1.55, marginBottom:8 }}>{a.d}</div>
                      <span style={{ fontFamily:F, fontSize:11.5, color:C.teal, fontWeight:600 }}>{a.time}</span>
                    </div>
                  </div>
                ))}
              </div>
            </>
          )}
          {/* Search results */}
          {search.trim().length > 0 && (
            <>
              <div style={{ marginBottom:24 }}>
                <Body muted>Showing results for <strong style={{ color:C.ink }}>"{search}"</strong></Body>
              </div>
              {filtered.length === 0 ? (
                <div style={{ textAlign:"center", padding:"48px 20px" }}>
                  <div style={{ width:52, height:52, borderRadius:"50%", background:C.low, display:"flex", alignItems:"center", justifyContent:"center", margin:"0 auto 16px" }}>
                    <Ic n="search" sz={22} col={C.inkSoft} />
                  </div>
                  <H3 style={{ marginBottom:10, color:C.inkVar }}>No articles found</H3>
                  <Body muted style={{ marginBottom:20 }}>Try a different search term, or contact our support team directly.</Body>
                  <Btn v="outline" onClick={() => nav("contact")}>Contact Support</Btn>
                </div>
              ) : filtered.map((cat, ci) => (
                <div key={ci} style={{ marginBottom:32 }}>
                  <div style={{ display:"flex", alignItems:"center", gap:8, marginBottom:14 }}>
                    <Ic n={cat.icon} sz={16} col={C.teal} />
                    <span style={{ fontFamily:F, fontSize:13, fontWeight:700, color:C.teal, textTransform:"uppercase", letterSpacing:"0.06em" }}>{cat.label}</span>
                  </div>
                  <div style={{ display:"grid", gridTemplateColumns:sm ? "1fr" : "repeat(2,1fr)", gap:12 }}>
                    {cat.articles.map((a, ai) => (
                      <div key={ai} style={{ background:C.surface, borderRadius:R, padding:"18px 20px", border:`1px solid ${C.outlineVar}`, boxShadow:C.sh1, cursor:"pointer" }}>
                        <div style={{ fontFamily:F, fontSize:14.5, fontWeight:700, color:C.ink, marginBottom:5 }}>{a.t}</div>
                        <div style={{ fontFamily:F, fontSize:13, color:C.inkVar, lineHeight:1.55, marginBottom:6 }}>{a.d}</div>
                        <span style={{ fontFamily:F, fontSize:11.5, color:C.teal, fontWeight:600 }}>{a.time}</span>
                      </div>
                    ))}
                  </div>
                </div>
              ))}
            </>
          )}
        </div>
      </section>

      {/* ── CONTACT STRIP ── */}
      <section style={{ padding:sm ? "44px 0" : "64px 0", background:C.low }}>
        <div style={{ ...px }}>
          <div style={{ display:"grid", gridTemplateColumns:sm ? "1fr" : "repeat(3,1fr)", gap:14 }}>
            {[
              {icon:"mail",  t:"Email Support",    d:"support@easygrox.com · We respond within 24 hours on business days."},
              {icon:"phone", t:"WhatsApp Help",    d:"Chat with our setup support team directly on WhatsApp."},
              {icon:"users", t:"Onboarding Help",  d:"Book a free 20-minute guided setup call with our team."},
            ].map((c, i) => (
              <div key={i} style={{ background:C.surface, borderRadius:R, padding:"24px 20px", border:`1px solid ${C.outlineVar}`, boxShadow:C.sh1, display:"flex", gap:14, alignItems:"flex-start" }}>
                <div style={{ width:42, height:42, borderRadius:R, background:C.tealLight, border:`1px solid ${C.tealBorder}`, display:"flex", alignItems:"center", justifyContent:"center", flexShrink:0 }}>
                  <Ic n={c.icon} sz={19} col={C.teal} />
                </div>
                <div>
                  <div style={{ fontFamily:F, fontSize:15, fontWeight:700, color:C.ink, marginBottom:5 }}>{c.t}</div>
                  <div style={{ fontFamily:F, fontSize:13.5, color:C.inkVar, lineHeight:1.6 }}>{c.d}</div>
                </div>
              </div>
            ))}
          </div>
        </div>
      </section>
    </>
  );
}

/* ─── GETTING STARTED PAGE ───────────────────────────────── */
function GettingStarted({ nav }) {
  const { sm, md } = useW();
  const px = { padding:`0 ${sm ? 16 : 24}px`, maxWidth:1200, margin:"0 auto" };
  const SP = sm ? "52px 0" : "80px 0";
  const [done, setDone] = useState({});
  const toggle = i => setDone(p => ({ ...p, [i]:!p[i] }));
  const completed = Object.values(done).filter(Boolean).length;

  const steps = [
    {
      phase:"01", icon:"lock", t:"Create your account", where:"This website · 30 seconds",
      tasks:["Go to easygrox.com and click Create Free Account","Enter your name, email, and a password","No credit card required — click Create Account","You are redirected to the EasyGrox app immediately"],
      tip:"Your account is created on this website. Everything after this happens inside the EasyGrox app.",
      milestone:"Account active",
    },
    {
      phase:"02", icon:"briefcase", t:"Set up your business profile", where:"App Settings · 3 minutes",
      tasks:["Open App Settings → Business Profile","Enter your business name and choose your category","Add your address, phone number, and WhatsApp","Upload your logo or profile photo","Save and continue"],
      tip:"Your business name and logo appear on your free booking website. Take 2 minutes to get this right.",
      milestone:"Profile complete",
    },
    {
      phase:"03", icon:"scissors", t:"Add your services", where:"App Settings · Services · 8–10 minutes",
      tasks:["Go to App Settings → Services → Add Service","Enter service name, category (e.g. Hair, Colour, Treatment)","Set the price and duration accurately","Repeat for every service you offer","Group related services into categories"],
      tip:"Service durations matter — they determine how much time is blocked in your calendar per appointment. Be accurate.",
      milestone:"Services menu ready",
    },
    {
      phase:"04", icon:"users", t:"Add your team members", where:"App Settings · Staff · 5 minutes",
      tasks:["Go to App Settings → Staff → Add Staff Member","Enter name, role, and contact details","Assign the services each staff member performs","Set their access level (Owner / Front Desk / Staff)","They will receive login credentials to their own dashboard"],
      tip:"Each staff member only needs to appear in booking slots for the services they are assigned to. This prevents incorrect bookings.",
      milestone:"Team onboarded",
    },
    {
      phase:"05", icon:"clock", t:"Set your opening hours", where:"App Settings · Hours · 3 minutes",
      tasks:["Go to App Settings → Opening Hours","Set open and close time for each day of the week","Toggle off days you are closed","Configure booking lead time (how far ahead clients can book)","Set a buffer time between appointments if needed"],
      tip:"Clients can only book during your configured opening hours. Setting this accurately prevents off-hours bookings.",
      milestone:"Hours configured",
    },
    {
      phase:"06", icon:"globe", t:"Preview and publish your booking website", where:"Website → Publish · 1 minute",
      tasks:["Open the Website tab inside the app","Preview your auto-generated booking website","Verify services, staff, hours, and contact details are correct","Choose your custom URL (yoursalon.easygrox.com)","Click Publish — your website is live instantly"],
      tip:"Share your booking link on Instagram bio, WhatsApp status, and Google Business Profile immediately after publishing.",
      milestone:"Website live",
    },
    {
      phase:"07", icon:"calendar", t:"Accept your first booking", where:"From your website or manually",
      tasks:["Share your booking link with a few existing clients","Or manually add an appointment from the calendar tab","When a client books online, it appears in your calendar instantly","You and the client both get a confirmation notification","Your first booking unlocks the full daily operations dashboard"],
      tip:"Your first booking is a milestone. Take a screenshot — your business is now officially running on EasyGrox.",
      milestone:"First booking received",
    },
    {
      phase:"08", icon:"pos", t:"Send your first invoice", where:"After first completed appointment",
      tasks:["When the appointment is done, tap Complete","The billing screen opens pre-loaded with the service","Add any retail products sold alongside the service","Select payment method and tap Collect","Receipt is sent to the client via WhatsApp automatically"],
      tip:"After your first invoice, explore the Daily Summary in Analytics. Your financial tracking begins from this moment.",
      milestone:"First invoice sent",
    },
  ];

  const totalTasks = steps.reduce((acc, s) => acc + s.tasks.length, 0);
  const progress = Math.round((completed / steps.length) * 100);

  return (
    <>
      {/* ── HERO ── */}
      <section style={{ padding:sm ? "52px 0 36px" : "80px 0 56px", background:C.bg }}>
        <div style={{ ...px }}>
          <div style={{ display:"grid", gridTemplateColumns:md ? "1fr" : "1fr 1fr", gap:md ? 32 : 64, alignItems:"center" }}>
            <div>
              <Chip teal style={{ marginBottom:18 }}>Interactive setup guide</Chip>
              <H1 style={{ marginBottom:18, marginTop:10 }}>Your business is almost ready. Let us get it live.</H1>
              <Body muted style={{ fontSize:sm ? 15.5 : 17, marginBottom:28, lineHeight:1.72 }}>Eight steps. Tick each one off as you complete it. Most businesses finish in under 30 minutes and accept their first booking on the same day.</Body>
              <Btn onClick={() => nav("signup")}>Create Free Account — Start Step 1</Btn>
            </div>
            <div style={{ background:C.surface, borderRadius:R, border:`1px solid ${C.outlineVar}`, boxShadow:C.sh2, padding:"24px 22px" }}>
              <div style={{ display:"flex", justifyContent:"space-between", alignItems:"center", marginBottom:12 }}>
                <H4>Launch Checklist</H4>
                <Chip teal>{completed} of {steps.length} done</Chip>
              </div>
              <div style={{ background:C.low, borderRadius:9999, height:8, marginBottom:16, overflow:"hidden" }}>
                <div style={{ width:`${progress}%`, height:"100%", background:C.teal, borderRadius:9999, transition:"width .4s ease" }} />
              </div>
              <div style={{ fontFamily:F, fontSize:13, color:C.inkVar, marginBottom:16 }}>
                {progress === 0 ? "Ready to start. Click the first step below." : progress < 50 ? "Good start — keep going." : progress < 100 ? "You are more than halfway there." : "All steps complete — your business is live!"}
              </div>
              {steps.map((s, i) => (
                <div key={i} onClick={() => toggle(i)} style={{ display:"flex", gap:10, alignItems:"center", padding:"9px 0", borderBottom:i < steps.length-1 ? `1px solid ${C.outlineVar}` : "none", cursor:"pointer" }}
                  onMouseEnter={e => e.currentTarget.style.opacity = "0.8"}
                  onMouseLeave={e => e.currentTarget.style.opacity = "1"}>
                  <div style={{ width:22, height:22, borderRadius:"50%", background:done[i] ? C.teal : C.surface, border:`2px solid ${done[i] ? C.teal : C.outlineVar}`, display:"flex", alignItems:"center", justifyContent:"center", flexShrink:0, transition:"all .18s" }}>
                    {done[i] && <Ic n="check" sz={12} col="#fff" />}
                  </div>
                  <div style={{ flex:1 }}>
                    <span style={{ fontFamily:F, fontSize:13.5, color:done[i] ? C.teal : C.ink, fontWeight:done[i] ? 600 : 400, textDecoration:done[i] ? "line-through" : "none" }}>{s.t}</span>
                  </div>
                  <span style={{ fontFamily:F, fontSize:11, color:C.inkSoft }}>{s.phase}</span>
                </div>
              ))}
            </div>
          </div>
        </div>
      </section>

      {/* ── STEP DETAIL CARDS ── */}
      <Divider />
      <section style={{ padding:SP, background:C.low }}>
        <div style={{ ...px }}>
          <div style={{ textAlign:"center", marginBottom:44 }}>
            <Label center>Every step explained</Label>
            <H2 center>Follow this guide inside the app</H2>
          </div>
          <div style={{ display:"flex", flexDirection:"column", gap:16 }}>
            {steps.map((s, i) => (
              <div key={i} style={{ background:done[i] ? C.tealLight : C.surface, borderRadius:R, border:`1px solid ${done[i] ? C.tealBorder : C.outlineVar}`, boxShadow:C.sh1, overflow:"hidden", transition:"all .2s" }}>
                <div style={{ display:"grid", gridTemplateColumns:sm ? "1fr" : "56px 1fr 1fr", gap:0 }}>
                  {/* Step number */}
                  <div style={{ background:done[i] ? C.teal : C.low, display:"flex", alignItems:"center", justifyContent:"center", padding:sm ? "16px" : "0", borderRight:sm ? "none" : `1px solid ${done[i] ? C.tealDark : C.outlineVar}`, borderBottom:sm ? `1px solid ${done[i] ? C.tealBorder : C.outlineVar}` : "none" }}>
                    <div style={{ fontFamily:F, fontSize:18, fontWeight:800, color:done[i] ? "#fff" : C.teal }}>{s.phase}</div>
                  </div>
                  {/* Tasks */}
                  <div style={{ padding:"22px 24px", borderRight:sm ? "none" : `1px solid ${done[i] ? C.tealBorder : C.outlineVar}` }}>
                    <div style={{ display:"flex", gap:10, alignItems:"center", marginBottom:14 }}>
                      <div style={{ width:34, height:34, borderRadius:9, background:done[i] ? C.teal : C.tealLight, border:`1px solid ${done[i] ? C.tealDark : C.tealBorder}`, display:"flex", alignItems:"center", justifyContent:"center" }}>
                        <Ic n={s.icon} sz={16} col={done[i] ? "#fff" : C.teal} />
                      </div>
                      <div>
                        <div style={{ fontFamily:F, fontSize:15, fontWeight:700, color:done[i] ? C.teal : C.ink }}>{s.t}</div>
                        <div style={{ fontFamily:F, fontSize:11.5, color:C.inkVar }}>{s.where}</div>
                      </div>
                    </div>
                    {s.tasks.map((task, ti) => (
                      <div key={ti} style={{ display:"flex", gap:8, marginBottom:8, alignItems:"flex-start" }}>
                        <div style={{ width:18, height:18, borderRadius:5, background:done[i] ? C.teal : C.tealLight, border:`1px solid ${done[i] ? C.tealDark : C.tealBorder}`, display:"flex", alignItems:"center", justifyContent:"center", flexShrink:0, marginTop:1 }}>
                          <span style={{ fontFamily:F, fontSize:9.5, fontWeight:800, color:done[i] ? "#fff" : C.teal }}>{ti+1}</span>
                        </div>
                        <span style={{ fontFamily:F, fontSize:13.5, color:C.inkVar, lineHeight:1.55 }}>{task}</span>
                      </div>
                    ))}
                  </div>
                  {/* Tip + milestone */}
                  <div style={{ padding:"22px 24px", display:"flex", flexDirection:"column", justifyContent:"space-between", gap:16 }}>
                    <div style={{ background:done[i] ? "rgba(0,101,101,0.08)" : C.low, borderRadius:R, padding:"14px 16px", border:`1px solid ${done[i] ? C.tealBorder : C.outlineVar}` }}>
                      <div style={{ fontFamily:F, fontSize:11, fontWeight:700, color:C.teal, textTransform:"uppercase", letterSpacing:"0.06em", marginBottom:6 }}>Pro tip</div>
                      <div style={{ fontFamily:F, fontSize:13, color:C.inkVar, lineHeight:1.6 }}>{s.tip}</div>
                    </div>
                    <div>
                      <div style={{ fontFamily:F, fontSize:11, fontWeight:700, color:C.teal, textTransform:"uppercase", letterSpacing:"0.06em", marginBottom:6 }}>Milestone unlocked</div>
                      <Chip teal>{s.milestone}</Chip>
                    </div>
                    <button onClick={() => toggle(i)} style={{ background:done[i] ? "rgba(0,101,101,0.1)" : C.teal, border:`1px solid ${done[i] ? C.tealBorder : "transparent"}`, borderRadius:R, padding:"10px 16px", color:done[i] ? C.teal : "#fff", fontFamily:F, fontSize:13.5, fontWeight:600, cursor:"pointer", display:"flex", alignItems:"center", gap:7, justifyContent:"center", transition:"all .18s" }}>
                      {done[i] ? <><Ic n="check" sz={14} col={C.teal} />Step Complete</> : <>Mark as Done</>}
                    </button>
                  </div>
                </div>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* ── CTA ── */}
      <section style={{ padding:sm ? "52px 0" : "80px 0", background:C.teal, textAlign:"center" }}>
        <div style={{ ...px }}>
          <H2 light center style={{ marginBottom:14 }}>Your business is {progress > 0 ? `${progress}% ready` : "waiting to launch"}</H2>
          <Body light center style={{ marginBottom:28, maxWidth:440, margin:"0 auto 28px" }}>Create your free account and follow this guide inside the app. Most businesses are live in under 30 minutes.</Body>
          <Btn onClick={() => nav("signup")}>Create Free Account — No Credit Card</Btn>
        </div>
      </section>
    </>
  );
}

/* ─── BLOG PAGE ──────────────────────────────────────────── */
function Blog({ nav }) {
  const { sm, md } = useW();
  const px = { padding:`0 ${sm ? 16 : 24}px`, maxWidth:1200, margin:"0 auto" };
  const SP = sm ? "52px 0" : "80px 0";
  const [activeTag, setActiveTag] = useState("All");

  const tags = ["All","Operations","Marketing","Staff","Clients","Retail","Growth"];

  const posts = [
    {
      tag:"Operations",  featured:true,
      title:"How to reduce no-shows by 60% — without awkward reminder calls",
      excerpt:"No-shows are the silent revenue killer in every salon. A client who confirms on WhatsApp and does not show up costs you the slot, the revenue, and potentially the relationship. Here is exactly what changes when you use automated reminders the right way.",
      read:"6 min read", icon:"bell",
    },
    {
      tag:"Marketing",   featured:false,
      title:"The SMS campaign that filled a dead Tuesday — in 4 hours",
      excerpt:"Tuesday at 11am. Three open slots. A simple SMS to 80 clients: limited offer, book today, 15% off. Four hours later, two slots filled and one waitlisted. Here is the exact message, the segment used, and the result.",
      read:"4 min read", icon:"tag",
    },
    {
      tag:"Clients",     featured:false,
      title:"Why your regulars stop coming — and the one change that brings them back",
      excerpt:"The average beauty client drifts away silently. No complaint, no goodbye. They just stop booking. Understanding the 6-week cycle and building an automated follow-up is the single highest-ROI operational change most salons make.",
      read:"5 min read", icon:"repeat",
    },
    {
      tag:"Operations",  featured:false,
      title:"Walk-in management: how to serve every walk-in without disrupting booked appointments",
      excerpt:"Walk-ins are revenue opportunities — but without a system, they create front-desk chaos. A digital queue that integrates with your calendar changes everything. Here is how top salons handle 40% walk-in traffic without stress.",
      read:"4 min read", icon:"zap",
    },
    {
      tag:"Growth",      featured:false,
      title:"Why your salon needs its own booking website — not a marketplace listing",
      excerpt:"Fresha and Booksy list your salon alongside every competitor in your area. A client searching for a haircut sees your salon, your competitor, and their prices. Your own booking website changes this dynamic completely.",
      read:"5 min read", icon:"globe",
    },
    {
      tag:"Staff",       featured:false,
      title:"Staff scheduling for salons: how to build rosters that actually work",
      excerpt:"A roster built on a whiteboard or a WhatsApp group is a roster that breaks down weekly. Here is a practical guide to shift scheduling, leave management, and utilisation tracking for beauty teams of any size.",
      read:"6 min read", icon:"users",
    },
    {
      tag:"Retail",      featured:false,
      title:"Retail selling for salon owners: the low-pressure approach that actually works",
      excerpt:"Most salon staff hate selling retail. Most clients feel sold to. Here is a method that frames retail recommendations as genuine client care — and increases retail revenue without training anyone to be a salesperson.",
      read:"5 min read", icon:"bag",
    },
    {
      tag:"Operations",  featured:false,
      title:"The 3 numbers every salon owner should read every morning",
      excerpt:"Revenue, no-show rate, and booking conversion. These three numbers tell you everything about the health of your business on any given day. Here is exactly what to look for and how to act on what you see.",
      read:"4 min read", icon:"bar",
    },
    {
      tag:"Marketing",   featured:false,
      title:"Birthday campaigns: the automation that generates bookings on autopilot",
      excerpt:"A personalised birthday discount, sent automatically on the right day, has the highest conversion rate of any marketing message in the beauty industry. Here is how to set it up once and let it run forever.",
      read:"3 min read", icon:"gift",
    },
  ];

  const displayed = activeTag === "All" ? posts : posts.filter(p => p.tag === activeTag);
  const featured = posts.find(p => p.featured);
  const rest = displayed.filter(p => !p.featured || activeTag !== "All");

  return (
    <>
      {/* ── HERO ── */}
      <section style={{ padding:sm ? "52px 0 36px" : "80px 0 56px", background:C.bg }}>
        <div style={{ ...px }}>
          <div style={{ textAlign:"center", marginBottom:sm ? 32 : 48 }}>
            <Label center>For beauty and wellness business owners</Label>
            <H1 style={{ marginBottom:16, marginTop:10, maxWidth:600, margin:"10px auto 16px" }}>Practical guides. Operational insights. Real results.</H1>
            <Body muted center style={{ fontSize:sm ? 15 : 16.5, maxWidth:520, margin:"0 auto" }}>No generic business content. Everything written for the daily realities of running a salon, spa, or grooming studio.</Body>
          </div>
          {/* Featured post */}
          {featured && activeTag === "All" && (
            <div style={{ background:C.teal, borderRadius:R, padding:sm ? "28px 22px" : "44px 48px", display:"grid", gridTemplateColumns:md ? "1fr" : "1fr 1fr", gap:md ? 24 : 48, alignItems:"center", marginBottom:48, boxShadow:"0 20px 48px rgba(0,101,101,0.22)" }}>
              <div>
                <div style={{ display:"flex", gap:8, marginBottom:16 }}>
                  <Chip sm style={{ background:"rgba(255,255,255,0.15)", color:"rgba(255,255,255,0.85)", border:"1px solid rgba(255,255,255,0.2)" }}>Featured</Chip>
                  <Chip sm style={{ background:"rgba(255,255,255,0.15)", color:"rgba(255,255,255,0.85)", border:"1px solid rgba(255,255,255,0.2)" }}>{featured.tag}</Chip>
                  <Chip sm style={{ background:"rgba(255,255,255,0.15)", color:"rgba(255,255,255,0.85)", border:"1px solid rgba(255,255,255,0.2)" }}>{featured.read}</Chip>
                </div>
                <H2 light style={{ marginBottom:16, lineHeight:1.2 }}>{featured.title}</H2>
                <Body light style={{ lineHeight:1.75, marginBottom:24 }}>{featured.excerpt}</Body>
                <Btn v="whiteSolid" sm>Read Article</Btn>
              </div>
              <div style={{ background:"rgba(255,255,255,0.1)", borderRadius:R, padding:"32px 28px", border:"1px solid rgba(255,255,255,0.15)", display:"flex", flexDirection:"column", alignItems:"center", justifyContent:"center", textAlign:"center" }}>
                <div style={{ width:56, height:56, borderRadius:R, background:"rgba(255,255,255,0.15)", display:"flex", alignItems:"center", justifyContent:"center", marginBottom:16 }}>
                  <Ic n={featured.icon} sz={28} col="#fff" />
                </div>
                <div style={{ fontFamily:F, fontSize:11, fontWeight:700, color:"rgba(255,255,255,0.5)", letterSpacing:"0.08em", textTransform:"uppercase", marginBottom:8 }}>Most read this month</div>
                <div style={{ fontFamily:F, fontSize:16, fontWeight:700, color:"#fff" }}>No-shows drop by 60%</div>
                <div style={{ fontFamily:F, fontSize:13, color:"rgba(255,255,255,0.5)", marginTop:4 }}>after implementing automated reminders</div>
              </div>
            </div>
          )}
        </div>
      </section>

      {/* ── POSTS ── */}
      <section style={{ padding:SP, background:C.low }}>
        <div style={{ ...px }}>
          {/* Tags */}
          <div style={{ display:"flex", gap:8, flexWrap:"wrap", marginBottom:32 }}>
            {tags.map((tag, i) => (
              <button key={i} onClick={() => setActiveTag(tag)} style={{ padding:"7px 18px", borderRadius:9999, border:`1.5px solid ${activeTag===tag ? C.teal : C.outlineVar}`, background:activeTag===tag ? C.teal : C.surface, color:activeTag===tag ? "#fff" : C.inkVar, fontWeight:600, fontSize:13, cursor:"pointer", fontFamily:F, transition:"all .18s" }}>
                {tag}
              </button>
            ))}
          </div>
          {/* Post grid */}
          <div style={{ display:"grid", gridTemplateColumns:sm ? "1fr" : md ? "1fr 1fr" : "repeat(3,1fr)", gap:18 }}>
            {rest.map((post, i) => (
              <div key={i} style={{ background:C.surface, borderRadius:R, border:`1px solid ${C.outlineVar}`, boxShadow:C.sh1, overflow:"hidden", cursor:"pointer", transition:"all .2s" }}
                onMouseEnter={e => { e.currentTarget.style.transform = "translateY(-4px)"; e.currentTarget.style.boxShadow = C.sh2; e.currentTarget.style.borderColor = C.teal; }}
                onMouseLeave={e => { e.currentTarget.style.transform = "none"; e.currentTarget.style.boxShadow = C.sh1; e.currentTarget.style.borderColor = C.outlineVar; }}>
                {/* Top bar */}
                <div style={{ background:C.tealLight, padding:"14px 18px", display:"flex", alignItems:"center", gap:10, borderBottom:`1px solid ${C.tealBorder}` }}>
                  <div style={{ width:34, height:34, borderRadius:8, background:C.surface, border:`1px solid ${C.tealBorder}`, display:"flex", alignItems:"center", justifyContent:"center" }}>
                    <Ic n={post.icon} sz={16} col={C.teal} />
                  </div>
                  <div style={{ display:"flex", gap:6 }}>
                    <Chip teal sm>{post.tag}</Chip>
                    <span style={{ fontFamily:F, fontSize:11.5, color:C.inkVar, alignSelf:"center" }}>{post.read}</span>
                  </div>
                </div>
                {/* Content */}
                <div style={{ padding:"20px 20px 24px" }}>
                  <div style={{ fontFamily:F, fontSize:15.5, fontWeight:700, color:C.ink, marginBottom:10, lineHeight:1.35 }}>{post.title}</div>
                  <div style={{ fontFamily:F, fontSize:13.5, color:C.inkVar, lineHeight:1.68 }}>{post.excerpt.substring(0, 120)}...</div>
                  <div style={{ marginTop:16, display:"flex", alignItems:"center", gap:6, color:C.teal }}>
                    <span style={{ fontFamily:F, fontSize:13, fontWeight:600, color:C.teal }}>Read article</span>
                    <Ic n="chevR" sz={13} col={C.teal} />
                  </div>
                </div>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* ── NEWSLETTER ── */}
      <section style={{ padding:SP, background:C.bg }}>
        <div style={{ ...px, maxWidth:640, margin:"0 auto", textAlign:"center" }}>
          <Label center>Stay in the loop</Label>
          <H2 center style={{ marginBottom:14, marginTop:10 }}>Practical salon insights in your inbox</H2>
          <Body muted center style={{ marginBottom:28 }}>Bi-weekly. Operational. No fluff. Unsubscribe any time.</Body>
          <div style={{ display:"flex", gap:10, flexDirection:sm ? "column" : "row" }}>
            <input placeholder="your@email.com" style={{ flex:1, height:48, borderRadius:R, border:`1px solid ${C.outlineVar}`, padding:"0 16px", fontSize:15, fontFamily:F, outline:"none", boxSizing:"border-box" }}
              onFocus={e => e.target.style.border = `1.5px solid ${C.teal}`}
              onBlur={e => e.target.style.border = `1px solid ${C.outlineVar}`} />
            <Btn>Subscribe</Btn>
          </div>
          <Body sm muted style={{ marginTop:12, fontSize:12 }}>No spam. Bi-weekly insights for beauty business owners.</Body>
        </div>
      </section>
    </>
  );
}

/* ─── ABOUT VELOUR PAGE ──────────────────────────────────── */
function About({ nav }) {
  const { sm, md } = useW();
  const px = { padding:`0 ${sm ? 16 : 24}px`, maxWidth:1200, margin:"0 auto" };
  const SP = sm ? "52px 0" : "80px 0";

  const values = [
    {icon:"zap",      t:"Operational simplicity",  d:"Every feature should be understood in seconds, not after reading documentation. If our own mothers could not use it, we rebuild it until they can."},
    {icon:"shield",   t:"Your data, your business", d:"Your client list, your revenue data, your business — it belongs to you. Always exportable. No commissions. No marketplace lock-in. No surprises."},
    {icon:"repeat",   t:"Ship weekly, improve daily",d:"We push improvements every week. Every single change is driven by what real salon owners asked for — not what we assumed they needed."},
    {icon:"users",    t:"We succeed when you succeed",d:"Our pricing is flat. Our model is simple. We have no incentive to extract more from you than your subscription. When your business grows, we grow with it."},
  ];

  const timeline = [
    {year:"2021", t:"The problem becomes clear",    d:"Our founders watched a close friend struggle to manage her salon on spreadsheets, WhatsApp, and paper registers — simultaneously. There had to be a better way."},
    {year:"2022", t:"First version built",          d:"A simple appointment calendar and billing tool, tested with 12 salons in Jaipur. The feedback was brutal and invaluable. We rebuilt from scratch."},
    {year:"2023", t:"Platform expands",             d:"Staff management, retail inventory, client profiles, and the first version of the free booking website. 400 businesses onboarded in 6 months."},
    {year:"2024", t:"Analytics and marketing added",d:"Six analytics dashboards, SMS campaigns, birthday automation, and re-engagement tools. The platform became a complete operating system."},
    {year:"2025", t:"10,000+ businesses on EasyGrox",d:"Trusted by over 10,000 salons, spas, studios, and grooming businesses across India. Average setup time: 28 minutes. Average rating: 4.9 / 5."},
    {year:"2026", t:"Still shipping. Still learning.",d:"Multi-location launched. Website analytics launched. Custom domains launched. We ask our users what to build next — and we actually listen."},
  ];

  return (
    <>
      {/* ── HERO ── */}
      <section style={{ padding:sm ? "56px 0 44px" : "96px 0 72px", background:C.bg }}>
        <div style={{ ...px }}>
          <div style={{ display:"grid", gridTemplateColumns:md ? "1fr" : "1fr 1fr", gap:md ? 36 : 80, alignItems:"center" }}>
            <div>
              <Chip teal style={{ marginBottom:18 }}>Our story</Chip>
              <H1 style={{ marginBottom:22, lineHeight:1.08 }}>We built EasyGrox because we watched talented salon owners spend more time managing chaos than doing what they love.</H1>
              <Body muted style={{ fontSize:sm ? 15.5 : 17, lineHeight:1.78 }}>Every stylist, barber, nail technician, and therapist we have ever spoken to is brilliant at their craft. The business side — scheduling, billing, staff, retention — takes time and energy away from the work they were born to do. EasyGrox exists to give that time back.</Body>
            </div>
            <div>
              <div style={{ display:"grid", gridTemplateColumns:"1fr 1fr", gap:12 }}>
                {[{v:"10,000+",l:"Businesses trusting EasyGrox"},{v:"4.9/5",l:"Average platform rating"},{v:"28 min",l:"Average setup time"},{v:"₹0",l:"Website cost, forever"}].map((s, i) => (
                  <div key={i} style={{ background:i === 0 ? C.teal : C.surface, borderRadius:R, padding:"24px 20px", border:`1px solid ${i === 0 ? C.teal : C.outlineVar}`, boxShadow:C.sh1 }}>
                    <div style={{ fontFamily:F, fontSize:28, fontWeight:800, color:i === 0 ? "#fff" : C.teal, lineHeight:1 }}>{s.v}</div>
                    <div style={{ fontFamily:F, fontSize:12.5, color:i === 0 ? "rgba(255,255,255,0.55)" : C.inkVar, marginTop:8 }}>{s.l}</div>
                  </div>
                ))}
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* ── WHAT WE BELIEVE ── */}
      <section style={{ padding:SP, background:C.low }}>
        <div style={{ ...px }}>
          <div style={{ textAlign:"center", marginBottom:44 }}>
            <Label center>What we believe</Label>
            <H2 center>The principles that drive every product decision</H2>
          </div>
          <div style={{ display:"grid", gridTemplateColumns:sm ? "1fr" : md ? "1fr 1fr" : "1fr 1fr 1fr 1fr", gap:16 }}>
            {values.map((v, i) => (
              <Card key={i} tealTop style={{ padding:"28px 22px" }}>
                <IcBox n={v.icon} />
                <H4 style={{ marginBottom:10 }}>{v.t}</H4>
                <Body sm muted style={{ lineHeight:1.68 }}>{v.d}</Body>
              </Card>
            ))}
          </div>
        </div>
      </section>

      {/* ── TIMELINE ── */}
      <section style={{ padding:SP, background:C.bg }}>
        <div style={{ ...px }}>
          <div style={{ textAlign:"center", marginBottom:48 }}>
            <Label center>Our journey</Label>
            <H2 center>From a notebook observation to 10,000 businesses</H2>
          </div>
          <div style={{ maxWidth:780, margin:"0 auto" }}>
            {timeline.map((ev, i) => (
              <div key={i} style={{ display:"grid", gridTemplateColumns:sm ? "52px 1fr" : "80px 1fr", gap:sm ? 16 : 24, marginBottom:i < timeline.length-1 ? 32 : 0, position:"relative" }}>
                {(i < timeline.length-1) && <div style={{ position:"absolute", left:sm ? 25 : 39, top:44, width:2, height:"calc(100% - 10px)", background:C.tealLight }} />}
                <div style={{ textAlign:"center", paddingTop:4 }}>
                  <div style={{ background:i === timeline.length-1 ? C.teal : C.surface, border:`2px solid ${C.teal}`, borderRadius:R, padding:"6px 4px", fontFamily:F, fontSize:sm ? 11 : 12, fontWeight:800, color:i === timeline.length-1 ? "#fff" : C.teal }}>{ev.year}</div>
                </div>
                <Card style={{ padding:"20px 22px", borderLeft:`3px solid ${C.teal}`, borderRadius:`0 ${R} ${R} 0` }}>
                  <div style={{ fontFamily:F, fontSize:15, fontWeight:700, color:C.ink, marginBottom:8 }}>{ev.t}</div>
                  <Body sm muted style={{ lineHeight:1.65 }}>{ev.d}</Body>
                </Card>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* ── PHILOSOPHY ── */}
      <section style={{ padding:SP, background:C.teal }}>
        <div style={{ ...px }}>
          <div style={{ display:"grid", gridTemplateColumns:md ? "1fr" : "1fr 1fr", gap:64, alignItems:"center" }}>
            <div>
              <Label light>Our product philosophy</Label>
              <H2 light style={{ marginBottom:20, marginTop:10 }}>This is not a feature list. This is a daily operating system.</H2>
              <Body light style={{ fontSize:sm ? 15 : 16.5, lineHeight:1.78, marginBottom:28 }}>We do not add features because they are impressive. We add them because a real salon owner, struggling with a real daily problem, told us they needed it. Every dashboard, every automation, every reminder was built in response to a specific pain point we heard from a specific business.</Body>
              <Body light style={{ fontSize:sm ? 15 : 16.5, lineHeight:1.78 }}>The result is a platform that feels less like software and more like a calm, intelligent business partner — one that handles the operational layer so you can focus on the craft.</Body>
            </div>
            <div style={{ display:"flex", flexDirection:"column", gap:14 }}>
              {["We ship every week — not every quarter","We talk to salon owners, not to analysts","Every feature starts with a real business problem","We never add complexity for its own sake","Your data is yours — no lock-in, ever","We measure success in your business outcomes, not ours"].map((p, i) => (
                <div key={i} style={{ display:"flex", gap:12, alignItems:"flex-start", background:"rgba(255,255,255,0.1)", borderRadius:R, padding:"14px 18px", border:"1px solid rgba(255,255,255,0.15)" }}>
                  <div style={{ width:22, height:22, borderRadius:6, background:"rgba(255,255,255,0.15)", display:"flex", alignItems:"center", justifyContent:"center", flexShrink:0 }}>
                    <Ic n="check" sz={12} col="#fff" />
                  </div>
                  <span style={{ fontFamily:F, fontSize:14.5, color:"rgba(255,255,255,0.85)", lineHeight:1.5 }}>{p}</span>
                </div>
              ))}
            </div>
          </div>
        </div>
      </section>

      {/* ── CTA ── */}
      <section style={{ padding:SP, background:C.bg, textAlign:"center" }}>
        <div style={{ ...px }}>
          <H2 center style={{ marginBottom:16 }}>Try it yourself — 30 minutes to go live</H2>
          <Body muted center style={{ maxWidth:440, margin:"0 auto 28px" }}>See the platform in the best way possible: by setting up your own business and publishing your free booking website.</Body>
          <div style={{ display:"flex", gap:12, justifyContent:"center", flexWrap:"wrap" }}>
            <Btn onClick={() => nav("signup")}>Create Free Account</Btn>
            <Btn v="outline" onClick={() => nav("howItWorks")}>How It Works</Btn>
          </div>
        </div>
      </section>
    </>
  );
}

/* ─── CONTACT PAGE ───────────────────────────────────────── */
function Contact({ nav }) {
  const { sm, md } = useW();
  const px = { padding:`0 ${sm ? 16 : 24}px`, maxWidth:1200, margin:"0 auto" };
  const SP = sm ? "52px 0" : "80px 0";
  const [formData, setFormData] = useState({ name:"", email:"", business:"", type:"", message:"" });
  const [category, setCategory] = useState("");
  const [sent, setSent] = useState(false);

  const categories = [
    {icon:"zap",      label:"Getting Started",      d:"I need help setting up my account or business profile."},
    {icon:"settings", label:"Technical Support",    d:"I am already a EasyGrox user with a question about a feature."},
    {icon:"globe",    label:"Booking Website Help", d:"Questions about my free booking website, URL, or analytics."},
    {icon:"users",    label:"Team Setup",           d:"Help adding staff, setting access levels, or managing shifts."},
    {icon:"bar",      label:"Reports and Billing",  d:"Questions about my analytics dashboard or subscription billing."},
    {icon:"briefcase",label:"Partnerships",         d:"Integration partnerships, reseller programs, or affiliates."},
  ];

  const contactOptions = [
    {icon:"mail",    t:"Email",      d:"support@easygrox.com", sub:"Response within 24 hours on business days"},
    {icon:"phone",   t:"WhatsApp",   d:"+91 98765 43210",    sub:"Quick support for active EasyGrox users"},
    {icon:"calendar",t:"Setup Call", d:"Book a free 20-minute guided setup call", sub:"For new users who want a walkthrough"},
  ];

  return (
    <>
      {/* ── HERO ── */}
      <section style={{ padding:sm ? "52px 0 36px" : "80px 0 56px", background:C.bg }}>
        <div style={{ ...px }}>
          <div style={{ display:"grid", gridTemplateColumns:md ? "1fr" : "1fr 1fr", gap:md ? 36 : 72, alignItems:"start" }}>
            <div>
              <Chip teal style={{ marginBottom:18 }}>No bots. No queues.</Chip>
              <H1 style={{ marginBottom:20, marginTop:10 }}>We are here. What do you need?</H1>
              <Body muted style={{ fontSize:sm ? 15.5 : 17, marginBottom:36, lineHeight:1.72 }}>Whether you are setting up for the first time or have been running your business on EasyGrox for a year — our team responds personally within 24 hours.</Body>
              {/* Contact options */}
              <div style={{ display:"flex", flexDirection:"column", gap:12, marginBottom:32 }}>
                {contactOptions.map((c, i) => (
                  <div key={i} style={{ display:"flex", gap:14, alignItems:"flex-start", background:C.surface, borderRadius:R, padding:"16px 18px", border:`1px solid ${C.outlineVar}`, boxShadow:C.sh1 }}>
                    <div style={{ width:40, height:40, borderRadius:R, background:C.tealLight, border:`1px solid ${C.tealBorder}`, display:"flex", alignItems:"center", justifyContent:"center", flexShrink:0 }}>
                      <Ic n={c.icon} sz={18} col={C.teal} />
                    </div>
                    <div>
                      <div style={{ fontFamily:F, fontSize:15, fontWeight:700, color:C.ink, marginBottom:2 }}>{c.t}</div>
                      <div style={{ fontFamily:F, fontSize:14, color:C.teal, fontWeight:500, marginBottom:2 }}>{c.d}</div>
                      <div style={{ fontFamily:F, fontSize:12.5, color:C.inkVar }}>{c.sub}</div>
                    </div>
                  </div>
                ))}
              </div>
              {/* Trust signals */}
              <div style={{ display:"flex", gap:16, flexWrap:"wrap" }}>
                {["24h response","Real team members","No sales pressure","Free setup help"].map((t, i) => (
                  <span key={i} style={{ display:"flex", alignItems:"center", gap:5, fontFamily:F, fontSize:12.5, color:C.inkVar }}>
                    <Ic n="check" sz={11} col={C.teal} />{t}
                  </span>
                ))}
              </div>
            </div>

            {/* FORM */}
            <div style={{ background:C.surface, borderRadius:R, border:`1px solid ${C.outlineVar}`, boxShadow:C.sh2, overflow:"hidden" }}>
              <div style={{ background:C.teal, padding:"18px 24px" }}>
                <div style={{ color:"#fff", fontSize:15, fontWeight:700 }}>Send us a message</div>
                <div style={{ color:"rgba(255,255,255,0.55)", fontSize:12, marginTop:2 }}>We respond within 24 hours — personally</div>
              </div>
              {sent ? (
                <div style={{ padding:"48px 32px", textAlign:"center" }}>
                  <div style={{ width:56, height:56, borderRadius:"50%", background:C.tealLight, border:`2px solid ${C.tealBorder}`, display:"flex", alignItems:"center", justifyContent:"center", margin:"0 auto 20px" }}>
                    <Ic n="check" sz={24} col={C.teal} />
                  </div>
                  <H3 style={{ marginBottom:10 }}>Message received</H3>
                  <Body muted style={{ marginBottom:24 }}>Our team will respond within 24 hours. If your question is urgent, WhatsApp us directly.</Body>
                  <Btn v="outline" onClick={() => setSent(false)}>Send another message</Btn>
                </div>
              ) : (
                <div style={{ padding:"24px 24px" }}>
                  {/* Category picker */}
                  <div style={{ marginBottom:20 }}>
                    <label style={{ display:"block", fontFamily:F, fontSize:13.5, fontWeight:600, color:C.ink, marginBottom:10 }}>What do you need help with?</label>
                    <div style={{ display:"grid", gridTemplateColumns:"1fr 1fr", gap:8 }}>
                      {categories.map((c, i) => (
                        <button key={i} onClick={() => setCategory(c.label)}
                          style={{ padding:"10px 12px", borderRadius:R, border:`1.5px solid ${category===c.label ? C.teal : C.outlineVar}`, background:category===c.label ? C.tealLight : C.surface, cursor:"pointer", textAlign:"left", transition:"all .15s" }}>
                          <div style={{ display:"flex", alignItems:"center", gap:7, marginBottom:3 }}>
                            <Ic n={c.icon} sz={13} col={category===c.label ? C.teal : C.inkSoft} />
                            <span style={{ fontFamily:F, fontSize:12.5, fontWeight:700, color:category===c.label ? C.teal : C.ink }}>{c.label}</span>
                          </div>
                          <div style={{ fontFamily:F, fontSize:11.5, color:C.inkVar, lineHeight:1.4 }}>{c.d}</div>
                        </button>
                      ))}
                    </div>
                  </div>
                  {/* Form fields */}
                  {[
                    {key:"name",     label:"Your name",         ph:"Full name",              type:"text"},
                    {key:"email",    label:"Email address",      ph:"your@email.com",          type:"email"},
                    {key:"business", label:"Business name",      ph:"e.g. Luxe Hair Studio",  type:"text"},
                  ].map((field) => (
                    <div key={field.key} style={{ marginBottom:14 }}>
                      <label style={{ display:"block", fontFamily:F, fontSize:13.5, fontWeight:600, color:C.ink, marginBottom:6 }}>{field.label}</label>
                      <input value={formData[field.key]} onChange={e => setFormData(p => ({ ...p, [field.key]:e.target.value }))} placeholder={field.ph} type={field.type}
                        style={{ width:"100%", height:44, borderRadius:R, border:`1px solid ${C.outlineVar}`, padding:"0 14px", fontSize:14.5, fontFamily:F, outline:"none", boxSizing:"border-box", background:C.bg }}
                        onFocus={e => e.target.style.border = `1.5px solid ${C.teal}`}
                        onBlur={e => e.target.style.border = `1px solid ${C.outlineVar}`} />
                    </div>
                  ))}
                  <div style={{ marginBottom:18 }}>
                    <label style={{ display:"block", fontFamily:F, fontSize:13.5, fontWeight:600, color:C.ink, marginBottom:6 }}>Your message</label>
                    <textarea value={formData.message} onChange={e => setFormData(p => ({ ...p, message:e.target.value }))} placeholder="Tell us exactly what you need help with. The more detail, the faster we can help."
                      style={{ width:"100%", height:100, borderRadius:R, border:`1px solid ${C.outlineVar}`, padding:"12px 14px", fontSize:14.5, fontFamily:F, outline:"none", boxSizing:"border-box", resize:"vertical", background:C.bg, lineHeight:1.6 }}
                      onFocus={e => e.target.style.border = `1.5px solid ${C.teal}`}
                      onBlur={e => e.target.style.border = `1px solid ${C.outlineVar}`} />
                  </div>
                  <Btn full onClick={() => { if (formData.name && formData.email) setSent(true); }}>Send Message</Btn>
                  <Body sm muted style={{ marginTop:12, fontSize:12, textAlign:"center" }}>We respond personally within 24 hours. No bots, no automated replies.</Body>
                </div>
              )}
            </div>
          </div>
        </div>
      </section>

      {/* ── FAQ STRIP ── */}
      <section style={{ padding:SP, background:C.low }}>
        <div style={{ ...px }}>
          <H3 style={{ marginBottom:24, textAlign:"center" }}>Quick answers</H3>
          <div style={{ display:"grid", gridTemplateColumns:sm ? "1fr" : md ? "1fr 1fr" : "repeat(3,1fr)", gap:14 }}>
            {[
              {q:"Do I need to call anyone to sign up?",    a:"No. Signup is completely self-serve on this website. Create your account, set up your business in the app, and publish your free website — no sales call required."},
              {q:"How long does it take to get a response?",a:"Within 24 hours on business days. For urgent issues, WhatsApp us directly — active EasyGrox users get priority response."},
              {q:"Is there a free trial?",                  a:"There is no trial — instead, you create a free account and set up your business fully before any payment is needed. You see exactly what you are getting before committing."},
              {q:"Can I get help migrating from another platform?",a:"Yes. Our team helps with data migration from Phorest, Fresha, Vagaro, and most other platforms at no charge. Mention it in your message and we will coordinate."},
              {q:"Can I book a setup call?",                a:"Yes. New users can book a free 20-minute call with our onboarding team. We walk through your store setup, answer your questions, and help you publish your booking website."},
              {q:"Do you offer partnerships or white-label?",a:"We work with selected integration and reseller partners. If you are interested in a partnership arrangement, choose Partnerships from the contact form and our team will reach out."},
            ].map((f, i) => (
              <Card key={i} style={{ padding:"22px 20px" }}>
                <div style={{ fontFamily:F, fontSize:14.5, fontWeight:700, color:C.ink, marginBottom:10, lineHeight:1.35 }}>{f.q}</div>
                <Body sm muted style={{ lineHeight:1.65 }}>{f.a}</Body>
              </Card>
            ))}
          </div>
        </div>
      </section>

      {/* ── CTA ── */}
      <section style={{ padding:sm ? "52px 0" : "80px 0", background:C.teal, textAlign:"center" }}>
        <div style={{ ...px }}>
          <H2 light center style={{ marginBottom:14 }}>Ready to set up your business?</H2>
          <Body light center style={{ maxWidth:440, margin:"0 auto 28px" }}>Create your free account and follow the guided setup. Most businesses are live in under 30 minutes — no help needed.</Body>
          <div style={{ display:"flex", gap:12, justifyContent:"center", flexWrap:"wrap" }}>
            <Btn onClick={() => nav("signup")}>Create Free Account</Btn>
            <Btn v="white" onClick={() => nav("howItWorks")}>See How It Works</Btn>
          </div>
        </div>
      </section>
    </>
  );
}



/* ─── SIGNUP / LOGIN → live app ──────────────────────────── */
function AppAuthRedirect() {
  useEffect(() => { goToAppAuth(); }, []);
  return (
    <section style={{ minHeight:"calc(100vh - 60px)", display:"flex", alignItems:"center", justifyContent:"center", background:C.bg }}>
      <Body muted center>Redirecting to EasyGrox sign in…</Body>
    </section>
  );
}

function Signup() {
  return <AppAuthRedirect />;
}

function Login() {
  return <AppAuthRedirect />;
}

/* ─── PRICING PAGE ───────────────────────────────────────── */
function Pricing({ nav }) {
  const { sm, md } = useW();
  const [annual, setAnnual] = useState(false);
  const [openFaq, setOpenFaq] = useState(null);
  const px = { padding:`0 ${sm ? 16 : 24}px`, maxWidth:1200, margin:"0 auto" };
  const sec = { padding:sm ? "56px 0" : "88px 0" };

  const plans = [
    { plan:"Solo",    for:"Independent stylists", price:"₹799",  apr:"₹666",  feats:["1 store · 1 staff login","Appointment calendar & POS","Client management","Automated SMS reminders","Free hosted booking website","Website visitor analytics"], hi:false },
    { plan:"Business",for:"Salons with a team",   price:"₹1,999",apr:"₹1,666", feats:["Everything in Solo","Up to 20 staff members","Shift scheduling & leave","Retail inventory","Full analytics (6 dashboards)","SMS & email campaigns","Free website + full analytics"], hi:true },
    { plan:"Multi-Location",for:"Multiple branches",price:"₹3,999",apr:"₹3,332",feats:["Everything in Business","Unlimited branches","Website per branch","Consolidated analytics","Priority support"], hi:false },
  ];

  const faqs = [
    { q:"Is the booking website really free forever?", a:"Yes. Every plan includes a fully hosted, mobile-responsive booking website at zero extra cost." },
    { q:"Can I switch plans later?", a:"Upgrade or downgrade anytime from your account settings." },
    { q:"Is there a contract?", a:"No long-term contracts. Pay monthly or annually and cancel whenever you need." },
  ];

  return (
    <>
      <section style={{ ...sec, background:C.bg, paddingTop:sm ? "48px" : "72px" }}>
        <div style={{ ...px, textAlign:"center", maxWidth:640, margin:"0 auto" }}>
          <Label center>No hidden fees</Label>
          <H1 style={{ marginTop:12, marginBottom:16 }}>Simple pricing. Free website on every plan.</H1>
          <Body muted center>Every account includes a hosted booking website, visitor analytics, and unlimited online bookings.</Body>
        </div>
      </section>
      <section style={{ ...sec, background:C.bg, paddingTop:0 }}>
        <div style={{ ...px }}>
          <div style={{ display:"flex", justifyContent:"center", marginBottom:36 }}>
            <div style={{ display:"inline-flex", alignItems:"center", gap:12, background:C.surface, border:`1px solid ${C.outlineVar}`, borderRadius:9999, padding:"6px 6px 6px 18px", boxShadow:C.sh1 }}>
              <span style={{ fontFamily:F, fontSize:13.5, fontWeight:annual ? 400 : 600, color:annual ? C.inkVar : C.ink }}>Monthly</span>
              <div onClick={() => setAnnual(!annual)} style={{ width:44, height:24, borderRadius:9999, background:annual ? C.teal : C.outlineVar, position:"relative", cursor:"pointer", transition:"background .2s" }}>
                <div style={{ width:18, height:18, borderRadius:"50%", background:"#fff", position:"absolute", top:3, left:annual ? 23 : 3, transition:"left .2s", boxShadow:"0 1px 4px rgba(0,0,0,0.15)" }} />
              </div>
              <span style={{ fontFamily:F, fontSize:13.5, fontWeight:annual ? 600 : 400, color:annual ? C.ink : C.inkVar }}>Annual</span>
              {annual && <Chip teal sm>Save 2 months</Chip>}
            </div>
          </div>
          <div style={{ display:"grid", gridTemplateColumns:sm ? "1fr" : md ? "1fr 1fr" : "1fr 1fr 1fr", gap:16 }}>
            {plans.map((p, i) => (
              <div key={i} style={{ background:p.hi ? C.teal : C.surface, borderRadius:R, padding:"30px 24px", border:`1px solid ${p.hi ? C.teal : C.outlineVar}`, boxShadow:p.hi ? "0 16px 40px rgba(0,101,101,0.28)" : C.sh1, transform:p.hi && !sm ? "scale(1.03)" : "none", position:"relative" }}>
                {p.hi && <div style={{ position:"absolute", top:-13, left:"50%", transform:"translateX(-50%)" }}><span style={{ background:C.surface, color:C.teal, fontFamily:F, fontSize:10, fontWeight:700, padding:"3px 14px", borderRadius:9999, border:`1px solid ${C.tealBorder}`, whiteSpace:"nowrap", textTransform:"uppercase", letterSpacing:"0.06em" }}>Most Popular</span></div>}
                <div style={{ fontFamily:F, fontSize:10, fontWeight:700, letterSpacing:"0.08em", textTransform:"uppercase", color:p.hi ? "rgba(255,255,255,0.4)" : C.inkVar, marginBottom:4 }}>{p.plan}</div>
                <div style={{ fontFamily:F, fontSize:13, color:p.hi ? "rgba(255,255,255,0.5)" : C.inkVar, marginBottom:18 }}>{p.for}</div>
                <div style={{ display:"flex", alignItems:"baseline", gap:4, marginBottom:20 }}>
                  <span style={{ fontFamily:F, fontSize:34, fontWeight:800, color:p.hi ? "#fff" : C.ink }}>{annual ? p.apr : p.price}</span>
                  <span style={{ fontFamily:F, fontSize:13, color:p.hi ? "rgba(255,255,255,0.4)" : C.inkVar }}>/mo</span>
                </div>
                <Divider style={{ background:p.hi ? "rgba(255,255,255,0.15)" : undefined, marginBottom:20 }} />
                {p.feats.map((f, fi) => (
                  <div key={fi} style={{ display:"flex", gap:9, marginBottom:11, alignItems:"flex-start" }}>
                    <Ic n="check" sz={13} col={p.hi ? "rgba(255,255,255,0.6)" : C.teal} />
                    <span style={{ fontFamily:F, fontSize:13.5, color:p.hi ? "rgba(255,255,255,0.8)" : C.inkVar }}>{f}</span>
                  </div>
                ))}
                <button type="button" onClick={() => nav(p.plan === "Multi-Location" ? "contact" : "signup")}
                  style={{ width:"100%", height:46, marginTop:20, borderRadius:R, border:`1px solid ${p.hi ? "rgba(255,255,255,0.2)" : C.teal}`, background:p.hi ? "rgba(255,255,255,0.12)" : C.teal, color:"#fff", fontWeight:600, fontSize:14, cursor:"pointer", fontFamily:F }}>
                  {p.plan === "Multi-Location" ? "Contact Us" : "Start Free"}
                </button>
              </div>
            ))}
          </div>
        </div>
      </section>
      <section style={{ ...sec, background:C.low }}>
        <div style={{ ...px, maxWidth:720, margin:"0 auto" }}>
          <H2 center style={{ marginBottom:32 }}>Pricing questions</H2>
          {faqs.map((f, i) => (
            <div key={i} style={{ borderBottom:`1px solid ${C.outlineVar}` }}>
              <button type="button" onClick={() => setOpenFaq(openFaq === i ? null : i)} style={{ width:"100%", padding:"16px 0", background:"none", border:"none", cursor:"pointer", display:"flex", justifyContent:"space-between", alignItems:"center", gap:14, fontFamily:F, textAlign:"left" }}>
                <span style={{ fontSize:15, fontWeight:600, color:C.ink }}>{f.q}</span>
                <Ic n={openFaq === i ? "minus" : "plus"} sz={14} col={C.teal} />
              </button>
              {openFaq === i && <div style={{ paddingBottom:16, fontFamily:F, fontSize:14.5, color:C.inkVar, lineHeight:1.75 }}>{f.a}</div>}
            </div>
          ))}
        </div>
      </section>
      <section style={{ ...sec, background:C.teal }}>
        <div style={{ ...px, textAlign:"center" }}>
          <H2 light center style={{ marginBottom:16 }}>Start free today</H2>
          <Body light center style={{ maxWidth:420, margin:"0 auto 28px" }}>No credit card required. Publish your booking website in under 30 minutes.</Body>
          <Btn onClick={() => nav("signup")}>Create Free Account</Btn>
        </div>
      </section>
    </>
  );
}

/* ─── SIMPLE PAGE (Product Tour, Changelog, …) ─────────────── */
function SimplePage({ nav, label, title, body, items = [] }) {
  const { sm, md } = useW();
  const px = { padding:`0 ${sm ? 16 : 24}px`, maxWidth:1200, margin:"0 auto" };

  return (
    <>
      <section style={{ padding:sm ? "52px 0 40px" : "80px 0 56px", background:C.bg, textAlign:"center" }}>
        <div style={{ ...px, maxWidth:680, margin:"0 auto" }}>
          <Label center>{label}</Label>
          <H1 style={{ marginTop:12, marginBottom:16 }}>{title}</H1>
          <Body muted center style={{ fontSize:sm ? 16 : 17 }}>{body}</Body>
          <div style={{ display:"flex", gap:12, justifyContent:"center", flexWrap:"wrap", marginTop:28 }}>
            <Btn onClick={() => nav("signup")}>Start for Free</Btn>
            <Btn v="outline" onClick={() => nav("howItWorks")}>How It Works</Btn>
          </div>
        </div>
      </section>
      <section style={{ padding:sm ? "44px 0" : "72px 0", background:C.low }}>
        <div style={{ ...px }}>
          <div style={{ display:"grid", gridTemplateColumns:sm ? "1fr" : md ? "1fr 1fr" : "repeat(3,1fr)", gap:14 }}>
            {items.map((item, i) => (
              <Card key={i} tealTop style={{ padding:"22px 20px" }}>
                <div style={{ width:42, height:42, borderRadius:R, background:C.tealLight, border:`1px solid ${C.tealBorder}`, display:"flex", alignItems:"center", justifyContent:"center", marginBottom:14, color:C.teal }}>
                  <Ic n={item.icon} sz={20} col={C.teal} />
                </div>
                <div style={{ fontFamily:F, fontSize:15, fontWeight:700, color:C.ink, marginBottom:8 }}>{item.t}</div>
                <div style={{ fontFamily:F, fontSize:13.5, color:C.inkVar, lineHeight:1.6 }}>{item.d}</div>
              </Card>
            ))}
          </div>
        </div>
      </section>
      <section style={{ padding:sm ? "52px 0" : "64px 0", background:C.teal, textAlign:"center" }}>
        <div style={{ ...px }}>
          <Body light center style={{ maxWidth:400, margin:"0 auto 24px" }}>Create your free account and publish your booking website today.</Body>
          <Btn onClick={() => nav("signup")}>Start for Free</Btn>
        </div>
      </section>
    </>
  );
}

/* ─── FEATURES OVERVIEW PAGE ─────────────────────────────── */
function FeaturesOverview({ nav }) {
  const { sm, md } = useW();
  const px = { padding:`0 ${sm ? 16 : 24}px`, maxWidth:1200, margin:"0 auto" };

  const features = [
    { key:"appointments", icon:"calendar", t:"Appointments & Calendar",   d:"Online bookings, walk-ins, and manual entries — one live calendar for your whole team.", tag:"Core" },
    { key:"pos",          icon:"pos",      t:"POS & Billing",              d:"Bill services and retail in under 60 seconds. All payment types. Digital receipts.", tag:"Core" },
    { key:"clients",      icon:"user",     t:"Client Management",         d:"Full visit history, colour notes, spend tracking, loyalty, and preferences per client.", tag:"Core" },
    { key:"staffMgmt",    icon:"users",    t:"Staff Management",          d:"Individual logins, shift scheduling, leave management, and performance tracking.", tag:"Team" },
    { key:"retail",       icon:"bag",      t:"Retail Inventory",           d:"Track stock, low-stock alerts, and retail sales — connected to your POS.", tag:"Revenue" },
    { key:"marketing",    icon:"tag",      t:"Marketing Campaigns",       d:"SMS, email, birthday automation, and re-engagement — all from your client data.", tag:"Growth" },
    { key:"reviews",      icon:"star",     t:"Review Management",         d:"Automatic review requests after every appointment. Protect and build your reputation.", tag:"Trust" },
    { key:"analytics",    icon:"bar",      t:"Analytics & Reports",        d:"Six dashboards: revenue, staff, clients, retail, appointments, website. One app.", tag:"Intelligence" },
    { key:"multiLocation",icon:"map",      t:"Multi-Location Management", d:"All branches from one login. Consolidated analytics. Per-branch booking websites.", tag:"Scale" },
    { key:"website",      icon:"globe",    t:"Free Booking Website",      d:"Hosted, mobile-ready booking website — auto-generated and free on every plan.", tag:"Free" },
  ];

  return (
    <>
      <section style={{ padding:sm ? "52px 0 36px" : "80px 0 56px", background:C.bg, textAlign:"center" }}>
        <div style={{ ...px, maxWidth:720, margin:"0 auto" }}>
          <Chip teal style={{ marginBottom:16 }}>Complete Business Software Ecosystem</Chip>
          <h1 style={{ fontFamily:F, fontSize:"clamp(28px,5vw,48px)", fontWeight:800, lineHeight:1.1, letterSpacing:"-0.02em", color:C.ink, margin:"16px 0 20px" }}>Every tool your beauty business needs. One platform.</h1>
          <p style={{ fontFamily:F, fontSize:sm ? 15.5 : 17, lineHeight:1.72, color:C.inkVar, marginBottom:32 }}>EasyGrox is not a booking tool with extras bolted on. It is a complete business operating system — every feature shares the same client, booking, and analytics data.</p>
          <div style={{ display:"flex", gap:12, justifyContent:"center", flexWrap:"wrap" }}>
            <Btn onClick={() => nav("signup")}>Start for Free</Btn>
            <Btn v="outline" onClick={() => nav("pricing")}>See Pricing</Btn>
          </div>
        </div>
      </section>
      <div style={{ background:C.surface, borderTop:`1px solid ${C.outlineVar}`, borderBottom:`1px solid ${C.outlineVar}` }}>
        <div style={{ ...px, padding:"14px 16px", display:"flex", flexWrap:"wrap", alignItems:"center", justifyContent:"center", gap:sm ? 10 : 28 }}>
          {[{v:"10,000+",l:"Businesses"},{v:"4.9/5",l:"Rating"},{v:"₹0",l:"Website cost"},{v:"30 min",l:"Setup time"}].map((s, i) => (
            <div key={i} style={{ textAlign:"center" }}>
              <div style={{ fontFamily:F, fontSize:18, fontWeight:800, color:C.teal }}>{s.v}</div>
              <div style={{ fontFamily:F, fontSize:11.5, color:C.inkVar }}>{s.l}</div>
            </div>
          ))}
        </div>
      </div>
      <section style={{ padding:sm ? "44px 0" : "72px 0", background:C.low }}>
        <div style={{ ...px }}>
          <div style={{ textAlign:"center", marginBottom:40 }}>
            <Label center>All features</Label>
            <h2 style={{ fontFamily:F, fontSize:"clamp(22px,3.5vw,36px)", fontWeight:800, color:C.ink, textAlign:"center", margin:"10px 0 0", letterSpacing:"-0.015em" }}>Click any feature to explore in depth</h2>
          </div>
          <div style={{ display:"grid", gridTemplateColumns:sm ? "1fr" : md ? "1fr 1fr" : "repeat(3,1fr)", gap:14 }} {...{}}>
            {features.map((f) => (
              <div key={f.key} onClick={() => nav(f.key)}
                style={{ background:C.surface, borderRadius:R, padding:"22px 20px", border:`1px solid ${C.outlineVar}`, cursor:"pointer", transition:"all .2s", boxShadow:C.sh1, display:"flex", gap:14, alignItems:"flex-start" }}
                onMouseEnter={e => { e.currentTarget.style.borderColor = C.teal; e.currentTarget.style.transform = "translateY(-2px)"; e.currentTarget.style.boxShadow = C.sh2; }}
                onMouseLeave={e => { e.currentTarget.style.borderColor = C.outlineVar; e.currentTarget.style.transform = "none"; e.currentTarget.style.boxShadow = C.sh1; }}>
                <div style={{ width:42, height:42, borderRadius:R, background:C.tealLight, border:`1px solid ${C.tealBorder}`, display:"flex", alignItems:"center", justifyContent:"center", color:C.teal, flexShrink:0 }}>
                  <Ic n={f.icon} sz={20} col={C.teal} />
                </div>
                <div style={{ flex:1 }}>
                  <div style={{ display:"flex", justifyContent:"space-between", alignItems:"flex-start", marginBottom:6 }}>
                    <div style={{ fontFamily:F, fontSize:14.5, fontWeight:700, color:C.ink }}>{f.t}</div>
                    <Chip teal sm>{f.tag}</Chip>
                  </div>
                  <div style={{ fontFamily:F, fontSize:13.5, color:C.inkVar, lineHeight:1.55 }}>{f.d}</div>
                </div>
              </div>
            ))}
          </div>
        </div>
      </section>
    </>
  );

}


/* ─── ROOT (production: src/App.jsx + src/constants/routes.js) ── */
export default function App() {
  return (
    <div style={{ fontFamily:F, color:C.ink, minHeight:"100vh", background:C.bg, padding:48, textAlign:"center" }}>
      <p style={{ fontSize:16, marginBottom:12 }}>This file is the content source of truth. Run the site with:</p>
      <code style={{ background:C.surface, padding:"8px 14px", borderRadius:8, fontSize:14 }}>npm run dev</code>
      <p style={{ fontSize:14, color:C.inkVar, marginTop:16 }}>URLs and routing live in <strong>src/App.jsx</strong>.</p>
    </div>
  );
}
