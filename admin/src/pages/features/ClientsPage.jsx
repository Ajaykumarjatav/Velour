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

export function ClientsPage({ nav }) {
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
