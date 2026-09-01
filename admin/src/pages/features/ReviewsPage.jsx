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

export function ReviewsPage({ nav }) {
  const { sm, md } = useW();
  const px = { padding:`0 ${sm ? 16 : 24}px`, maxWidth:1200, margin:"0 auto" };
  const SP = sm ? "52px 0" : "80px 0";

  return (
    <FeaturePageShell nav={nav} label="Review Management" title="Build your reputation. Automatically."
      tagline="Every completed appointment triggers a review request at the right moment. Collect ratings, spot issues before they become problems, and showcase your reputation on your booking website."
      mockup={<ClientMock />}
      relatedPages={[{key:"clients",label:"Client Management"},{key:"marketing",label:"Marketing"},{key:"website",label:"Free Booking Website"}]}>

      <section style={{ padding:SP, background:C.low }}>
        <div style={{ ...px }}>
          <PainGain
            pains={["Happy clients never leave reviews — only unhappy ones do — your rating suffers","Manually asking every client for a review is awkward and gets forgotten","Negative review appears on Google — you find out 2 weeks later with no context","No idea which staff member gets the best client feedback","Reviews scattered across Google, WhatsApp, and social — no consolidated view","Cannot showcase your review reputation on your booking website"]}
            gains={["Automatic review request sent after every appointment — at the perfect moment","Rating collected inside EasyGrox before client is asked to share publicly","Poor rating triggers an internal alert — you can resolve the issue before it goes online","Rating tracked per staff member — see who clients love and who needs coaching","All reviews in one dashboard — respond, track, and analyze from one screen","Top reviews displayed on your free booking website to build booking confidence"]}
          />
        </div>
      </section>

      <section style={{ padding:SP, background:C.bg }}>
        <div style={{ ...px }}>
          <div style={{ textAlign:"center", marginBottom:40 }}>
            <Label center>Review workflow</Label>
            <H2 center>How the reputation engine works</H2>
          </div>
          <div style={{ maxWidth:640, margin:"0 auto" }}>
            <WorkflowStep n="1" title="Appointment marked complete" desc="Staff taps Complete on the appointment card. Service confirmed, billing triggered." note="Automatic trigger" />
            <WorkflowStep n="2" title="Review request sent via WhatsApp" desc="Client receives a short message: How was your visit today? Rate with one tap." note="1-5 star rating" />
            <WorkflowStep n="3" title="If 5 stars — publicly share" desc="Client prompted to share the rating on Google or your booking website. High-satisfaction clients go public." note="Builds online presence" />
            <WorkflowStep n="4" title="If 3 stars or below — internal alert" desc="You see the low rating in your dashboard immediately. Reach out to resolve before it impacts your public reputation." note="Protect reputation" />
            <WorkflowStep n="5" title="Review appears in your dashboard" desc="All ratings tracked per staff member and overall. Average rating updated in real time." note="Staff performance" />
            <WorkflowStep n="6" title="Top reviews shown on booking website" desc="Best reviews displayed on your EasyGrox booking website — building social proof for new clients deciding whether to book." note="Converts visitors to bookings" final />
          </div>
        </div>
      </section>
    </FeaturePageShell>
  );
}

/* ═══════════════════════════════════════════════════════════
   8. REPORTS & ANALYTICS
═══════════════════════════════════════════════════════════ */
