import React from "react";
import { C, F, R } from "../../constants/theme";
import { useW } from "../../hooks/useWindowWidth";
import { Ic } from "../icons/Icon";
import { Label, H1, H2, H3, Body, Btn } from "../ui";

export function FeaturePageShell({ nav, title, label, tagline, mockup, children, relatedPages }) {
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
