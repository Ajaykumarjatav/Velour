import React, { useState } from "react";
import { C, F, R } from "../../constants/theme";
import { useW } from "../../hooks/useWindowWidth";
import { Ic } from "../../components/icons/Icon";
import { Chip, Label, H2, H3, H4, Body, Btn, Card, Divider, IcBox } from "../../components/ui";
import { FeaturePageShell } from "../../components/layout/FeaturePageShell";
import {
  StatBadge, WorkflowStep, PainGain, Scenario, Quote,
  CalendarMock, POSMock, AnalyticsMock, ClientMock, StaffMock, WebsiteMock,
} from "../../components/shared/marketingBlocks";

export function AnalyticsPage({ nav }) {
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
