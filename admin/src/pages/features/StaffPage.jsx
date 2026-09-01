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

export function StaffPage({ nav }) {
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
