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

export function MarketingPage({ nav }) {
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
