import React from "react";
import { C, F, R } from "../constants/theme";
import { useW } from "../hooks/useWindowWidth";
import { Ic } from "../components/icons/Icon";
import { Chip, Label, Btn } from "../components/ui";

export function FeaturesOverview({ nav }) {
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
