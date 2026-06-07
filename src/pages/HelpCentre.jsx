import React, { useState } from "react";
import { C, F, R } from "../constants/theme";
import { useW } from "../hooks/useWindowWidth";
import { Ic } from "../components/icons/Icon";
import { Chip, Label, H1, H2, H3, H4, Body, Btn, Card, Divider, IcBox } from "../components/ui";

export function HelpCentre({ nav }) {
  const { sm, md } = useW();
  const px = { padding:`0 ${sm ? 16 : 24}px`, maxWidth:1200, margin:"0 auto" };
  const SP = sm ? "52px 0" : "80px 0";
  const [activeCategory, setActiveCategory] = useState(0);
  const [search, setSearch] = useState("");

  const categories = [
    {
      icon:"zap",     label:"Getting Started",
      articles:[
        {t:"How to create your EasyGrox account",          d:"Step-by-step walkthrough from the signup page to your first login.",        time:"2 min read"},
        {t:"Setting up your store for the first time",   d:"Adding business details, logo, address, and choosing your business category.", time:"4 min read"},
        {t:"Adding your services and pricing",           d:"Create your full service menu with prices, durations, and categories.",       time:"3 min read"},
        {t:"Creating your first staff profile",          d:"Add a team member, set their services, and configure their access level.",   time:"3 min read"},
        {t:"Publishing your free booking website",       d:"Preview your website, set your custom URL, and go live in under 1 minute.", time:"2 min read"},
      ],
    },
    {
      icon:"calendar",label:"Appointments & Calendar",
      articles:[
        {t:"How online bookings appear in your calendar", d:"What happens the moment a client books through your website.",              time:"2 min read"},
        {t:"Adding a walk-in appointment",                d:"How to log a walk-in in 10 seconds and assign a staff member.",            time:"1 min read"},
        {t:"Rescheduling or cancelling a booking",        d:"Move or cancel any appointment and automatically notify the client.",      time:"2 min read"},
        {t:"Setting buffer time between appointments",    d:"Configure automatic gaps between sessions per service or per staff.",      time:"2 min read"},
        {t:"Understanding appointment status labels",     d:"Confirmed, Active, Completed, No-Show — what each status means.",         time:"2 min read"},
      ],
    },
    {
      icon:"pos",     label:"POS & Billing",
      articles:[
        {t:"How to bill a client after a service",       d:"Open the billing screen, confirm services, select payment, collect.",      time:"2 min read"},
        {t:"Accepting UPI, card, and cash payments",     d:"All payment types available in the POS and how each is tracked.",          time:"2 min read"},
        {t:"Adding retail products to a service bill",   d:"Sell a shampoo alongside a service in one transaction.",                   time:"2 min read"},
        {t:"Creating and applying discount codes",        d:"Set up percentage or fixed discounts and apply them at checkout.",         time:"3 min read"},
        {t:"Sending a digital receipt to a client",      d:"Send via WhatsApp, SMS, or email — instantly after payment.",              time:"1 min read"},
      ],
    },
    {
      icon:"users",   label:"Staff Management",
      articles:[
        {t:"Adding a new staff member",                  d:"Create a profile, assign services, and set their access level.",           time:"3 min read"},
        {t:"Understanding role-based access levels",     d:"Owner, Front Desk, and Staff — what each role can see and do.",            time:"3 min read"},
        {t:"Setting up shift schedules",                 d:"Configure weekly working hours per staff member.",                         time:"3 min read"},
        {t:"Approving a leave request",                  d:"Review and approve leave — calendar blocks automatically.",                time:"2 min read"},
        {t:"Viewing staff performance reports",          d:"Revenue, appointments, utilisation, and rating per team member.",          time:"2 min read"},
      ],
    },
    {
      icon:"bag",     label:"Retail & Inventory",
      articles:[
        {t:"Adding products to your inventory",          d:"Create product entries with price, cost, category, and stock level.",      time:"3 min read"},
        {t:"Setting minimum stock levels and alerts",    d:"Configure when low-stock alerts fire for each product.",                   time:"2 min read"},
        {t:"Tracking retail sales by staff member",      d:"See which team member sells the most retail each month.",                 time:"2 min read"},
        {t:"Adjusting stock manually",                   d:"Log stock arrivals, breakage, or returns with a reason and timestamp.",   time:"2 min read"},
        {t:"Reading your retail analytics",              d:"Best-sellers, margin, and retail revenue as % of total business.",        time:"2 min read"},
      ],
    },
    {
      icon:"globe",   label:"Booking Website",
      articles:[
        {t:"Understanding your free booking website",   d:"What is included, how it works, and what clients experience.",             time:"3 min read"},
        {t:"Customising your website URL",              d:"Set yoursalon.easygrox.com or connect your own domain.",                     time:"2 min read"},
        {t:"Adding photos and business description",    d:"Upload images and write your business profile from inside the app.",       time:"3 min read"},
        {t:"Reading your website analytics",            d:"Visitors, traffic sources, most-viewed pages, and booking conversion.",   time:"3 min read"},
        {t:"Sharing your booking link effectively",     d:"Instagram bio, WhatsApp status, Google Business, and QR code.",           time:"2 min read"},
      ],
    },
    {
      icon:"bar",     label:"Reports & Analytics",
      articles:[
        {t:"Your morning analytics routine",            d:"What to check every morning and what each number means.",                  time:"4 min read"},
        {t:"Reading revenue reports",                   d:"Daily, weekly, monthly revenue — by service, staff, and category.",       time:"3 min read"},
        {t:"Understanding your no-show rate",           d:"How it is calculated, what a healthy rate is, and how to reduce it.",     time:"3 min read"},
        {t:"Client retention and at-risk clients",      d:"How EasyGrox flags clients who are overdue and how to re-engage them.",     time:"3 min read"},
        {t:"Staff utilisation rate explained",          d:"What utilisation rate means and how to use it for scheduling decisions.", time:"3 min read"},
      ],
    },
    {
      icon:"tag",     label:"Marketing",
      articles:[
        {t:"Sending your first SMS campaign",           d:"Choose a segment, write a message, send — and see the results.",          time:"4 min read"},
        {t:"Setting up birthday automation",            d:"Configure once — personalised birthday offers sent automatically forever.", time:"3 min read"},
        {t:"Re-engagement campaigns for lapsed clients",d:"How to automatically reach clients who have not visited in 30+ days.",    time:"3 min read"},
        {t:"Reading campaign analytics",                d:"Delivery rate, bookings generated, and revenue attributed per campaign.", time:"2 min read"},
        {t:"Targeting a specific client segment",       d:"Filter by visit frequency, spend level, last service, and more.",        time:"3 min read"},
      ],
    },
  ];

  const filtered = search.trim().length > 0
    ? categories.map(c => ({ ...c, articles:c.articles.filter(a => a.t.toLowerCase().includes(search.toLowerCase()) || a.d.toLowerCase().includes(search.toLowerCase())) })).filter(c => c.articles.length > 0)
    : categories;

  return (
    <>
      {/* ── HERO ── */}
      <section style={{ padding:sm ? "52px 0 36px" : "80px 0 56px", background:C.teal }}>
        <div style={{ ...px, textAlign:"center" }}>
          <Label center light>Help Centre</Label>
          <H1 style={{ color:"#fff", marginBottom:16, marginTop:10 }}>How can we help you today?</H1>
          <Body light center style={{ fontSize:sm ? 15 : 17, marginBottom:32, maxWidth:480, margin:"0 auto 32px" }}>Search articles, browse by category, or contact our team. We respond within 24 hours — no bots.</Body>
          {/* Search */}
          <div style={{ maxWidth:560, margin:"0 auto", position:"relative" }}>
            <div style={{ position:"absolute", left:16, top:"50%", transform:"translateY(-50%)", pointerEvents:"none" }}>
              <Ic n="search" sz={18} col={C.inkSoft} />
            </div>
            <input value={search} onChange={e => setSearch(e.target.value)} placeholder="Search help articles — e.g. walk-in, billing, staff schedule..."
              style={{ width:"100%", height:52, borderRadius:R, border:`2px solid ${C.tealBorder}`, padding:"0 16px 0 46px", fontSize:15, fontFamily:F, outline:"none", boxSizing:"border-box", boxShadow:"0 4px 20px rgba(0,0,0,0.15)" }}
              onFocus={e => e.target.style.border = `2px solid ${C.teal}`}
              onBlur={e => e.target.style.border = `2px solid ${C.tealBorder}`} />
          </div>
          <div style={{ display:"flex", gap:8, justifyContent:"center", flexWrap:"wrap", marginTop:16 }}>
            {["Walk-in","No-shows","Billing","Staff schedule","Website","Reminders"].map((tag, i) => (
              <button key={i} onClick={() => setSearch(tag)} style={{ background:"rgba(255,255,255,0.15)", border:"1px solid rgba(255,255,255,0.3)", borderRadius:9999, padding:"5px 14px", fontSize:12.5, color:"rgba(255,255,255,0.85)", fontFamily:F, cursor:"pointer", fontWeight:500, transition:"all .15s" }}
                onMouseEnter={e => e.currentTarget.style.background = "rgba(255,255,255,0.25)"}
                onMouseLeave={e => e.currentTarget.style.background = "rgba(255,255,255,0.15)"}>
                {tag}
              </button>
            ))}
          </div>
        </div>
      </section>

      {/* ── CATEGORIES + ARTICLES ── */}
      <section style={{ padding:SP, background:C.bg }}>
        <div style={{ ...px }}>
          {search.trim().length === 0 && (
            <>
              {/* Category tabs */}
              <div style={{ display:"flex", gap:8, flexWrap:"wrap", marginBottom:36, justifyContent:sm ? "flex-start" : "center" }}>
                {categories.map((c, i) => (
                  <button key={i} onClick={() => setActiveCategory(i)}
                    style={{ display:"flex", alignItems:"center", gap:6, padding:"8px 16px", borderRadius:9999, border:`1.5px solid ${activeCategory===i ? C.teal : C.outlineVar}`, background:activeCategory===i ? C.teal : C.surface, color:activeCategory===i ? "#fff" : C.inkVar, fontWeight:600, fontSize:sm ? 12.5 : 13, cursor:"pointer", fontFamily:F, transition:"all .18s", whiteSpace:"nowrap" }}>
                    <Ic n={c.icon} sz={14} col={activeCategory===i ? "#fff" : C.inkVar} />
                    {c.label}
                  </button>
                ))}
              </div>
              {/* Active category articles */}
              <div style={{ display:"grid", gridTemplateColumns:sm ? "1fr" : "repeat(2,1fr)", gap:14 }}>
                {categories[activeCategory].articles.map((a, i) => (
                  <div key={i} style={{ background:C.surface, borderRadius:R, padding:"20px 20px", border:`1px solid ${C.outlineVar}`, boxShadow:C.sh1, cursor:"pointer", transition:"all .2s", display:"flex", gap:14, alignItems:"flex-start" }}
                    onMouseEnter={e => { e.currentTarget.style.borderColor = C.teal; e.currentTarget.style.transform = "translateY(-2px)"; e.currentTarget.style.boxShadow = C.sh2; }}
                    onMouseLeave={e => { e.currentTarget.style.borderColor = C.outlineVar; e.currentTarget.style.transform = "none"; e.currentTarget.style.boxShadow = C.sh1; }}>
                    <div style={{ width:36, height:36, borderRadius:9, background:C.tealLight, border:`1px solid ${C.tealBorder}`, display:"flex", alignItems:"center", justifyContent:"center", flexShrink:0 }}>
                      <Ic n={categories[activeCategory].icon} sz={17} col={C.teal} />
                    </div>
                    <div style={{ flex:1 }}>
                      <div style={{ fontFamily:F, fontSize:14.5, fontWeight:700, color:C.ink, marginBottom:5, lineHeight:1.35 }}>{a.t}</div>
                      <div style={{ fontFamily:F, fontSize:13, color:C.inkVar, lineHeight:1.55, marginBottom:8 }}>{a.d}</div>
                      <span style={{ fontFamily:F, fontSize:11.5, color:C.teal, fontWeight:600 }}>{a.time}</span>
                    </div>
                  </div>
                ))}
              </div>
            </>
          )}
          {/* Search results */}
          {search.trim().length > 0 && (
            <>
              <div style={{ marginBottom:24 }}>
                <Body muted>Showing results for <strong style={{ color:C.ink }}>"{search}"</strong></Body>
              </div>
              {filtered.length === 0 ? (
                <div style={{ textAlign:"center", padding:"48px 20px" }}>
                  <div style={{ width:52, height:52, borderRadius:"50%", background:C.low, display:"flex", alignItems:"center", justifyContent:"center", margin:"0 auto 16px" }}>
                    <Ic n="search" sz={22} col={C.inkSoft} />
                  </div>
                  <H3 style={{ marginBottom:10, color:C.inkVar }}>No articles found</H3>
                  <Body muted style={{ marginBottom:20 }}>Try a different search term, or contact our support team directly.</Body>
                  <Btn v="outline" onClick={() => nav("contact")}>Contact Support</Btn>
                </div>
              ) : filtered.map((cat, ci) => (
                <div key={ci} style={{ marginBottom:32 }}>
                  <div style={{ display:"flex", alignItems:"center", gap:8, marginBottom:14 }}>
                    <Ic n={cat.icon} sz={16} col={C.teal} />
                    <span style={{ fontFamily:F, fontSize:13, fontWeight:700, color:C.teal, textTransform:"uppercase", letterSpacing:"0.06em" }}>{cat.label}</span>
                  </div>
                  <div style={{ display:"grid", gridTemplateColumns:sm ? "1fr" : "repeat(2,1fr)", gap:12 }}>
                    {cat.articles.map((a, ai) => (
                      <div key={ai} style={{ background:C.surface, borderRadius:R, padding:"18px 20px", border:`1px solid ${C.outlineVar}`, boxShadow:C.sh1, cursor:"pointer" }}>
                        <div style={{ fontFamily:F, fontSize:14.5, fontWeight:700, color:C.ink, marginBottom:5 }}>{a.t}</div>
                        <div style={{ fontFamily:F, fontSize:13, color:C.inkVar, lineHeight:1.55, marginBottom:6 }}>{a.d}</div>
                        <span style={{ fontFamily:F, fontSize:11.5, color:C.teal, fontWeight:600 }}>{a.time}</span>
                      </div>
                    ))}
                  </div>
                </div>
              ))}
            </>
          )}
        </div>
      </section>

      {/* ── CONTACT STRIP ── */}
      <section style={{ padding:sm ? "44px 0" : "64px 0", background:C.low }}>
        <div style={{ ...px }}>
          <div style={{ display:"grid", gridTemplateColumns:sm ? "1fr" : "repeat(3,1fr)", gap:14 }}>
            {[
              {icon:"mail",  t:"Email Support",    d:"support@easygrox.com · We respond within 24 hours on business days."},
              {icon:"phone", t:"WhatsApp Help",    d:"Chat with our setup support team directly on WhatsApp."},
              {icon:"users", t:"Onboarding Help",  d:"Book a free 20-minute guided setup call with our team."},
            ].map((c, i) => (
              <div key={i} style={{ background:C.surface, borderRadius:R, padding:"24px 20px", border:`1px solid ${C.outlineVar}`, boxShadow:C.sh1, display:"flex", gap:14, alignItems:"flex-start" }}>
                <div style={{ width:42, height:42, borderRadius:R, background:C.tealLight, border:`1px solid ${C.tealBorder}`, display:"flex", alignItems:"center", justifyContent:"center", flexShrink:0 }}>
                  <Ic n={c.icon} sz={19} col={C.teal} />
                </div>
                <div>
                  <div style={{ fontFamily:F, fontSize:15, fontWeight:700, color:C.ink, marginBottom:5 }}>{c.t}</div>
                  <div style={{ fontFamily:F, fontSize:13.5, color:C.inkVar, lineHeight:1.6 }}>{c.d}</div>
                </div>
              </div>
            ))}
          </div>
        </div>
      </section>
    </>
  );
}

/* ─── GETTING STARTED PAGE ───────────────────────────────── */
