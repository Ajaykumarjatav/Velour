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

export function RetailPage({ nav }) {
  const { sm, md } = useW();
  const px = { padding:`0 ${sm ? 16 : 24}px`, maxWidth:1200, margin:"0 auto" };
  const SP = sm ? "52px 0" : "80px 0";

  return (
    <FeaturePageShell nav={nav} label="Retail Inventory" title="Know exactly what is on your shelves. Always."
      tagline="Track every product, get low-stock alerts before you run out, see which products and staff generate the most retail revenue — all connected to your POS so inventory updates the moment a sale is made."
      mockup={<AnalyticsMock />}
      relatedPages={[{key:"pos",label:"POS & Billing"},{key:"analytics",label:"Analytics"},{key:"clients",label:"Client Management"}]}>

      <section style={{ padding:SP, background:C.low }}>
        <div style={{ ...px }}>
          <PainGain
            pains={["Mid-service you reach for a product and the bottle is empty — client waiting","No system to track which products are sold — reorder based on guessing","Best-selling shampoo runs out on a Saturday — no alert, no backup plan","Staff sell retail informally — no record, no commission tracking","Retail margin invisible — you do not know which products make you money","Overstock of slow-moving product taking up shelf space and locking up cash"]}
            gains={["Low-stock alert fires before you run out — reorder with days to spare","Every retail sale logged at POS — complete sales history per product","Stock threshold set per product — automatic alert when quantity drops below minimum","Retail sales tracked per staff member — see who recommends and sells most","Cost price and selling price tracked — gross margin calculated automatically","Slow-mover report shows which products to discount or stop stocking"]}
          />
        </div>
      </section>

      <section style={{ padding:SP, background:C.bg }}>
        <div style={{ ...px }}>
          <div style={{ textAlign:"center", marginBottom:40 }}>
            <Label center>Full capability set</Label>
            <H2 center>Everything inside Retail and Inventory</H2>
          </div>
          <div style={{ display:"grid", gridTemplateColumns:sm ? "1fr" : md ? "1fr 1fr" : "repeat(4,1fr)", gap:14 }}>
            {[
              { n:"bag",     t:"Product Catalogue",      d:"Add every product with name, brand, category, price, cost, and stock level." },
              { n:"bell",    t:"Low-Stock Alerts",       d:"Set minimum quantity per product. Instant alert on your dashboard when hit." },
              { n:"bar",     t:"Sales Analytics",        d:"Best-sellers by units and revenue. Retail as % of total business." },
              { n:"refresh", t:"Stock Adjustments",      d:"Log arrivals, breakage, returns. Every movement tracked with timestamp." },
              { n:"users",   t:"Staff Sales Tracking",   d:"Which team member sells the most retail — by product and by value." },
              { n:"pos",     t:"POS Integration",        d:"Sell at POS — inventory deducts automatically. No double entry." },
              { n:"trend",   t:"Margin Tracking",        d:"Cost vs. selling price per product. Know your most profitable lines." },
              { n:"repeat",  t:"Reorder Suggestions",    d:"Based on sales velocity — EasyGrox recommends reorder quantities." },
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
   6. MARKETING MANAGEMENT
═══════════════════════════════════════════════════════════ */
