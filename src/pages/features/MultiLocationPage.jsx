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

export function MultiLocationPage({ nav }) {
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
