import React, { useState } from "react";
import { C, F, R } from "../constants/theme";
import { useW } from "../hooks/useWindowWidth";
import { Ic } from "../components/icons/Icon";
import { Chip, Label, H1, H2, H3, H4, Body, Btn, Card, Divider, IcBox } from "../components/ui";

export function About({ nav }) {
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

