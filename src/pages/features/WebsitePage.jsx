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

export function WebsitePage({ nav }) {
  const { sm, md } = useW();
  const px = { padding:`0 ${sm ? 16 : 24}px`, maxWidth:1200, margin:"0 auto" };
  const SP = sm ? "52px 0" : "80px 0";

  return (
    <FeaturePageShell nav={nav} label="Free Booking Website" title="Your salon. Your website. Zero cost."
      tagline="A professional, mobile-optimised booking website — hosted by EasyGrox at zero cost, auto-generated from your store data, and published with one click. Clients book directly with you, 24 hours a day. No competitors. No marketplace. Just your brand."
      mockup={<WebsiteMock />}
      relatedPages={[{key:"analytics",label:"Analytics"},{key:"appointments",label:"Appointments"},{key:"marketing",label:"Marketing"}]}>

      <section style={{ padding:SP, background:C.low }}>
        <div style={{ ...px }}>
          <PainGain
            pains={["Clients book through Fresha or Booksy — listed alongside all your competitors","Your website developer charges ₹20,000 to build a basic booking page and ₹5,000/year to maintain","Every time you add a service or update a price, you have to message the developer or update manually","Clients call after 8pm to book — you miss the call, they book somewhere else","No visibility into how many people visit your online presence or whether they are booking","Marketplace takes a commission on every booking through their platform"]}
            gains={["Your own branded booking website with your logo — no competitors visible anywhere on the page","Completely free — no hosting fee, no domain purchase, no developer, no renewals ever","Update a price inside EasyGrox and your website reflects it in seconds — fully automatic","Clients book at midnight, weekends, or during service — 24/7 with no intervention","Full visitor analytics in your EasyGrox dashboard — traffic sources, conversion rate, top pages","Zero commission — clients book directly with you, every rupee stays in your business"]}
          />
        </div>
      </section>

      <section style={{ padding:SP, background:C.bg }}>
        <div style={{ ...px }}>
          <div style={{ display:"grid", gridTemplateColumns:md ? "1fr" : "1fr 1fr", gap:64, alignItems:"start" }}>
            <div>
              <Label>How your website goes live</Label>
              <H2 style={{ marginBottom:14, marginTop:10 }}>From zero to published in under 30 minutes</H2>
              <Body muted style={{ marginBottom:32 }}>Your booking website is built automatically from your store data. The moment you complete setup, it is ready to publish.</Body>
              <WorkflowStep n="1" title="Complete your store setup in the app" desc="Add your business details, logo, services with prices, staff profiles, and opening hours." note="Inside the app" />
              <WorkflowStep n="2" title="Preview your website" desc="Your website is auto-generated and ready to preview inside the app. See exactly what clients will see." note="Preview button" />
              <WorkflowStep n="3" title="Set your custom URL" desc="Choose yoursalon.easygrox.com as your URL. Or connect your own domain in one setting — no technical steps." note="Free URL included" />
              <WorkflowStep n="4" title="Click Publish" desc="One click. Your website is live, hosted on fast and secure servers, mobile-optimised and accepting bookings immediately." note="Goes live instantly" />
              <WorkflowStep n="5" title="Share your booking link" desc="Add the link to your Instagram bio, WhatsApp status, Google Business Profile, and all your marketing materials." note="Your clients book" />
              <WorkflowStep n="6" title="Watch bookings arrive" desc="Every booking from your website appears instantly in your calendar — assigned to the correct staff, reminder sent automatically." note="Fully automated" final />
            </div>
            <div style={{ position:"sticky", top:80 }}>
              <WebsiteMock />
              <div style={{ marginTop:16, display:"grid", gridTemplateColumns:"1fr 1fr", gap:10 }}>
                <StatBadge value="₹0" label="Hosting cost ever" good />
                <StatBadge value="24/7" label="Booking availability" good />
                <StatBadge value="1 click" label="To publish" good />
                <StatBadge value="0" label="Developers needed" good />
              </div>
            </div>
          </div>
        </div>
      </section>

      <section style={{ padding:SP, background:C.low }}>
        <div style={{ ...px }}>
          <div style={{ textAlign:"center", marginBottom:40 }}>
            <Label center>What your website includes</Label>
            <H2 center>A complete booking website — free forever</H2>
          </div>
          <div style={{ display:"grid", gridTemplateColumns:sm ? "1fr" : md ? "1fr 1fr" : "repeat(3,1fr)", gap:14 }}>
            {[
              { n:"link",    t:"Free Custom URL",      d:"yoursalon.easygrox.com. Connect your own domain anytime with one setting." },
              { n:"shield",  t:"Free Hosting Forever", d:"Hosted on fast, secure, SSL-encrypted servers. No bill. No renewal. Ever." },
              { n:"refresh", t:"Always Up-to-Date",    d:"Change a price, add a service, update your hours — website updates in seconds." },
              { n:"calendar",t:"Live Online Booking",  d:"Clients browse services, choose a stylist, pick a time, and confirm — all on your website." },
              { n:"globe",   t:"Google-Indexable",     d:"Your service pages are indexable by Google. New clients search and find you organically." },
              { n:"bar",     t:"Visitor Analytics",    d:"Every visit, traffic source, and booking tracked automatically in your EasyGrox dashboard." },
              { n:"users",   t:"Staff Profiles",       d:"Each staff member has their own page with photo, specialisations, and available times." },
              { n:"star",    t:"Reviews Showcased",    d:"Your top client reviews displayed to build confidence in new visitors." },
              { n:"map",     t:"Per-Branch Websites",  d:"Multi-location businesses get a separate website per branch — each with its own URL." },
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

      <section style={{ padding:SP, background:C.bg }}>
        <div style={{ ...px }}>
          <div style={{ textAlign:"center", marginBottom:40 }}>
            <Label center>Website vs marketplace comparison</Label>
            <H2 center>Why your own website beats a directory listing</H2>
          </div>
          <div style={{ display:"grid", gridTemplateColumns:sm ? "1fr" : "1fr 1fr", gap:16 }}>
            <Card style={{ padding:"28px 24px", border:`1px solid ${C.outlineVar}` }}>
              <div style={{ fontFamily:F, fontSize:11, fontWeight:700, letterSpacing:"0.08em", textTransform:"uppercase", color:C.err, marginBottom:18 }}>Fresha / Booksy Directory</div>
              {["Your salon listed alongside every local competitor","Clients browse by price — you compete to be cheapest","Platform takes commission on every booking","Your client data belongs to the platform — not you","Platform controls your profile, branding, and visibility","Zero control over how your brand is presented"].map((p, i) => (
                <div key={i} style={{ display:"flex", gap:9, marginBottom:12, alignItems:"flex-start" }}>
                  <Ic n="xmark" sz={14} col={C.err} style={{ marginTop:2, flexShrink:0 }} />
                  <Body sm muted style={{ lineHeight:1.55 }}>{p}</Body>
                </div>
              ))}
            </Card>
            <Card ink style={{ padding:"28px 24px", border:"none" }}>
              <div style={{ fontFamily:F, fontSize:11, fontWeight:700, letterSpacing:"0.08em", textTransform:"uppercase", color:"rgba(255,255,255,0.45)", marginBottom:18 }}>Your EasyGrox Booking Website</div>
              {["Your salon — no competitors visible anywhere on your page","Clients choose you because they found you directly","Zero commission — every rupee stays in your business","Your client data — always exportable, always yours","Your logo, your brand, your URL, your domain","Full control over services, prices, photos, and messaging"].map((g, i) => (
                <div key={i} style={{ display:"flex", gap:9, marginBottom:12, alignItems:"flex-start" }}>
                  <div style={{ width:18, height:18, borderRadius:5, background:"rgba(255,255,255,0.15)", border:"1px solid rgba(255,255,255,0.2)", display:"flex", alignItems:"center", justifyContent:"center", flexShrink:0, marginTop:1 }}>
                    <Ic n="check" sz={10} col="#fff" />
                  </div>
                  <Body sm light style={{ lineHeight:1.55 }}>{g}</Body>
                </div>
              ))}
            </Card>
          </div>
        </div>
      </section>

      <section style={{ padding:SP, background:C.low }}>
        <div style={{ ...px }}>
          <div style={{ textAlign:"center", marginBottom:40 }}>
            <Label center>Real results</Label>
            <H2 center>What happens when clients can book online</H2>
          </div>
          <div style={{ display:"grid", gridTemplateColumns:sm ? "1fr" : md ? "1fr 1fr" : "repeat(3,1fr)", gap:16 }}>
            <Quote metric="+8 bookings per week" text="I added my EasyGrox link to my Instagram bio and within 2 weeks I was getting 8 extra bookings per week from people who found me on Instagram at 9pm when I was already closed." name="Priya Sharma" role="Luxe Hair Studio, Jaipur" />
            <Quote metric="48% of bookings now online" text="Half my bookings now come through the website without me being involved. I wake up to a confirmed calendar. The manual booking phone calls have almost stopped." name="Ravi Nair" role="The Barbers Co., Kochi" />
            <Quote metric="Zero website maintenance cost" text="I was paying a developer ₹5,000 a month to maintain my old website. My EasyGrox website updates itself when I change prices. The saving paid for my EasyGrox subscription 2.5 times over." name="Meena Kapoor" role="Serenity Spa, Mumbai" />
          </div>
        </div>
      </section>
    </FeaturePageShell>
  );
}
