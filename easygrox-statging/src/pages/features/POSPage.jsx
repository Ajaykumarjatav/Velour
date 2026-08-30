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

export function POSPage({ nav }) {
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
