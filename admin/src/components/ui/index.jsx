import React, { useState } from "react";
import { C, F, R } from "../../constants/theme";
import { Ic } from "../icons/Icon";

export const Chip = ({ children, teal, warn, sm, style = {} }) => (
  <span style={{ display:"inline-flex", alignItems:"center", gap:4, background:teal ? C.tealLight : warn ? C.warnBg : C.mid, color:teal ? C.teal : warn ? C.warn : C.inkVar, border:`1px solid ${teal ? C.tealBorder : warn ? C.warnBorder : C.outlineVar}`, borderRadius:9999, fontSize:sm ? 11 : 12, fontWeight:600, fontFamily:F, padding:sm ? "3px 10px" : "4px 13px", letterSpacing:"0.03em", ...style }}>
    {children}
  </span>
);

export const Label = ({ children, center, light }) => (
  <div style={{ fontFamily:F, fontSize:11.5, fontWeight:700, letterSpacing:"0.08em", textTransform:"uppercase", color:light ? "rgba(255,255,255,0.5)" : C.teal, marginBottom:10, textAlign:center ? "center" : undefined }}>
    {children}
  </div>
);

export function H1({ children, style = {} }) {
  return <h1 style={{ fontFamily:F, fontSize:"clamp(28px,5vw,52px)", fontWeight:800, lineHeight:1.1, letterSpacing:"-0.02em", color:C.ink, margin:0, ...style }}>{children}</h1>;
}
export function H2({ children, light, center, style = {} }) {
  return <h2 style={{ fontFamily:F, fontSize:"clamp(22px,3.5vw,38px)", fontWeight:800, lineHeight:1.15, letterSpacing:"-0.015em", color:light ? "#fff" : C.ink, margin:0, textAlign:center ? "center" : undefined, ...style }}>{children}</h2>;
}
export function H3({ children, light, style = {} }) {
  return <h3 style={{ fontFamily:F, fontSize:"clamp(17px,2.2vw,22px)", fontWeight:700, lineHeight:1.3, color:light ? "#fff" : C.ink, margin:0, ...style }}>{children}</h3>;
}
export function Body({ children, muted, light, sm, center, style = {} }) {
  return <p style={{ fontFamily:F, fontSize:sm ? 13.5 : 15.5, lineHeight:1.7, color:light ? "rgba(255,255,255,0.7)" : muted ? C.inkVar : C.ink, margin:0, textAlign:center ? "center" : undefined, ...style }}>{children}</p>;
}
export function H4({ children, style = {} }) {
  return <h4 style={{ fontFamily:F, fontSize:16, fontWeight:700, color:C.ink, margin:0, ...style }}>{children}</h4>;
}
export const Div = ({ style = {} }) => <div style={{ height:1, background:C.outlineVar, opacity:0.5, ...style }} />;

export function Btn({ children, v = "primary", onClick, full, sm, style = {} }) {
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

export const Divider = ({ style = {} }) => <div style={{ height:1, background:C.outlineVar, opacity:0.5, ...style }} />;

export function Card({ children, style = {}, tealTop, tealBorder: tb, ink }) {
  const [hov, setHov] = useState(false);
  return (
    <div onMouseEnter={() => setHov(true)} onMouseLeave={() => setHov(false)}
      style={{ background:ink ? C.teal : C.surface, borderRadius:R, border:`1px solid ${tb ? C.teal : ink ? "transparent" : C.outlineVar}`, padding:"24px 20px", transition:"all .2s", boxShadow:hov ? C.sh2 : C.sh1, borderTop:tealTop ? `3px solid ${C.teal}` : undefined, ...style }}>
      {children}
    </div>
  );
}

export const IcBox = ({ n, big }) => (
  <div style={{ width:big ? 52 : 44, height:big ? 52 : 44, borderRadius:R, background:C.tealLight, border:`1px solid ${C.tealBorder}`, display:"flex", alignItems:"center", justifyContent:"center", marginBottom:14, color:C.teal, flexShrink:0 }}>
    <Ic n={n} sz={big ? 24 : 20} col={C.teal} />
  </div>
);

