import React, { useState } from "react";
import { C, F, R } from "../constants/theme";
import { useW } from "../hooks/useWindowWidth";
import { Ic } from "../components/icons/Icon";
import { Chip, Label, H1, H2, H3, Body, Btn, Card, Divider } from "../components/ui";
import { DashMock } from "../components/mockups/DashMock";
import { BookingMock } from "../components/mockups/BookingMock";

export function Home({ nav }) {
  const { sm, md } = useW();
  const [tab, setTab] = useState(0);
  const [openFaq, setOpenFaq] = useState(null);
  const [annual, setAnnual] = useState(false);

  const features = [
    { n:"calendar", t:"Appointment Calendar",  d:"Online bookings, walk-ins, and manual entries — one colour-coded calendar for your whole team." },
    { n:"users",    t:"Staff Management",       d:"Individual logins, shift scheduling, leave tracking, role-based access, and personal dashboards." },
    { n:"pos",      t:"POS & Billing",          d:"Bill services and retail in under 60 seconds. All payment types. Digital receipts instantly." },
    { n:"user",     t:"Client Management",      d:"Full visit history, colour notes, spend tracking, preferences, and loyalty per client." },
    { n:"bag",      t:"Retail Inventory",       d:"Track stock, get low-stock alerts, and sell products at the POS in the same transaction." },
    { n:"globe",    t:"Free Booking Website",   d:"Hosted, mobile-ready, Google-indexed booking website — auto-generated and free on every plan.", free:true },
    { n:"bar",      t:"Website Analytics",      d:"Visitors, traffic sources, top pages, and booking conversion — tracked automatically." },
    { n:"trend",    t:"Business Analytics",     d:"Revenue, staff, clients, retail, appointments — six dashboards in one app." },
    { n:"bell",     t:"Automated Reminders",    d:"SMS and WhatsApp confirmations and reminders. Salons report 50-60% fewer no-shows." },
    { n:"tag",      t:"Marketing Campaigns",    d:"SMS campaigns, birthday offers, and re-engagement — built into your dashboard." },
  ];

  const bizTypes = [
    { icon:"scissors", t:"Hair Salon",    d:"Stylists, colour, retail" },
    { icon:"razor",    t:"Barber Shop",   d:"Queues, cuts, billing" },
    { icon:"nail",     t:"Nail Studio",   d:"Technicians, gel, art" },
    { icon:"lotus",    t:"Spa & Massage", d:"Rooms, therapists, packages" },
    { icon:"lipstick", t:"Makeup Artist", d:"Sessions, bridal, kit" },
    { icon:"ink",      t:"Tattoo Studio", d:"Artists, deposits, artwork" },
    { icon:"paw",      t:"Pet Grooming",  d:"Pets, profiles, recurring" },
  ];

  const tabs = [
    { label:"Dashboard",    pts:["Today revenue, appointments, website visits","KPI cards updated in real time","Weekly revenue bar chart","Upcoming appointment list with status","Quick actions from the home screen"] },
    { label:"Calendar",     pts:["All bookings across all staff in one view","Online, walk-in, and manual — all unified","Drag-and-drop rescheduling with SMS to client","Staff column view — see everyone side by side","Switch between daily, weekly, and monthly"] },
    { label:"POS",          pts:["Service pre-filled from appointment card","Bill services and retail together","Cash, card, UPI, wallet — all accepted","Digital receipt via WhatsApp or SMS","End-of-day revenue and cash summary"] },
    { label:"Analytics",    pts:["Revenue, appointments, staff, clients, retail","Website visitor data alongside business data","Booking conversion rate from your website","Staff utilisation and performance comparison","Period-over-period trend built in"] },
  ];

  const plans = [
    { plan:"Solo",    for:"Independent stylists", price:"₹799",  apr:"₹666",  feats:["1 store · 1 staff login","Appointment calendar & POS","Client management","Automated SMS reminders","Free hosted booking website","Website visitor analytics"], hi:false },
    { plan:"Business",for:"Salons with a team",   price:"₹1,999",apr:"₹1,666", feats:["Everything in Solo","Up to 20 staff members","Shift scheduling & leave","Retail inventory","Full analytics (6 dashboards)","SMS & email campaigns","Free website + full analytics"], hi:true },
    { plan:"Multi-Location",for:"Multiple branches",price:"₹3,999",apr:"₹3,332",feats:["Everything in Business","Unlimited branches","Website per branch","Consolidated analytics","Priority support"], hi:false },
  ];

  const testimonials = [
    { q:"Before EasyGrox I managed bookings on WhatsApp. Now clients book through my website, my team has their schedules, and I see exactly what each stylist earned this month.", n:"Priya Sharma", r:"Luxe Hair Studio, Jaipur" },
    { q:"The free booking website was a genuine surprise. Analytics showing where my traffic comes from helped me put the link in my Instagram bio and watch conversions climb.", n:"Ravi Nair",   r:"The Barbers Co., Kochi" },
    { q:"Managing 3 spa branches from one login — each with its own website and reports — is something no previous software could do at this price.", n:"Meena Kapoor", r:"Serenity Spa, Mumbai" },
  ];

  const faqs = [
    { q:"Is the booking website really free forever?", a:"Yes. Every plan — including Solo — includes a fully hosted, mobile-responsive booking website at zero extra cost. No domain purchase, no hosting fee, no renewals ever." },
    { q:"How is EasyGrox different from Fresha or Booksy?", a:"Fresha and Booksy are marketplaces — your salon listed alongside competitors. EasyGrox gives you your own branded website. Clients come directly to you. Your data, your brand." },
    { q:"How long does setup take?", a:"Most salon owners complete setup and publish their booking website within 20-30 minutes. The app guides you step by step with a progress checklist." },
    { q:"Can I manage multiple branches?", a:"Yes. Multi-Location plan gives every branch its own website, dashboard, and analytics — all from one login." },
    { q:"Do I need to download an app?", a:"No. EasyGrox is a Progressive Web App that works in any browser on any device — phone, tablet, or desktop. No download needed." },
  ];

  const sec = { padding:sm ? "56px 0" : "88px 0" };
  const px = { padding:sm ? "0 16px" : "0 24px", maxWidth:1200, margin:"0 auto" };

  return (
    <>
      {/* HERO */}
      <section style={{ padding:sm ? "56px 0 48px" : "88px 0 72px", background:C.bg }}>
        <div style={{ ...px, display:"grid", gridTemplateColumns:md ? "1fr" : "1fr 1fr", gap:md ? 40 : 64, alignItems:"center" }}>
          <div>
            <Chip teal style={{ marginBottom:18 }}>Business management, elevated</Chip>
            <H1 style={{ marginBottom:18, marginTop:14 }}>Run your calendar, clients, and team in one calm place</H1>
            <Body muted style={{ fontSize:sm ? 16 : 18, marginBottom:32, lineHeight:1.7 }}>
              Appointments, staff, billing, inventory, marketing — and a <strong style={{ color:C.teal }}>free hosted booking website</strong> with built-in analytics. One login. Set up in 30 minutes.
            </Body>
            <div style={{ display:"flex", gap:12, marginBottom:28, flexWrap:"wrap" }}>
              <Btn onClick={() => nav("signup")}>Start for Free</Btn>
              <Btn v="outline">Watch Demo</Btn>
            </div>
            <div style={{ display:"flex", gap:8, flexWrap:"wrap" }}>
              {["No credit card","Free website included","30 min setup"].map((t, i) => (
                <span key={i} style={{ display:"flex", alignItems:"center", gap:5, fontFamily:F, fontSize:12.5, color:C.inkVar, background:C.surface, border:`1px solid ${C.outlineVar}`, borderRadius:9999, padding:"4px 12px", boxShadow:C.sh1 }}>
                  <Ic n="check" sz={11} col={C.teal} /> {t}
                </span>
              ))}
            </div>
          </div>
          <div style={{ position:"relative", paddingBottom:md ? 0 : 28 }}>
            <DashMock />
            {!md && (
              <div style={{ position:"absolute", bottom:0, right:-12 }}>
                <BookingMock />
              </div>
            )}
          </div>
          {md && (
            <div style={{ display:"flex", justifyContent:"center" }}>
              <BookingMock />
            </div>
          )}
        </div>
      </section>

      {/* PROOF BAR */}
      <div style={{ background:C.surface, borderTop:`1px solid ${C.outlineVar}`, borderBottom:`1px solid ${C.outlineVar}` }}>
        <div style={{ ...px, padding:"14px 16px" }}>
          <div style={{ display:"flex", flexWrap:"wrap", alignItems:"center", justifyContent:"center", gap:sm ? 14 : 32 }}>
            <div style={{ display:"flex", alignItems:"center", gap:6 }}>
              <div style={{ display:"flex", gap:1 }}>{[1,2,3,4,5].map(s => <Ic key={s} n="star" sz={14} col={C.teal} />)}</div>
              <span style={{ fontFamily:F, fontSize:13, color:C.inkVar, marginLeft:4 }}>4.9 / 5 · 2,000+ reviews</span>
            </div>
            {!sm && <div style={{ width:1, height:18, background:C.outlineVar }} />}
            <span style={{ fontFamily:F, fontSize:13, color:C.inkVar }}>Trusted by <strong style={{ color:C.ink }}>10,000+</strong> beauty businesses</span>
          </div>
        </div>
      </div>

      {/* BUSINESS TYPES */}
      <section style={{ ...sec, background:C.low }}>
        <div style={{ ...px }}>
          <div style={{ textAlign:"center", marginBottom:40 }}>
            <Label center>Specialised for your business</Label>
            <H2 center>Built for every beauty and wellness business</H2>
          </div>
          <div style={{ display:"grid", gridTemplateColumns:sm ? "repeat(4,1fr)" : "repeat(7,1fr)", gap:10 }}>
            {bizTypes.map((b, i) => (
              <div key={i}
                style={{ background:C.surface, borderRadius:R, padding:sm ? "14px 6px" : "20px 8px", textAlign:"center", cursor:"pointer", boxShadow:C.sh1, border:`1px solid ${C.outlineVar}`, transition:"all .2s" }}
                onMouseEnter={e => { e.currentTarget.style.borderColor = C.teal; e.currentTarget.style.transform = "translateY(-3px)"; e.currentTarget.style.boxShadow = C.sh2; }}
                onMouseLeave={e => { e.currentTarget.style.borderColor = C.outlineVar; e.currentTarget.style.transform = "none"; e.currentTarget.style.boxShadow = C.sh1; }}
                onClick={() => nav("bizType", { type:b.t })}>
                <div style={{ display:"flex", justifyContent:"center", marginBottom:8 }}>
                  <Ic n={b.icon} sz={sm ? 20 : 24} col={C.teal} />
                </div>
                <div style={{ fontFamily:F, fontSize:sm ? 10.5 : 12, fontWeight:700, color:C.ink }}>{b.t}</div>
                {!sm && <div style={{ fontFamily:F, fontSize:10, color:C.inkVar, marginTop:2, lineHeight:1.4 }}>{b.d}</div>}
              </div>
            ))}
          </div>
        </div>
      </section>

      <Divider />

      {/* FREE WEBSITE */}
      <section style={{ ...sec, background:C.bg }}>
        <div style={{ ...px }}>
          <div style={{ display:"grid", gridTemplateColumns:md ? "1fr" : "1fr auto", gap:md ? 32 : 64, alignItems:"center" }}>
            <div>
              <Label>Free — Every Plan</Label>
              <H2 style={{ marginBottom:18, marginTop:10 }}>Your salon gets a free booking website — hosted by us</H2>
              <Body muted style={{ marginBottom:28, fontSize:sm ? 15 : 17 }}>
                No domain purchase. No hosting fee. No developer. Complete your store setup in the app — your booking website is ready to publish with one click.
              </Body>
              <div style={{ display:"grid", gridTemplateColumns:sm ? "1fr" : "1fr 1fr", gap:14, marginBottom:28 }}>
                {[
                  { n:"link",    t:"Free Custom URL",      d:"yoursalon.easygrox.com or connect your own domain." },
                  { n:"shield",  t:"Free Hosting Forever", d:"Fast, secure servers — zero cost, zero renewals." },
                  { n:"refresh", t:"Always Up-to-Date",    d:"Change a price — website updates in seconds." },
                  { n:"bar",     t:"Built-in Analytics",   d:"Visitors, sources, conversion tracked automatically." },
                  { n:"bell",    t:"Online Booking 24/7",  d:"Clients book at any hour without calling you." },
                  { n:"globe",   t:"Google-Indexable",     d:"New clients find you in Google Search." },
                ].map((f, i) => (
                  <div key={i} style={{ display:"flex", gap:10, alignItems:"flex-start" }}>
                    <div style={{ width:34, height:34, borderRadius:R, background:C.tealLight, border:`1px solid ${C.tealBorder}`, display:"flex", alignItems:"center", justifyContent:"center", flexShrink:0 }}>
                      <Ic n={f.n} sz={15} col={C.teal} />
                    </div>
                    <div>
                      <div style={{ fontFamily:F, fontSize:13.5, fontWeight:600, color:C.ink, marginBottom:2 }}>{f.t}</div>
                      <div style={{ fontFamily:F, fontSize:12.5, color:C.inkVar, lineHeight:1.55 }}>{f.d}</div>
                    </div>
                  </div>
                ))}
              </div>
              <Btn v="outline" onClick={() => nav("website")}>Learn More About the Free Website</Btn>
            </div>
            <div style={{ display:"flex", justifyContent:"center" }}>
              <BookingMock />
            </div>
          </div>
        </div>
      </section>

      {/* FEATURES GRID */}
      <section style={{ ...sec, background:C.low }}>
        <div style={{ ...px }}>
          <div style={{ textAlign:"center", marginBottom:44 }}>
            <Label center>Everything connected</Label>
            <H2 center>Ten tools. One login.</H2>
            <Body muted center style={{ maxWidth:520, margin:"12px auto 0", fontSize:sm ? 15 : 16 }}>One connected system — every feature shares the same client, booking, and analytics data. No stitching tools together.</Body>
          </div>
          <div style={{ display:"grid", gridTemplateColumns:sm ? "1fr 1fr" : md ? "repeat(3,1fr)" : "repeat(5,1fr)", gap:12 }}>
            {features.map((f, i) => (
              <div key={i}
                style={{ background:f.free ? C.teal : C.surface, borderRadius:R, padding:"20px 16px", border:`1px solid ${f.free ? C.teal : C.outlineVar}`, cursor:"pointer", transition:"all .2s", position:"relative", boxShadow:f.free ? "0 8px 24px rgba(0,101,101,0.25)" : C.sh1 }}
                onMouseEnter={e => { if (!f.free) { e.currentTarget.style.borderColor = C.teal; e.currentTarget.style.transform = "translateY(-2px)"; e.currentTarget.style.boxShadow = C.sh2; } }}
                onMouseLeave={e => { if (!f.free) { e.currentTarget.style.borderColor = C.outlineVar; e.currentTarget.style.transform = "none"; e.currentTarget.style.boxShadow = C.sh1; } }}>
                {f.free && <div style={{ position:"absolute", top:10, right:12 }}><Chip sm style={{ background:"rgba(255,255,255,0.18)", color:"rgba(255,255,255,0.8)", border:"1px solid rgba(255,255,255,0.25)" }}>Free</Chip></div>}
                <div style={{ color:f.free ? "#fff" : C.teal, marginBottom:10 }}><Ic n={f.n} sz={20} col={f.free ? "#fff" : C.teal} /></div>
                <div style={{ fontFamily:F, fontSize:13.5, fontWeight:700, color:f.free ? "#fff" : C.ink, marginBottom:5 }}>{f.t}</div>
                <div style={{ fontFamily:F, fontSize:12.5, color:f.free ? "rgba(255,255,255,0.62)" : C.inkVar, lineHeight:1.55 }}>{f.d}</div>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* PRODUCT SHOWCASE */}
      <section style={{ ...sec, background:C.bg }}>
        <div style={{ ...px }}>
          <div style={{ textAlign:"center", marginBottom:36 }}>
            <Label center>Real screens. Real workflow.</Label>
            <H2 center>The app in action</H2>
          </div>
          <div style={{ display:"flex", gap:8, marginBottom:28, justifyContent:"center", flexWrap:"wrap" }}>
            {tabs.map((t, i) => (
              <button key={i} onClick={() => setTab(i)} style={{ padding:"9px 18px", borderRadius:9999, border:`1.5px solid ${tab === i ? C.teal : C.outlineVar}`, background:tab === i ? C.teal : C.surface, color:tab === i ? "#fff" : C.inkVar, fontWeight:600, fontSize:sm ? 13 : 13.5, cursor:"pointer", transition:"all .18s", fontFamily:F, boxShadow:tab === i ? "0 4px 16px rgba(0,101,101,0.25)" : C.sh1 }}>
                {t.label}
              </button>
            ))}
          </div>
          <div style={{ display:"grid", gridTemplateColumns:md ? "1fr" : "1fr 1fr", gap:md ? 28 : 56, alignItems:"start" }}>
            {!md && <DashMock compact />}
            <div>
              <H3 style={{ marginBottom:8, color:C.teal }}>{tabs[tab].label}</H3>
              <Divider style={{ margin:"14px 0" }} />
              {tabs[tab].pts.map((p, i) => (
                <div key={i} style={{ display:"flex", gap:10, marginBottom:12, alignItems:"flex-start" }}>
                  <div style={{ width:20, height:20, borderRadius:6, background:C.tealLight, border:`1px solid ${C.tealBorder}`, display:"flex", alignItems:"center", justifyContent:"center", flexShrink:0, marginTop:2 }}>
                    <Ic n="check" sz={11} col={C.teal} />
                  </div>
                  <Body muted style={{ fontSize:14.5, lineHeight:1.6 }}>{p}</Body>
                </div>
              ))}
              <div style={{ marginTop:22 }}>
                <Btn v="outline" onClick={() => nav("appointments")}>Explore Features</Btn>
              </div>
            </div>
            {md && <DashMock compact />}
          </div>
        </div>
      </section>

      {/* ANALYTICS DARK SECTION */}
      <section style={{ ...sec, background:C.teal }}>
        <div style={{ ...px }}>
          <div style={{ textAlign:"center", marginBottom:44 }}>
            <Label center light>Complete visibility</Label>
            <H2 light center style={{ maxWidth:580, margin:"12px auto 14px" }}>Your website and business — both fully tracked, automatically</H2>
            <Body light center style={{ maxWidth:500, margin:"0 auto" }}>Unlike standalone booking tools, EasyGrox tracks both the management data and the website data — in one dashboard.</Body>
          </div>
          <div style={{ display:"grid", gridTemplateColumns:sm ? "1fr" : "1fr 1fr", gap:16, marginBottom:32 }}>
            {[
              { title:"Business Analytics", ico:"trend",  items:[{ico:"pos",l:"Revenue",v:"₹1,24,800/mo"},{ico:"calendar",l:"Appointments",v:"312"},{ico:"user",l:"Retention",v:"74%"},{ico:"users",l:"Staff Utilisation",v:"88%"}] },
              { title:"Website Analytics",  ico:"globe",  items:[{ico:"globe",l:"Visitors",v:"2,480/mo"},{ico:"bar",l:"Top Source",v:"Instagram 42%"},{ico:"target",l:"Conversion",v:"18.3%"},{ico:"calendar",l:"Online Bookings",v:"314"}] },
            ].map((col, ci) => (
              <div key={ci} style={{ background:"rgba(255,255,255,0.12)", borderRadius:R, padding:22, border:"1px solid rgba(255,255,255,0.18)" }}>
                <div style={{ display:"flex", alignItems:"center", gap:7, marginBottom:16 }}>
                  <Ic n={col.ico} sz={14} col="rgba(255,255,255,0.6)" />
                  <div style={{ fontFamily:F, fontSize:10, fontWeight:700, color:"rgba(255,255,255,0.5)", letterSpacing:"0.08em", textTransform:"uppercase" }}>{col.title}</div>
                </div>
                <div style={{ display:"grid", gridTemplateColumns:"1fr 1fr", gap:9 }}>
                  {col.items.map((m, mi) => (
                    <div key={mi} style={{ background:"rgba(255,255,255,0.12)", borderRadius:R, padding:13 }}>
                      <Ic n={m.ico} sz={14} col="rgba(255,255,255,0.5)" />
                      <div style={{ fontFamily:F, color:"#fff", fontSize:17, fontWeight:800, marginTop:6 }}>{m.v}</div>
                      <div style={{ fontFamily:F, color:"rgba(255,255,255,0.45)", fontSize:10.5, marginTop:2 }}>{m.l}</div>
                    </div>
                  ))}
                </div>
              </div>
            ))}
          </div>
          <div style={{ textAlign:"center" }}>
            <Btn v="whiteSolid" onClick={() => nav("analytics")}>Explore Analytics</Btn>
          </div>
        </div>
      </section>

      {/* TESTIMONIALS */}
      <section style={{ ...sec, background:C.low }}>
        <div style={{ ...px }}>
          <div style={{ textAlign:"center", marginBottom:40 }}>
            <Label center>From real salon owners</Label>
            <H2 center>Trusted across India</H2>
          </div>
          <div style={{ display:"grid", gridTemplateColumns:sm ? "1fr" : md ? "1fr 1fr" : "1fr 1fr 1fr", gap:16 }}>
            {testimonials.map((t, i) => (
              <Card key={i} tealTop style={{ padding:"26px 22px" }}>
                <div style={{ display:"flex", gap:1, marginBottom:14 }}>
                  {[1,2,3,4,5].map(s => <Ic key={s} n="star" sz={13} col={C.teal} />)}
                </div>
                <Body style={{ fontSize:14.5, lineHeight:1.78, marginBottom:20, fontStyle:"italic", color:C.inkVar }}>"{t.q}"</Body>
                <Divider style={{ marginBottom:16 }} />
                <div style={{ fontFamily:F, fontSize:13.5, fontWeight:700, color:C.ink }}>{t.n}</div>
                <div style={{ fontFamily:F, fontSize:12.5, color:C.inkVar, marginTop:2 }}>{t.r}</div>
              </Card>
            ))}
          </div>
        </div>
      </section>

      {/* PRICING */}
      <section style={{ ...sec, background:C.bg }}>
        <div style={{ ...px }}>
          <div style={{ textAlign:"center", marginBottom:40 }}>
            <Label center>No hidden fees</Label>
            <H2 center>Simple pricing. Free website on every plan.</H2>
          </div>
          {/* Toggle */}
          <div style={{ display:"flex", justifyContent:"center", marginBottom:36 }}>
            <div style={{ display:"inline-flex", alignItems:"center", gap:12, background:C.surface, border:`1px solid ${C.outlineVar}`, borderRadius:9999, padding:"6px 6px 6px 18px", boxShadow:C.sh1 }}>
              <span style={{ fontFamily:F, fontSize:13.5, fontWeight:annual ? 400 : 600, color:annual ? C.inkVar : C.ink }}>Monthly</span>
              <div onClick={() => setAnnual(!annual)} style={{ width:44, height:24, borderRadius:9999, background:annual ? C.teal : C.outlineVar, position:"relative", cursor:"pointer", transition:"background .2s" }}>
                <div style={{ width:18, height:18, borderRadius:"50%", background:"#fff", position:"absolute", top:3, left:annual ? 23 : 3, transition:"left .2s", boxShadow:"0 1px 4px rgba(0,0,0,0.15)" }} />
              </div>
              <span style={{ fontFamily:F, fontSize:13.5, fontWeight:annual ? 600 : 400, color:annual ? C.ink : C.inkVar }}>Annual</span>
              {annual && <Chip teal sm>Save 2 months</Chip>}
            </div>
          </div>
          <div style={{ display:"grid", gridTemplateColumns:sm ? "1fr" : md ? "1fr 1fr" : "1fr 1fr 1fr", gap:16 }}>
            {plans.map((p, i) => (
              <div key={i} style={{ background:p.hi ? C.teal : C.surface, borderRadius:R, padding:"30px 24px", border:`1px solid ${p.hi ? C.teal : C.outlineVar}`, boxShadow:p.hi ? "0 16px 40px rgba(0,101,101,0.28)" : C.sh1, transform:p.hi && !sm ? "scale(1.03)" : "none", position:"relative" }}>
                {p.hi && <div style={{ position:"absolute", top:-13, left:"50%", transform:"translateX(-50%)" }}><span style={{ background:C.surface, color:C.teal, fontFamily:F, fontSize:10, fontWeight:700, padding:"3px 14px", borderRadius:9999, border:`1px solid ${C.tealBorder}`, whiteSpace:"nowrap", textTransform:"uppercase", letterSpacing:"0.06em" }}>Most Popular</span></div>}
                <div style={{ fontFamily:F, fontSize:10, fontWeight:700, letterSpacing:"0.08em", textTransform:"uppercase", color:p.hi ? "rgba(255,255,255,0.4)" : C.inkVar, marginBottom:4 }}>{p.plan}</div>
                <div style={{ fontFamily:F, fontSize:13, color:p.hi ? "rgba(255,255,255,0.5)" : C.inkVar, marginBottom:18 }}>{p.for}</div>
                <div style={{ display:"flex", alignItems:"baseline", gap:4, marginBottom:20 }}>
                  <span style={{ fontFamily:F, fontSize:34, fontWeight:800, color:p.hi ? "#fff" : C.ink }}>{annual ? p.apr : p.price}</span>
                  <span style={{ fontFamily:F, fontSize:13, color:p.hi ? "rgba(255,255,255,0.4)" : C.inkVar }}>/mo</span>
                </div>
                <Divider style={{ background:p.hi ? "rgba(255,255,255,0.15)" : undefined, marginBottom:20 }} />
                {p.feats.map((f, fi) => (
                  <div key={fi} style={{ display:"flex", gap:9, marginBottom:11, alignItems:"flex-start" }}>
                    <Ic n="check" sz={13} col={p.hi ? "rgba(255,255,255,0.6)" : C.teal} style={{ marginTop:2, flexShrink:0 }} />
                    <span style={{ fontFamily:F, fontSize:13.5, color:p.hi ? "rgba(255,255,255,0.8)" : C.inkVar }}>{f}</span>
                  </div>
                ))}
                <button onClick={() => nav("signup")} style={{ width:"100%", height:46, marginTop:20, borderRadius:R, border:`1px solid ${p.hi ? "rgba(255,255,255,0.2)" : C.teal}`, background:p.hi ? "rgba(255,255,255,0.12)" : C.teal, color:"#fff", fontWeight:600, fontSize:14, cursor:"pointer", fontFamily:F, transition:"all .18s" }}
                  onMouseEnter={e => e.currentTarget.style.opacity = "0.85"}
                  onMouseLeave={e => e.currentTarget.style.opacity = "1"}>
                  {p.plan === "Multi-Location" ? "Contact Us" : "Start Free"}
                </button>
              </div>
            ))}
          </div>
          <div style={{ textAlign:"center", marginTop:24 }}>
            <button onClick={() => nav("pricing")} style={{ background:"none", border:"none", color:C.teal, fontFamily:F, fontSize:13.5, fontWeight:600, cursor:"pointer", textDecoration:"underline" }}>
              See full pricing and feature comparison
            </button>
          </div>
        </div>
      </section>

      {/* FAQ */}
      <section style={{ ...sec, background:C.low }}>
        <div style={{ ...px }}>
          <div style={{ display:"grid", gridTemplateColumns:md ? "1fr" : "1fr 1.2fr", gap:md ? 36 : 72, alignItems:"start" }}>
            <div>
              <Label>Common questions</Label>
              <H2 style={{ marginTop:10, marginBottom:18 }}>Everything you need to know</H2>
              <Body muted style={{ marginBottom:24 }}>Our team responds within 24 hours. No bots.</Body>
              <Btn v="outline" onClick={() => nav("contact")}>Talk to Us</Btn>
            </div>
            <div>
              {faqs.map((f, i) => (
                <div key={i} style={{ borderBottom:`1px solid ${C.outlineVar}` }}>
                  <button onClick={() => setOpenFaq(openFaq === i ? null : i)} style={{ width:"100%", padding:"16px 0", background:"none", border:"none", cursor:"pointer", display:"flex", justifyContent:"space-between", alignItems:"center", gap:14, fontFamily:F, textAlign:"left" }}>
                    <span style={{ fontSize:15, fontWeight:600, color:C.ink }}>{f.q}</span>
                    <Ic n={openFaq === i ? "minus" : "plus"} sz={14} col={C.teal} />
                  </button>
                  {openFaq === i && <div style={{ paddingBottom:16, fontFamily:F, fontSize:14.5, color:C.inkVar, lineHeight:1.75 }}>{f.a}</div>}
                </div>
              ))}
            </div>
          </div>
        </div>
      </section>

      {/* CTA */}
      <section style={{ ...sec, background:C.teal }}>
        <div style={{ ...px, textAlign:"center" }}>
          <Label center light>The calm alternative to chaos</Label>
          <H2 light center style={{ maxWidth:540, margin:"12px auto 16px" }}>Your salon, running like it should</H2>
          <Body light center style={{ maxWidth:420, margin:"0 auto 32px" }}>Sign up free. Set up your store. Publish your free website. Manage smarter — in under 30 minutes.</Body>
          <div style={{ display:"flex", gap:12, justifyContent:"center", flexWrap:"wrap" }}>
            <Btn onClick={() => nav("signup")}>Start for Free</Btn>
            <Btn v="white">Watch Demo</Btn>
          </div>
          <p style={{ fontFamily:F, color:"rgba(255,255,255,0.32)", fontSize:12, marginTop:20 }}>No credit card · Free website · Cancel anytime</p>
        </div>
      </section>
    </>
  );
}
